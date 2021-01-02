<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Access extends Model
{
    protected $fillable = ['project_id', 'user_id', 'role_id'];

    function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
