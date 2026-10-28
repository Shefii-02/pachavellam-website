<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A student can turn push notifications off in the app; while off, no push (panel or chat) goes to any of their devices. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'push_enabled')) {
            Schema::table('users', fn (Blueprint $t) => $t->boolean('push_enabled')->default(true));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'push_enabled')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('push_enabled'));
        }
    }
};
