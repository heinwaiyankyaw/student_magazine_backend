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

}
