<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentPermission;
use App\Models\Folder;
use App\Models\FolderPermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentPermissionService
{
    public function setDocumentPermissions(Document $document, array $permissions, User $grantor, $force = false)
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
            $existingPermissions = $this->checkExistingPermissions($document->id, $permissions);

            if (!empty($existingPermissions)) {
                return [
                    'status' => 'warning',
                    'message' => 'Terdapat permission yang sudah ada',
                    'existing_permissions' => $existingPermissions,
                    'success' => false
                ];
            }
        }

        DB::transaction(function () use ($document, $permissions, $grantor, $force) {
            // Jika force, hapus existing permissions terlebih dahulu
            if ($force) {
                $this->removeExistingPermissions($document->id, $permissions);
            }

            // Buat semua kombinasi yang mungkin
            $combinations = $this->generateCombinations($permissions);

            // Loop setiap kombinasi dan setiap permission_type
            foreach ($combinations as $combination) {
                foreach ($permissions['permission_types'] as $permissionType) {
                    DocumentPermission::create([
                        'document_id' => $document->id,
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

    private function checkExistingPermissions($documentId, array $permissions)
    {
        $existingPermissions = [];
        $combinations = $this->generateCombinations($permissions);

        foreach ($combinations as $combination) {
            foreach ($permissions['permission_types'] as $permissionType) {
                $existing = DocumentPermission::where('document_id', $documentId)
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

    private function removeExistingPermissions($documentId, array $permissions)
    {
        $combinations = $this->generateCombinations($permissions);

        foreach ($combinations as $combination) {
            foreach ($permissions['permission_types'] as $permissionType) {
                DocumentPermission::where('document_id', $documentId)
                    ->where('user_id', $combination['user_id'])
                    ->where('role_id', $combination['role_id'])
                    ->where('unit_id', $combination['unit_id'])
                    ->where('permission_type', $permissionType)
                    ->delete();
            }
        }
    }
}
