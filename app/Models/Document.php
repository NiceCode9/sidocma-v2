<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'file_extension',
        'folder_id',
        'category_id',
        'document_number',
        'version',
        'status',
        'tags',
        'metadata',
        'is_active',
        'is_letter',
        'is_confidential',
        'expiry_date',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'is_confidential' => 'boolean',
        'is_letter' => 'boolean',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'file_size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'version', 'is_confidential'])
            ->logOnlyDirty();
    }

    // Relationships
    public function shares()
    {
        return $this->hasOne(DocumentShare::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function permissions()
    {
        return $this->hasMany(DocumentPermission::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    // Helper Methods
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'deleted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }
}
