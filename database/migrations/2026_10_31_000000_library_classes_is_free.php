<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A library class can be marked "free for everyone": it is shown on the website / app home for its exam type without enrolment. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_classes', function (Blueprint $t) {
            $t->boolean('is_free')->default(false)->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('library_classes', fn (Blueprint $t) => $t->dropColumn('is_free'));
    }
};
