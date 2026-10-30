<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The cutoff mark of an exam: students below it get a "below cutoff" note with their mark. */
return new class extends Migration {
    public function up()
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->decimal('cutoff_mark', 6, 2)->nullable()->after('ended_at');
        });
    }

    public function down()
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->dropColumn('cutoff_mark');
        });
    }
};
