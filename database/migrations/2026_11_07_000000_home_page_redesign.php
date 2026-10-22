<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Home page redesign:
 *   competitive_exams.image   picture on the exam type tab ("What are you preparing for?")
 *   counselling_requests      "Talk to a counsellor" form on the home page, followed up under Website content > Counselling
 * Associate partners, key points and FAQ groups are content_items (kinds partner / key_point, faq.type), so need no table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('competitive_exams', 'image')) {
            Schema::table('competitive_exams', function (Blueprint $t) {
                $t->string('image')->nullable()->after('color');
            });
        }

        Schema::create('counselling_requests', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('phone', 20);
            $t->string('email', 150)->nullable();
            $t->unsignedBigInteger('exam_id')->nullable()->index();   // exam type the student is preparing for
            $t->string('preferred_time', 40)->nullable();
            $t->text('message')->nullable();
            $t->unsignedBigInteger('user_id')->nullable()->index();   // signed in student, when there is one
            $t->string('status', 12)->default('new');                // new | contacted | closed
            $t->text('staff_note')->nullable();
            $t->unsignedBigInteger('handled_by')->nullable();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselling_requests');
        if (Schema::hasColumn('competitive_exams', 'image')) {
            Schema::table('competitive_exams', fn (Blueprint $t) => $t->dropColumn('image'));
        }
    }
};
