<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Staff Access: every feature now has a View and a Manage permission. Runs the StaffPermissionSeeder (adds the View ones and any other missing permission); Manage already includes View. */
return new class extends Migration
{
    private array $names = ['Exam courses view', 'Question library view', 'Class library view', 'Roaring study view', 'Payment links view', 'Content view', 'Trash view', 'Staff access view'];

    public function up(): void
    {
        (new \Database\Seeders\StaffPermissionSeeder())->run();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', $this->names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
