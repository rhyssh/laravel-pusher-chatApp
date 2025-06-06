<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password',];
    protected $hidden = ['password','remember_token',];
    protected $casts = ['email_verified_at' => 'datetime',];

    //Add the below function
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

