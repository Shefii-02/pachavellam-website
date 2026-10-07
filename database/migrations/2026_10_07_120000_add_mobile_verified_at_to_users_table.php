<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'mobile_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('mobile_verified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Column pre-exists in production; intentionally left in place.
    }
};
