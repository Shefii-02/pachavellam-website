<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\CourseAdmission;
use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\Payments\AdmissionService;
use App\Services\Payments\PaymentException;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Course admissions (admin/admissions): who joined which course, add a student from the backend, cancel (with refund) or remove.
 * Admin only - it is money.
 */
class AdmissionController extends Controller
{
    public function __construct(private AdmissionService $service)
    {
    }

    /** Admin, or staff with the permission (and, for one admission, access to its exam type). */
    private function adminOnly(Request $request, string $permission = 'Admissions view', ?int $examId = null): void
    {
        StaffAccess::authorize($request, $permission, $examId);
    }

    /** Admissions that match the filters (shared by the list and the CSV). */
    public static function filtered(Request $request)
    {
        $status = $request->status;

        return StaffAccess::scope(CourseAdmission::query(), $request->user())
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course))
            ->when($status === 'expired', fn ($q) => $q->where('status', 'active')->whereNotNull('valid_until')->where('valid_until', '<', now()))
            ->when($status === 'active', fn ($q) => $q->where('status', 'active')->where(fn ($w) => $w->whereNull('valid_until')->orWhere('valid_until', '>=', now())))
            ->when(in_array($status, ['pending', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->source))
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->gateway))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->q . '%')->orWhere('mobile', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%')));
    }

    private function examsFor(Request $request)
    {
        return StaffAccess::scope(CompetitiveExam::orderBy('position'), $request->user(), 'id')->get();
    }

    public function index(Request $request)
    {
        $this->adminOnly($request);

        $base = self::filtered($request);
        $totals = (clone $base)->whereNotNull('admitted_at')->selectRaw('count(*) c, coalesce(sum(amount_paid),0) paid, coalesce(sum(refunded_amount),0) refunded, coalesce(sum(commission_amount),0) commission, coalesce(sum(case when status = "active" then amount else 0 end),0) value, coalesce(sum(case when status = "active" then greatest(amount - amount_paid, 0) else 0 end),0) due')->first();
        $list = (clone $base)->with(['user:id,name,mobile,email', 'course:id,title', 'exam:id,short_name,name'])->orderByDesc('id')->paginate(per_page(25))->withQueryString();

        return view('admin.finance.admissions.index', [
            'list' => $list, 'totals' => $totals, 'exams' => $this->examsFor($request),
            'courses' => StaffAccess::scope(ExamCourse::orderBy('title'), $request->user())->get(['id', 'title', 'exam_id']),
        ]);
    }

    public function create(Request $request)
    {
        $this->adminOnly($request, 'Admissions manage');

        return view('admin.finance.admissions.form', [
            'exams' => $this->examsFor($request),
            'courses' => StaffAccess::scope(ExamCourse::where('status', 'published')->orderBy('title'), $request->user())->get(['id', 'title', 'exam_id', 'course_type', 'price', 'discount_price']),
            'selectedCourse' => $request->course,
            'methods' => config('payments.manual_methods'),
        ]);
    }

    /** JSON for the student search box. */
    public function students(Request $request)
    {
        $this->adminOnly($request);
        $q = trim((string) $request->q);

        $rows = User::where('type', 'Student')->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('mobile', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->orderByDesc('id')->limit(12)->get(['id', 'name', 'mobile', 'email'])
            ->map(fn ($u) => ['id' => $u->id, 'label' => trim(($u->name ?: 'Student #' . $u->id) . ' · ' . ($u->mobile ?: $u->email ?: ''), ' ·')]);

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $this->adminOnly($request, 'Admissions manage');
        $data = $request->validate([
            'user_id' => 'required|integer', 'course_id' => 'required|integer', 'method' => 'required|in:' . implode(',', array_keys(config('payments.manual_methods'))),
            'amount' => 'nullable|numeric|min:0|max:10000000', 'discount' => 'nullable|numeric|min:0|max:10000000', 'coupon_code' => 'nullable|string|max:60',
            'paid_at' => 'nullable|date', 'valid_until' => 'nullable|date', 'notes' => 'nullable|string|max:1000',
        ], ['user_id.required' => 'Search and choose the student.']);

        $student = User::where('type', 'Student')->find($data['user_id']);
        $course = ExamCourse::find($data['course_id']);
        if ($course) {
            $this->adminOnly($request, 'Admissions manage', $course->exam_id);
        }
        if (!$student || !$course) {
            return back()->withInput()->withErrors(['user_id' => 'Student or course not found.']);
        }

        try {
            $admission = $this->service->addByStaff($student, $course, $data, $request->user());
        } catch (PaymentException $e) {
            return back()->withInput()->withErrors(['course_id' => $e->getMessage()]);
        }

        return redirect(url('admin/admissions/' . $admission->id))->with('success', 'Student added to the course.');
    }

    public function show(Request $request, $id)
    {
        $this->adminOnly($request);
        $a = CourseAdmission::withTrashed()->with(['user:id,name,mobile,email', 'course:id,title,exam_id', 'exam:id,short_name,name', 'transactions'])->findOrFail($id);

        abort_unless(StaffAccess::hasExam($request->user(), $a->exam_id), 403, 'You do not have access to this exam type.');

        return view('admin.finance.admissions.show', [
            'a' => $a,
            'refundable' => round($a->amount_paid - $a->refunded_amount, 2),
            'hasOnlinePayment' => $a->transactions->where('type', 'payment')->where('status', 'success')->whereIn('gateway', ['razorpay', 'phonepe'])->isNotEmpty(),
            'creator' => $a->created_by ? User::find($a->created_by)?->name : null,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $a = CourseAdmission::findOrFail($id);
        $this->adminOnly($request, 'Admissions manage', $a->exam_id);
        $data = $request->validate([
            'reason' => 'required|string|max:290', 'refund_mode' => 'required|in:none,manual,gateway', 'refund_amount' => 'nullable|numeric|min:0|max:10000000',
        ], ['reason.required' => 'Write the reason for cancelling.']);

        try {
            $this->service->cancel($a, $data['reason'], (float) ($data['refund_amount'] ?? 0), $data['refund_mode'], $request->user());
        } catch (PaymentException $e) {
            return back()->withInput()->withErrors(['refund_amount' => $e->getMessage()]);
        }

        return redirect(url('admin/admissions/' . $a->id))->with('success', 'Admission cancelled. The student no longer has the course.');
    }

    public function recordPayment(Request $request, $id)
    {
        $a = CourseAdmission::findOrFail($id);
        $this->adminOnly($request, 'Admissions manage', $a->exam_id);
        $data = $request->validate(['method' => 'required|in:cash,upi,bank', 'amount' => 'required|numeric|min:0.01|max:10000000', 'paid_at' => 'nullable|date']);

        try {
            $this->service->recordPayment($a, $data['method'], (float) $data['amount'], $data['paid_at'] ?? null, $request->user());
        } catch (PaymentException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('success', 'Payment recorded.');
    }

    public function destroy(Request $request, $id)
    {
        $a = CourseAdmission::findOrFail($id);
        $this->adminOnly($request, 'Admissions manage', $a->exam_id);

        try {
            $this->service->remove($a);
        } catch (PaymentException $e) {
            return back()->withErrors(['remove' => $e->getMessage()]);
        }

        return redirect(url('admin/admissions'))->with('success', 'Admission record removed. Its payment entries are kept for the accounts.');
    }

    public function export(Request $request)
    {
        $this->adminOnly($request);
        $rows = self::filtered($request)->with(['user:id,name,mobile,email', 'course:id,title', 'exam:id,short_name,name'])->orderByDesc('id')->limit(20000)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Student', 'Mobile', 'Email', 'Exam type', 'Course', 'Status', 'Source', 'Gateway', 'Price', 'Discount', 'Payable', 'Paid', 'Refunded', 'Commission %', 'Commission', 'Net after commission', 'Coupon', 'Admitted', 'Valid until', 'Cancelled', 'Reason']);
            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->id, $a->user?->name, $a->user?->mobile, $a->user?->email, $a->exam?->short_name ?: $a->exam?->name, $a->course?->title, $a->displayStatus(), $a->source, $a->gateway,
                    $a->original_amount, $a->discount_amount, $a->amount, $a->amount_paid, $a->refunded_amount, $a->commission_percent, $a->commission_amount, $a->net(), $a->coupon_code,
                    $a->admitted_at?->format('Y-m-d H:i'), $a->valid_until?->format('Y-m-d') ?? 'lifetime', $a->cancelled_at?->format('Y-m-d H:i'), $a->cancel_reason,
                ]);
            }
            fclose($out);
        }, 'admissions-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
