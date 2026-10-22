<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Special videos become Free classes: same list, same form, with parent / child folders.
 * The old KPSC subject of each video becomes a folder of the KPSC exam type (sub subjects become child folders),
 * the video keeps its public id (uid), so every existing player link keeps working, and its chat messages move with it.
 * Rows of other sections (e.g. "unacademy") are not touched.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('content_items', fn (Blueprint $t) => $t->boolean('on_home')->default(false)->after('status'));
        Schema::table('content_folders', fn (Blueprint $t) => $t->unsignedBigInteger('legacy_ref')->nullable()->after('kind'));   // kpsc_subjects.id

        $kpsc = DB::table('competitive_exams')->where('slug', 'kpsc')->first();
        if (!$kpsc) {
            return;
        }
        $folderOf = [];   // kpsc_subjects.id => content_folders.id
        $folderFor = function ($subjectId) use (&$folderFor, &$folderOf, $kpsc) {
            if (!$subjectId) {
                return null;
            }
            if (isset($folderOf[$subjectId])) {
                return $folderOf[$subjectId];
            }
            $s = DB::table('kpsc_subjects')->where('id', $subjectId)->first();
            if (!$s) {
                return null;
            }
            $parent = ($s->parent_id ?? 0) && (int) $s->parent_id !== (int) $s->id ? $folderFor($s->parent_id) : null;
            $id = DB::table('content_folders')->insertGetId([
                'exam_id' => $kpsc->id, 'kind' => 'promo_class', 'legacy_ref' => $s->id, 'parent_id' => $parent,
                'title' => trim((string) $s->subject_title) ?: 'Folder ' . $s->id, 'position' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('content_folders')->where('id', $id)->update(['position' => $id]);

            return $folderOf[$subjectId] = $id;
        };

        DB::table('special_videos')->whereNull('section')->orderBy('id')->each(function ($v) use ($kpsc, $folderFor) {
            if (DB::table('content_items')->where('uid', $v->uid)->exists()) {
                return;
            }
            $itemId = DB::table('content_items')->insertGetId([
                'uid' => $v->uid, 'kind' => 'promo_class', 'title' => $v->title, 'link_url' => 'https://youtu.be/' . $v->redirection,
                'mode' => 'recorded', 'chat_mode' => ($v->chat_mode ?? 'off') === 'always' ? 'always' : 'off',
                'folder_id' => $folderFor($v->category), 'on_home' => true, 'status' => (int) $v->status, 'position' => (int) $v->position ?: $v->id,
                'created_at' => $v->created_at ?? now(), 'updated_at' => now(),
            ]);
            DB::table('content_item_exams')->insert(['item_id' => $itemId, 'exam_id' => $kpsc->id]);
            DB::table('class_chat_messages')->where('source', 'video')->where('item_id', $v->id)->update(['source' => 'item', 'item_id' => $itemId]);
        });
    }

    public function down(): void
    {
        DB::table('content_items')->where('kind', 'promo_class')->where('on_home', true)->whereIn('uid', DB::table('special_videos')->pluck('uid'))->delete();
        DB::table('content_folders')->whereNotNull('legacy_ref')->delete();
        Schema::table('content_folders', fn (Blueprint $t) => $t->dropColumn('legacy_ref'));
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn('on_home'));
    }
};
