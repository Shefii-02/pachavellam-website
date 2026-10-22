<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * request_logs            + platform / app_version / app_build per request
 * user_platform_activity  one row per user per platform: latest app version, device and last active time
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_logs', function (Blueprint $t) {
            $t->string('platform', 10)->nullable()->after('is_api');   // ios | android | web | app (older build, platform unknown)
            $t->string('app_version', 30)->nullable()->after('platform');
            $t->string('app_build', 30)->nullable()->after('app_version');
            $t->index(['platform', 'created_at']);
        });

        Schema::create('user_platform_activity', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('platform', 10);                // ios | android | web | app
            $t->string('app_version', 30)->nullable();
            $t->string('app_build', 30)->nullable();
            $t->string('os_version', 50)->nullable();
            $t->string('device_model', 100)->nullable();   // phone model, or "Chrome on Windows" for web
            $t->string('user_agent', 255)->nullable();
            $t->string('last_ip', 45)->nullable();
            $t->timestamp('first_seen_at')->nullable();
            $t->timestamp('last_active_at')->nullable();
            $t->timestamps();

            $t->unique(['user_id', 'platform']);
            $t->index(['platform', 'last_active_at']);
            $t->index('last_active_at');
            $t->index(['platform', 'app_version']);
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_platform_activity');

        Schema::table('request_logs', function (Blueprint $t) {
            $t->dropIndex(['platform', 'created_at']);
            $t->dropColumn(['platform', 'app_version', 'app_build']);
        });
    }
};
