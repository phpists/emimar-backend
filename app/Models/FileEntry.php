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

    public function scopeOnlyAccessibleTo($query, $userId, $groupIds)
    {
        return $query->where(function ($q) use ($userId, $groupIds) {
            // Прямі заборони (is_visible = false)
            $q->whereNotIn('id', function ($sub) use ($userId, $groupIds) {
                $sub->select('file_entry_id')
                    ->from('file_entries_permissions')
                    ->where(function ($w) use ($userId, $groupIds) {
                        $w->where('user_id', $userId);

                        if (!empty($groupIds)) {
                            $w->orWhereIn('group_id', $groupIds);
                        }
                    })
                    ->where('is_visible', false);
            });

            // А доступ по проекту — як і раніше:
            $q->whereIn('project_id', function ($sub) use ($userId, $groupIds) {
                $sub->select('id')
                    ->from('projects')
                    ->where(function ($w) use ($userId, $groupIds) {
                        $w->where(function ($q1) use ($userId) {
                            $q1->where('rules_type', 'users')
                                ->whereIn('id', function ($sub2) use ($userId) {
                                    $sub2->select('project_id')
                                        ->from('projects_users')
                                        ->where('user_id', $userId);
                                });
                        });

                        if (!empty($groupIds)) {
                            $w->orWhere(function ($q2) use ($groupIds) {
                                $q2->where('rules_type', 'groups')
                                    ->whereIn('id', function ($sub3) use ($groupIds) {
                                        $sub3->select('project_id')
                                            ->from('groups_projects')
                                            ->whereIn('group_id', $groupIds);
                                    });
                            });
                        }
                    });
            });
        });
    }


}
