<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Courses go to Trash (soft delete) like library questions, classes and course exams do, so a mistaken delete can be undone. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('exam_courses', function (Blueprint $t) {
            $t->softDeletes();
            $t->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam_courses', fn (Blueprint $t) => $t->dropColumn(['deleted_at', 'deleted_by']));
    }
};
