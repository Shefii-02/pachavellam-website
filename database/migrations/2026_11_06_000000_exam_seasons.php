<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Daily, OMR, CA and GK exam series are run in seasons: a season has a title, dates, a cover and the exams of that season. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seasons', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->string('kind', 10);                         // daily | omr | ca | gk
            $t->string('title');
            $t->string('tagline')->nullable();
            $t->text('description')->nullable();
            $t->date('starts_on')->nullable();
            $t->date('ends_on')->nullable();
            $t->string('cover_image')->nullable();
            $t->boolean('is_paid')->default(false);
            $t->string('status', 10)->default('draft');     // draft | published | archived
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['exam_id', 'kind']);
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });
        Schema::table('course_exams', function (Blueprint $t) {
            $t->unsignedBigInteger('season_id')->nullable()->after('feature_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('course_exams', fn (Blueprint $t) => $t->dropColumn('season_id'));
        Schema::dropIfExists('exam_seasons');
    }
};
