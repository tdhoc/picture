<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = "users";
    protected $fillable = [
        'id', 
        'username', 
        'type', 
        'email'
    ];
    protected $hidden = [
        'password', 
        'remember_token'
    ];
}
