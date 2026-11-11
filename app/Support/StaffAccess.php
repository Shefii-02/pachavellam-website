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

        return self::$cache[$user->id] ??= DB::table('staff_exam_access')->where('user_id', $user->id)->pluck('exam_id')
            // plus the exam types of the person's roles (General > Roles)
            ->merge(DB::table('role_exam_access')->join('model_has_roles', 'model_has_roles.role_id', '=', 'role_exam_access.role_id')
                ->where('model_has_roles.model_id', $user->id)->where('model_has_roles.model_type', $user->getMorphClass())->pluck('role_exam_access.exam_id'))
            ->map(fn ($v) => (int) $v)->unique()->values()->all();
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
        if (self::isAdmin($user)) {
            return true;
        }
        if (!$user) {
            return false;
        }
        if ($user->can($permission)) {
            return true;
        }
        // Manage includes View
        $manage = self::manageOf($permission);

        return $manage !== null && $user->can($manage);
    }

    /** "Question library view" -> "Question library manage" (null when the feature has no manage permission). */
    public static function manageOf(string $viewPermission): ?string
    {
        if (!str_ends_with($viewPermission, ' view')) {
            return null;
        }
        foreach ([' manage', ' mark'] as $suffix) {
            $manage = substr($viewPermission, 0, -5) . $suffix;
            if (in_array($manage, self::permissionNames(), true)) {
                return $manage;
            }
        }

        return null;
    }

    /** "Question library manage" -> "Question library view" (null when the feature has no view permission). */
    public static function viewOf(string $managePermission): ?string
    {
        if (!str_ends_with($managePermission, ' manage')) {
            return null;
        }
        $view = substr($managePermission, 0, -7) . ' view';

        return in_array($view, self::permissionNames(), true) ? $view : null;
    }

    /** Pages that only show a form to change something (opening them needs Manage, not View). */
    public static function isChangePage(Request $request): bool
    {
        $uri = $request->route()?->uri() ?? $request->path();
        foreach (explode('/', $uri) as $segment) {
            if (preg_match('/^(create|edit|add|import|bulk|quick|starter|storage|library|build|upload)$|(^|[-_])(edit|create|add|upload)([-_]|$)/', $segment)) {
                return true;
            }
        }

        return false;
    }

    /** Turns a Manage permission into its View one for pages that only show something; marks the page read-only for a View-only person. */
    public static function levelFor(Request $request, string $permission): string
    {
        if ($request->isMethodSafe() && !self::isChangePage($request) && ($view = self::viewOf($permission))) {
            if (!self::can($request->user(), $permission)) {
                $request->attributes->set('staff_read_only', true);
            }

            return $view;
        }

        return $permission;
    }

    public static function authorize(Request $request, string $permission, ?int $examId = null): void
    {
        $user = $request->user();
        // a Manage permission guards a whole feature: just looking at a page (GET) only needs View
        $permission = self::levelFor($request, $permission);
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
