<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class MainColor extends Model
{
    protected $table = "main_color";
    public $timestamps = false;
    public function picture(){
        return $this->belongsTo('App\Http\Model\Picture');
    }
}
