<?php

namespace App\Models;

use App\Enums\DisposisiStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disposisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_id',
        'document_id',
        'no_agenda',
        'tanggal_naskah',
        'masuk_tu',
        'tgl_no_naskah',
        'asal_naskah',
        'isi_informasi',
        'sifat',
        'catatan_lain',
        'batas_waktu',
        'status',
        'created_by',
        'approved_by',
        'forwarded_from',
    ];

    protected $casts = [
        'tanggal_naskah' => 'date',
        'masuk_tu' => 'datetime',
        'batas_waktu' => 'date',
        'status' => DisposisiStatus::class,
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function targets()
    {
        return $this->hasMany(DisposisiTarget::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function forwardedFrom()
    {
        return $this->belongsTo(Disposisi::class, 'forwarded_from');
    }

    public function forwardedDisposisis()
    {
        return $this->hasMany(Disposisi::class, 'forwarded_from');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function isEditable(): bool
    {
        return $this->status === DisposisiStatus::Diproses;
    }

    public function canManage(User $user): bool
    {
        if ($user->hasRole('super admin')) return true;

        return $user->hasRole('direktur') && $this->created_by === $user->id;
    }

    public function canEdit(User $user): bool
    {
        if (!$this->canManage($user)) return false;

        // Super admin boleh edit semua status
        if ($user->hasRole('super admin')) return true;

        // Selainnya (direktur) hanya status diproses
        return $this->status === DisposisiStatus::Diproses;
    }

    public function canDelete(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function getTargetUnitIds(): array
    {
        return $this->targets->pluck('unit_id')->toArray();
    }
}
