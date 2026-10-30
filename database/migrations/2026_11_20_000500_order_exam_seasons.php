<?php

use App\Models\ExamSeason;
use Illuminate\Database\Migrations\Migration;

/** Seasons get a real show order (position 1, 2, 3 ... per exam and series), starting from the order they are shown in today (newest first). */
return new class extends Migration {
    public function up()
    {
        ExamSeason::query()->get(['id', 'exam_id', 'kind', 'starts_on'])->groupBy(fn ($s) => $s->exam_id . '|' . $s->kind)->each(function ($group) {
            $group->sortBy([['starts_on', 'desc'], ['id', 'desc']])->values()->each(fn ($s, $i) => ExamSeason::whereKey($s->id)->update(['position' => $i + 1]));
        });
    }

    public function down()
    {
    }
};
