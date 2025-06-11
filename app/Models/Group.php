<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = [
        'title',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'groups_users');
    }
}
