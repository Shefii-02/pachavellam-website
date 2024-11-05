<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackPost extends Model
{
    use HasFactory;
    
    
    function requests() {

    return $this->hasMany('App\Models\FeedbackRequests','feedback_id','id');
    }
}
