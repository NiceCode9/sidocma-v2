<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class DocumentPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'role_id',
        'permission_type',
        'granted_by',
        'unit_id',
    ];

    // Relationships
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
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
}
