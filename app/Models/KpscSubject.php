<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpscSubject extends Model
{
    use HasFactory;
    function parents(){
        $this->hasMany('App\Models\KpscSubject','id','parent_id');
    }
    
     public function child()
    {
        return $this->hasMany(KpscSubject::class, 'parent_id');
    }
}
