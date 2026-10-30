<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The sort number of a builder exam (special / GK / ...): the public exams page shows the smallest number first (null = no number, goes last). */
return new class extends Migration {
    public function up()
    {
        Schema::table('course_exams', function (Blueprint $table) {
            $table->unsignedInteger('sort_no')->nullable()->after('season_position');
        });
    }

    public function down()
    {
        Schema::table('course_exams', fn (Blueprint $table) => $table->dropColumn('sort_no'));
    }
};
