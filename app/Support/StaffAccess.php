<?php

namespace App\Support;

use App\Models\CompetitiveExam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Who may do what, and in which exam types.
 *   - users.type "Admin": everything, every exam type.
 *   - everyone else (Admin Staff): needs the permission (from a role or ticked for them) and, for exam scoped permissions,
 *     access to the exam type (Staff Access page, table staff_exam_access).
 */
class StaffAccess
{
    private static array $cache = [];

    public static function isAdmin(?User $user): bool
    {
        return $user && $user->type === 'Admin';
    }

    /** Exam type ids the person may work in; null means every exam type. */
    public static function examIds(?User $user): ?array
    {
        if (!$user) {
            return [];
        }
        if (self::isAdmin($user)) {
            return null;
        }

        return self::$cache[$user->id] ??= DB::table('staff_exam_access')->where('user_id', $user->id)->pluck('exam_id')->map(fn ($v) => (int) $v)->all();
    }

    public static function forget(?int $userId = null): void
    {
        $userId ? self::$cache = array_diff_key(self::$cache, [$userId => 1]) : self::$cache = [];
    }

    public static function hasExam(?User $user, ?int $examId): bool
    {
        $ids = self::examIds($user);

        return $ids === null || $examId === null || in_array((int) $examId, $ids, true);
    }

    /** Permission only (no exam type). */
    public static function can(?User $user, string $permission): bool
    {
        return self::isAdmin($user) || ($user && $user->can($permission));
    }

    /** Permission, and the exam type when one is given. */
    public static function canIn(?User $user, string $permission, ?int $examId = null): bool
    {
        return self::can($user, $permission) && self::hasExam($user, $examId);
    }

    public static function authorize(Request $request, string $permission, ?int $examId = null): void
    {
        $user = $request->user();
        abort_unless(self::can($user, $permission), 403, 'You do not have permission for this (' . $permission . ').');
        abort_unless(self::hasExam($user, $examId), 403, 'You do not have access to this exam type.');
    }

    /** Keeps only the rows of the exam types the person may see. */
    public static function scope($query, ?User $user, string $column = 'exam_id')
    {
        $ids = self::examIds($user);

        return $ids === null ? $query : $query->whereIn($column, $ids ?: [0]);
    }

    public static function examIdBySlug(string $slug): ?int
    {
        static $map = null;
        $map ??= CompetitiveExam::pluck('id', 'slug')->all();

        return $map[$slug] ?? null;
    }

    /** All the permission names defined in config/staff_access.php. */
    public static function permissionNames(): array
    {
        return collect(config('staff_access.groups'))->flatMap(fn ($g) => array_keys($g))->values()->all();
    }
}
