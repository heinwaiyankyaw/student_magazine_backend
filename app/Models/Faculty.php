<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class, 'faculty_id');
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'faculty_id');
    }
}