<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | KPSC Daily Exam tab: free / premium, several subjects, exam types, categories and sub categories, and the link to the
 | course exams made from it. Questions added from the question library remember which library question they came from.
 | Nothing existing changes: the columns the app reads (subject, status, dates ...) stay as they are.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $t) {
            $t->string('access_type', 8)->default('free')->after('status');     // free | premium
        });

        Schema::table('kpsc_daily_exams_details', function (Blueprint $t) {
            $t->unsignedBigInteger('library_question_id')->nullable()->after('source_detail_id')->index();   // copied from the question library
        });

        Schema::table('course_exams', function (Blueprint $t) {
            $t->unsignedBigInteger('legacy_exam_id')->nullable()->after('feature_id')->index();   // the Daily Exam it was made from
        });

        Schema::create('kpsc_daily_exam_subjects', function (Blueprint $t) {
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('subject_id');
            $t->primary(['exam_id', 'subject_id']);
            $t->index('subject_id');
        });

        Schema::create('kpsc_daily_exam_categories', function (Blueprint $t) {
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('category_id');
            $t->primary(['exam_id', 'category_id']);
            $t->index('category_id');
        });

        Schema::create('kpsc_daily_exam_types', function (Blueprint $t) {
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('exam_type_id');                     // competitive_exams
            $t->primary(['exam_id', 'exam_type_id']);
            $t->index('exam_type_id');
        });

        Schema::create('kpsc_daily_exam_courses', function (Blueprint $t) {
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('course_exam_id')->nullable();       // the course exam made for that course
            $t->timestamps();
            $t->primary(['exam_id', 'course_id']);
            $t->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpsc_daily_exam_courses');
        Schema::dropIfExists('kpsc_daily_exam_types');
        Schema::dropIfExists('kpsc_daily_exam_categories');
        Schema::dropIfExists('kpsc_daily_exam_subjects');
        Schema::table('course_exams', fn (Blueprint $t) => $t->dropColumn('legacy_exam_id'));
        Schema::table('kpsc_daily_exams_details', fn (Blueprint $t) => $t->dropColumn('library_question_id'));
        Schema::table('kpsc_daily_exams', fn (Blueprint $t) => $t->dropColumn('access_type'));
    }
};
