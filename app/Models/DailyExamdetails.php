<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyExamdetails extends Model
{
    use HasFactory;
    protected $table = 'kpsc_daily_exams_details';

    /** Every saved question is copied into the question library as well (see LegacyQuestionSync). */
    protected static function booted(): void
    {
        static::saved(function (self $row) {
            try {
                (new \App\Services\LegacyQuestionSync())->syncOne($row->id);
            } catch (\Throwable $e) {
                report($e);   // the old module must keep working even if the library copy fails
            }
        });
    }
}
