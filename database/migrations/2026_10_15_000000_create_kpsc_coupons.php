<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coupons for KPSC courses.
 *
 * kpsc_coupons              the coupon and its rules (discount, limits, validity, which courses)
 * kpsc_coupon_courses       courses a coupon is limited to (when applies_to = selected)
 * kpsc_coupon_redemptions   every time a coupon was used: who, which course, amounts. Powers the analytics
 *                           and the usage limits (a "reversed" row no longer counts as a use).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpsc_coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();                         // stored upper case
            $t->string('name')->nullable();                           // internal label, e.g. "Onam offer"
            $t->text('description')->nullable();

            $t->string('discount_type', 8)->default('percent');       // percent | fixed
            $t->decimal('discount_value', 10, 2);
            $t->decimal('max_discount', 10, 2)->nullable();           // cap for percent coupons

            $t->decimal('min_purchase', 10, 2)->default(0);           // course price must be at least this
            $t->decimal('max_purchase', 10, 2)->nullable();           // ... and at most this (optional)

            $t->unsignedInteger('total_limit')->nullable();           // null = unlimited uses overall
            $t->unsignedInteger('per_user_limit')->nullable()->default(1);   // null = unlimited per student

            $t->string('applies_to', 8)->default('all');              // all | selected
            $t->dateTime('starts_at')->nullable();
            $t->dateTime('expires_at')->nullable();
            $t->boolean('is_active')->default(true);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['is_active', 'expires_at']);
        });

        Schema::create('kpsc_coupon_courses', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('coupon_id');
            $t->unsignedBigInteger('course_id');
            $t->timestamps();

            $t->unique(['coupon_id', 'course_id']);
            $t->index('course_id');
            $t->foreign('coupon_id')->references('id')->on('kpsc_coupons')->cascadeOnDelete();
            $t->foreign('course_id')->references('id')->on('kpsc_courses')->cascadeOnDelete();
        });

        Schema::create('kpsc_coupon_redemptions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('coupon_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('course_id')->nullable();
            $t->string('code', 40);                                   // snapshot of the code used
            $t->decimal('original_amount', 10, 2);                    // course price before the coupon
            $t->decimal('discount_amount', 10, 2);
            $t->decimal('final_amount', 10, 2);                       // what the student pays
            $t->string('status', 10)->default('redeemed');            // redeemed | reversed
            $t->string('source', 10)->default('app');                 // app | web | admin
            $t->string('reference')->nullable();                      // order / payment id
            $t->timestamp('reversed_at')->nullable();
            $t->string('reversed_reason')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();         // admin who recorded it
            $t->timestamps();

            $t->index(['coupon_id', 'status']);
            $t->index(['user_id', 'coupon_id', 'status']);
            $t->index('course_id');
            $t->index('created_at');
            $t->foreign('coupon_id')->references('id')->on('kpsc_coupons')->restrictOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $t->foreign('course_id')->references('id')->on('kpsc_courses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpsc_coupon_redemptions');
        Schema::dropIfExists('kpsc_coupon_courses');
        Schema::dropIfExists('kpsc_coupons');
    }
};
