<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** MCQ exams can be attempted again: every attempt is a row, attempt_no 1 is the one the leaderboard ranks. */
return new class extends Migration
{
    public function up()
    {
        Schema::table('kpsc_daily_exams_attempt', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempt_no')->default(1)->after('exam_id');
            $table->index(['exam_id', 'attempt_no']);
        });
    }

    public function down()
    {
        Schema::table('kpsc_daily_exams_attempt', function (Blueprint $table) {
            $table->dropIndex(['exam_id', 'attempt_no']);
            $table->dropColumn('attempt_no');
        });
    }
};
