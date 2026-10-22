<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 | Staff activity log, bell notifications, new-join marks, exam-type access for staff, the new permissions, and soft delete (Trash)
 | for the main content tables.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable()->index();      // the staff member (null = system)
            $t->string('user_name', 120)->nullable();                     // kept even if the person is later removed
            $t->string('user_type', 30)->nullable();
            $t->string('action', 40)->index();                            // created | updated | deleted | restored | login | logout | cancelled | exported ...
            $t->string('subject_type', 60)->nullable()->index();          // short name: Course, Question, Admission ...
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->string('subject_label', 250)->nullable();                 // the title at that moment
            $t->unsignedBigInteger('exam_id')->nullable()->index();       // which exam type it belongs to
            $t->string('description', 500)->nullable();
            $t->json('changes')->nullable();                              // {field: [old, new]}
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->string('url', 300)->nullable();
            $t->timestamp('created_at')->useCurrent()->index();
            $t->index(['subject_type', 'subject_id']);
        });

        Schema::create('staff_notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type', 30)->index();                              // new_join | interest | admission | cancellation | payment_failed
            $t->string('title', 200);
            $t->string('body', 500)->nullable();
            $t->string('url', 300)->nullable();
            $t->string('icon', 40)->nullable();
            $t->unsignedBigInteger('exam_id')->nullable()->index();       // null = every exam type
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('staff_notification_reads', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('notification_id');
            $t->timestamp('read_at')->useCurrent();
            $t->primary(['user_id', 'notification_id']);
        });

        Schema::create('lead_marks', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();                 // the new join (a student)
            $t->unsignedBigInteger('marked_by')->nullable();
            $t->dateTime('marked_at')->nullable();
            $t->string('note', 500)->nullable();
            $t->string('outcome', 20)->nullable();                        // contacted | interested | not_interested | no_answer | admitted
            $t->timestamps();
        });

        Schema::create('staff_exam_access', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('exam_id');
            $t->primary(['user_id', 'exam_id']);
            $t->index('exam_id');
        });

        foreach (['library_questions', 'library_classes', 'course_exams', 'roaring_studies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
                $t->unsignedBigInteger('deleted_by')->nullable();
            });
        }

        // ---------------------------------------------------------------- permissions
        $guard = config('auth.defaults.guard', 'web');
        $now = now();
        $roleIds = DB::table('roles')->pluck('id', 'name');
        foreach (config('staff_access.groups') as $perms) {
            foreach ($perms as $name => $cfg) {
                DB::table('permissions')->updateOrInsert(['name' => $name, 'guard_name' => $guard], ['created_at' => $now, 'updated_at' => $now]);
                $pid = DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->value('id');
                foreach (array_unique(array_merge(['Admin'], $cfg['roles'])) as $roleName) {
                    if (isset($roleIds[$roleName])) {
                        DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $roleIds[$roleName]]);
                    }
                }
            }
        }
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // existing staff keep what they had: access to every exam type that exists today (an Admin narrows it later)
        $exams = DB::table('competitive_exams')->pluck('id');
        foreach (DB::table('users')->where('type', 'Admin Staff')->pluck('id') as $uid) {
            foreach ($exams as $eid) {
                DB::table('staff_exam_access')->updateOrInsert(['user_id' => $uid, 'exam_id' => $eid]);
            }
        }
    }

    public function down(): void
    {
        foreach (['library_questions', 'library_classes', 'course_exams', 'roaring_studies'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['deleted_at', 'deleted_by']));
        }
        $names = collect(config('staff_access.groups'))->flatMap(fn ($g) => array_keys($g))->all();
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        Schema::dropIfExists('staff_exam_access');
        Schema::dropIfExists('lead_marks');
        Schema::dropIfExists('staff_notification_reads');
        Schema::dropIfExists('staff_notifications');
        Schema::dropIfExists('activity_logs');
    }
};
