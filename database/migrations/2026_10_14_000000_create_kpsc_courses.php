<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * KPSC courses (LMS).
 *
 * kpsc_features           master list of things a course can include (daily exams, OMR exams, materials, ...)
 * kpsc_courses            the course itself
 * kpsc_course_features    which features a course includes (+ exam scope: common / subject based)
 * kpsc_course_staff       staff and teachers who have access to a course (many per course)
 * kpsc_course_nodes       course content tree: folders > sub folders > files / articles
 * kpsc_course_node_links  a file linked to other content (e.g. a question paper -> its quick learn notes)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpsc_features', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 60)->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('icon', 60)->default('bi-star');
            // tree = course has its own folders/files/articles, exam = exams (common or subject based), flag = course includes an existing section
            $t->string('content_kind', 10)->default('flag');
            $t->boolean('supports_subjects')->default(false);   // exam features: can be common or per subject
            $t->boolean('is_system')->default(false);           // seeded ones cannot be deleted
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->timestamps();
        });

        Schema::create('kpsc_courses', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('short_description', 300)->nullable();
            $t->longText('description')->nullable();
            $t->string('cover_image')->nullable();
            $t->string('promo_video_url', 500)->nullable();
            $t->unsignedBigInteger('category_id')->nullable()->index();   // kpsc_categories
            $t->string('level', 12)->default('all');                      // beginner | intermediate | advanced | all
            $t->string('language', 6)->default('ml');                     // ml | en | both
            $t->string('course_type', 6)->default('free');                // free | paid
            $t->decimal('price', 10, 2)->default(0);
            $t->decimal('discount_price', 10, 2)->nullable();
            $t->string('validity_type', 10)->default('lifetime');         // lifetime | days | date
            $t->unsignedInteger('validity_days')->nullable();
            $t->date('valid_until')->nullable();
            $t->date('starts_on')->nullable();
            $t->date('ends_on')->nullable();
            $t->decimal('duration_hours', 6, 1)->nullable();
            $t->json('highlights')->nullable();                           // "what you will learn"
            $t->json('requirements')->nullable();
            $t->text('audience')->nullable();
            $t->json('tags')->nullable();
            $t->boolean('certificate_enabled')->default(false);
            $t->boolean('is_featured')->default(false);
            $t->string('status', 10)->default('draft');                   // draft | published | archived
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['status', 'position']);
        });

        Schema::create('kpsc_course_features', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('feature_id');
            $t->string('scope', 10)->default('common');       // common = all subjects | subject = only the chosen subjects
            $t->json('subject_ids')->nullable();               // kpsc_subjects.id
            $t->string('note')->nullable();
            $t->timestamps();

            $t->unique(['course_id', 'feature_id']);
            $t->foreign('course_id')->references('id')->on('kpsc_courses')->cascadeOnDelete();
            $t->foreign('feature_id')->references('id')->on('kpsc_features')->cascadeOnDelete();
        });

        Schema::create('kpsc_course_staff', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('user_id');
            $t->string('member_role', 10)->default('staff');   // teacher | staff
            $t->boolean('can_edit')->default(true);             // false = can only view the course
            $t->timestamps();

            $t->unique(['course_id', 'user_id']);
            $t->index('user_id');
            $t->foreign('course_id')->references('id')->on('kpsc_courses')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('kpsc_course_nodes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('feature_id');
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->string('node_type', 8);                         // folder | file | article
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('file_kind', 8)->nullable();             // pdf | doc | audio | video | image | link | other
            $t->string('file_path')->nullable();
            $t->string('file_name')->nullable();                // original name, shown to students
            $t->unsignedBigInteger('file_size')->nullable();
            $t->string('external_url', 500)->nullable();
            $t->string('audio_path')->nullable();               // voice version of an article
            $t->longText('body')->nullable();                   // article text
            $t->boolean('is_free_preview')->default(false);
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['course_id', 'feature_id', 'parent_id', 'position'], 'kpsc_course_nodes_tree');
            $t->foreign('course_id')->references('id')->on('kpsc_courses')->cascadeOnDelete();
            $t->foreign('feature_id')->references('id')->on('kpsc_features')->cascadeOnDelete();
            $t->foreign('parent_id')->references('id')->on('kpsc_course_nodes')->cascadeOnDelete();
        });

        Schema::create('kpsc_course_node_links', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('node_id');
            $t->unsignedBigInteger('related_node_id');
            $t->string('relation', 15)->default('notes');     // notes | solution | related
            $t->timestamps();

            $t->unique(['node_id', 'related_node_id']);
            $t->foreign('node_id')->references('id')->on('kpsc_course_nodes')->cascadeOnDelete();
            $t->foreign('related_node_id')->references('id')->on('kpsc_course_nodes')->cascadeOnDelete();
        });

        $now = now();
        foreach ([
            // slug, name, description, icon, kind, supports_subjects
            ['daily-exams', 'Daily exams', 'Daily practice exams, common to all subjects or for chosen subjects.', 'bi-calendar-check', 'exam', true],
            ['omr-exams', 'OMR exams', 'Model / OMR exam series.', 'bi-pencil-square', 'exam', true],
            ['special-exams', 'Special exams', 'Subject based or topic based special exams.', 'bi-award', 'exam', true],
            ['materials', 'Study materials', 'Folders and sub folders of files: study material, syllabus, exam oriented special notes, quick learn notes, audio and more.', 'bi-folder2-open', 'tree', false],
            ['articles', 'Articles', 'Current affairs, history and other articles, with a voice version to listen to.', 'bi-newspaper', 'tree', false],
            ['previous-questions', 'Previous question papers', 'Year > exam stage > question paper, each linkable to quick learn notes.', 'bi-journal-text', 'tree', false],
            ['free-classes', 'Free classes', 'Includes the free class section.', 'bi-play-circle', 'flag', false],
            ['paid-classes', 'Paid classes', 'Includes the paid class section.', 'bi-play-btn-fill', 'flag', false],
            ['premium-daily-ca', 'Premium daily CA', 'Premium daily current affairs.', 'bi-stars', 'flag', false],
            ['psc-bulletin', 'PSC bulletin', 'Includes the PSC bulletin section.', 'bi-book', 'flag', false],
        ] as $i => [$slug, $name, $desc, $icon, $kind, $subjects]) {
            DB::table('kpsc_features')->insert([
                'slug' => $slug, 'name' => $name, 'description' => $desc, 'icon' => $icon,
                'content_kind' => $kind, 'supports_subjects' => $subjects, 'is_system' => true,
                'position' => $i + 1, 'status' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kpsc_course_node_links');
        Schema::dropIfExists('kpsc_course_nodes');
        Schema::dropIfExists('kpsc_course_staff');
        Schema::dropIfExists('kpsc_course_features');
        Schema::dropIfExists('kpsc_courses');
        Schema::dropIfExists('kpsc_features');
    }
};
