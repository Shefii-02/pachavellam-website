<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 | Roaring Study (was "Roaring 40's" / daily buckets): timed study packs made of sections of notes, PDFs, voice notes, links and
 | recorded classes. A pack is tagged to one or more exam types and is either open to everyone (free) or limited to courses.
 | The old psc_daily_buckets rows are carried over (one pack per class date, one section per day title); the old table is kept.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('roaring_studies', function (Blueprint $t) {
            $t->id();
            $t->string('title', 200);
            $t->text('description')->nullable();
            $t->string('cover_image')->nullable();
            $t->string('access_type', 8)->default('global');       // global (free, everyone) | course (only students of the chosen courses)
            $t->dateTime('publish_at')->nullable();                 // when it opens - every pack has its own time
            $t->dateTime('end_at')->nullable();
            $t->string('status', 10)->default('draft');             // draft | published | archived
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->string('legacy_ref', 40)->nullable()->index();
            $t->timestamps();
            $t->index(['status', 'publish_at']);
        });

        Schema::create('roaring_study_exams', function (Blueprint $t) {
            $t->unsignedBigInteger('roaring_id');
            $t->unsignedBigInteger('exam_id');
            $t->primary(['roaring_id', 'exam_id']);
            $t->index('exam_id');
            $t->foreign('roaring_id')->references('id')->on('roaring_studies')->cascadeOnDelete();
        });

        Schema::create('roaring_study_courses', function (Blueprint $t) {
            $t->unsignedBigInteger('roaring_id');
            $t->unsignedBigInteger('course_id');
            $t->primary(['roaring_id', 'course_id']);
            $t->index('course_id');
            $t->foreign('roaring_id')->references('id')->on('roaring_studies')->cascadeOnDelete();
        });

        Schema::create('roaring_sections', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('roaring_id')->index();
            $t->string('title', 200);
            $t->unsignedInteger('position')->default(0);
            $t->dateTime('release_at')->nullable();                 // a section may open later than the pack
            $t->timestamps();
            $t->foreign('roaring_id')->references('id')->on('roaring_studies')->cascadeOnDelete();
        });

        Schema::create('roaring_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('section_id')->index();
            $t->string('type', 8);                                  // text | pdf | voice | link | class
            $t->string('title', 250);
            $t->longText('content')->nullable();                    // the text, or the link
            $t->string('file_path')->nullable();                    // pdf / voice file
            $t->unsignedBigInteger('class_id')->nullable()->index();  // a class of the class library
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->timestamps();
            $t->foreign('section_id')->references('id')->on('roaring_sections')->cascadeOnDelete();
        });

        $this->carryOverOldBuckets();
    }

    private function carryOverOldBuckets(): void
    {
        if (!Schema::hasTable('psc_daily_buckets')) {
            return;
        }
        $kpsc = DB::table('competitive_exams')->where('slug', 'kpsc')->value('id');
        if (!$kpsc) {
            return;
        }

        $typeMap = ['Pdf' => 'pdf', 'Voice Msg' => 'voice', 'Link' => 'link', 'Text Msg' => 'text'];
        $now = now();
        $packs = [];
        $sections = [];

        foreach (DB::table('psc_daily_buckets')->orderBy('class_date')->orderBy('position')->orderBy('id')->get() as $row) {
            $type = $typeMap[$row->type] ?? null;
            if (!$type) {
                continue;
            }
            // a file item whose file is no longer on disk would show nothing: leave it out (and do not create an empty pack for it)
            $file = null;
            $content = $row->content;
            if (in_array($type, ['pdf', 'voice'], true)) {
                $old = 'daily-buckets/' . $row->content;
                if (!$row->content || !Storage::disk('public')->exists($old)) {
                    continue;
                }
                $file = 'roaring-study/' . Str::random(40) . '.' . pathinfo($row->content, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($old, $file);   // a copy, so the old module keeps its own file
                $content = null;
            }

            $date = $row->class_date ?: substr((string) $row->created_at, 0, 10);
            $time = $row->class_time ?: '00:00:00';

            $packs[$date] ??= DB::table('roaring_studies')->insertGetId([
                'title' => 'Roaring Study ' . date('d M Y', strtotime($date)), 'access_type' => 'global', 'status' => 'published',
                'publish_at' => $date . ' ' . substr($time, 0, 8), 'position' => 0, 'legacy_ref' => 'bucket-date:' . $date,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            if (!DB::table('roaring_study_exams')->where('roaring_id', $packs[$date])->exists()) {
                DB::table('roaring_study_exams')->insert(['roaring_id' => $packs[$date], 'exam_id' => $kpsc]);
            }

            $sectionTitle = trim((string) $row->day_title) !== '' ? trim($row->day_title) : 'General';
            $sections[$date . '|' . $sectionTitle] ??= DB::table('roaring_sections')->insertGetId([
                'roaring_id' => $packs[$date], 'title' => mb_substr($sectionTitle, 0, 200), 'position' => count($sections), 'created_at' => $now, 'updated_at' => $now,
            ]);

            DB::table('roaring_items')->insert([
                'section_id' => $sections[$date . '|' . $sectionTitle], 'type' => $type, 'title' => mb_substr((string) ($row->title ?: 'Untitled'), 0, 250),
                'content' => $content, 'file_path' => $file, 'position' => (int) $row->position, 'status' => (bool) $row->status,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roaring_items');
        Schema::dropIfExists('roaring_sections');
        Schema::dropIfExists('roaring_study_courses');
        Schema::dropIfExists('roaring_study_exams');
        Schema::dropIfExists('roaring_studies');
    }
};
