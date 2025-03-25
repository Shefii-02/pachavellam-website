<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrevqstnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prev_questionpaper', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('subcategory');
            $table->string('title');
            $table->string('qstn_paper');
            $table->string('ans_key');
            $table->integer('download')->default(0);
            $table->integer('position')->default(0);
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('prev_questionpaper');
    }
}
