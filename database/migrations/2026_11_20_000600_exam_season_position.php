<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The order of the exams inside a season, set by dragging in the admin (null = not placed yet). */
return new class extends Migration {
    public function up()
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->unsignedInteger('season_position')->nullable()->after('series_season_id');
        });
        Schema::table('course_exams', function (Blueprint $table) {
            $table->unsignedInteger('season_position')->nullable()->after('season_id');
        });
    }

    public function down()
    {
        Schema::table('kpsc_daily_exams', fn (Blueprint $table) => $table->dropColumn('season_position'));
        Schema::table('course_exams', fn (Blueprint $table) => $table->dropColumn('season_position'));
    }
};
