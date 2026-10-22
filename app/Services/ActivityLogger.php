<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Writes the staff activity log. Only staff (not students, not the system) are logged.
 *
 *   ActivityLogger::record('cancelled', $admission, 'Cancelled: wrong student');       // explicit event
 *   ActivityLogger::quietly(fn () => ...);                                              // a bulk job logs one summary line instead of thousands
 * Models get automatic created / updated / deleted / restored events through the LogsActivity trait.
 */
class ActivityLogger
{
    private static int $muted = 0;

    /** Fields never written to the log (secrets) and fields that are only noise. */
    private const HIDDEN = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'token', 'key_secret', 'salt_key'];
    private const NOISE = ['updated_at', 'created_at', 'deleted_at', 'position', 'view_count', 'opens', 'last_opened_at'];

    public static function quietly(callable $fn)
    {
        self::$muted++;
        try {
            return $fn();
        } finally {
            self::$muted--;
        }
    }

    public static function actor(): ?User
    {
        $u = Auth::user();

        return ($u && $u->type !== 'Student') ? $u : null;
    }

    /** Short readable name of a model class: LibraryQuestion -> "Library question". */
    public static function typeName(Model|string $subject): string
    {
        $class = is_string($subject) ? $subject : class_basename($subject);

        return ucfirst(strtolower(trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $class))));
    }

    public static function record(string $action, Model|string|null $subject = null, ?string $description = null, ?array $changes = null, ?int $examId = null): ?ActivityLog
    {
        if (self::$muted > 0) {
            return null;
        }
        $user = self::actor();
        if (!$user && !in_array($action, ['login_failed'], true)) {
            return null;   // students and system jobs are not staff activity
        }

        $label = null;
        $subjectId = null;
        $type = null;
        if ($subject instanceof Model) {
            $type = self::typeName($subject);
            $subjectId = $subject->getKey();
            $label = self::labelOf($subject);
            $examId ??= self::examOf($subject);
        } elseif (is_string($subject)) {
            $type = $subject;
        }

        try {
            $request = request();
            // an Admin viewing the panel as a staff member: say so in the log
            if ($request && $request->hasSession() && ($impId = $request->session()->get(\App\Http\Controllers\Staff\ImpersonationController::KEY)) && $action !== 'login' && $action !== 'logout') {
                $description = trim(($description ?? '') . ' [done by Admin ' . (\App\Models\User::find($impId)?->name ?? '#' . $impId) . ' while viewing as this staff member]');
            }

            return ActivityLog::create([
                'user_id' => $user?->id, 'user_name' => $user?->name, 'user_type' => $user?->type, 'action' => $action, 'subject_type' => $type,
                'subject_id' => $subjectId, 'subject_label' => $label ? mb_substr($label, 0, 250) : null, 'exam_id' => $examId,
                'description' => $description ? mb_substr($description, 0, 490) : null, 'changes' => $changes ?: null,
                'ip' => $request?->ip(), 'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 250) : null,
                'url' => $request && !app()->runningInConsole() ? mb_substr($request->method() . ' ' . $request->path(), 0, 290) : null,
            ]);
        } catch (\Throwable $e) {
            report($e);   // the log must never break the thing being logged

            return null;
        }
    }

    public static function labelOf(Model $m): string
    {
        foreach (['title', 'name', 'code', 'tag', 'examtitle', 'file_name'] as $f) {
            $v = $m->getAttribute($f);
            if (is_string($v) && trim($v) !== '') {
                return trim(strip_tags($v));
            }
        }
        if ($m instanceof \App\Models\LibraryQuestion) {
            return $m->plainQuestion(100);
        }
        if ($m instanceof \App\Models\CourseAdmission) {
            return 'Admission #' . $m->id . ' (' . ($m->user?->name ?: 'student ' . $m->user_id) . ' → ' . ($m->course?->title ?: 'course ' . $m->course_id) . ')';
        }

        return '#' . $m->getKey();
    }

    private static function examOf(Model $m): ?int
    {
        $id = $m->getAttribute('exam_id');

        return $id ? (int) $id : null;
    }

    /** {field: [old, new]} for what changed on a model; secrets hidden, long text shortened. */
    public static function diff(Model $m): array
    {
        $out = [];
        foreach ($m->getChanges() as $field => $new) {
            if (in_array($field, self::NOISE, true)) {
                continue;
            }
            $old = $m->getOriginal($field);
            $out[$field] = in_array($field, self::HIDDEN, true) ? ['•••', '•••'] : [self::short($old), self::short($new)];
        }

        return $out;
    }

    private static function short($v)
    {
        if (is_array($v)) {
            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
        }

        return is_string($v) ? mb_substr(strip_tags($v), 0, 120) : $v;
    }
}
