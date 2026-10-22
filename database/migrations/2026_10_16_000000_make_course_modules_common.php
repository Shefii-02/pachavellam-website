<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Categories, courses, features and coupons were built once per exam (kpsc_*, rrb_categories, ...).
 * They are the same for every exam type, so they become common tables that carry an exam type:
 *
 *   competitive_exams (the exam types, managed in General)
 *     exam_categories          tree of categories / sub categories, per exam type          (was kpsc_categories, rrb_categories, ...)
 *     exam_features            what a course can include; available to all exam types or chosen ones   (was kpsc_features)
 *     exam_courses             a course belongs to one exam type                            (was kpsc_courses)
 *       exam_course_features / exam_course_staff / exam_course_nodes / exam_course_node_links / exam_course_categories
 *     exam_coupons             a coupon works for all exam types or chosen ones             (was kpsc_coupons)
 *       exam_coupon_exams / exam_coupon_courses / exam_coupon_redemptions
 *
 * Existing rows are carried over (they all belong to KPSC). The per-exam category tables are dropped once their rows are copied.
 * Subject based features now pick CATEGORIES of the course's exam type (for KTET and UGC NET the categories are the subjects).
 */
return new class extends Migration
{
    private array $oldCategoryTables = [
        'kpsc' => 'kpsc_categories',
        'rrb' => 'rrb_categories',
        'ssc' => 'ssc_categories',
        'ktet' => 'ktet_categories',
        'ugc-net' => 'ugc_net_categories',
    ];

    public function up(): void
    {
        $kpscId = DB::table('competitive_exams')->where('slug', 'kpsc')->value('id');

        // ---------------------------------------------------------------- categories
        Schema::create('exam_categories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('name');
            $t->string('slug');
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('icon')->nullable();
            $t->unsignedTinyInteger('depth')->default(0);
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->boolean('is_featured')->default(false);
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->json('settings')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->unique(['exam_id', 'slug']);
            $t->index(['exam_id', 'parent_id', 'position']);
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
            $t->foreign('parent_id')->references('id')->on('exam_categories')->cascadeOnDelete();
        });

        // copy rows of the old per-exam tables, keeping the tree (old id => new id)
        $idMap = [];
        foreach ($this->oldCategoryTables as $slug => $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $examId = DB::table('competitive_exams')->where('slug', $slug)->value('id');
            if (!$examId) {
                continue;
            }
            $rows = DB::table($table)->orderBy('depth')->orderBy('id')->get();
            foreach ($rows as $row) {
                $new = (array) $row;
                $oldId = $new['id'];
                unset($new['id']);
                $new['exam_id'] = $examId;
                $new['parent_id'] = $row->parent_id ? ($idMap[$slug][$row->parent_id] ?? null) : null;
                $idMap[$slug][$oldId] = DB::table('exam_categories')->insertGetId($new);
            }
        }

        // courses used kpsc_categories ids -> point them at the copies
        if (Schema::hasTable('kpsc_course_categories')) {
            Schema::table('kpsc_course_categories', function (Blueprint $t) {
                $t->dropForeign(['category_id']);
            });
            foreach (DB::table('kpsc_course_categories')->get() as $row) {
                $new = $idMap['kpsc'][$row->category_id] ?? null;
                $new ? DB::table('kpsc_course_categories')->where('id', $row->id)->update(['category_id' => $new])
                     : DB::table('kpsc_course_categories')->where('id', $row->id)->delete();
            }
        }
        foreach (DB::table('kpsc_courses')->whereNotNull('category_id')->get(['id', 'category_id']) as $c) {
            DB::table('kpsc_courses')->where('id', $c->id)->update(['category_id' => $idMap['kpsc'][$c->category_id] ?? null]);
        }

        foreach ($this->oldCategoryTables as $table) {
            Schema::dropIfExists($table);
        }

        // ------------------------------------------------------------------ features
        Schema::rename('kpsc_features', 'exam_features');
        Schema::table('exam_features', function (Blueprint $t) {
            $t->string('exam_scope', 8)->default('all')->after('supports_subjects');   // all | selected
        });
        Schema::create('exam_feature_exams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('feature_id');
            $t->unsignedBigInteger('exam_id');
            $t->timestamps();
            $t->unique(['feature_id', 'exam_id']);
            $t->foreign('feature_id')->references('id')->on('exam_features')->cascadeOnDelete();
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });

        // ------------------------------------------------------------------- courses
        Schema::rename('kpsc_courses', 'exam_courses');
        Schema::table('exam_courses', function (Blueprint $t) {
            $t->unsignedBigInteger('exam_id')->nullable()->after('id')->index();
        });
        DB::table('exam_courses')->update(['exam_id' => $kpscId]);
        if ($kpscId) {
            DB::statement('ALTER TABLE exam_courses MODIFY exam_id BIGINT UNSIGNED NOT NULL');
        }
        Schema::table('exam_courses', function (Blueprint $t) {
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->restrictOnDelete();
        });

        Schema::rename('kpsc_course_features', 'exam_course_features');
        Schema::table('exam_course_features', function (Blueprint $t) {
            $t->renameColumn('subject_ids', 'category_ids');
        });
        // "subject based" used to point at KPSC subjects; it now points at categories, so old selections are reset
        DB::table('exam_course_features')->where('scope', 'subject')->update(['scope' => 'category', 'category_ids' => null]);
        DB::table('exam_course_features')->where('scope', 'category')->update(['scope' => 'common']);

        Schema::rename('kpsc_course_staff', 'exam_course_staff');
        Schema::rename('kpsc_course_nodes', 'exam_course_nodes');
        Schema::rename('kpsc_course_node_links', 'exam_course_node_links');
        Schema::rename('kpsc_course_categories', 'exam_course_categories');
        Schema::table('exam_course_categories', function (Blueprint $t) {
            $t->foreign('category_id')->references('id')->on('exam_categories')->cascadeOnDelete();
        });

        // ------------------------------------------------------------------- coupons
        Schema::rename('kpsc_coupons', 'exam_coupons');
        Schema::table('exam_coupons', function (Blueprint $t) {
            $t->string('exam_scope', 8)->default('all')->after('per_user_limit');   // all | selected exam types
        });
        Schema::create('exam_coupon_exams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('coupon_id');
            $t->unsignedBigInteger('exam_id');
            $t->timestamps();
            $t->unique(['coupon_id', 'exam_id']);
            $t->index('exam_id');
            $t->foreign('coupon_id')->references('id')->on('exam_coupons')->cascadeOnDelete();
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
        });
        Schema::rename('kpsc_coupon_courses', 'exam_coupon_courses');
        Schema::rename('kpsc_coupon_redemptions', 'exam_coupon_redemptions');
        Schema::table('exam_coupon_redemptions', function (Blueprint $t) {
            $t->unsignedBigInteger('exam_id')->nullable()->after('course_id')->index();   // snapshot, so analytics can group by exam type
        });
        DB::statement('UPDATE exam_coupon_redemptions r JOIN exam_courses c ON c.id = r.course_id SET r.exam_id = c.exam_id');
    }

    public function down(): void
    {
        // The per-exam tables were merged into common ones; there is no meaningful way back.
        throw new RuntimeException('This migration merges per-exam tables into common tables and cannot be rolled back.');
    }
};
