<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];

    public function contribution()
    {
        return $this->belongsTo(Contribution::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}