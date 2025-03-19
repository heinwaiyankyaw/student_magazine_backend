<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleNotification extends Model
{
    use HasFactory;

    protected $table = 'role_notification'; // Explicitly define the table name
    protected $fillable = ['role_id', 'notification_id', 'createby', 'updateby'];
}
