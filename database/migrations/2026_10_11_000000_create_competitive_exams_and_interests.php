<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * competitive_exams          master list of exams on the platform (new exams are just new rows)
 * student_exam_interests     which exams each student is interested in (many to many)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitive_exams', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 60)->unique();            // kpsc, rrb, ssc, ktet, ugc-net, ...
            $t->string('name');
            $t->string('short_name', 30)->nullable();
            $t->text('description')->nullable();
            $t->string('color', 20)->nullable();         // badge colour in the admin
            $t->string('performance_source', 40)->nullable(); // key in App\Services\StudentPerformance::SOURCES
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->timestamps();
        });

        Schema::create('student_exam_interests', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('exam_id');
            $t->string('source', 20)->default('app');    // app | web | admin
            $t->timestamps();

            $t->unique(['user_id', 'exam_id']);
            $t->index('exam_id');
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });

        $now = now();
        foreach ([
            ['kpsc', 'Kerala PSC', 'KPSC', '#f59e0b', 'kpsc'],
            ['rrb', 'Railway Recruitment Board', 'RRB', '#0ea5e9', null],
            ['ssc', 'Staff Selection Commission', 'SSC', '#8b5cf6', null],
            ['ktet', 'Kerala Teacher Eligibility Test', 'KTET', '#10b981', null],
            ['ugc-net', 'UGC NET', 'UGC NET', '#ef4444', null],
        ] as $i => [$slug, $name, $short, $color, $source]) {
            DB::table('competitive_exams')->insert([
                'slug' => $slug, 'name' => $name, 'short_name' => $short, 'color' => $color,
                'performance_source' => $source, 'position' => $i + 1, 'status' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exam_interests');
        Schema::dropIfExists('competitive_exams');
    }
};
