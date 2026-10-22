<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KPSC category tree - same structure as rrb_categories, ssc_categories, ktet_categories and
 * ugc_net_categories: parent_id makes it a tree, so every child can be a parent of more children.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kpsc_categories')) {
            return;
        }

        Schema::create('kpsc_categories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('parent_id')->nullable()->index();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('icon')->nullable();
            $t->unsignedTinyInteger('depth')->default(0);
            $t->unsignedInteger('position')->default(0);
            $t->boolean('status')->default(true);
            $t->boolean('is_featured')->default(false);
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->json('settings')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->foreign('parent_id', 'kpsc_categories_parent_fk')->references('id')->on('kpsc_categories')->cascadeOnDelete();
            $t->index(['parent_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpsc_categories');
    }
};
