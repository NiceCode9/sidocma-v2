<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'version',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'changes_description',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // Relationships
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
