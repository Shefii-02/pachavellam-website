<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('individual_key', 40)->nullable()->unique();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('avatar_path', 500)->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('only_admins_can_send')->default(false);
            $table->boolean('only_admins_can_add_members')->default(false);
            $table->boolean('only_admins_can_edit_info')->default(false);
            $table->unsignedBigInteger('pinned_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview')->nullable();
            $table->unsignedBigInteger('last_message_sender_id')->nullable();
            $table->string('last_message_type', 20)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('last_message_sender_id')->references('id')->on('users')->nullOnDelete();

            $table->index('type');
            $table->index(['is_public', 'type']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
