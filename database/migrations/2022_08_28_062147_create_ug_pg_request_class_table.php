<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUgPgRequestClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ug_pg_request_class', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('mobile');
            $table->string('college')->nullable();
            $table->string('university')->nullable();
            $table->string('course')->nullable();
            $table->text('subject')->nullable();
            $table->text('notes')->nullable();
            $table->text('comments')->nullable();
            $table->string('type')->nullable();
            $table->string('semester')->nullable();
            $table->boolean('status')->default(0)->nullable();
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
        Schema::dropIfExists('ug_pg_request_class');
    }
}
