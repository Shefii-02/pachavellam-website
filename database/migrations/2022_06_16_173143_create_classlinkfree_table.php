<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClasslinkfreeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_link_free', function (Blueprint $table) {
            $table->id();
            $table->string('category_id');
            $table->string('subcategory_id')->default(0)->nullable();
            $table->string('title')->nullable();
            $table->string('link')->nullable();
            $table->string('type')->default(0)->nullable();
            $table->integer('view_count')->default(0);
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
        Schema::dropIfExists('class_link_free');
    }
}
