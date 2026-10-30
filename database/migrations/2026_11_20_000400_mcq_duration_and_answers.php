<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** MCQ exam player: an optional duration per exam, and one row per question answered in an attempt (answer, time spent, review mark). */
return new class extends Migration {
    public function up()
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_min')->nullable()->after('cutoff_mark');   // null = no countdown, the timer counts up
        });

        Schema::create('kpsc_daily_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedTinyInteger('chosen')->nullable();     // 1-4, null = not answered
            $table->boolean('is_correct')->nullable();
            $table->boolean('marked')->default(false);
            $table->unsignedSmallInteger('seconds')->default(0);
            $table->unique(['attempt_id', 'question_id']);
            $table->index(['exam_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpsc_daily_exam_answers');
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->dropColumn('duration_min');
        });
    }
};
