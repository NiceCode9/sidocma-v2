<?php

namespace App\Policies;

use App\Models\Disposisi;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuratPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function view(User $user, Surat $surat): bool
    {
        if ($user->hasRole(['super admin', 'direktur'])) return true;

        return Disposisi::where('surat_id', $surat->id)
            ->whereHas('targets', fn($q) => $q->where('unit_id', $user->unit_id))
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Surat $surat): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function delete(User $user, Surat $surat): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function restore(User $user, Surat $surat): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function forceDelete(User $user, Surat $surat): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function canDownload(User $user, Surat $surat)
    {
        return $user->hasRole(['super admin', 'direktur'])
            || $user->unit_id === $surat->user->unit_id
            || Disposisi::where('surat_id', $surat->id)
                ->whereHas('targets', fn($q) => $q->where('unit_id', $user->unit_id))
                ->exists();
    }
}
