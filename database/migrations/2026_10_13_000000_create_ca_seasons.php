<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Current Affairs exams in seasons.
 *
 * kpsc_ca_seasons          a season (name, status, tags, schedule type, dates)
 * kpsc_daily_exams         + season_id, exam_kind (daily|weekly|weekend|monthly|other), slot_no, period_*, exam_title
 * kpsc_daily_exams_details + source_detail_id  (set when a question was copied from a daily exam)
 * kpsc_ca_exam_sources     which daily exams a weekly / weekend exam pulled its questions from
 *
 * Existing "CA-Daily-Exam" rows are moved into "Season 1" so nothing is orphaned.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpsc_ca_seasons', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('cover_image')->nullable();
            $t->string('status', 12)->default('draft');          // draft | active | completed | archived
            $t->string('schedule_type', 12)->default('daily');   // daily | weekly | weekend | mixed | custom
            $t->date('starts_on')->nullable();
            $t->date('ends_on')->nullable();
            $t->json('tags')->nullable();
            $t->boolean('is_featured')->default(false);          // the season shown to apps that do not ask for one
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['status', 'position']);
        });

        Schema::table('kpsc_daily_exams', function (Blueprint $t) {
            $t->unsignedBigInteger('season_id')->nullable()->after('section')->index();
            $t->string('exam_kind', 12)->nullable()->after('season_id');
            $t->unsignedInteger('slot_no')->nullable()->after('exam_kind');   // day number / week number / ...
            $t->string('exam_title')->nullable()->after('slot_no');           // display name, e.g. "Weekend Test 3"
            $t->date('period_start')->nullable()->after('exam_title');        // dates a weekly / weekend exam covers
            $t->date('period_end')->nullable()->after('period_start');
            $t->index(['season_id', 'exam_kind', 'slot_no']);
        });

        Schema::table('kpsc_daily_exams_details', function (Blueprint $t) {
            $t->unsignedBigInteger('source_detail_id')->nullable()->after('exam_id')->index();
        });

        Schema::create('kpsc_ca_exam_sources', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id')->index();          // the weekly / weekend exam
            $t->unsignedBigInteger('source_exam_id')->index();   // the daily exam questions came from
            $t->string('mode', 15)->default('all');              // all | random
            $t->unsignedInteger('question_count')->default(0);   // how many were copied
            $t->timestamps();
        });

        // move existing CA daily exams into a first season
        $existing = DB::table('kpsc_daily_exams')->where('section', 'CA-Daily-Exam')->whereNull('season_id');
        if ((clone $existing)->exists()) {
            $now = now();
            $first = (clone $existing)->min('exam_date');
            $last = (clone $existing)->max('exam_date');

            $seasonId = DB::table('kpsc_ca_seasons')->insertGetId([
                'name' => 'Season 1',
                'slug' => 'season-1',
                'description' => 'Created automatically from the existing Current Affairs daily exams.',
                'status' => 'active',
                'schedule_type' => 'daily',
                'starts_on' => $first,
                'ends_on' => null,
                'tags' => json_encode(['daily']),
                'is_featured' => true,
                'position' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('kpsc_daily_exams')->where('section', 'CA-Daily-Exam')->whereNull('season_id')
                ->update([
                    'season_id' => $seasonId,
                    'exam_kind' => 'daily',
                    'slot_no' => DB::raw('CAST(level AS UNSIGNED)'),
                    'exam_title' => DB::raw("CONCAT('Day ', level)"),
                ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kpsc_ca_exam_sources');

        Schema::table('kpsc_daily_exams_details', function (Blueprint $t) {
            $t->dropColumn('source_detail_id');
        });

        Schema::table('kpsc_daily_exams', function (Blueprint $t) {
            $t->dropIndex(['season_id', 'exam_kind', 'slot_no']);
            $t->dropColumn(['season_id', 'exam_kind', 'slot_no', 'exam_title', 'period_start', 'period_end']);
        });

        Schema::dropIfExists('kpsc_ca_seasons');
    }
};
