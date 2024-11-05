<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapsuleComment extends Model
{
    use HasFactory;
     protected $table = 'capsule_comment';
     
    public function author()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }
    
}
