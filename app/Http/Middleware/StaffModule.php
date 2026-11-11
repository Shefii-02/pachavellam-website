<?php

namespace App\Http\Middleware;

use App\Support\StaffAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Guards the older per exam type screens (admin/kpsc/..., admin/rrb/..., admin/ssc/..., admin/ktet/..., admin/ugc-net/..., admin/ug-pg/...).
 * The exam type comes from the address; the feature from config('staff_access.modules'). Looking needs View, changing needs Manage.
 */
class StaffModule
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $slug = (string) $request->segment(2);
        $examId = StaffAccess::examIdBySlug($slug);
        abort_unless(StaffAccess::hasExam($user, $examId), 403, 'You do not have access to this exam type.');

        $section = (string) $request->segment(3);
        $modules = config('staff_access.modules');
        if (!in_array($section, $modules['open'], true)) {
            $feature = $modules['sections'][$section] ?? $modules['default'];
            StaffAccess::authorize($request, $feature . ' manage', $examId);
        }

        return $next($request);
    }
}
