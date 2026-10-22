<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An event shown in the staff bell: new join, new admission, cancellation ... One row for everyone; reads are kept per person. */
class StaffNotification extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'staff_notifications';

    protected $guarded = ['id'];

    protected $casts = ['created_at' => 'datetime'];

    /** The permission needed to see each kind of notification. */
    public const PERMISSION = [
        'new_join' => 'New joins view', 'interest' => 'New joins view',
        'admission' => 'Admissions view', 'cancellation' => 'Admissions view', 'payment_failed' => 'Admissions view',
    ];

    public const STYLE = [
        'new_join' => ['bi-person-plus', 'primary'], 'interest' => ['bi-star', 'warning'], 'admission' => ['bi-person-check', 'success'],
        'cancellation' => ['bi-x-circle', 'danger'], 'payment_failed' => ['bi-exclamation-triangle', 'warning'],
    ];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }
}
