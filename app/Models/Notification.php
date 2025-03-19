<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'message', 'active_flag', 'createby', 'updateby'];

    // Relationship with users who received the notification
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notification');
    }

    // Relationship with roles that receive the notification
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_notification');
    }

    // Creator of the notification
    public function creator()
    {
        return $this->belongsTo(User::class, 'createby');
    }

    // Updater of the notification
    public function updater()
    {
        return $this->belongsTo(User::class, 'updateby');
    }
}
