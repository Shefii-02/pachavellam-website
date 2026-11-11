<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * competitive_exams.features: the sidebar features an exam type has (null = all), chosen in General > Exam Types.
 * role_exam_access: the exam types a role gives to everyone who has the role (added to what is ticked for the person).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitive_exams', function (Blueprint $t) {
            $t->json('features')->nullable()->after('performance_source');
        });
        Schema::create('role_exam_access', function (Blueprint $t) {
            $t->unsignedBigInteger('role_id');
            $t->unsignedBigInteger('exam_id');
            $t->primary(['role_id', 'exam_id']);
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_exam_access');
        Schema::table('competitive_exams', fn (Blueprint $t) => $t->dropColumn('features'));
    }
};
