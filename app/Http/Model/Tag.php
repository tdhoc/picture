<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = "tag";
    public $timestamps = false;
    public function picture(){
        return $this->hasMany("App\Http\Model\PictureTag");
    }
}
