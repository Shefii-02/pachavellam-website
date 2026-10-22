<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('name', 120);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('folder_id')->nullable()->index();
            $t->string('path', 400)->unique();            // relative to storage/app/public/files/ - the same form content items already store
            $t->string('name', 255);                      // original file name
            $t->string('title', 255)->nullable();
            $t->string('alt', 255)->nullable();
            $t->string('type', 12)->index();              // image | video | audio | document | other
            $t->string('ext', 10)->nullable();
            $t->string('mime', 100)->nullable();
            $t->unsignedBigInteger('size')->default(0);
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->string('source', 20)->default('upload');   // upload | import (existing files indexed by media:import)
            $t->timestamps();
            $t->softDeletes();
            $t->index(['folder_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_folders');
    }
};
