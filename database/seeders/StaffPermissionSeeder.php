<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates every Staff Access permission of config/staff_access.php (View and Manage of each feature) and gives each
 * to the roles listed for it (the Admin role always). Safe to run again: it only adds, never removes.
 *   php artisan db:seed --class=StaffPermissionSeeder
 */
class StaffPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $now = now();
        $roleIds = DB::table('roles')->pluck('id', 'name');
        $hasCategory = \Illuminate\Support\Facades\Schema::hasColumn('permissions', 'category');   // the Permission page groups by it

        foreach (config('staff_access.groups') as $group => $perms) {
            foreach ($perms as $name => $cfg) {
                DB::table('permissions')->updateOrInsert(['name' => $name, 'guard_name' => $guard], ['updated_at' => $now] + ($hasCategory ? ['category' => $group] : []));
                DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->whereNull('created_at')->update(['created_at' => $now]);
                $pid = DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->value('id');
                foreach (array_unique(array_merge(['Admin'], $cfg['roles'])) as $roleName) {
                    if (isset($roleIds[$roleName])) {
                        DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $roleIds[$roleName]]);
                    }
                }
            }
        }
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
