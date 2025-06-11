<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupProject extends Model
{
    protected $table = 'groups_projects';
    protected $fillable = [
        'group_id',
        'project_id'
    ];
}
