<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Class library (video classes by link: YouTube, CDN, S3 ...), classes placed in courses (free / paid classes),
 | per-user watch tracking, and the text content / date used by premium daily CA entries.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_folders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id')->index();
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('name', 150);
            $t->string('description', 500)->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->foreign('parent_id')->references('id')->on('class_folders')->cascadeOnDelete();
        });

        Schema::create('library_classes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id')->index();
            $t->unsignedBigInteger('folder_id')->index();
            $t->string('title', 250);
            $t->text('description')->nullable();
            $t->longText('details')->nullable();                       // what the class covers (formatted)
            $t->string('source', 10)->default('youtube');              // youtube | cdn | s3 | link
            $t->text('url')->nullable();                               // youtube / cdn / other link
            $t->string('storage_disk', 30)->nullable();                // s3 or another configured disk
            $t->string('storage_path', 500)->nullable();               // path of the file on that disk
            $t->string('thumbnail')->nullable();
            $t->string('instructor', 150)->nullable();
            $t->string('language', 5)->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->date('class_date')->nullable();
            $t->json('tags')->nullable();
            $t->string('notes_url', 500)->nullable();                  // notes / pdf for the class
            $t->string('status', 10)->default('published');            // draft | published | archived
            // watch rules (for the app / web player to enforce)
            $t->unsignedInteger('max_watch_minutes')->nullable();      // total minutes one user may watch (null = no limit)
            $t->unsignedInteger('max_views')->nullable();              // times one user may start the class
            $t->unsignedInteger('access_days')->nullable();            // days of access after the first view
            $t->unsignedTinyInteger('complete_percent')->default(90);  // watched % that counts as completed
            $t->boolean('allow_speed')->default(true);
            $t->boolean('allow_seek')->default(true);
            $t->boolean('allow_download')->default(false);
            $t->boolean('resume_playback')->default(true);
            $t->boolean('watermark_user')->default(false);             // show the student's name / number over the video
            $t->unsignedBigInteger('view_count')->default(0);
            $t->string('legacy_ref', 40)->nullable()->index();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index(['exam_id', 'folder_id', 'status']);
        });

        Schema::create('course_classes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('course_id');
            $t->unsignedBigInteger('feature_id');                      // free-classes | paid-classes
            $t->unsignedBigInteger('class_id');
            $t->unsignedInteger('position')->default(0);
            $t->dateTime('unlock_at')->nullable();
            $t->boolean('is_preview')->default(false);                 // a free sample inside a paid course
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->unique(['course_id', 'feature_id', 'class_id']);
            $t->index('class_id');
            $t->foreign('class_id')->references('id')->on('library_classes')->cascadeOnDelete();
        });

        Schema::create('class_watch_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('class_id');
            $t->unsignedBigInteger('course_id')->nullable();
            $t->unsignedInteger('watched_seconds')->default(0);
            $t->unsignedInteger('last_position_seconds')->default(0);
            $t->unsignedInteger('views')->default(0);
            $t->boolean('completed')->default(false);
            $t->dateTime('first_watched_at')->nullable();
            $t->dateTime('last_watched_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'class_id']);
            $t->index('class_id');
            $t->foreign('class_id')->references('id')->on('library_classes')->cascadeOnDelete();
        });

        Schema::table('course_exams', function (Blueprint $t) {
            $t->date('entry_date')->nullable()->after('title');        // premium daily CA: the day it is for
            $t->longText('body')->nullable()->after('description');    // premium daily CA: the text content
        });

        // free / paid classes have their own screen; premium daily CA is built like an exam (text and / or questions)
        DB::table('exam_features')->whereIn('slug', ['free-classes', 'paid-classes'])->update(['content_kind' => 'classes']);
        DB::table('exam_features')->where('slug', 'premium-daily-ca')->update([
            'content_kind' => 'exam',
            'description' => 'Premium daily current affairs: text content and / or questions, one entry per day.',
        ]);
    }

    public function down(): void
    {
        DB::table('exam_features')->whereIn('slug', ['free-classes', 'paid-classes', 'premium-daily-ca'])->update(['content_kind' => 'flag']);
        Schema::table('course_exams', fn (Blueprint $t) => $t->dropColumn(['entry_date', 'body']));
        Schema::dropIfExists('class_watch_logs');
        Schema::dropIfExists('course_classes');
        Schema::dropIfExists('library_classes');
        Schema::dropIfExists('class_folders');
    }
};
