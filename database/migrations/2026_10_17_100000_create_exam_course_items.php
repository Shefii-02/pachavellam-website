<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Items a course takes from an existing bank: daily exams, OMR exams, class categories, bulletin issues ...
 * Only a reference is stored (the id in the source table); the name and date are read live from the source.
 * Which source a feature uses is described in config/course_item_sources.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_course_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('feature_id');
            $t->unsignedBigInteger('source_id');                  // id in the source table
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->unique(['course_id', 'feature_id', 'source_id']);
            $t->index(['course_id', 'feature_id', 'position']);
            $t->foreign('course_id')->references('id')->on('exam_courses')->cascadeOnDelete();
            $t->foreign('feature_id')->references('id')->on('exam_features')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_course_items');
    }
};
