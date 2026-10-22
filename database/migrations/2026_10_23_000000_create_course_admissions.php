<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Course admissions (a student joining a course), the payments behind them (Razorpay / PhonePe / cash added by staff),
 | and the setting that switches the active payment gateway.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_admissions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id')->index();
            $t->unsignedBigInteger('course_id')->index();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('status', 10)->default('pending');            // pending | active | cancelled
            $t->string('source', 8)->default('app');                  // app | web | admin
            $t->string('gateway', 10)->nullable();                    // razorpay | phonepe | cash | upi | bank | free
            $t->decimal('original_amount', 10, 2)->default(0);        // course price at the time
            $t->decimal('discount_amount', 10, 2)->default(0);        // coupon / staff discount
            $t->decimal('amount', 10, 2)->default(0);                 // what the student has to pay
            $t->decimal('amount_paid', 10, 2)->default(0);            // what actually came in
            $t->decimal('refunded_amount', 10, 2)->default(0);
            $t->unsignedBigInteger('coupon_redemption_id')->nullable();
            $t->string('coupon_code', 40)->nullable();
            $t->decimal('commission_percent', 5, 2)->default(0);      // % taken from the retained amount, as set when it was paid
            $t->decimal('commission_amount', 10, 2)->default(0);
            $t->dateTime('admitted_at')->nullable();
            $t->dateTime('valid_until')->nullable();                   // null = lifetime
            $t->dateTime('cancelled_at')->nullable();
            $t->string('cancel_reason', 300)->nullable();
            $t->unsignedBigInteger('cancelled_by')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();         // staff who added it (backend admission)
            $t->timestamps();
            $t->softDeletes();
            $t->index(['course_id', 'user_id', 'status']);
            $t->index(['status', 'admitted_at']);
        });

        Schema::create('payment_transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('admission_id')->index();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('course_id')->index();
            $t->unsignedBigInteger('exam_id')->index();
            $t->string('type', 8)->default('payment');                // payment | refund
            $t->string('gateway', 10);                                // razorpay | phonepe | cash | upi | bank | free
            $t->string('merchant_ref', 40)->unique();                 // our order number, sent to the gateway
            $t->string('gateway_order_id', 100)->nullable()->index();
            $t->string('gateway_payment_id', 100)->nullable()->index();
            $t->decimal('amount', 10, 2);
            $t->string('currency', 3)->default('INR');
            $t->string('status', 10)->default('created');             // created | pending | success | failed
            $t->string('method', 30)->nullable();                     // upi, card, netbanking ...
            $t->string('failure_reason', 300)->nullable();
            $t->json('request')->nullable();
            $t->json('response')->nullable();
            $t->dateTime('paid_at')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index(['status', 'paid_at']);
        });

        Schema::create('payment_settings', function (Blueprint $t) {
            $t->string('key', 60)->primary();
            $t->text('value')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('course_admissions');
    }
};
