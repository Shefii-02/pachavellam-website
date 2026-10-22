<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - landing target on banners / stories / other content: a custom link, a course, an exam type or a named page
 * - push_campaigns: every push sent from the panel and how many devices it reached
 * - device_tokens: remember which device it is (a person can have several: android, ios, web)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $t) {
            $t->string('target_type', 12)->default('link')->after('link_url');   // link | course | exam | page | none
            $t->string('target_value', 120)->nullable()->after('target_type');   // course id / exam slug / page key
        });
        Schema::table('banner_slider', function (Blueprint $t) {
            $t->string('target_type', 12)->default('link');
            $t->string('target_value', 120)->nullable();
        });
        Schema::table('device_tokens', function (Blueprint $t) {
            $t->string('device_name', 120)->nullable()->after('platform');
            $t->string('app_version', 30)->nullable()->after('device_name');
        });

        Schema::create('push_campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('body');
            $t->string('image')->nullable();
            $t->string('target_type', 12)->default('none');
            $t->string('target_value', 120)->nullable();
            $t->string('audience', 20)->default('all');        // all | exams | users
            $t->json('audience_exam_ids')->nullable();
            $t->json('platforms')->nullable();                  // null = every platform
            $t->unsignedBigInteger('content_item_id')->nullable();
            $t->unsignedInteger('users')->default(0);
            $t->unsignedInteger('devices')->default(0);
            $t->unsignedInteger('delivered')->default(0);
            $t->unsignedInteger('failed')->default(0);
            $t->string('status', 12)->default('queued');       // queued | sent | no_credentials | no_devices
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_campaigns');
        Schema::table('device_tokens', fn (Blueprint $t) => $t->dropColumn(['device_name', 'app_version']));
        Schema::table('banner_slider', fn (Blueprint $t) => $t->dropColumn(['target_type', 'target_value']));
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn(['target_type', 'target_value']));
    }
};
