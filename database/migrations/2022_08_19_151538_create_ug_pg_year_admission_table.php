<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUgPgYearAdmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ug_pg_year_admission', function (Blueprint $table) {
            $table->id();
            $table->string('university_name');
            $table->string('level_name');
            $table->string('course_name');
            $table->string('year_admission');
            $table->string('name_slug');
            $table->integer('position')->default(0);
            $table->boolean('status')->default(0);
            $table->integer('author_id');
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
        Schema::dropIfExists('ug_pg_year_admission');
    }
}
