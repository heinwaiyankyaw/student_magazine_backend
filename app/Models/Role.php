<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'role_notification');
    }

}
