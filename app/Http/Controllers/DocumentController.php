<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentPermission;
use App\Models\Folder;
use App\Models\FolderPermission;
use App\Models\User;
use App\Notifications\DocumentNotification;
use App\Services\DocumentPermissionService;
use App\Services\DocumentService;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;

class DocumentController extends Controller
{
    protected $documentService;
    protected $documentPermissionService;
    protected $permissionService;

    public function __construct(DocumentService $documentService, PermissionService $permissionService, DocumentPermissionService $documentPermissionService)
    {
        $this->documentService = $documentService;
        $this->documentPermissionService = $documentPermissionService;
        $this->permissionService = $permissionService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'description' => 'nullable|string',
            'document_number' => 'nullable|string',
            'is_latter' => 'nullable|boolean',
            'category' => 'nullable|exists:document_categories,id',
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,docx,doc,xlsx,xls,ppt,pptx,zip|max:20480',
            'is_letter' => 'boolean',
        ]);

        $folder = Folder::find($request->folder_id);
        $user = Auth::user();

        if (!$this->permissionService->canUploadDocument($user, $folder)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membuat dokumen di folder ini'
            ], 403);
        }

        $files = $request->file('files');
        $description = $request->input('description');
        $documentNumber = $request->input('document_number');
        $isLatter = $request->boolean('is_letter');
        $category = $request->input('category');
        $uplaodedDocuments = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                $document = $this->documentService->uploadDocument(
                    $file,
                    $folder,
                    $user,
                    $description,
                    $isLatter,
                    $documentNumber,
                    $category,
                );

                $uplaodedDocuments[] = $document;

                // Kirim notifikasi ke users yang memiliki akses ke folder
                $this->notifyFolderUsers($document, $folder, $user, 'document_uploaded');
            } catch (\Throwable $th) {
                $errors[] = [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $th->getMessage(),
                ];
            }
        }

        if (empty($uplaodedDocuments)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload semua file, silahkan cek kembali file yang diupload',
                'errors' => $errors,
            ], 500);
        }

        $successCount = count($uplaodedDocuments);
        $totalCount = count($files);
        $message = $successCount === $totalCount
            ? "Berhasil mengupload {$successCount} file"
            : "Berhasil mengupload {$successCount} dari {$totalCount} file";

        if ($successCount < $totalCount) {
            $message = 'Beberapa dokumen gagal diupload, silahkan cek kembali file yang diupload';
        }
        // $errorCount = count($errors);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $uplaodedDocuments,
            'summary' => [
                'total' => $totalCount,
                'success' => $successCount,
                'errors' => $errors,
            ],
        ], 201);
    }

    protected function notifyFolderUsers(Document $document, Folder $folder, User $actor, string $type)
    {
        // Get all users with folder permissions (excluding the actor)
        // Ambil user_ids langsung dari permission
        $directUserIds = FolderPermission::where('folder_id', $folder->id)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        // Ambil user_ids dari unit permissions
        $unitUserIds = User::whereIn('unit_id', function ($query) use ($folder) {
            $query->select('unit_id')
                ->from('folder_permissions')
                ->where('folder_id', $folder->id)
                ->whereNotNull('unit_id');
        })->pluck('id');

        // Gabungkan dan filter
        $userIds = $directUserIds->merge($unitUserIds)
            ->unique()
            ->reject(function ($userId) use ($actor) {
                return $userId == $actor->id;
            });

        $users = User::whereIn('id', $userIds)->get();

        // Send notifications
        Notification::send($users, new DocumentNotification($document, $type, $actor));
    }

    /**
     * Notify users who received document permissions
     */
    protected function notifyPermissionGranted(Document $document, Request $request, User $actor)
    {
        $userIds = $request->input('user_id', []);

        // Get users from roles
        if ($request->has('role_id')) {
            $roleUsers = User::whereHas('roles', function ($query) use ($request) {
                $query->whereIn('id', $request->input('role_id'));
            })->pluck('id');
            $userIds = array_merge($userIds, $roleUsers->toArray());
        }

        // Get users from units
        if ($request->has('unit_id')) {
            $unitUsers = User::whereIn('unit_id', $request->input('unit_id'))->pluck('id');
            $userIds = array_merge($userIds, $unitUsers->toArray());
        }

        // Remove duplicates and actor
        $userIds = array_unique($userIds);
        $userIds = array_diff($userIds, [$actor->id]);

        $users = User::whereIn('id', $userIds)->get();

        Notification::send($users, new DocumentNotification(
            $document,
            'document_permission_granted',
            $actor
        ));
    }


    public function download(Document $document)
    {
        $user = Auth::user();

        if (!$this->permissionService->canAccessDocument($user, $document, 'download')) {
            return response()->json([
                'error' => 'Anda tidak memiliki izin untuk mengunduh dokumen ini'
            ], 403);
        }

        $document->shares->incrementDownload();
        $document->shares->markAsRead();
        $document->shares->save();

        try {
            $filePath = $this->documentService->downloadDocument($document);
            return response()->download($filePath, $document->file_name);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Gagal mendownload file: ' . $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Document $document)
    {
        $user = Auth::user();

        // Check permission
        if (!$this->permissionService->canAccessFolder($user, $document->folder, 'delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk menghapus dokumen ini'
            ], 403);
        }

        try {
            $this->documentService->deleteDocument($document, $user);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getDocumentInfo(Document $document)
    {
        $documentInfo = [
            'name' => $document->title,
            'description' => $document->description,
            // 'created_at' => Carbon::parse($document->created_at)->format('d M Y'),
            'created_at' => $document->created_at,
        ];
        return response()->json([
            'success' => true,
            'document' => $documentInfo,
        ]);
    }

    public function getPermissions(Request $request)
    {
        $documentId = $request->input('document_id');
        $user = Auth::user();

        if (!$this->permissionService->canManagePermissions($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda Tidak memiliki izin untuk mengelola izin folder'
            ], 403);
        }

        $documentPermissions = DocumentPermission::with(['document', 'user', 'role', 'unit'])
            ->where('document_id', $documentId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documentPermissions,
        ]);
    }

    public function setPermissions(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'permission_types' => 'required|array|min:1',
            'permission_types.*' => 'in:read,write,download,delete',
            'unit_id' => 'nullable|array',
            'unit_id.*' => 'exists:units,id',
            'role_id' => 'nullable|array',
            'role_id.*' => 'exists:roles,id',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id',
            'force' => 'nullable',
        ]);

        $user = Auth::user();

        if (!$this->permissionService->canManagePermissions($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengelola izin folder'
            ], 403);
        }

        // Validasi: minimal salah satu dari user, role, atau unit harus dipilih
        if (
            empty($request->input('user_id')) &&
            empty($request->input('role_id')) &&
            empty($request->input('unit_id'))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal pilih salah satu: User, Role, atau Unit'
            ], 422);
        }

        $document = Document::findOrFail($request->input('document_id'));
        $force = $request->input('force', false);

        try {
            $result = $this->documentPermissionService->setDocumentPermissions($document, [
                'units' => $request->input('unit_id', []),
                'users' => $request->input('user_id', []),
                'roles' => $request->input('role_id', []),
                'permission_types' => $request->input('permission_types', [])
            ], $user, $force);

            if ($result['status'] === 'warning') {
                return response()->json([
                    'success' => false,
                    'status' => 'warning',
                    'message' => $result['message'],
                    'existing_permissions' => $result['existing_permissions']
                ], 409);
            }

            if ($result['status'] === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan permission: ' . $e->getMessage()
            ], 422);
        }
    }

    public function removePermission(string $id)
    {
        try {

            $user = Auth::user();
            if (!$this->permissionService->canManagePermissions($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengelola izin folder'
                ], 403);
            }

            $documentPermission = DocumentPermission::findOrFail($id);
            $documentPermission->delete();
            return response()->json([
                'success' => true,
                'message' => 'Document Permission Berhasil di hapus',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silahkan hubungi Tim IT',
            ]);
        }
    }

    public function viewFile(Document $document)
    {
        // $document = Document::find($id);
        $user = Auth::user();
        $filePath = Storage::disk('public')->path($document->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $mimeType = $this->getMimeType($fileExtension);

        $docxHtml = null;
        if (in_array($fileExtension, ['docx', 'doc'])) {
            $docxHtml = $this->convertDocxToHtml($document->id);
        }

        return view('folders.view-document', compact('document', 'fileExtension', 'docxHtml'));
    }

    public function streamFile(Document $document)
    {
        // $surat = Document::find($id);
        $filePath = Storage::disk('public')->path($document->file_path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $mimeType = $this->getMimeType($fileExtension);

        // Set headers to prevent download
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->file_path . '"',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Convert DOCX to HTML
     */
    private function convertDocxToHtml(Document $document)
    {
        // $surat = Surat::find($id);
        $filePath = storage_path('app/public/' . $document->file);
        $cacheFile = storage_path('app/public/docx_cache/' . $document->id . '.html');

        // Check if HTML cache exists and is newer than original file
        if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($filePath)) {
            return file_get_contents($cacheFile);
        }

        try {
            // Create cache directory
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            // Load DOCX file
            $phpWord = IOFactory::load($filePath);

            // Convert to HTML
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

            // Save to cache
            $htmlWriter->save($cacheFile);

            // Read and clean HTML
            $html = file_get_contents($cacheFile);

            // Clean up HTML (remove unwanted styles, scripts)
            $html = $this->cleanDocxHtml($html);

            // Save cleaned HTML
            file_put_contents($cacheFile, $html);

            return $html;
        } catch (\Exception $e) {
            Log::error('DOCX to HTML conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean DOCX HTML output
     */
    private function cleanDocxHtml($html)
    {
        // Remove head section and keep only body content
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Clean up inline styles (optional)
        $html = preg_replace('/style="[^"]*"/i', '', $html);

        // Add custom styling
        $html = '<div class="docx-content" style="
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
    ">' . $html . '</div>';

        return $html;
    }

    /**
     * Serve DOCX as HTML
     */
    public function viewDocxHtml(Document $document)
    {
        // $surat = Surat::find($id);s
        // dd($surat);
        $cacheFile = storage_path('app/public/docx_cache/' . $document->id . '.html');

        if (!file_exists($cacheFile)) {
            $html = $this->convertDocxToHtml($document->id);
            if (!$html) {
                abort(404, 'Tidak dapat mengkonversi dokumen');
            }
        } else {
            $html = file_get_contents($cacheFile);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
