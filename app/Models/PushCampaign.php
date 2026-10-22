<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushCampaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['audience_exam_ids' => 'array', 'platforms' => 'array'];
}
