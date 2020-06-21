<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $table = "subcategory";
    public $timestamps = false;
    public function picture(){
        return $this->hasMany("App\Http\Model\Picture");
    }
}
