<?php

namespace App\Models;

use App\Events\SuratCreate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
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
                'opened_by' => Auth::user()->name ?? 'System',
            ]);
        }
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
