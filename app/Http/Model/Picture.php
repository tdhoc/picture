<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    protected $table = "picture";
    protected $fillable = [
        'id', 
        'link',
        'author_name',
        'description', 
        'resolution', 
        'size', 
        'view', 
        'download',
        'users_id',
        'thumb'
    ];
    public function subcategory(){
        return $this->belongsTo('App\Http\Model\Subcategory');
    }
    public function tag(){
        return $this->hasMany("App\Http\Model\PictureTag");
    }
    public function color(){
        return $this->hasMany("App\Http\Model\MainColor");
    }

}
