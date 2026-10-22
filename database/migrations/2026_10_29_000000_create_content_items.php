<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One shared place for Banners, What's New, Announcements, Articles and Notifications.
 * Each item is shown for the exam types ticked on it (content_item_exams).
 * Existing KPSC rows (special_tab, whats_new, psc_news, notification) are copied in and tagged KPSC.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $t) {
            $t->id();
            $t->string('kind', 20)->index();            // banner | whats_new | announcement | article | notification
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('link_url', 500)->nullable();
            $t->string('attachment')->nullable();
            $t->string('type', 30)->nullable();          // announcement: exam | other
            $t->string('bg_color', 20)->nullable();
            $t->string('text_color', 20)->nullable();
            $t->boolean('new_tab')->default(false);
            $t->boolean('is_pinned')->default(false);
            $t->date('starts_at')->nullable();
            $t->date('ends_at')->nullable();
            $t->date('published_at')->nullable();
            $t->date('last_date')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('legacy_id')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('content_item_exams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('item_id');
            $t->unsignedBigInteger('exam_id');
            $t->timestamps();
            $t->unique(['item_id', 'exam_id']);
            $t->index('exam_id');
            $t->foreign('item_id')->references('id')->on('content_items')->cascadeOnDelete();
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });

        $now = now();
        $kpsc = DB::table('competitive_exams')->where('slug', 'kpsc')->value('id');
        $copy = function (string $table, string $kind, callable $map) use ($now, $kpsc) {
            if (!Schema::hasTable($table)) {
                return;
            }
            foreach (DB::table($table)->get() as $r) {
                $id = DB::table('content_items')->insertGetId(array_merge([
                    'kind' => $kind, 'legacy_id' => $r->id, 'created_at' => $r->created_at ?? $now, 'updated_at' => $now,
                ], $map($r)));
                if ($kpsc) {
                    DB::table('content_item_exams')->insert(['item_id' => $id, 'exam_id' => $kpsc, 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        };
        $copy('special_tab', 'banner', fn ($r) => ['image' => $r->image, 'link_url' => $r->redirection, 'position' => (int) $r->position, 'status' => (int) $r->status]);
        $copy('whats_new', 'whats_new', fn ($r) => ['title' => $r->title, 'image' => $r->image, 'link_url' => $r->redirection, 'bg_color' => $r->bg_color, 'text_color' => $r->text_color, 'new_tab' => (int) $r->target === 1, 'position' => (int) $r->position, 'status' => (int) $r->status]);
        $copy('psc_news', 'article', fn ($r) => ['title' => $r->title, 'image' => $r->image, 'description' => $r->description, 'type' => $r->type, 'published_at' => $r->post_date, 'position' => (int) $r->position, 'status' => (int) $r->status]);
        $copy('notification', 'notification', fn ($r) => ['title' => $r->title, 'link_url' => $r->redirection, 'created_by' => $r->author_id, 'published_at' => substr((string) $r->created_at, 0, 10) ?: null, 'position' => (int) $r->id]);

        $guard = config('auth.defaults.guard', 'web');
        DB::table('permissions')->updateOrInsert(['name' => 'Content manage', 'guard_name' => $guard], ['created_at' => $now, 'updated_at' => $now]);
        $pid = DB::table('permissions')->where('name', 'Content manage')->where('guard_name', $guard)->value('id');
        foreach (['Admin', 'Office Staff', 'Data Entry', 'Developer'] as $role) {
            if ($rid = DB::table('roles')->where('name', $role)->value('id')) {
                DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $rid]);
            }
        }
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('content_item_exams');
        Schema::dropIfExists('content_items');
        $ids = DB::table('permissions')->where('name', 'Content manage')->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
