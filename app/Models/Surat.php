<?php

namespace App\Models;

use App\Events\SuratCreated;
use App\Events\SuratRead;
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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($surat) {
            broadcast(new SuratCreated($surat));
        });
    }

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
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
                'opened_by' => Auth::user()->name,
            ]);

            broadcast(new SuratRead($this));
        }
    }
}
