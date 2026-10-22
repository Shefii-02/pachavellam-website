<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_seasons', function (Blueprint $t) {
            // what the season is built around (shown as a label): a subject, a category, an exam ...
            $t->string('basis', 12)->nullable()->after('tagline');          // subject | category | exam | other
            $t->string('basis_name', 120)->nullable()->after('basis');
            // free or paid (is_paid): a paid season is bought like a course
            $t->decimal('price', 10, 2)->default(0)->after('is_paid');
            $t->decimal('discount_price', 10, 2)->nullable()->after('price');
            $t->string('validity_type', 10)->default('lifetime')->after('discount_price');   // lifetime | days | date
            $t->unsignedInteger('validity_days')->nullable()->after('validity_type');
            $t->date('valid_until')->nullable()->after('validity_days');
            $t->unsignedSmallInteger('free_exams')->default(0)->after('valid_until');       // the first N exams of a paid season are free for everyone
            $t->unsignedBigInteger('course_id')->nullable()->after('free_exams');            // the hidden course the purchase is made on
        });

        // the old KPSC daily / model exams join a season through this (kpsc_daily_exams.season_id is the CA season)
        Schema::table('kpsc_daily_exams', function (Blueprint $t) {
            $t->unsignedBigInteger('series_season_id')->nullable()->after('season_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('kpsc_daily_exams', function (Blueprint $t) {
            $t->dropIndex(['series_season_id']);
            $t->dropColumn('series_season_id');
        });
        Schema::table('exam_seasons', fn (Blueprint $t) => $t->dropColumn(['basis', 'basis_name', 'price', 'discount_price', 'validity_type', 'validity_days', 'valid_until', 'free_exams', 'course_id']));
    }
};
