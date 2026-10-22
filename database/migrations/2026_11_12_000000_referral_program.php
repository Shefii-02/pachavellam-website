<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->string('referral_code', 12)->nullable()->unique()->after('reffered_to'));

        // who invited whom (one row per invited student)
        Schema::create('referrals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('referrer_id')->index();
            $t->unsignedBigInteger('referred_id')->unique();
            $t->string('code', 12);
            $t->string('platform', 10)->default('web');          // web | android | ios
            $t->string('status', 12)->default('joined');         // joined | rewarded | rejected
            $t->integer('referrer_coins')->default(0);
            $t->integer('referred_coins')->default(0);
            $t->timestamp('rewarded_at')->nullable();
            $t->string('note', 200)->nullable();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });

        // every coin a student gets that is not from a test (referral rewards, admin bonus, corrections)
        Schema::create('coin_ledger', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->integer('coins');                                // negative to take coins back
            $t->string('type', 30);                              // referral_referrer | referral_welcome | bonus | adjustment
            $t->unsignedBigInteger('ref_id')->nullable();        // referrals.id for referral rewards
            $t->string('note', 200)->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['type', 'ref_id']);
        });

        Schema::create('portal_settings', function (Blueprint $t) {
            $t->string('key', 60)->primary();
            $t->text('value')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
        DB::table('portal_settings')->insert([
            ['key' => 'referral.enabled', 'value' => '1', 'updated_at' => now()],
            ['key' => 'referral.referrer_coins', 'value' => '50', 'updated_at' => now()],
            ['key' => 'referral.referred_coins', 'value' => '20', 'updated_at' => now()],
            ['key' => 'referral.qualify', 'value' => 'first_test', 'updated_at' => now()],   // signup | first_test
            ['key' => 'referral.max_per_user', 'value' => '0', 'updated_at' => now()],       // 0 = no limit
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_settings');
        Schema::dropIfExists('coin_ledger');
        Schema::dropIfExists('referrals');
        Schema::table('users', function (Blueprint $t) {
            $t->dropUnique(['referral_code']);
            $t->dropColumn('referral_code');
        });
    }
};
