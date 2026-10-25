<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** How much of each feature "What we provide" shows on an exam type page (set in General > Course Features). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_features', function (Blueprint $t) {
            $t->unsignedTinyInteger('list_limit')->default(3);      // items listed on the card (0 = description only)
            $t->unsignedTinyInteger('season_limit')->default(3);    // seasons listed on the card
            $t->unsignedTinyInteger('season_items_limit')->default(6);   // exams listed inside each season
        });
    }

    public function down(): void
    {
        Schema::table('exam_features', fn (Blueprint $t) => $t->dropColumn(['list_limit', 'season_limit', 'season_items_limit']));
    }
};
