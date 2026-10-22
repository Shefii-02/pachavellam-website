<?php

namespace App\Http\Middleware;

use App\Models\CompetitiveExam;
use App\Support\StaffAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Roaring Study opened from an exam type (admin/exam/{exam}/roaring-study) shows that exam type's packs inside the exam's own
 * sidebar, like courses and the libraries. Without {exam} it is the all-exam-types list (admin/roaring-study).
 */
class RoaringScope
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('exam');
        if ($slug) {
            $exam = CompetitiveExam::where('slug', $slug)->firstOrFail();
            $allowed = StaffAccess::examIds($request->user());
            abort_unless($allowed === null || in_array($exam->id, $allowed, true), 403, 'You do not have access to this exam type.');

            $request->route()->forgetParameter('exam');
            $request->attributes->set('rs_exam', $exam);
            if (!$request->filled('exam')) {
                $request->merge(['exam' => $exam->slug]);
            }
            view()->share(['rsBase' => $exam->examUrl('roaring-study'), 'rsLayout' => $exam->layout(), 'rsExam' => $exam]);
        } else {
            view()->share(['rsBase' => url('admin/roaring-study'), 'rsLayout' => 'admin.main-layout', 'rsExam' => null]);
        }

        return $next($request);
    }
}
