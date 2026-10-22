<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Staff chat (staff only; the realtime part runs in the Node.js chat-server). Laravel owns the schema.
 | Direct chats and groups. A group's members have a role and individual permissions; the Admin accounts are members of every group
 | from the moment it is made, with every permission, and an Admin can open any chat.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_chats', function (Blueprint $t) {
            $t->id();
            $t->string('type', 8);                                      // direct | group
            $t->string('direct_key', 40)->nullable()->unique();         // "12:57" for a direct chat, so the same two people share one chat
            $t->string('name', 150)->nullable();
            $t->string('description', 500)->nullable();
            $t->boolean('only_admins_can_send')->default(false);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->dateTime('last_message_at')->nullable()->index();
            $t->string('last_message_preview', 160)->nullable();
            $t->unsignedBigInteger('last_message_sender_id')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('staff_chat_members', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('chat_id');
            $t->unsignedBigInteger('user_id');
            $t->string('role', 8)->default('member');                   // owner | admin | member
            $t->boolean('can_send')->default(true);
            $t->boolean('can_add_members')->default(false);
            $t->boolean('can_remove_members')->default(false);
            $t->boolean('can_edit_info')->default(false);
            $t->boolean('can_manage_roles')->default(false);
            $t->boolean('can_delete_messages')->default(false);
            $t->unsignedBigInteger('added_by')->nullable();
            $t->dateTime('joined_at')->useCurrent();
            $t->dateTime('left_at')->nullable();
            $t->dateTime('muted_until')->nullable();
            $t->unsignedBigInteger('last_read_message_id')->nullable();
            $t->timestamps();
            $t->unique(['chat_id', 'user_id']);
            $t->index('user_id');
            $t->foreign('chat_id')->references('id')->on('staff_chats')->cascadeOnDelete();
        });

        Schema::create('staff_chat_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('chat_id');
            $t->unsignedBigInteger('sender_id')->nullable();            // null = system line ("Asha added Ravi")
            $t->string('type', 8)->default('text');                     // text | image | file | system
            $t->text('body')->nullable();
            $t->unsignedBigInteger('reply_to_id')->nullable();
            $t->string('attachment_path')->nullable();
            $t->string('attachment_name')->nullable();
            $t->string('attachment_mime', 100)->nullable();
            $t->unsignedBigInteger('attachment_size')->nullable();
            $t->dateTime('edited_at')->nullable();
            $t->dateTime('deleted_at')->nullable();
            $t->unsignedBigInteger('deleted_by')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['chat_id', 'id']);
            $t->foreign('chat_id')->references('id')->on('staff_chats')->cascadeOnDelete();
        });

        Schema::create('staff_chat_reactions', function (Blueprint $t) {
            $t->unsignedBigInteger('message_id');
            $t->unsignedBigInteger('user_id');
            $t->string('emoji', 16);
            $t->timestamp('created_at')->useCurrent();
            $t->primary(['message_id', 'user_id', 'emoji']);
            $t->foreign('message_id')->references('id')->on('staff_chat_messages')->cascadeOnDelete();
        });

        // who may chat: a permission, given to the staff roles that exist today (Admins always can)
        $guard = config('auth.defaults.guard', 'web');
        DB::table('permissions')->updateOrInsert(['name' => 'Staff chat use', 'guard_name' => $guard], ['created_at' => now(), 'updated_at' => now()]);
        $pid = DB::table('permissions')->where('name', 'Staff chat use')->where('guard_name', $guard)->value('id');
        foreach (['Admin', 'Office Staff', 'Data Entry', 'Developer'] as $role) {
            if ($rid = DB::table('roles')->where('name', $role)->value('id')) {
                DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $rid]);
            }
        }
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_chat_reactions');
        Schema::dropIfExists('staff_chat_messages');
        Schema::dropIfExists('staff_chat_members');
        Schema::dropIfExists('staff_chats');
        $ids = DB::table('permissions')->where('name', 'Staff chat use')->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
