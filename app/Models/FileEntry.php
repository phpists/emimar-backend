<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileEntry extends Model
{
    protected $table = 'file_entries';
    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'project_id',
        'path',
        'full_name',
        'mime_type',
        'size',
        'pos'
    ];

    public function parent()
    {
        return $this->belongsTo(FileEntry::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FileEntry::class, 'parent_id');
    }

    public function isFolder()
    {
        return $this->type === 'folder';
    }

    public function isFile()
    {
        return $this->type === 'file';
    }

    public function getFullNameAttribute($parameter)
    {
        if ($this->isFile()){
            return asset(Storage::disk('public')->url($this->path));
        }

        return $parameter;
    }

    /**
     * Базова структура проекту
     * @param int $projectId
     */
    public static function createProjectStructure(int $projectId): void
    {
        $basePath = "projects/{$projectId}";

        $structure = [
            'lead' => 'Lead',
            'lead/notes' => 'Notes',
            'lead/drawing' => 'Drawing',
            'lead/drawing/project' => 'Project',
            'lead/drawing/emimar' => 'Emimar',
            'lead/suppliers_offer' => "Supplier's offer",
            'deal' => 'Deal',
            'deal/commercial_offer' => "Commercial offer",
        ];

        $folders = [];
        foreach ($structure as $relativePath => $fullName) {
            $fullPath = "{$basePath}/{$relativePath}";

            Storage::makeDirectory($fullPath);

            $parts = explode('/', $relativePath);
            $parentId = null;
            $currentPath = $basePath;

            foreach ($parts as $part) {
                $currentPath .= '/' . $part;

                $existing = FileEntry::where('path', $currentPath)
                    ->where('project_id', $projectId)
                    ->where('type', 'folder')
                    ->first();

                if ($existing) {
                    $parentId = $existing->id;
                    continue;
                }

                $folder = FileEntry::create([
                    'name' => $part,
                    'type' => 'folder',
                    'parent_id' => $parentId,
                    'project_id' => $projectId,
                    'path' => $currentPath,
                    'full_name' => $fullName,
                ]);

                $parentId = $folder->id;
            }
        }
    }


}
