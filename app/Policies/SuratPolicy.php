<?php

namespace App\Policies;

use App\Models\Surat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuratPolicy
{
    /**
     * Surat privat hanya bisa diakses oleh:
     *  - pengirim surat,
     *  - penerima tercatat (surat_recipients),
     *  - atau surat lama tanpa penerima tercatat (grandfathered).
     * Berlaku untuk semua peran, termasuk super admin dan direktur.
     */
    public function canAccess(User $user, Surat $surat): bool
    {
        if ($surat->user_id === $user->id) {
            return true;
        }

        if (!$surat->hasTrackedRecipients()) {
            return true;
        }

        return $surat->isRecipient($user);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super admin', 'direktur']);
    }

    public function view(User $user, Surat $surat): bool
    {
        return $this->canAccess($user, $surat);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Surat $surat): bool
    {
        return $this->canAccess($user, $surat);
    }

    public function delete(User $user, Surat $surat): bool
    {
        return $this->canAccess($user, $surat);
    }

    public function restore(User $user, Surat $surat): bool
    {
        return $this->canAccess($user, $surat);
    }

    public function forceDelete(User $user, Surat $surat): bool
    {
        return $this->canAccess($user, $surat);
    }

    public function canDownload(User $user, Surat $surat)
    {
        return $this->canAccess($user, $surat);
    }
}