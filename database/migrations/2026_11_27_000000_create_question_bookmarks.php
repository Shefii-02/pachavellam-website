<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('question_bookmarks')) {
            return;
        }
        Schema::create('question_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('question_id');
            $table->timestamps();
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'exam_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_bookmarks');
    }
};
