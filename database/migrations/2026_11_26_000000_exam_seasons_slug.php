<?php

use App\Models\ExamSeason;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exam_seasons', 'slug')) {
            Schema::table('exam_seasons', function (Blueprint $t) {
                $t->string('slug', 120)->nullable()->after('title');
                $t->index(['exam_id', 'kind', 'slug']);
            });
        }
        // give every existing season its address (the model makes the slug on save)
        ExamSeason::withTrashed()->whereNull('slug')->orderBy('id')->get()->each->save();
    }

    public function down(): void
    {
        Schema::table('exam_seasons', function (Blueprint $t) {
            $t->dropIndex(['exam_id', 'kind', 'slug']);
            $t->dropColumn('slug');
        });
    }
};
