<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $fillable = [
        'title',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'projects_users');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'groups_projects');
    }
}
