<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A course can sit in several KPSC categories (used to filter courses by category in the app / website).
 * kpsc_courses.category_id stays as the "main" category (the first one chosen).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpsc_course_categories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('category_id');
            $t->timestamps();

            $t->unique(['course_id', 'category_id']);
            $t->index('category_id');
            $t->foreign('course_id')->references('id')->on('kpsc_courses')->cascadeOnDelete();
            $t->foreign('category_id')->references('id')->on('kpsc_categories')->cascadeOnDelete();
        });

        // courses that already had a single category keep it
        $now = now();
        DB::table('kpsc_courses')->whereNotNull('category_id')->get(['id', 'category_id'])->each(function ($c) use ($now) {
            if (DB::table('kpsc_categories')->where('id', $c->category_id)->exists()) {
                DB::table('kpsc_course_categories')->insertOrIgnore(['course_id' => $c->id, 'category_id' => $c->category_id, 'created_at' => $now, 'updated_at' => $now]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpsc_course_categories');
    }
};
