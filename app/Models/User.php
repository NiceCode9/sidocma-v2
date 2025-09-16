<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'username',
        'kode_user',
        'email',
        'password',
        'unit_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'username', 'unit_id', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function createdFolders()
    {
        return $this->hasMany(Folder::class, 'created_by');
    }

    public function updatedFolders()
    {
        return $this->hasMany(Folder::class, 'updated_by');
    }

    public function createdDocuments()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function updatedDocuments()
    {
        return $this->hasMany(Document::class, 'updated_by');
    }

    public function approvedDocuments()
    {
        return $this->hasMany(Document::class, 'approved_by');
    }

    public function folderPermissions()
    {
        return $this->hasMany(FolderPermission::class, 'user_id');
    }

    public function documentPermissions()
    {
        return $this->hasMany(DocumentPermission::class, 'user_id');
    }

    public function grantedFolderPermissions()
    {
        return $this->hasMany(FolderPermission::class, 'granted_by');
    }

    public function grantedDocumentPermissions()
    {
        return $this->hasMany(DocumentPermission::class, 'granted_by');
    }

    public function documentAccessLogs()
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $unit)
    {
        return $query->where('unit_id', $unit);
    }
}
