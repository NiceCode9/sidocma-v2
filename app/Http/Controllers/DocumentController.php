<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\DocumentService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    protected $documentService;
    protected $permissionService;

    public function __construct(DocumentService $documentService, PermissionService $permissionService)
    {
        $this->documentService = $documentService;
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
            'files.*' => 'required|file|mimes:pdf,docx,doc,xlsx,xls,ppt,pptx,zip|max:10480',
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
        $isLatter = $request->boolean('is_latter');
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
}
