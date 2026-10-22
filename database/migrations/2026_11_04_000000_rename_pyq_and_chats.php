<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** "Previous question papers" is called PYQ everywhere. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('exam_features')->where('slug', 'previous-questions')->update(['name' => 'PYQ']);
    }

    public function down(): void
    {
        DB::table('exam_features')->where('slug', 'previous-questions')->update(['name' => 'Previous question papers']);
    }
};
