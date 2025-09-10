<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Str;

class FolderService
{
    public function createFolder(array $data, User $creator)
    {
        $folder = new Folder();
        $folder->name = $data['name'];
        $folder->slug = Str::slug($data['name']);
        $folder->description = $data['description'];
        $folder->parent_id = $data['parent_id'];
        $folder->created_by = $creator->id;
        $folder->updated_by = $creator->id;

        // Set default path dan level untuk menghindari NOT NULL constraint
        if ($folder->parent_id) {
            $parent = Folder::find($folder->parent_id);
            $folder->level = $parent ? $parent->level + 1 : 0;
            // Temporary path yang akan diupdate setelah save
            $folder->path = $parent ? $parent->path . '/temp' : '/temp';
        } else {
            $folder->level = 0;
            $folder->path = '/temp';
        }

        $folder->save();
        $folder->updatePath();

        return $folder;
    }

    public function deleteFolder(Folder $folder): bool
    {
        // Soft delete folder (ubah is_active menjadi false)
        $folder->update(['is_active' => false]);

        // Soft delete semua children folders
        $this->deactivateChildren($folder);

        // Soft delete semua documents dalam folder
        $folder->documents()->update(['is_active' => false]);

        return true;
    }

    private function deactivateChildren(Folder $folder): void
    {
        $children = $folder->children;
        foreach ($children as $child) {
            $child->update(['is_active' => false]);
            $child->documents()->update(['is_active' => false]);
            $this->deactivateChildren($child);
        }
    }
}
