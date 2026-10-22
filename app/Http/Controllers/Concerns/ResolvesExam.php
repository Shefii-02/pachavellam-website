<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CompetitiveExam;

/** The common exam pages live at admin/exam/{exam}/... where {exam} is the exam type's slug. */
trait ResolvesExam
{
    /** The exam type for the address (hidden exam types still work in the admin). */
    protected function exam(string $slug): CompetitiveExam
    {
        return CompetitiveExam::where('slug', $slug)->firstOrFail();
    }

    /** Name shown in page titles: "KPSC", "UGC NET", ... */
    protected function examLabel(CompetitiveExam $exam): string
    {
        return $exam->short_name ?: $exam->name;
    }
}
