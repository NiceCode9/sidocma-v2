<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Folder;
use App\Models\FolderPermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function canAccessFolder(User $user, Folder $folder, $action = 'read')
    {
        // Folder umum (tanpa unit) bisa diakses semua user
        if (is_null($folder->parent_id) && $action === 'read') {
            return true;
        }

        // Creator folder selalu bisa akses
        if ($folder->created_by === $user->id) return true;

        // Admin dan direktur dapat mengakses semua folder
        if ($user->hasRole(['admin', 'direktur'])) return true;

        // direct permission
        $directPermission = FolderPermission::where('folder_id', $folder->id)
            ->where('user_id', $user->id)
            ->where('permission_type', $action)
            ->exists();

        if ($directPermission) return true;

        // Unit permission
        $unitPermission = FolderPermission::where('folder_id', $folder->id)
            ->where('unit_id', $user->unit_id)
            ->where('permission_type', $action)
            ->exists();

        if ($unitPermission) return true;

        $rolePermission = FolderPermission::where('folder_id', $folder->id)
            ->whereIn('role_id', $user->roles->pluck('id'))
            ->where('permission_type', $action)
            ->exists();

        if ($rolePermission) return true;

        return false;
    }

    public function canCreateFolder(User $user, ?Folder $parentFolder = null): bool
    {
        // Admin dan direktur dapat membuat folder di semua unit
        if ($user->hasRole(['super admin', 'direktur'])) return true;

        // Jika tidak ada parent folder (root level), hanya admin/direktur yang bisa
        if (!$parentFolder) {
            return false;
        }

        // Check permission to write in parent folder
        return $this->canAccessFolder($user, $parentFolder, 'write');
    }

    public function setFolderPermissions(Folder $folder, array $permissions, User $grantor)
    {
        DB::transaction(function () use ($folder, $permissions, $grantor) {
            // Set permissions untuk units
            if (!empty($permissions['units'])) {
                foreach ($permissions['units'] as $unitId) {
                    foreach ($permissions['permission_types'] as $permissionType) {
                        FolderPermission::create([
                            'folder_id' => $folder->id,
                            'unit_id' => $unitId,
                            'permission_type' => $permissionType,
                            'granted_by' => $grantor->id,
                        ]);
                    }
                }
            }

            // Set permissions untuk roles
            if (!empty($permissions['roles'])) {
                foreach ($permissions['roles'] as $roleId) {
                    foreach ($permissions['permission_types'] as $permissionType) {
                        FolderPermission::create([
                            'folder_id' => $folder->id,
                            'role_id' => $roleId,
                            'permission_type' => $permissionType,
                            'granted_by' => $grantor->id,
                        ]);
                    }
                }
            }

            // Set permissions untuk users
            if (!empty($permissions['users'])) {
                foreach ($permissions['users'] as $userId) {
                    foreach ($permissions['permission_types'] as $permissionType) {
                        FolderPermission::create([
                            'folder_id' => $folder->id,
                            'user_id' => $userId,
                            'permission_type' => $permissionType,
                            'granted_by' => $grantor->id,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Get accessible folders untuk user
     */
    public function getAccessibleFolders(User $user): Collection
    {
        if ($user->hasRole(['super admin', 'direktur'])) {
            return Folder::active()->get();
        }
        $unitFolders = Folder::active()
            ->whereHas('permissions', function ($query) use ($user) {
                $query->whereNull('unit_id')
                    ->orWhere('user_id', $user->id)
                    ->orWhere('unit_id', $user->unit_id);
            })
            ->get();

        // Get folders dengan permission khusus
        $permissionFolderIds = FolderPermission::where('user_id', $user->id)
            ->orWhere('unit_id', $user->unit_id)
            ->where('permission_type', 'read')
            ->pluck('folder_id');

        $permissionFolders = Folder::active()
            ->whereIn('id', $permissionFolderIds)
            ->get();

        return $unitFolders->merge($permissionFolders)->unique('id');
    }

    public function getAccessibleDocuments(User $user, ?Folder $folder = null): Collection
    {
        $query = Document::active()->with(['folder', 'uploader', 'category']);

        if ($folder) {
            // Get documents dari folder tertentu
            $query->where('folder_id', $folder->id);
        } else {
            // Get semua accessible documents
            $accessibleFolderIds = $this->getAccessibleFolders($user)->pluck('id');
            $query->whereIn('folder_id', $accessibleFolderIds);
        }

        return $query->get();
    }
}
