<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('driver', 30);                       // sms driver used for this send
            $table->boolean('is_new_user')->default(false);     // no account existed when the OTP was requested
            $table->string('otp', 10)->nullable();              // only stored when OTP_LOG_PLAIN=true
            $table->string('status', 20)->default('sent');      // sent | verified | failed | expired
            $table->string('error', 500)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasColumn('users', 'apple_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('apple_id')->nullable()->after('google_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_logs');
        if (Schema::hasColumn('users', 'apple_id')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('apple_id'));
        }
    }
};
