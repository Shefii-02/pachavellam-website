<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Question library of an exam type.
 *
 *   question_folders             folders and sub folders
 *   library_questions            one question (language independent part: folder, right answer, pictures)
 *   library_question_texts       the words of the question in each language (one row for a single language question)
 *   library_question_categories  the categories / sub categories a question belongs to (many)
 *   library_import_batches       every file that was imported, with its result, so it can be reviewed or undone
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_folders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['exam_id', 'parent_id', 'position']);
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
            $t->foreign('parent_id')->references('id')->on('question_folders')->cascadeOnDelete();
        });

        Schema::create('library_import_batches', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('folder_id')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->string('file_name');
            $t->string('file_format', 8);                 // docx | csv | xlsx
            $t->string('file_type', 12);                  // english | native | image | multi
            $t->string('language', 8)->nullable();        // for single language files
            $t->json('category_ids')->nullable();
            $t->unsignedInteger('total_found')->default(0);
            $t->unsignedInteger('imported')->default(0);
            $t->unsignedInteger('skipped')->default(0);
            $t->unsignedInteger('images_stored')->default(0);
            $t->json('problems')->nullable();             // [{row, message}]
            $t->string('status', 10)->default('done');    // done | undone
            $t->timestamp('undone_at')->nullable();
            $t->timestamps();

            $t->index(['exam_id', 'created_at']);
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
            $t->foreign('folder_id')->references('id')->on('question_folders')->nullOnDelete();
        });

        Schema::create('library_questions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->unsignedBigInteger('folder_id');
            $t->unsignedTinyInteger('correct_option');           // 1 - 4
            $t->string('question_image')->nullable();
            $t->json('option_images')->nullable();               // {"1": path, "3": path}
            $t->string('explanation_image')->nullable();
            $t->string('difficulty', 10)->nullable();            // easy | medium | hard
            $t->char('question_hash', 40)->nullable();           // to spot the same question twice in a folder
            $t->string('source', 10)->default('manual');         // manual | docx | csv | xlsx
            $t->unsignedBigInteger('import_batch_id')->nullable();
            $t->boolean('status')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['exam_id', 'folder_id']);
            $t->index(['folder_id', 'question_hash']);
            $t->index('import_batch_id');
            $t->foreign('exam_id')->references('id')->on('competitive_exams')->cascadeOnDelete();
            $t->foreign('folder_id')->references('id')->on('question_folders')->restrictOnDelete();
            $t->foreign('import_batch_id')->references('id')->on('library_import_batches')->nullOnDelete();
        });

        Schema::create('library_question_texts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('question_id');
            $t->string('lang', 8);                                // en | ml | ta | hi | kn
            $t->longText('question')->nullable();
            $t->text('option_1')->nullable();
            $t->text('option_2')->nullable();
            $t->text('option_3')->nullable();
            $t->text('option_4')->nullable();
            $t->text('explanation')->nullable();
            $t->timestamps();

            $t->unique(['question_id', 'lang']);
            $t->foreign('question_id')->references('id')->on('library_questions')->cascadeOnDelete();
        });

        Schema::create('library_question_categories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('question_id');
            $t->unsignedBigInteger('category_id');

            $t->unique(['question_id', 'category_id']);
            $t->index('category_id');
            $t->foreign('question_id')->references('id')->on('library_questions')->cascadeOnDelete();
            $t->foreign('category_id')->references('id')->on('exam_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_question_categories');
        Schema::dropIfExists('library_question_texts');
        Schema::dropIfExists('library_questions');
        Schema::dropIfExists('library_import_batches');
        Schema::dropIfExists('question_folders');
    }
};
