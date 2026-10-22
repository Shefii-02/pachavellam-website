<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student asked for a call back from a counsellor (home page form). Followed up under Website content > Counselling. */
class CounsellingRequest extends Model
{
    protected $guarded = ['id'];

    public const STATUSES = ['new' => ['New', 'danger'], 'contacted' => ['Contacted', 'warning'], 'closed' => ['Closed', 'success']];

    public const TIMES = ['Morning (9 am - 12 pm)', 'Afternoon (12 pm - 4 pm)', 'Evening (4 pm - 8 pm)', 'Any time'];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
