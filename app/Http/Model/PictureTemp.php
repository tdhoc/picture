<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class PictureTemp extends Model
{
    protected $table = "picture_temp";
    protected $fillable = [
        'name',
        'url',
        'users_id',
    ];
    public $timestamps = false;
}