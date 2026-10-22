<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Capsule and Stories (banner_slider) can be shown for several exam types.
 *   capsule_exams  capsule  -> exam types it appears in
 *   story_exams    story    -> exam types it appears in
 * Everything that exists today was KPSC content, so it is attached to KPSC.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capsule_exams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('capsule_id');
            $t->unsignedBigInteger('exam_id');
            $t->timestamps();

            $t->unique(['capsule_id', 'exam_id']);
            $t->index('exam_id');
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });

        Schema::create('story_exams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('story_id');
            $t->unsignedBigInteger('exam_id');
            $t->timestamps();

            $t->unique(['story_id', 'exam_id']);
            $t->index('exam_id');
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });

        $kpsc = DB::table('competitive_exams')->where('slug', 'kpsc')->value('id');
        if ($kpsc) {
            $now = now();
            DB::statement('INSERT INTO capsule_exams (capsule_id, exam_id, created_at, updated_at) SELECT id, ?, ?, ? FROM capsule', [$kpsc, $now, $now]);
            DB::statement('INSERT INTO story_exams (story_id, exam_id, created_at, updated_at) SELECT id, ?, ?, ? FROM banner_slider', [$kpsc, $now, $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('story_exams');
        Schema::dropIfExists('capsule_exams');
    }
};
