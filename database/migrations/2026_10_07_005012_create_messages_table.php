<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type', 20)->default('text');
            $table->text('body')->nullable();
            $table->unsignedBigInteger('reply_to_message_id')->nullable();
            $table->unsignedBigInteger('forwarded_from_message_id')->nullable();
            $table->unsignedBigInteger('forwarded_from_conversation_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reply_to_message_id')->references('id')->on('messages')->nullOnDelete();
            $table->foreign('forwarded_from_message_id')->references('id')->on('messages')->nullOnDelete();
            $table->foreign('forwarded_from_conversation_id')->references('id')->on('conversations')->nullOnDelete();

            $table->index(['conversation_id', 'id']);
            $table->index('sender_id');
            $table->index('reply_to_message_id');
            $table->index('forwarded_from_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
