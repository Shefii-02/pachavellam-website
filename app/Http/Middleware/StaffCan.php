<?php

namespace App\Http\Middleware;

use App\Support\StaffAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Route guard:  ->middleware('staff.can:Question library manage')
 * Checks the permission and, when the address has an {exam} (slug), that the person has access to that exam type.
 */
class StaffCan
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $examId = null;
        $slug = $request->route('exam');
        if (is_string($slug) && $slug !== '') {
            $examId = StaffAccess::examIdBySlug($slug);
        }
        if ($permission === 'access') {
            abort_unless(StaffAccess::hasExam($request->user(), $examId), 403, 'You do not have access to this exam type.');
        } else {
            StaffAccess::authorize($request, $permission, $examId);
        }

        return $next($request);
    }
}
