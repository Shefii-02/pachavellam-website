<?php

use App\Support\Landing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - content_items.category_id: articles and free videos belong to an exam category / sub category (shown on its /c/{slug} page)
 * - content_items.audio_url / audio: an article's recorded voice (link or uploaded file); with none the apps / website read it aloud
 * - site_pages: custom pages written in the panel (/p/{slug}) and prebuilt pages (an existing address); both can be chosen as a banner / story / push / menu target
 * - site_settings: small key / value settings (the light and dark logos)
 * - menu_items: the side menu of the website (label, target, sub items, order, show / hide)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $t) {
            $t->unsignedBigInteger('category_id')->nullable()->after('folder_id')->index();
            $t->string('audio_url', 500)->nullable();
            $t->string('audio')->nullable();
        });

        Schema::create('site_pages', function (Blueprint $t) {
            $t->id();
            $t->string('kind', 10)->default('custom');          // custom (has a body, lives at /p/{slug}) | prebuilt (points to an existing address)
            $t->string('title');
            $t->string('slug', 80)->unique();                    // custom: the /p/{slug} address; prebuilt: the key used in pickers
            $t->string('path', 200)->nullable();                 // prebuilt: web path, e.g. /free-classes
            $t->longText('body')->nullable();
            $t->string('image')->nullable();
            $t->string('meta_title')->nullable();
            $t->string('meta_description', 300)->nullable();
            $t->boolean('status')->default(true);
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $now = now();
        foreach (Landing::PAGES as $key => [$label, $path]) {
            DB::table('site_pages')->insert(['kind' => 'prebuilt', 'title' => $label, 'slug' => $key, 'path' => $path, 'status' => 1, 'position' => 0, 'created_at' => $now, 'updated_at' => $now]);
        }

        Schema::create('site_settings', function (Blueprint $t) {
            $t->string('key', 60)->primary();                    // logo_light | logo_dark
            $t->text('value')->nullable();
            $t->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('label', 80);
            $t->string('icon', 40)->nullable();                  // bootstrap icon, e.g. bi-play-btn
            $t->string('target_type', 12)->default('link');      // link | course | exam | page | none
            $t->string('target_value', 120)->nullable();
            $t->string('link_url', 500)->nullable();
            $t->boolean('new_tab')->default(false);
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('site_pages');
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn(['category_id', 'audio_url', 'audio']));
    }
};
