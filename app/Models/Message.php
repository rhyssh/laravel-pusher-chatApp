<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['message'];

    //Add the below function
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
