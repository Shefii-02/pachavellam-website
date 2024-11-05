<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionPapersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_papers', function (Blueprint $table) {
            $table->id();
            $table->string('university_name');
            $table->string('level_name');
            $table->string('course_name');
            $table->string('semester_name');
            $table->string('subject_name'); 
            $table->string('year');
            $table->string('title');
            $table->text('type')->nullable();
            $table->string('name_slug');
            $table->text('content');
            $table->text('notes')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('status')->default(0);
            $table->integer('author_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_papers');
    }
}
