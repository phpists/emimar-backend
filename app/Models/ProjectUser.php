<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $table = 'projects_users';
    protected $fillable = [
        'project_id',
        'user_id'
    ];
}
