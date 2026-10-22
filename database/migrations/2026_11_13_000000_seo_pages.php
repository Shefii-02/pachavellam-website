<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // what the staff type for a page's search / share tags; a page without a row (or with empty fields) keeps the automatic text
        Schema::create('seo_pages', function (Blueprint $t) {
            $t->id();
            $t->string('page_key', 80)->unique();        // home | exams | courses | ... | exam:kpsc
            $t->string('title', 160)->nullable();
            $t->string('description', 400)->nullable();
            $t->string('keywords', 400)->nullable();
            $t->string('og_image', 255)->nullable();     // path under storage/files
            $t->string('robots', 30)->nullable();        // index,follow | noindex,follow | noindex,nofollow
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }
};
