<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A course feature shows an uploaded picture instead of a Bootstrap icon class.
 * The old `icon` column held class names such as "bi-star", which mean nothing as a picture, so it is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_features', function (Blueprint $t) {
            $t->string('image')->nullable()->after('description');
        });

        Schema::table('exam_features', function (Blueprint $t) {
            $t->dropColumn('icon');
        });
    }

    public function down(): void
    {
        Schema::table('exam_features', function (Blueprint $t) {
            $t->string('icon', 60)->default('bi-star')->after('description');
            $t->dropColumn('image');
        });
    }
};
