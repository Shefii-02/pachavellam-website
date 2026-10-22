<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One category tree table per exam module, named "<prefix>_categories".
 * parent_id makes it a tree: a child can be a parent of further children.
 */
return new class extends Migration
{
    private array $prefixes = ['rrb', 'ssc', 'ugc_net', 'ktet'];

    public function up(): void
    {
        foreach ($this->prefixes as $prefix) {
            $table = $prefix . '_categories';

            if (Schema::hasTable($table)) {
                continue;
            }

            Schema::create($table, function (Blueprint $t) use ($table) {
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
                $t->json('settings')->nullable();   // room for future features
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();

                $t->foreign('parent_id', $table . '_parent_fk')
                    ->references('id')->on($table)->cascadeOnDelete();
                $t->index(['parent_id', 'position']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->prefixes as $prefix) {
            Schema::dropIfExists($prefix . '_categories');
        }
    }
};
