<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // an exam of a paid season that anyone can attempt without buying the season (set when the exam is created)
        Schema::table('course_exams', fn (Blueprint $t) => $t->boolean('free_access')->default(false)->after('is_paid'));
        Schema::table('kpsc_daily_exams', fn (Blueprint $t) => $t->boolean('free_access')->default(false)->after('series_season_id'));
    }

    public function down(): void
    {
        Schema::table('kpsc_daily_exams', fn (Blueprint $t) => $t->dropColumn('free_access'));
        Schema::table('course_exams', fn (Blueprint $t) => $t->dropColumn('free_access'));
    }
};
