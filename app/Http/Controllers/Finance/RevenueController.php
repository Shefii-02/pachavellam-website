<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\CourseAdmission;
use App\Models\ExamCourse;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentManager;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Revenue and admissions analytics (admin/revenue), the payment transactions list, and the gateway switch. Admin only.
 *
 * Money is counted per admission, in the month the student was admitted: paid, refunded, the platform commission on what was kept
 * (COMMISSION_PERCENT in .env, fixed per admission at the time it was paid) and what is left after the commission.
 */
class RevenueController extends Controller
{
    private function adminOnly(Request $request, string $permission = 'Revenue view'): void
    {
        StaffAccess::authorize($request, $permission);
    }

    /** Month range from the filter (YYYY-MM): default the last 12 months. @return array{0: Carbon, 1: Carbon} */
    private function range(Request $request): array
    {
        $parse = fn ($v, $fallback) => (is_string($v) && preg_match('/^\d{4}-\d{2}$/', $v)) ? Carbon::createFromFormat('Y-m-d', $v . '-01')->startOfMonth() : $fallback;
        $from = $parse($request->from, now()->startOfMonth()->subMonths(11));
        $to = $parse($request->to, now()->startOfMonth());
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to->copy()->endOfMonth()];
    }

    private function admissions(Request $request, Carbon $from, Carbon $to)
    {
        return StaffAccess::scope(CourseAdmission::query()->counted(), $request->user())->whereBetween('admitted_at', [$from, $to])
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course))
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->gateway))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->source));
    }

    public function index(Request $request, PaymentManager $gateways)
    {
        $this->adminOnly($request);
        [$from, $to] = $this->range($request);
        $base = $this->admissions($request, $from, $to);

        $sums = 'count(*) as admissions, sum(status = "cancelled") as cancelled, coalesce(sum(amount_paid),0) as gross, coalesce(sum(refunded_amount),0) as refunds, coalesce(sum(commission_amount),0) as commission, coalesce(sum(case when status = "active" then amount else 0 end),0) as fee_value, coalesce(sum(case when status = "active" then greatest(amount - amount_paid, 0) else 0 end),0) as due';
        $total = (clone $base)->selectRaw($sums)->first();

        $byMonth = (clone $base)->selectRaw("DATE_FORMAT(admitted_at, '%Y-%m') as ym, $sums")->groupBy('ym')->get()->keyBy('ym');
        $months = [];
        for ($m = $from->copy(); $m->lte($to); $m->addMonth()) {
            $row = $byMonth->get($m->format('Y-m'));
            $gross = (float) ($row->gross ?? 0);
            $refunds = (float) ($row->refunds ?? 0);
            $commission = (float) ($row->commission ?? 0);
            $months[] = [
                'ym' => $m->format('Y-m'), 'label' => $m->format('M Y'), 'admissions' => (int) ($row->admissions ?? 0), 'cancelled' => (int) ($row->cancelled ?? 0),
                'value' => (float) ($row->fee_value ?? 0), 'due' => (float) ($row->due ?? 0),
                'gross' => $gross, 'refunds' => $refunds, 'net' => round($gross - $refunds, 2), 'commission' => $commission, 'payout' => round($gross - $refunds - $commission, 2),
            ];
        }

        $groupRows = fn (string $column, ?callable $label = null) => (clone $base)->selectRaw("$column as k, $sums")->groupBy('k')->orderByDesc('gross')->limit(10)->get()
            ->map(fn ($r) => ['key' => $label ? $label($r->k) : $r->k, 'admissions' => (int) $r->admissions, 'gross' => (float) $r->gross, 'refunds' => (float) $r->refunds,
                'commission' => (float) $r->commission, 'payout' => round($r->gross - $r->refunds - $r->commission, 2)])->all();

        $courseNames = ExamCourse::pluck('title', 'id');
        $examNames = CompetitiveExam::get()->mapWithKeys(fn ($e) => [$e->id => $e->short_name ?: $e->name]);

        // payments that did not complete in the same period (not part of revenue)
        $tx = StaffAccess::scope(PaymentTransaction::where('type', 'payment'), $request->user())->whereBetween('created_at', [$from, $to])
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course));
        $failed = (clone $tx)->where('status', 'failed')->count();
        $abandoned = (clone $tx)->whereIn('status', ['created', 'pending'])->where('created_at', '<', now()->subHours(2))->count();

        // channels: money per channel, and how many payments that were started actually completed
        $channelRows = (clone $base)->selectRaw("source as k, $sums")->groupBy('k')->get()->keyBy('k');
        $attempts = StaffAccess::scope(PaymentTransaction::where('type', 'payment'), $request->user())->where('gateway', '!=', 'free')->whereBetween('created_at', [$from, $to])
            ->whereNotIn('gateway', ['cash', 'upi', 'bank'])
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course))
            ->selectRaw('channel as k, count(*) as started, sum(status = "success") as ok, sum(status = "failed") as failed')->groupBy('k')->get()->keyBy('k');
        $channels = [];
        foreach (\App\Services\Payments\AdmissionService::CHANNELS as $key => $label) {
            $r = $channelRows->get($key);
            $t = $attempts->get($key);
            if (!$r && !$t) {
                continue;
            }
            $g = (float) ($r->gross ?? 0);
            $rf = (float) ($r->refunds ?? 0);
            $cm = (float) ($r->commission ?? 0);
            $started = (int) ($t->started ?? 0);
            $channels[] = [
                'key' => $key, 'label' => $label, 'admissions' => (int) ($r->admissions ?? 0), 'cancelled' => (int) ($r->cancelled ?? 0), 'gross' => $g, 'refunds' => $rf,
                'commission' => $cm, 'payout' => round($g - $rf - $cm, 2), 'avg' => ($r->admissions ?? 0) ? round($g / $r->admissions, 2) : 0,
                'started' => $started, 'paid' => (int) ($t->ok ?? 0), 'failed' => (int) ($t->failed ?? 0), 'conversion' => $started ? round(($t->ok ?? 0) / $started * 100, 1) : null,
            ];
        }
        usort($channels, fn ($a, $b) => $b['gross'] <=> $a['gross']);

        // monthly collected money split by channel (for the stacked chart)
        $byMonthChannel = (clone $base)->selectRaw("DATE_FORMAT(admitted_at, '%Y-%m') as ym, source as k, coalesce(sum(amount_paid - refunded_amount),0) as net")->groupBy('ym', 'k')->get()->groupBy('k');
        $channelSeries = [];
        foreach ($byMonthChannel as $key => $rows) {
            $byYm = $rows->keyBy('ym');
            $channelSeries[] = ['name' => \App\Services\Payments\AdmissionService::CHANNELS[$key] ?? $key, 'data' => array_map(fn ($m) => round((float) ($byYm[$m['ym']]->net ?? 0), 2), $months)];
        }

        // payment links in the period
        $linkStats = StaffAccess::scope(\App\Models\PaymentLink::query(), $request->user())->whereBetween('created_at', [$from, $to])
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course))
            ->selectRaw('count(*) as made, sum(status = "paid") as paid, sum(status = "active" and expires_at < now()) as expired, sum(status = "cancelled") as cancelled')->first();

        $gross = (float) $total->gross;
        $refunds = (float) $total->refunds;

        return view('admin.finance.revenue', [
            'from' => $from, 'to' => $to, 'months' => $months,
            'kpi' => [
                'admissions' => (int) $total->admissions, 'cancelled' => (int) $total->cancelled, 'gross' => $gross, 'refunds' => $refunds, 'net' => round($gross - $refunds, 2),
                'commission' => (float) $total->commission, 'payout' => round($gross - $refunds - (float) $total->commission, 2),
                'value' => (float) $total->fee_value, 'due' => (float) $total->due,
                'average' => $total->admissions ? round($gross / $total->admissions, 2) : 0, 'failed' => $failed, 'abandoned' => $abandoned,
            ],
            'byGateway' => $groupRows('gateway', fn ($k) => PaymentTransaction::GATEWAYS[$k] ?? ($k ?: '—')),
            'channels' => $channels, 'channelSeries' => $channelSeries, 'linkStats' => $linkStats,
            'byCourse' => $groupRows('course_id', fn ($k) => $courseNames[$k] ?? ('Course #' . $k)),
            'byExam' => $groupRows('exam_id', fn ($k) => $examNames[$k] ?? ('Exam #' . $k)),
            'exams' => StaffAccess::scope(CompetitiveExam::orderBy('position'), $request->user(), 'id')->get(), 'courses' => StaffAccess::scope(ExamCourse::orderBy('title'), $request->user())->get(['id', 'title', 'exam_id']),
            'commissionPercent' => (float) config('payments.commission_percent'),
            'activeGateway' => $gateways->activeName(), 'configured' => $gateways->status(),
        ]);
    }

    public function exportMonthly(Request $request)
    {
        $this->adminOnly($request);
        [$from, $to] = $this->range($request);
        $rows = $this->admissions($request, $from, $to)->selectRaw("DATE_FORMAT(admitted_at, '%Y-%m') as ym, count(*) as n, sum(status = 'cancelled') as cancelled, coalesce(sum(amount_paid),0) as gross, coalesce(sum(refunded_amount),0) as refunds, coalesce(sum(commission_amount),0) as commission")
            ->groupBy('ym')->orderBy('ym')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Month', 'Admissions', 'Cancelled', 'Gross', 'Refunds', 'Net', 'Commission', 'After commission']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->ym, $r->n, $r->cancelled, $r->gross, $r->refunds, round($r->gross - $r->refunds, 2), $r->commission, round($r->gross - $r->refunds - $r->commission, 2)]);
            }
            fclose($out);
        }, 'revenue-monthly-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ============================================================ transactions

    private function transactions(Request $request)
    {
        return StaffAccess::scope(PaymentTransaction::query(), $request->user())
            ->when($request->filled('exam'), fn ($q) => $q->whereIn('exam_id', CompetitiveExam::where('slug', $request->exam)->pluck('id')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course))
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->gateway))
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->channel))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('merchant_ref', 'like', '%' . $request->q . '%')->orWhere('gateway_payment_id', 'like', '%' . $request->q . '%')
                ->orWhere('gateway_order_id', 'like', '%' . $request->q . '%')->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->q . '%')->orWhere('mobile', 'like', '%' . $request->q . '%'))));
    }

    public function transactionsIndex(Request $request)
    {
        $this->adminOnly($request);
        $list = $this->transactions($request)->with(['user:id,name,mobile', 'course:id,title'])->orderByDesc('id')->paginate(per_page(30))->withQueryString();

        return view('admin.finance.transactions', [
            'list' => $list, 'exams' => StaffAccess::scope(CompetitiveExam::orderBy('position'), $request->user(), 'id')->get(), 'courses' => StaffAccess::scope(ExamCourse::orderBy('title'), $request->user())->get(['id', 'title', 'exam_id']),
        ]);
    }

    public function transactionsExport(Request $request)
    {
        $this->adminOnly($request);
        $rows = $this->transactions($request)->with(['user:id,name,mobile', 'course:id,title'])->orderByDesc('id')->limit(30000)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Date', 'Type', 'Gateway', 'Order no.', 'Gateway order', 'Gateway payment', 'Student', 'Mobile', 'Course', 'Amount', 'Status', 'Method', 'Reason']);
            foreach ($rows as $t) {
                fputcsv($out, [$t->id, ($t->paid_at ?? $t->created_at)?->format('Y-m-d H:i'), $t->type, $t->gateway, $t->merchant_ref, $t->gateway_order_id, $t->gateway_payment_id, $t->user?->name, $t->user?->mobile, $t->course?->title, $t->amount, $t->status, $t->method, $t->failure_reason]);
            }
            fclose($out);
        }, 'transactions-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ============================================================== gateway

    public function switchGateway(Request $request, PaymentManager $gateways)
    {
        abort_unless(StaffAccess::isAdmin($request->user()), 403, 'Only an Admin can switch the payment gateway.');
        $name = $request->validate(['gateway' => 'required|in:razorpay,phonepe'])['gateway'];
        \App\Services\ActivityLogger::record('updated', 'Payment gateway', 'Switched new payments to ' . ucfirst($name));

        if (!$gateways->gateway($name)->configured()) {
            return back()->withErrors(['gateway' => ucfirst($name) . ' has no keys in the .env file yet, so it cannot be switched on.']);
        }
        try {
            $gateways->switchTo($name, $request->user()->id);
        } catch (PaymentException $e) {
            return back()->withErrors(['gateway' => $e->getMessage()]);
        }

        return back()->with('success', 'New payments now go through ' . ucfirst($name) . '. Payments already started finish on the gateway they began with.');
    }
}
