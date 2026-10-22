<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - exam_subjects: groups / folders per exam type (subjects) that free YouTube classes are filed under
 * - content_items.subject_id: the folder of a free promo class
 * - exam_features: "General knowledge exams" next to daily and OMR exams
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id')->index();
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('title');
            $t->string('image')->nullable();
            $t->string('color', 20)->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });

        Schema::table('content_items', function (Blueprint $t) {
            $t->unsignedBigInteger('subject_id')->nullable()->after('type')->index();
        });

        if (!DB::table('exam_features')->where('slug', 'gk-exams')->exists()) {
            $pos = (int) DB::table('exam_features')->max('position') + 1;
            DB::table('exam_features')->insert([
                'slug' => 'gk-exams', 'name' => 'General knowledge exams', 'description' => 'General knowledge exam series.',
                'content_kind' => 'exam', 'supports_subjects' => 0, 'exam_scope' => 'all', 'is_system' => 1, 'position' => $pos, 'status' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('exam_features')->where('slug', 'gk-exams')->delete();
        Schema::table('content_items', fn (Blueprint $t) => $t->dropColumn('subject_id'));
        Schema::dropIfExists('exam_subjects');
    }
};
