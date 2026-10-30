<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An instruction text written once and picked by many exams (kind: omr | mcq). */
class ExamInstruction extends Model
{
    protected $guarded = [];

    protected $casts = ['is_default' => 'boolean'];

    /** The template an exam shows: the one it picked, else the default of its kind. */
    public static function forExam(DailyExam $exam, string $kind): ?self
    {
        if ($exam->instruction_id && ($picked = self::find($exam->instruction_id))) {
            return $picked;
        }

        return self::where('kind', $kind)->where('is_default', true)->first();
    }

    /** Only the markup an instruction needs. */
    public static function clean(?string $html): string
    {
        return strip_tags((string) $html, '<p><br><ul><ol><li><b><strong><i><em><u><h5><h6>');
    }
}
