<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class PictureTag extends Model
{
    protected $table = "picture_tag";
    public $timestamps = false;
    public function picture(){
        return $this->belongsTo('App\Http\Model\Picture');
    }
    public function tag(){
        return $this->belongsTo('App\Http\Model\Tag');
    }
}
