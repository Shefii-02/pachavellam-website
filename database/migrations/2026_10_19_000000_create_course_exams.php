<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Exams built inside a course (daily exams, OMR exams, special exams), with sections, and the question library
 | growing to question types and up to six options.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('library_questions', function (Blueprint $t) {
            $t->string('question_type', 12)->default('single')->after('exam_id');   // single | multiple | truefalse
            $t->json('correct_options')->nullable()->after('correct_option');       // [1,3] for multiple answer questions
            $t->boolean('is_rich')->default(false)->after('difficulty');            // question / explanation hold formatted html
            $t->string('legacy_ref', 40)->nullable()->after('source')->index();     // e.g. "daily:123" - the old table row it was copied from
        });
        Schema::table('library_question_texts', function (Blueprint $t) {
            $t->text('option_5')->nullable()->after('option_4');
            $t->text('option_6')->nullable()->after('option_5');
        });

        Schema::create('course_exams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id')->index();          // exam type (competitive_exams)
            $t->unsignedBigInteger('course_id')->index();
            $t->unsignedBigInteger('feature_id')->index();        // daily-exams | omr-exams | special-exams
            $t->string('title', 200);
            $t->text('description')->nullable();
            $t->string('mode', 12)->default('moderate');          // easy | moderate | difficult | advanced
            $t->unsignedInteger('duration_minutes')->nullable();  // total time; with several sections it is the sum of the section times
            $t->unsignedInteger('avg_seconds_per_question')->nullable();   // null = total time / questions
            $t->decimal('right_mark', 6, 2)->default(1);
            $t->decimal('negative_mark', 6, 2)->default(0);
            $t->decimal('total_mark', 8, 2)->nullable();           // null = worked out from the questions
            $t->dateTime('start_at')->nullable();
            $t->dateTime('end_at')->nullable();
            $t->string('status', 10)->default('draft');           // draft | published | archived
            $t->unsignedInteger('position')->default(0);
            $t->boolean('is_paid')->default(false);
            $t->dateTime('unlock_at')->nullable();
            $t->unsignedInteger('max_attempts')->default(0);       // 0 = no limit
            $t->text('instruction')->nullable();
            $t->string('instruction_pdf')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index(['course_id', 'feature_id', 'status']);
        });

        Schema::create('course_exam_sections', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_exam_id')->index();
            $t->string('title', 150)->nullable();                  // empty for an exam with one section
            $t->unsignedInteger('position')->default(0);
            $t->unsignedInteger('duration_minutes')->nullable();   // each section has its own time limit
            $t->decimal('right_mark', 6, 2)->nullable();           // null = the exam's mark
            $t->decimal('negative_mark', 6, 2)->nullable();
            $t->text('instruction')->nullable();
            $t->string('instruction_pdf')->nullable();
            $t->timestamps();
            $t->foreign('course_exam_id')->references('id')->on('course_exams')->cascadeOnDelete();
        });

        Schema::create('course_exam_questions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_exam_id');
            $t->unsignedBigInteger('section_id');
            $t->unsignedBigInteger('question_id');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
            $t->unique(['course_exam_id', 'question_id']);
            $t->index('section_id');
            $t->foreign('course_exam_id')->references('id')->on('course_exams')->cascadeOnDelete();
            $t->foreign('section_id')->references('id')->on('course_exam_sections')->cascadeOnDelete();
            $t->foreign('question_id')->references('id')->on('library_questions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exam_questions');
        Schema::dropIfExists('course_exam_sections');
        Schema::dropIfExists('course_exams');
        Schema::table('library_question_texts', fn (Blueprint $t) => $t->dropColumn(['option_5', 'option_6']));
        Schema::table('library_questions', fn (Blueprint $t) => $t->dropColumn(['question_type', 'correct_options', 'is_rich', 'legacy_ref']));
    }
};
