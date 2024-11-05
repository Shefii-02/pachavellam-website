<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyExamDiscussionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_exam_discussion', function (Blueprint $table) {
            $table->id();
            $table->string('exam_id')->nullable();
            $table->string('sender_id')->nullable();
            $table->longText('comment')->nullable();
            $table->longText('reply')->nullable();
            $table->longText('reply_id')->nullable();
            $table->string('sender_ip')->nullable();
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
        Schema::dropIfExists('daily_exam_discussion');
    }
}
