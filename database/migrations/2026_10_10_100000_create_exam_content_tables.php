<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates "<prefix>_<section>" tables for every exam module from config/exam_sections.php
 * e.g. rrb_banners, ssc_study_materials, ugc_net_stories, ktet_syllabus
 */
return new class extends Migration
{
    private array $prefixes = ['rrb', 'ssc', 'ugc_net', 'ktet'];

    public function up(): void
    {
        foreach ($this->prefixes as $prefix) {
            foreach (config('exam_sections') as $key => $section) {
                $table = $prefix . '_' . str_replace('-', '_', $key);

                if (Schema::hasTable($table)) {
                    continue;
                }

                Schema::create($table, function (Blueprint $t) use ($section) {
                    $t->id();

                    foreach ($section['fields'] as $f) {
                        $name = $f['name'];
                        match ($f['type']) {
                            'url' => $t->string($name, 500)->nullable(),
                            'textarea' => $t->longText($name)->nullable(),
                            'number' => $t->integer($name)->nullable(),
                            'date' => $t->date($name)->nullable(),
                            'select' => $t->string($name, 50)->nullable(),
                            'category' => $t->unsignedBigInteger($name)->nullable()->index(),
                            'checkbox' => $t->boolean($name)->default(false),
                            default => $t->string($name)->nullable(),   // text, image, file
                        };
                    }

                    $t->unsignedInteger('position')->default(0);
                    $t->boolean('status')->default(true);
                    $t->unsignedBigInteger('created_by')->nullable();
                    $t->timestamps();

                    $t->index(['status', 'position']);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->prefixes as $prefix) {
            foreach (config('exam_sections') as $key => $section) {
                Schema::dropIfExists($prefix . '_' . str_replace('-', '_', $key));
            }
        }
    }
};
