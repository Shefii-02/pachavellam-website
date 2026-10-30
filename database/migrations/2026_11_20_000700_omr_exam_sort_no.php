<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The sort number of an exam: the public list of OMR exams shows the smallest number first (null = no number, goes last). */
return new class extends Migration {
    public function up()
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->unsignedInteger('sort_no')->nullable()->after('season_position');
        });
    }

    public function down()
    {
        Schema::table('kpsc_daily_exams', fn (Blueprint $table) => $table->dropColumn('sort_no'));
    }
};
