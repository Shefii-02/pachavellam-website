<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePscDailyBucketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psc_daily_buckets', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->text('type');
            $table->text('title');
            $table->text('content')->nullable();
            $table->text('class_date')->nullable();
            $table->text('class_time')->nullable();
            $table->integer('attempt')->default(0)->nullable();
            $table->integer('position')->default(1)->nullable();
            $table->boolean('status')->default(1)->nullable();
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
        Schema::dropIfExists('psc_daily_buckets');
    }
}
