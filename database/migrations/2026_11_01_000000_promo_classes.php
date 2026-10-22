<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Free promo classes (YouTube, live scheduled or recorded) live in the common content table. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $t) {
            $t->string('mode', 10)->nullable()->after('type');            // live | recorded
            $t->dateTime('scheduled_at')->nullable()->after('published_at');
        });
        if (Schema::hasColumn('library_classes', 'is_free')) {
            Schema::table('library_classes', fn (Blueprint $t) => $t->dropColumn('is_free'));
        }
    }

    public function down(): void
    {
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn(['mode', 'scheduled_at']));
        Schema::table('library_classes', fn (Blueprint $t) => $t->boolean('is_free')->default(false));
    }
};
