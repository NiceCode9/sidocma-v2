<?php

namespace App\Models;

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
        }
    }
}
