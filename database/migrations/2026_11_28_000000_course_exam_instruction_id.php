<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The instruction template (admin exam instructions) an exam picked, so the form can show which one it is. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('course_exams', 'instruction_id')) {
            Schema::table('course_exams', fn (Blueprint $t) => $t->unsignedBigInteger('instruction_id')->nullable()->after('instruction_pdf'));
        }
    }

    public function down(): void
    {
        Schema::table('course_exams', fn (Blueprint $t) => $t->dropColumn('instruction_id'));
    }
};
