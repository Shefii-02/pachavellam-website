<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactDbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_db', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('mobile');
            $table->string('college')->nullable();
            $table->string('university')->nullable();
            $table->string('course')->nullable();
            $table->string('place')->nullable();
            $table->string('district')->nullable();
            $table->string('name_confirm')->nullable();
            $table->string('email_confirm')->nullable();
            $table->string('mobile_confirm')->nullable();
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
        Schema::dropIfExists('contact_db');
    }
}
