<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use App\Models\Folder;
use App\Models\FolderPermission;
use App\Models\Unit;
use App\Models\User;
use App\Services\FolderService;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class FolderController extends Controller
{
    protected $folderService;
    protected $permissionService;

    public function __construct(FolderService $folderService, PermissionService $permissionService)
    {
        $this->folderService = $folderService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $categories = DocumentCategory::all();
        return view('folders.index', compact('categories'));
    }

    public function browse(Request $request, Folder $folder = null)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        // Check permission if accessing specific folder
        if ($folder && !$this->permissionService->canAccessFolder($user, $folder)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke folder ini',
                'folders' => [],
                'documents' => [],
                'breadcrumb' => [],
                'current_folder' => null,
                'has_more' => false,
                'total_items' => 0,
                'loaded_items' => 0,
                'offset' => $offset,
                'limit' => $limit
            ], 200); // Kembalikan HTTP 200 agar ditangani oleh .done()
        }

        // Get all accessible folders first
        $foldersQuery = $folder ? $folder->children() : Folder::rootFolders();
        $allFolders = $foldersQuery
            ->with(['creator'])
            ->active()
            ->get()
            ->filter(function ($folder) use ($user) {
                return $this->permissionService->canAccessFolder($user, $folder);
            });

        // Get all documents if in specific folder
        $allDocuments = collect([]);
        if ($folder) {
            $allDocuments = $folder->documents()->active()->get();
        }

        // Combine folders and documents
        $allItems = collect();

        // Add folders first
        $allFolders->each(function ($folder) use ($allItems) {
            $allItems->push([
                'id' => $folder->id,
                'name' => $folder->name,
                'description' => $folder->description,
                // 'unit' => $folder->unit ? $folder->unit->name : null,
                'creator' => $folder->creator->name,
                'created_at' => $folder->created_at->format('d M Y'),
                'documents_count' => $folder->documents()->active()->count(),
                'subfolders_count' => $folder->children()->active()->count(),
                'total_size' => $folder->documents()->active()->sum('file_size'),
                'type' => 'folder',
                'sort_order' => 1 // Folders first
            ]);
        });

        // Add documents
        $allDocuments->each(function ($document) use ($allItems) {
            $allItems->push([
                'id' => $document->id,
                'name' => $document->title,
                'original_name' => $document->file_name,
                'file_size' => $document->file_size,
                'extension' => $document->file_extension,
                'created_at' => $document->created_at->format('d M Y'),
                'mime_type' => $document->mime_type,
                'type' => 'document',
                'sort_order' => 2 // Documents after folders
            ]);
        });

        // Sort by type (folders first) then by name
        $allItems = $allItems->sortBy([
            ['sort_order', 'asc'],
            ['name', 'asc']
        ]);

        // Apply offset and limit
        $items = $allItems->skip($offset)->take($limit);
        $hasMore = $allItems->count() > ($offset + $limit);

        // Separate folders and documents for response
        $folders = $items->where('type', 'folder')->values();
        $documents = $items->where('type', 'document')->values();

        // Get breadcrumb
        $breadcrumb = [];
        if ($folder) {
            $breadcrumb = $folder->getBreadcrumb()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name
                ];
            });
        }

        return response()->json([
            'success' => true,
            'folders' => $folders,
            'documents' => $documents,
            'breadcrumb' => $breadcrumb,
            'current_folder' => $folder ? [
                'id' => $folder->id,
                'name' => $folder->name,
                'description' => $folder->description
            ] : null,
            'has_more' => $hasMore,
            'total_items' => $allItems->count(),
            'loaded_items' => $offset + $items->count(),
            'offset' => $offset,
            'limit' => $limit
        ]);
    }

    public function getFolderInfo(string $id)
    {
        $folder = Folder::find($id);
        if ($folder && !$this->permissionService->canAccessFolder(Auth::user(), $folder)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke folder ini',
            ], 200); // Kembalikan HTTP 200 agar ditangani oleh .done()
        }

        $folderInfo = [
            'name' => $folder->name,
            'description' => $folder->description,
            'created_at' => Carbon::parse($folder->created_at)->locale('id')->format('d M Y'),
            'subfolders_count' => $folder->children()->active()->count(),
            'document_count' => $folder->documents()->active()->count(),
        ];

        return response()->json([
            'success' => true,
            'folder' => $folderInfo,
        ]);
    }

    /**
     * Create new folder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:folders,id',
            'unit_id' => 'nullable|exists:units,id',
            'units' => 'nullable|array',
            'units.*' => 'nullable|exists:units,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|exists:roles,id',
            'permission_type' => 'nullable|in:read,write,delete'
        ]);

        $user = Auth::user();

        // Check permission to create folder
        $parentFolder = $request->parent_id ? Folder::find($request->parent_id) : null;
        if (!$this->permissionService->canCreateFolder($user, $parentFolder)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk membuat folder'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Create folder
            $folder = $this->folderService->createFolder([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'unit_id' => $request->unit_id ?? $user->unit_id
            ], $user);

            // Set permissions if provided
            if ($request->has('units') || $request->has('roles') || $request->has('users')) {
                $this->permissionService->setFolderPermissions($folder, [
                    'units' => $request->input('units', []),
                    'roles' => $request->input('roles', []),
                    'users' => $request->input('users', []),
                    'permission_types' => $request->permission_types ?? ['read']
                ], $user);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dibuat',
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'creator' => $folder->creator->name,
                    'created_at' => $folder->created_at->format('d M Y'),
                    'documents_count' => 0,
                    'subfolders_count' => 0,
                    'total_size' => 0,
                    'type' => 'folder'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat folder: ' . $e->getMessage()
            ], 422);
        }
    }


    /**
     * Delete folder
     */
    public function destroy(Folder $folder)
    {
        $user = Auth::user();

        if (!$this->permissionService->canAccessFolder($user, $folder, 'delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk menghapus folder'
            ], 403);
        }

        try {
            DB::beginTransaction();
            $this->folderService->deleteFolder($folder);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus folder: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Search folders and documents
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $user = Auth::user();
        $accessibleFolders = $this->permissionService->getAccessibleFolders($user);

        if (!$query) {
            return response()->json(['folders' => [], 'documents' => []]);
        }

        // Search folders
        $folders = $accessibleFolders
            ->filter(function ($folder) use ($query) {
                return stripos($folder->name, $query) !== false;
            })
            ->take(10)
            ->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'type' => 'folder',
                    'path' => $folder->getBreadcrumb()->pluck('name')->implode('/')
                ];
            });

        // Search documents
        $documents = $this->permissionService->getAccessibleDocuments($user)
            ->filter(function ($document) use ($query) {
                return stripos($document->title, $query) !== false ||
                    stripos($document->file_name, $query) !== false;
            })
            ->take(10)
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->title,
                    'original_name' => $document->file_name,
                    'type' => 'document',
                    'folder_name' => $document->folder->name
                ];
            });

        return response()->json([
            'folders' => $folders->values(),
            'documents' => $documents->values(),
        ]);
    }


    public function getUnits()
    {
        $units = Unit::select('id', 'name')->get();
        return response()->json($units);
    }

    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }

    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'unit_id')
            ->with('unit:id,name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'display_name' => $user->name . ' (' . ($user->unit ? $user->unit->name : 'No Unit') . ')'
                ];
            });

        return response()->json($users);
    }

    public function getPermissionFolder(Request $request)
    {
        $folderId = $request->input('folder_id');
        $user = Auth::user();

        if (!$this->permissionService->canManagePermissions($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda Tidak memiliki izin untuk mengelola izin folder'
            ], 403);
        }

        $folderPermission = FolderPermission::with(['folder', 'user', 'role', 'unit'])
            ->where('folder_id', $folderId)
            ->get();
        return response()->json([
            'success' => true,
            'data' => $folderPermission
        ]);
    }

    public function setPermissionFolder(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'permission_types' => 'required|array|min:1',
            'permission_types.*' => 'in:read,write,download,delete',
            'unit_id' => 'nullable|array',
            'unit_id.*' => 'exists:units,id',
            'role_id' => 'nullable|array',
            'role_id.*' => 'exists:roles,id',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id',
            'force' => 'nullable|boolean'
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

        $folder = Folder::findOrFail($request->input('folder_id'));
        $force = $request->input('force', false);

        try {
            $result = $this->permissionService->setFolderPermissions($folder, [
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan permission: ' . $e->getMessage()
            ], 422);
        }
    }

    public function deletePermissionFolder(string $id)
    {
        try {
            if (!$this->permissionService->canManagePermissions(auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengelola izin folder'
                ], 403);
            }

            $folderPermission = FolderPermission::find($id);
            $folderPermission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permisson Folder Berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silahkan hubungi Tim IT',
            ]);
        }
    }
}
