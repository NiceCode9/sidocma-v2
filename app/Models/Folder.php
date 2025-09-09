<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Folder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'path',
        'level',
        'category_id',
        'color',
        'icon',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'parent_id', 'is_active'])
            ->logOnlyDirty();
    }

    // Boot method to handle path generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($folder) {
            $folder->generatePath();
        });

        static::updating(function ($folder) {
            if ($folder->isDirty('parent_id') || $folder->isDirty('name')) {
                $folder->generatePath();
            }
        });
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function permissions()
    {
        return $this->hasMany(FolderPermission::class);
    }

    // Helper Methods
    public function generatePath()
    {
        $path = $this->name;
        $level = 0;

        if ($this->parent_id) {
            $parent = $this->parent;
            $path = $parent->path . '/' . $this->name;
            $level = $parent->level + 1;
        }

        $this->path = $path;
        $this->level = $level;
    }

    public function getAllDescendants()
    {
        return $this->children()->with('children');
    }

    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootFolders($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
