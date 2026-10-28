<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - device_sessions: every signed in device (website browser or app) of a person; can be removed from the panel or by the person
 * - users.device_limit: per person override of the device limit (null = the common limit)
 * - blocked_ips: IPs blocked (or always allowed) by the security gate
 * - ad_units: Google ads (AdMob in the apps, AdSense on the website) - what shows, where, and how often
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_sessions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('sid', 40)->unique();                    // carried inside the app token / website session
            $t->string('channel', 4);                            // web | app
            $t->string('platform', 10)->nullable();              // android | ios | web | app
            $t->string('device_name', 120)->nullable();
            $t->string('os_version', 50)->nullable();
            $t->string('app_version', 30)->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('last_active_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->string('revoked_by', 20)->nullable();            // user | admin | limit | logout | other-devices
            $t->timestamps();
            $t->index(['user_id', 'revoked_at']);
            $t->index(['channel', 'revoked_at', 'last_active_at']);
        });
        Schema::table('users', fn (Blueprint $t) => $t->unsignedTinyInteger('device_limit')->nullable());

        Schema::create('blocked_ips', function (Blueprint $t) {
            $t->id();
            $t->string('ip', 45)->unique();
            $t->string('kind', 5)->default('block');            // block | allow
            $t->string('reason', 150)->nullable();
            $t->timestamp('until')->nullable();                  // null = until removed
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('ad_units', function (Blueprint $t) {
            $t->id();
            $t->string('platform', 4);                           // app | web
            $t->string('type', 20);                              // banner | native | interstitial | rewarded | app_open | web_banner | web_infeed | web_article
            $t->string('label', 80);
            $t->string('unit_android', 80)->nullable();
            $t->string('unit_ios', 80)->nullable();
            $t->string('slot', 40)->nullable();                  // web: AdSense ad slot id
            $t->boolean('enabled')->default(false);
            $t->json('pages')->nullable();                       // page keys where it shows (see App\Support\AdPages)
            $t->unsignedSmallInteger('every_n')->default(0);     // in a list: one ad after every N items (0 = none)
            $t->unsignedSmallInteger('max_per_page')->default(2);// in a paged list: at most this many ads on one page of results
            $t->unsignedSmallInteger('start_page')->default(1);  // in a paged list: ads begin from this page number
            $t->unsignedSmallInteger('cap_minutes')->default(3); // full screen ads: least minutes between two
            $t->boolean('hide_for_paid')->default(false);        // students with a bought course see no ad of this kind
            $t->timestamps();
            $t->unique(['platform', 'type']);
        });
        $now = now();
        // Google's public test ads: safe defaults, all switched off until the real ids are entered
        // where each ad goes by default: reading / browsing pages only - never sign in, payment, tests or forms
        $seed = [
            // platform, type, label, android, ios, slot, every_n, pages, cap_minutes, hide_for_paid
            ['app', 'banner', 'Banner (bottom of a page)', 'ca-app-pub-3940256099942544/6300978111', 'ca-app-pub-3940256099942544/2934735716', null, 0, ['home', 'exam', 'articles', 'article_detail', 'category', 'classes', 'courses'], 3, 0],
            ['app', 'native', 'Native (inside lists)', 'ca-app-pub-3940256099942544/2247696110', 'ca-app-pub-3940256099942544/3986624511', null, 6, ['articles', 'classes', 'courses'], 3, 0],
            ['app', 'interstitial', 'Interstitial (after finishing a page)', 'ca-app-pub-3940256099942544/1033173712', 'ca-app-pub-3940256099942544/4411468910', null, 0, ['article_detail', 'class_player'], 10, 1],
            ['app', 'rewarded', 'Rewarded video', 'ca-app-pub-3940256099942544/5224354917', 'ca-app-pub-3940256099942544/1712485313', null, 0, [], 3, 1],
            ['app', 'app_open', 'App open (after a long break)', 'ca-app-pub-3940256099942544/9257395921', 'ca-app-pub-3940256099942544/5575463023', null, 0, [], 30, 1],
            ['web', 'web_banner', 'Website banner (top of a page)', null, null, '', 0, ['articles', 'classes', 'category', 'article_detail'], 3, 0],
            ['web', 'web_infeed', 'Website in-feed (inside lists)', null, null, '', 6, ['articles', 'classes'], 3, 0],
            ['web', 'web_article', 'Website in-article (after the article)', null, null, '', 0, ['article_detail'], 3, 0],
        ];
        foreach ($seed as [$platform, $type, $label, $a, $i, $slot, $every, $pages, $cap, $paid]) {
            DB::table('ad_units')->insert(['platform' => $platform, 'type' => $type, 'label' => $label, 'unit_android' => $a, 'unit_ios' => $i, 'slot' => $slot, 'enabled' => 0, 'pages' => json_encode($pages), 'every_n' => $every,
                'max_per_page' => 2, 'start_page' => 1, 'cap_minutes' => $cap, 'hide_for_paid' => $paid, 'created_at' => $now, 'updated_at' => $now]);
        }
        // calm rules for full screen ads: none in the first minutes of a visit, a few at most per visit
        foreach (['ads_quiet_minutes' => '3', 'ads_session_max' => '3'] as $k => $v) {
            DB::table('site_settings')->updateOrInsert(['key' => $k], ['value' => $v, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_units');
        Schema::dropIfExists('blocked_ips');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('device_limit'));
        Schema::dropIfExists('device_sessions');
    }
};
