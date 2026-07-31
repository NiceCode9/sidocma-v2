<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisposisiTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposisi_id',
        'unit_id',
        'instruksi',
        'paraf',
        'paraf_at',
        'keterangan',
    ];

    protected $casts = [
        'instruksi' => 'array',
        'paraf' => 'boolean',
        'paraf_at' => 'datetime',
    ];

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
