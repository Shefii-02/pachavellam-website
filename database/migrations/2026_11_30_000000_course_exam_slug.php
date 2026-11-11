<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** course_exams.slug: the exam's own address under its season, e.g. /rrb/daily-exams/ssc-chsl/chsl-mock-1 (older exams get one when first needed). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_exams', function (Blueprint $t) {
            $t->string('slug', 200)->nullable()->after('title')->index();
        });
    }

    public function down(): void
    {
        Schema::table('course_exams', fn (Blueprint $t) => $t->dropColumn('slug'));
    }
};
