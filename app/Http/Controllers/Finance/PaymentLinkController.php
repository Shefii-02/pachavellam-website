<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCourse;
use App\Models\PaymentLink;
use App\Models\User;
use App\Services\Payments\AdmissionService;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentLinkService;
use App\Support\StaffAccess;
use Illuminate\Http\Request;

/** Payment links made by staff (admin/payment-links): create one for a student, send it on WhatsApp, see which were paid, cancel. Admin only. */
class PaymentLinkController extends Controller
{
    private function adminOnly(Request $request, ?int $examId = null): void
    {
        StaffAccess::authorize($request, 'Payment links manage', $examId);
    }

    public function index(Request $request)
    {
        $this->adminOnly($request);
        $status = $request->status;

        $list = StaffAccess::scope(PaymentLink::query(), $request->user())->with(['user:id,name,mobile', 'course:id,title', 'exam:id,short_name,name'])
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->channel))
            ->when($status === 'expired', fn ($q) => $q->where('status', 'active')->where('expires_at', '<', now()))
            ->when($status === 'active', fn ($q) => $q->where('status', 'active')->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>=', now())))
            ->when(in_array($status, ['paid', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->q . '%')->orWhere('mobile', 'like', '%' . $request->q . '%')))
            ->orderByDesc('id')->paginate(per_page(25))->withQueryString();

        return view('admin.finance.links.index', ['list' => $list, 'exams' => StaffAccess::scope(CompetitiveExam::orderBy('position'), $request->user(), 'id')->get()]);
    }

    public function create(Request $request)
    {
        $this->adminOnly($request);

        return view('admin.finance.links.form', [
            'exams' => StaffAccess::scope(CompetitiveExam::orderBy('position'), $request->user(), 'id')->get(),
            'courses' => StaffAccess::scope(ExamCourse::where('status', 'published')->where('course_type', '!=', 'free')->orderBy('title'), $request->user())->get(['id', 'title', 'exam_id', 'price', 'discount_price']),
            'selectedCourse' => $request->course,
        ]);
    }

    public function store(Request $request, PaymentLinkService $links)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'user_id' => 'required|integer', 'course_id' => 'required|integer', 'channel' => 'required|in:whatsapp,web,android,ios,admin',
            'coupon_code' => 'nullable|string|max:60', 'hours' => 'required|integer|min:1|max:720', 'note' => 'nullable|string|max:290', 'message' => 'nullable|string|max:1500',
        ], ['user_id.required' => 'Search and choose the student.']);

        $student = User::where('type', 'Student')->find($data['user_id']);
        $course = ExamCourse::find($data['course_id']);
        if (!$student || !$course) {
            return back()->withInput()->withErrors(['user_id' => 'Student or course not found.']);
        }
        $this->adminOnly($request, $course->exam_id);

        try {
            $link = $links->create($student, $course, $data['channel'], $data['coupon_code'] ?? null, (int) $data['hours'], $data['note'] ?? null, $request->user()->id, $data['message'] ?? null);
        } catch (PaymentException $e) {
            return back()->withInput()->withErrors(['course_id' => $e->getMessage()]);
        }

        return redirect(url('admin/payment-links'))->with('success', 'Payment link created.')->with('new_link', $link->id);
    }

    public function cancel(Request $request, $id)
    {
        $this->adminOnly($request);
        $link = PaymentLink::findOrFail($id);
        $this->adminOnly($request, $link->exam_id);
        if ($link->status !== 'active') {
            return back()->withErrors(['link' => 'Only an unpaid link can be cancelled.']);
        }
        $link->update(['status' => 'cancelled']);
        // the order made with the link is closed too, unless a payment is already in progress
        \App\Models\CourseAdmission::where('id', $link->admission_id)->where('status', 'pending')
            ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Payment link cancelled', 'cancelled_by' => $request->user()->id]);

        return back()->with('success', 'Payment link cancelled; it no longer opens.');
    }
}
