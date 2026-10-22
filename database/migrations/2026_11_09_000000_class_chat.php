<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $t) {
            $t->string('chat_mode', 10)->default('off')->after('mode');   // off | live (only around the live time) | always
        });
        Schema::create('class_chat_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('item_id')->index();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('body', 400);
            $t->timestamp('created_at')->useCurrent();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_chat_messages');
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn('chat_mode'));
    }
};
