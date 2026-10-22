<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* Staff chat extras: pinned message, archive / mark-unread per person, starred messages, forwarded flag, quick replies. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('staff_chats', function (Blueprint $t) {
            $t->unsignedBigInteger('pinned_message_id')->nullable()->after('only_admins_can_send');
        });
        Schema::table('staff_chat_members', function (Blueprint $t) {
            $t->dateTime('archived_at')->nullable()->after('muted_until');
            $t->boolean('marked_unread')->default(false)->after('archived_at');
        });
        Schema::table('staff_chat_messages', function (Blueprint $t) {
            $t->boolean('forwarded')->default(false)->after('reply_to_id');
        });

        Schema::create('staff_chat_stars', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('message_id');
            $t->timestamp('created_at')->useCurrent();
            $t->primary(['user_id', 'message_id']);
            $t->foreign('message_id')->references('id')->on('staff_chat_messages')->cascadeOnDelete();
        });

        // ready-made answers for the sales / support team: type "/" in the message box
        Schema::create('staff_chat_quick_replies', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable()->index();   // null = shared with every staff member (made by an Admin)
            $t->string('shortcut', 30);
            $t->text('body');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_chat_quick_replies');
        Schema::dropIfExists('staff_chat_stars');
        Schema::table('staff_chat_messages', fn (Blueprint $t) => $t->dropColumn('forwarded'));
        Schema::table('staff_chat_members', fn (Blueprint $t) => $t->dropColumn(['archived_at', 'marked_unread']));
        Schema::table('staff_chats', fn (Blueprint $t) => $t->dropColumn('pinned_message_id'));
    }
};
