<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentShare extends Model
{
    protected $fillable = [
        'uuid',
        'document_id',
        'created_by',
        'password',
        'expires_at',
        'download_count',
        'max_downloads',
        'is_active',
        'is_read',
        'read_at',
        'opened_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
        'is_active' => 'boolean',
        'download_count' => 'integer',
        'max_downloads' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function incrementDownload(): void
    {
        $this->increment('download_count');
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
                'opened_by' => auth()->user()->name,
            ]);
        }
    }
}
