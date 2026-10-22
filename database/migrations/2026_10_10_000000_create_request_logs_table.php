<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $t) {
            $t->id();
            $t->boolean('is_api')->default(false);          // true = mobile app / api, false = website
            $t->string('method', 10);
            $t->string('path', 255);                         // no query string (may hold tokens)
            $t->string('route_name', 150)->nullable();
            $t->unsignedSmallInteger('status');
            $t->unsignedInteger('duration_ms')->default(0);
            $t->unsignedBigInteger('bytes_in')->default(0);
            $t->unsignedBigInteger('bytes_out')->default(0);
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->string('error', 500)->nullable();            // short message for 5xx
            $t->timestamp('created_at')->useCurrent();

            $t->index(['created_at', 'is_api']);
            $t->index(['is_api', 'status']);
            $t->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
