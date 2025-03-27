<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'profile',
        'first_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'faculty_id',
        'is_password_change',
        'last_login_at',
        'last_login_ip',
        'is_suspended',
        'active_flag',
        'create_by',
        'update_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function logs()
    {
        return $this->hasMany(TransactionLog::class, 'user_id');
    }

    // Get notifications related with this user
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'user_notification', 'user_id', 'notification_id')
                    ->where('notifications.active_flag', 1)
                    ->withPivot('is_read')
                    ->latest();
    }

    // Get notifications related with this user's role
    public function roleNotifications()
    {
        return $this->hasManyThrough(
            Notification::class,
            RoleNotification::class,
            'role_id',         // Foreign key in role_notification (role_id)
            'id',              // Foreign key in notifications (id)
            'role_id',         // Foreign key in users (role_id)
            'notification_id'  // Foreign key in role_notification (notification_id)
        )->where('notifications.active_flag', 1)
        ->latest();
    }

}
