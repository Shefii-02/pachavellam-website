<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewmodalqaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_modal_qa', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('options');
            $table->text('currect_ans');
            $table->string('subject')->nullable();
            $table->integer('attempt')->default(0)->nullable();
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
        Schema::dropIfExists('new_modal_qa');
    }
}
