<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpsc_ca_daily_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('exam_id');
            $table->timestamp('attend_started_at')->nullable();
            $table->timestamp('attend_ended_at')->nullable();
            $table->string('total')->nullable();
            $table->string('star')->nullable();
            $table->integer('right')->nullable();
            $table->integer('wrong')->nullable();
            $table->integer('skipped')->nullable();
            $table->longText('summary')->nullable();
            $table->enum('status', ['0', '1', '2'])->default('0');
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
        Schema::dropIfExists('kpsc_ca_daily_exam_attempts');
    }
}
