<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // which exam series of which exam type are shown as cards on the home page (staff tick them in admin/home-exams)
        Schema::create('home_exam_series', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('exam_id');
            $t->string('series', 20);                 // daily-exams | omr-exams | gk-exams | ca-exams
            $t->boolean('show_home')->default(false);
            $t->unsignedInteger('position')->default(0);
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
            $t->unique(['exam_id', 'series']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_exam_series');
    }
};
