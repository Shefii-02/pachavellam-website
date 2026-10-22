<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Categories a student marked as favourite on the Exams tab (e.g. "Banking" under SSC). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_favourite_categories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('category_id');
            $t->timestamps();
            $t->unique(['user_id', 'category_id']);
            $t->index('category_id');
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('category_id')->references('id')->on('exam_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_favourite_categories');
    }
};
