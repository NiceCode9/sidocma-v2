<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;

class PermissionService
{
    public function canAccessFolder(User $user, Folder $folder)
    {
        // Admin dan direktur dapat mengakses semua folder
        if ($user->hasRole(['admin', 'direktur'])) {
            return true;
        }

        // Folder umum (tanpa unit) bisa diakses semua user
        if (is_null($folder->unit_id)) {
            return true;
        }

        // User bisa akses folder dari unit mereka
        if ($folder->unit_id === $user->unit_id) {
            return true;
        }

        return false;
    }
}
