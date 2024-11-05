<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrentaffairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('current_affairs', function (Blueprint $table) {
            $table->id();
            $table->string('year')->nullable(true);
            $table->string('month')->nullable(true);
            $table->string('type')->nullable(true);
            $table->string('day')->nullable(true);
            $table->string('title')->nullable(true);
            $table->string('file_name')->nullable(true);
            $table->text('question')->nullable(true);
            $table->text('answer')->nullable(true);
            $table->text('note')->nullable(true);
            $table->integer('position')->default(0);
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('current_affairs');
    }
}
