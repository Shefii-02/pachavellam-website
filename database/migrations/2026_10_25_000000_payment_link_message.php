<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* The message that goes with a payment link (WhatsApp / SMS / email text), and the staff order made together with it. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_links', function (Blueprint $t) {
            $t->text('message')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('payment_links', fn (Blueprint $t) => $t->dropColumn('message'));
    }
};
