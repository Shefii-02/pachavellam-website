<?php

use App\Models\ContentItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Public ids of free classes / videos: 10 random letters and numbers instead of the first 14-letter ids. */
return new class extends Migration {
    public function up(): void
    {
        foreach (['content_items', 'special_videos'] as $table) {
            DB::table($table)->whereRaw('char_length(uid) > 10')->orderBy('id')->each(function ($r) use ($table) {
                DB::table($table)->where('id', $r->id)->update(['uid' => ContentItem::makeUid()]);
            });
        }
    }

    public function down(): void
    {
    }
};
