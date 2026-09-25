<?php

namespace App\Models;

use App\Events\SuratCreate;
use App\Policies\SuratPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

#[UsePolicy(SuratPolicy::class)]
class Surat extends Model
{
    protected $fillable = [
        'user_id',
        'no_surat',
        'perihal',
        'keterangan',
        'file',
        'is_read',
        'opened_by',
        'read_at',
        'needs_disposisi',
        'disposisi_no_agenda',
        'disposisi_tgl_naskah',
        'disposisi_masuk_tu',
        'disposisi_tgl_no_naskah',
        'disposisi_asal_naskah',
        'disposisi_informasi_naskah',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'needs_disposisi' => 'boolean',
        'disposisi_tgl_naskah' => 'date',
        'disposisi_masuk_tu' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'surat_recipients')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function isRecipient(User $user): bool
    {
        return $this->recipients()->where('users.id', $user->id)->exists();
    }

    /**
     * Surat dianggap "tidak privat" (legacy/grandfathered) bila tidak memiliki
     * satupun penerima tercatat. Surat semacam itu tetap bisa diakses peran-peran
     * berwenang agar data lama tidak hilang.
     */
    public function hasTrackedRecipients(): bool
    {
        return $this->recipients()->exists();
    }

    /**
     * Scope surat yang bisa diakses user: pengirim, penerima tercatat,
     * atau surat legacy (tanpa penerima tercatat).
     */
    public function scopeAccessibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('surats.user_id', $user->id)
                ->orWhereDoesntHave('recipients')
                ->orWhereHas('recipients', fn($r) => $r->where('users.id', $user->id));
        });
    }

    public function getRouteKeyName()
    {
        return 'no_surat';
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
                'opened_by' => Auth::user()->id ?? 1,
            ]);
        }
    }

    /**
     * Tandai terbaca untuk user tertentu: tulis read_at pada pivot
     * surat_recipients (per penerima) + jaga kompatibilitas read_at global.
     */
    public function markAsReadBy(User $user): void
    {
        if ($this->isRecipient($user)) {
            $this->recipients()->updateExistingPivot($user->id, ['read_at' => now()]);
        }

        $this->markAsRead();
    }

    // Add accessor untuk check if read
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    // Add scope untuk surat yang belum dibaca
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Add scope untuk surat yang sudah dibaca
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
