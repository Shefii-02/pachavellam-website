<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Achievements (stories imported from the Instagram highlights, or added by hand) can carry a video. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $t) {
            $t->string('source_ref', 40)->nullable()->after('legacy_id')->index();   // Instagram media id: stops the same story being imported twice
            $t->string('media_type', 10)->nullable()->after('source_ref');           // video | image
            $t->unsignedSmallInteger('duration')->nullable()->after('media_type');
            $t->string('media_path')->nullable()->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn(['source_ref', 'media_type', 'duration', 'media_path']));
    }
};
