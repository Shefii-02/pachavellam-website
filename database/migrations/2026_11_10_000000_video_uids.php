<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        foreach (['content_items', 'special_videos'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->string('uid', 16)->nullable()->unique()->after('id'));
            DB::table($table)->whereNull('uid')->orderBy('id')->each(fn ($r) => DB::table($table)->where('id', $r->id)->update(['uid' => Str::lower(Str::random(14))]));
        }
    }

    public function down(): void
    {
        foreach (['content_items', 'special_videos'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['uid']);
                $t->dropColumn('uid');
            });
        }
    }
};
