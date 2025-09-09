<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class FolderPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'user_id',
        'role_id',
        'unit_id',
        'permission_type',
        'granted_by',
    ];

    // Relationships
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function grantor()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    public function scopeByPermissionType($query, $type)
    {
        return $query->where('permission_type', $type);
    }
}
