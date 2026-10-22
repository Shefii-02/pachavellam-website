<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('special_videos', fn (Blueprint $t) => $t->string('chat_mode', 10)->default('off')->after('status'));   // off | always
        Schema::table('class_chat_messages', function (Blueprint $t) {
            $t->string('source', 8)->default('item')->after('id');   // item = content_items (free classes), video = special_videos
            $t->index(['source', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('class_chat_messages', function (Blueprint $t) {
            $t->dropIndex(['source', 'item_id']);
            $t->dropColumn('source');
        });
        Schema::table('special_videos', fn (Blueprint $t) => $t->dropColumn('chat_mode'));
    }
};
