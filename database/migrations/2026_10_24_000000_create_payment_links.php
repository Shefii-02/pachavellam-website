<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Payment links (an order for a course that is paid on a web page - sent on WhatsApp, or opened from the website / Android / iOS app),
 | and the channel (web / android / ios / whatsapp / admin) on every payment so each channel can be analysed on its own.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_links', function (Blueprint $t) {
            $t->id();
            $t->string('token', 48)->unique();
            $t->unsignedBigInteger('exam_id')->index();
            $t->unsignedBigInteger('course_id')->index();
            $t->unsignedBigInteger('user_id')->index();                 // the student who will pay
            $t->string('channel', 8)->default('whatsapp');              // web | android | ios | whatsapp | admin
            $t->string('coupon_code', 40)->nullable();
            $t->string('note', 300)->nullable();
            $t->string('status', 10)->default('active');                // active | paid | cancelled
            $t->dateTime('expires_at')->nullable();
            $t->unsignedBigInteger('admission_id')->nullable()->index(); // the order made when the student pressed Pay
            $t->unsignedInteger('opens')->default(0);
            $t->dateTime('last_opened_at')->nullable();
            $t->dateTime('paid_at')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::table('payment_transactions', function (Blueprint $t) {
            $t->string('channel', 8)->nullable()->after('gateway')->index();
        });

        // payments made before channels existed take the channel of their admission ("app" stays as a legacy value)
        DB::statement('UPDATE payment_transactions t JOIN course_admissions a ON a.id = t.admission_id SET t.channel = a.source WHERE t.channel IS NULL');
    }

    public function down(): void
    {
        Schema::table('payment_transactions', fn (Blueprint $t) => $t->dropColumn('channel'));
        Schema::dropIfExists('payment_links');
    }
};
