<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** How long a student took on an OMR exam (the optional timer on the exam page); the average is shown to everyone. */
return new class extends Migration {
    public function up()
    {
        Schema::create('omr_exam_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exam_id')->index();
            $table->unsignedInteger('seconds');
            $table->timestamps();
            $table->unique(['user_id', 'exam_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('omr_exam_times');
    }
};
