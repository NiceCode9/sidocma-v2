<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentPermission;
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
        // if (is_null($folder->parent_id) && $action === 'read') {
        //     return true;
        // }

        if (!$folder->permissions()->exists() && $action == 'read') return true;

        // Creator folder selalu bisa akses
        if ($folder->created_by === $user->id) return true;

        // Admin dan direktur dapat mengakses semua folder
        if ($user->hasRole(['admin', 'direktur'])) return true;

        // Cek permission berdasarkan kombinasi yang ada di database
        $hasPermission = FolderPermission::where('folder_id', $folder->id)
            ->where('permission_type', $action)
            ->where(function ($query) use ($user) {
                $userRoleIds = $user->roles->pluck('id')->toArray();

                $query->where(function ($q) use ($user, $userRoleIds) {
                    // Case 1: Exact match - user, role, dan unit semuanya match
                    $q->where('user_id', $user->id)
                        ->whereIn('role_id', $userRoleIds)
                        ->where('unit_id', $user->unit_id);
                })
                    ->orWhere(function ($q) use ($user, $userRoleIds) {
                        // Case 2: User + Role match, unit null (berlaku untuk semua unit)
                        $q->where('user_id', $user->id)
                            ->whereIn('role_id', $userRoleIds)
                            ->whereNull('unit_id');
                    })
                    ->orWhere(function ($q) use ($user) {
                        // Case 3: User + Unit match, role null (berlaku untuk semua role)
                        $q->where('user_id', $user->id)
                            ->whereNull('role_id')
                            ->where('unit_id', $user->unit_id);
                    })
                    ->orWhere(function ($q) use ($userRoleIds, $user) {
                        // Case 4: Role + Unit match, user null (berlaku untuk semua user dengan role dan unit ini)
                        $q->whereNull('user_id')
                            ->whereIn('role_id', $userRoleIds)
                            ->where('unit_id', $user->unit_id);
                    })
                    ->orWhere(function ($q) use ($user) {
                        // Case 5: Hanya User match
                        $q->where('user_id', $user->id)
                            ->whereNull('role_id')
                            ->whereNull('unit_id');
                    })
                    ->orWhere(function ($q) use ($userRoleIds) {
                        // Case 6: Hanya Role match (berlaku untuk semua user dengan role ini)
                        $q->whereNull('user_id')
                            ->whereIn('role_id', $userRoleIds)
                            ->whereNull('unit_id');
                    })
                    ->orWhere(function ($q) use ($user) {
                        // Case 7: Hanya Unit match (berlaku untuk semua user di unit ini)
                        $q->whereNull('user_id')
                            ->whereNull('role_id')
                            ->where('unit_id', $user->unit_id);
                    });
            })
            ->exists();

        return $hasPermission;
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

    public function setFolderPermissions(Folder $folder, array $permissions, User $grantor, $force = false)
    {
        // Validasi input
        if (empty($permissions['permission_types']) || !is_array($permissions['permission_types'])) {
            return [
                'status' => 'error',
                'message' => 'Permission types harus diisi',
                'success' => false
            ];
        }

        // Jika tidak force, cek existing permissions
        if (!$force) {
            $existingPermissions = $this->checkExistingPermissions($folder->id, $permissions);

            if (!empty($existingPermissions)) {
                return [
                    'status' => 'warning',
                    'message' => 'Terdapat permission yang sudah ada',
                    'existing_permissions' => $existingPermissions,
                    'success' => false
                ];
            }
        }

        DB::transaction(function () use ($folder, $permissions, $grantor, $force) {
            // Jika force, hapus existing permissions terlebih dahulu
            if ($force) {
                $this->removeExistingPermissions($folder->id, $permissions);
            }

            // Buat semua kombinasi yang mungkin
            $combinations = $this->generateCombinations($permissions);

            // Loop setiap kombinasi dan setiap permission_type
            foreach ($combinations as $combination) {
                foreach ($permissions['permission_types'] as $permissionType) {
                    FolderPermission::create([
                        'folder_id' => $folder->id,
                        'user_id' => $combination['user_id'],
                        'role_id' => $combination['role_id'],
                        'unit_id' => $combination['unit_id'],
                        'permission_type' => $permissionType,
                        'granted_by' => $grantor->id,
                    ]);
                }
            }
        });

        return [
            'status' => 'success',
            'message' => $force ? 'Permission berhasil diperbarui' : 'Permission berhasil disetel',
            'success' => true
        ];
    }

    /**
     * Generate semua kombinasi yang mungkin dari users, roles, dan units
     */
    private function generateCombinations(array $permissions)
    {
        $combinations = [];

        // Pastikan array tidak kosong, beri nilai null jika kosong
        $users = !empty($permissions['users']) ? $permissions['users'] : [null];
        $roles = !empty($permissions['roles']) ? $permissions['roles'] : [null];
        $units = !empty($permissions['units']) ? $permissions['units'] : [null];

        // Generate semua kombinasi cartesian product
        foreach ($users as $userId) {
            foreach ($roles as $roleId) {
                foreach ($units as $unitId) {
                    // Skip jika semua null (tidak ada yang dipilih)
                    if (is_null($userId) && is_null($roleId) && is_null($unitId)) {
                        continue;
                    }

                    $combinations[] = [
                        'user_id' => $userId,
                        'role_id' => $roleId,
                        'unit_id' => $unitId
                    ];
                }
            }
        }

        return $combinations;
    }

    private function checkExistingPermissions($folderId, array $permissions)
    {
        $existingPermissions = [];
        $combinations = $this->generateCombinations($permissions);

        foreach ($combinations as $combination) {
            foreach ($permissions['permission_types'] as $permissionType) {
                $existing = FolderPermission::where('folder_id', $folderId)
                    ->where('user_id', $combination['user_id'])
                    ->where('role_id', $combination['role_id'])
                    ->where('unit_id', $combination['unit_id'])
                    ->where('permission_type', $permissionType)
                    ->with(['user', 'role', 'unit'])
                    ->first();

                if ($existing) {
                    $existingPermissions[] = [
                        'type' => 'combination',
                        'user_name' => $existing->user->name ?? 'N/A',
                        'role_name' => $existing->role->name ?? 'N/A',
                        'unit_name' => $existing->unit->name ?? 'N/A',
                        'permission_type' => $permissionType,
                        'id' => $existing->id
                    ];
                }
            }
        }

        return $existingPermissions;
    }

    private function removeExistingPermissions($folderId, array $permissions)
    {
        $combinations = $this->generateCombinations($permissions);

        foreach ($combinations as $combination) {
            foreach ($permissions['permission_types'] as $permissionType) {
                FolderPermission::where('folder_id', $folderId)
                    ->where('user_id', $combination['user_id'])
                    ->where('role_id', $combination['role_id'])
                    ->where('unit_id', $combination['unit_id'])
                    ->where('permission_type', $permissionType)
                    ->delete();
            }
        }
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

    public function canAccessDocument(User $user, Document $document, $action = 'read'): bool
    {
        if ($document->is_active) return true;

        if ($document->created_by === $user->id) return true;

        $directPermission = DocumentPermission::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->where('permission_type', $action)
            ->exists();

        if ($directPermission) return true;

        $rolePermission = DocumentPermission::where('document_id', $document->id)
            ->where('role_id', $user->role_id)
            ->where('permission_type', $action)
            ->exists();

        if ($rolePermission) return true;

        $unitPermissions = DocumentPermission::where('document_id', $document->id)
            ->where('unit_id', $user->unit_id)
            ->where('permission_type', $action)
            ->exists();

        if ($unitPermissions) return true;

        return false;
    }

    public function canManagePermissions(User $user): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function canUploadDocument(User $user, Folder $folder): bool
    {
        if ($this->canAccessFolder($user, $folder, 'write')) return true;

        return false;
    }

    public function canDownloadDocument(User $user, Document $document): bool
    {
        if ($document->created_by === $user->id) return true;

        if ($this->canAccessDocument($user, $document, 'download')) return true;

        return false;
    }

    public function canDeleteDocument(User $user, Document $document): bool
    {
        if ($document->created_by === $user->id) return true;

        if ($this->canAccessDocument($user, $document, 'delete')) return true;

        return false;
    }
}
