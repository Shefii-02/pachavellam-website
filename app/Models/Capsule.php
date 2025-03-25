<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capsule extends Model
{
    use HasFactory;
    protected $table = 'capsule';
    
    
    public function author()
    {
        return $this->hasOne('App\Models\User','id','author_id');
    }
    
    
    public function comments(){
        return $this->hasMany('App\Models\CapsuleComment','cap_id','id');
    }
    
    public function likes(){
        return $this->hasMany('App\Models\CapsuleLike','cap_id','id');
    }
}
