<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** site_pages kind "html": a ready-made .html file (for example made with Claude) uploaded in the panel, shown at /p/{slug} under an exam type. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $t) {
            $t->string('html_file')->nullable();
            $t->unsignedBigInteger('exam_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('site_pages', function (Blueprint $t) {
            $t->dropColumn(['html_file', 'exam_id']);
        });
    }
};
