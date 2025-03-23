<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
