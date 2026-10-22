<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Syllabus, Study materials and Results are kept in folders (parent and child folders) per exam type. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_folders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->string('kind', 20);                          // syllabus | material | result
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->string('title');
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['exam_id', 'kind', 'parent_id']);
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });
        Schema::table('content_items', function (Blueprint $t) {
            $t->unsignedBigInteger('folder_id')->nullable()->after('subject_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn('folder_id'));
        Schema::dropIfExists('content_folders');
    }
};
