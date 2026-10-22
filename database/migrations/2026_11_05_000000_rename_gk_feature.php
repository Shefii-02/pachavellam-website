<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('exam_features')->where('slug', 'gk-exams')->update(['name' => 'GK exams', 'description' => 'GK exam series.']);
    }

    public function down(): void
    {
        DB::table('exam_features')->where('slug', 'gk-exams')->update(['name' => 'General knowledge exams', 'description' => 'General knowledge exam series.']);
    }
};
