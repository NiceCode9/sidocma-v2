<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return view('folders.index')->render();
    }

    public function browse(Request $request, Folder $folder = null)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        // Check permission if accessing specific folder
        if ($folder && !$this->permissionService->canAccessFolder($user, $folder)) {
            return response()->json(['error' => 'Tidak memiliki akses ke folder ini'], 403);
        }

        // Get all accessible folders first
        $foldersQuery = $folder ? $folder->children() : Folder::rootFolders();
        $allFolders = $foldersQuery
            ->with(['creator', 'unit'])
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
                'unit' => $folder->unit ? $folder->unit->name : null,
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
                'name' => $document->name,
                'original_name' => $document->original_name,
                'file_size' => $document->file_size,
                'extension' => $document->extension,
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
}
