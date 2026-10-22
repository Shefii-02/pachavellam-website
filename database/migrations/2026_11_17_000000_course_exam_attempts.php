<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students taking builder exams (course_exams) on the website:
 *   course_exam_attempts   one sitting of one exam: answers, time per section, score (rank / percentile come from the first attempts)
 *   course_exam_reports    "Report" on a question (wrong answer, typo, ...) and "Was this solution helpful?"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_exam_attempts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('course_exam_id');
            $t->unsignedInteger('attempt_no')->default(1);         // 1 = the attempt that counts for rank
            $t->string('status', 12)->default('running');          // running | paused | submitted
            $t->string('timing', 10)->default('overall');          // overall | sectional (each section has its own time, one after another)
            $t->string('lang', 10)->nullable();
            $t->unsignedBigInteger('current_section_id')->nullable();
            $t->unsignedBigInteger('current_question_id')->nullable();
            $t->json('answers')->nullable();                       // {question_id: {o: [2], m: 1 (marked), v: 1 (visited), t: seconds}}
            $t->json('sections')->nullable();                      // {section_id: {used: seconds, done: 1}}
            $t->unsignedInteger('time_used')->default(0);          // seconds
            $t->timestamp('last_tick_at')->nullable();             // time only runs while the test page is open
            $t->decimal('score', 8, 2)->nullable();
            $t->decimal('max_score', 8, 2)->nullable();
            $t->decimal('negative', 8, 2)->nullable();
            $t->unsignedInteger('correct')->default(0);
            $t->unsignedInteger('wrong')->default(0);
            $t->unsignedInteger('skipped')->default(0);
            $t->json('section_scores')->nullable();                // {section_id: {score, max, correct, wrong, skipped, negative, time}}
            $t->unsignedTinyInteger('rating')->nullable();         // "Rate this test" 1 - 5
            $t->timestamp('started_at')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
            $t->index(['course_exam_id', 'status', 'attempt_no', 'score'], 'cea_exam_status_attempt_score_idx');
            $t->index(['user_id', 'course_exam_id']);
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('course_exam_id')->references('id')->on('course_exams')->cascadeOnDelete();
        });

        Schema::create('course_exam_reports', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('course_exam_id');
            $t->unsignedBigInteger('question_id');
            $t->string('kind', 12)->default('report');             // report | helpful | unhelpful
            $t->string('reason', 40)->nullable();
            $t->string('note', 500)->nullable();
            $t->string('status', 12)->default('new');              // new | fixed | closed
            $t->timestamps();
            $t->index(['course_exam_id', 'kind']);
            $t->index(['question_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exam_reports');
        Schema::dropIfExists('course_exam_attempts');
    }
};
