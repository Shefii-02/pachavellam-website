<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One thing a staff member did (or a login), kept for good. */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $guarded = ['id'];

    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];

    public const COLORS = [
        'created' => 'success', 'updated' => 'primary', 'deleted' => 'danger', 'restored' => 'info', 'login' => 'secondary', 'logout' => 'secondary',
        'login_failed' => 'warning', 'cancelled' => 'danger', 'refunded' => 'danger', 'exported' => 'dark', 'marked' => 'success', 'unmarked' => 'secondary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }
}
