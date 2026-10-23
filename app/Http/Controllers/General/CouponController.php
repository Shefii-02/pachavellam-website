<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCoupon;
use App\Models\ExamCouponRedemption;
use App\Models\ExamCourse;
use App\Models\User;
use App\Services\CouponException;
use App\Services\CouponService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Coupons (General > Coupons, Admin only): rules, limits, which exam types and courses they apply to, redemptions and analytics.
 * One coupon can cover every exam type, or only chosen ones (e.g. KPSC and KTET).
 */
class CouponController extends Controller
{
    public function __construct(private CouponService $coupons)
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user() && $request->user()->type === 'Admin', 403, 'Only an Admin can manage coupons.');

            return $next($request);
        });
    }

    // ------------------------------------------------------------------ list

    public function index(Request $request)
    {
        $all = ExamCoupon::query()
            ->withCount(['redemptions as used_count' => fn ($q) => $q->where('status', 'redeemed')])
            ->withSum(['redemptions as discount_total' => fn ($q) => $q->where('status', 'redeemed')], 'discount_amount')
            ->withCount('courses')->with('exams:id,short_name,name')
            ->when($request->filled('exam'), fn ($q) => $q->forExam(CompetitiveExam::where('slug', $request->exam)->value('id') ?? 0))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', '%' . $request->q . '%')->orWhere('name', 'like', '%' . $request->q . '%')))
            ->when($request->filled('course'), fn ($q) => $q->where(fn ($w) => $w->where('applies_to', 'all')
                ->orWhereIn('id', fn ($s) => $s->select('coupon_id')->from('exam_coupon_courses')->where('course_id', $request->course))))
            ->orderByDesc('id')->get();

        // the state (active / paused / expired ...) depends on dates and usage, so it is worked out here
        $statuses = ['active' => 'Active', 'paused' => 'Paused', 'scheduled' => 'Scheduled', 'expired' => 'Expired', 'usedup' => 'Used up'];
        $map = ['Active' => 'active', 'Paused' => 'paused', 'Scheduled' => 'scheduled', 'Expired' => 'expired', 'Used up' => 'usedup'];

        $filtered = $all->filter(fn ($c) => !$request->filled('status') || ($map[$c->state((int) $c->used_count)[0]] ?? '') === $request->status)->values();

        $page = max(1, (int) $request->get('page', 1));
        $list = new LengthAwarePaginator($filtered->forPage($page, 20)->values(), $filtered->count(), 20, $page, ['path' => $request->url(), 'query' => $request->query()]);

        $totals = DB::table('exam_coupon_redemptions')->where('status', 'redeemed')
            ->selectRaw('COUNT(*) as uses, COALESCE(SUM(discount_amount),0) as discount, COALESCE(SUM(final_amount),0) as revenue')->first();

        return view('admin.general.coupons.index', [
            'list' => $list,
            'statuses' => $statuses,
            'totals' => $totals,
            'activeNow' => $all->filter(fn ($c) => $c->state((int) $c->used_count)[2])->count(),
            'courses' => ExamCourse::with('exam:id,short_name,name')->where('course_type', 'paid')->orderBy('title')->get()->mapWithKeys(fn ($c) => [$c->id => $c->title . ' (' . ($c->exam->short_name ?: $c->exam->name) . ')']),
            'exams' => CompetitiveExam::orderBy('position')->get(),
        ]);
    }

    // ------------------------------------------------------------ create/edit

    public function create()
    {
        return view('admin.general.coupons.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        $request->merge(['code' => ExamCoupon::normalizeCode((string) $request->input('code'))]);
        $bulk = (int) $request->input('bulk_count') >= 2;

        $data = $this->validated($request, null, $bulk);
        $courseIds = $data['course_ids'];
        $examIds = $data['exam_ids'];
        unset($data['course_ids'], $data['exam_ids']);

        $created = [];
        DB::transaction(function () use ($data, $courseIds, $examIds, $bulk, $request, &$created) {
            $codes = $bulk ? $this->generateCodes((int) $request->bulk_count, (string) $request->input('bulk_prefix')) : [$data['code']];

            foreach ($codes as $i => $code) {
                $coupon = ExamCoupon::create(array_merge($data, [
                    'code' => $code,
                    'name' => $bulk && $data['name'] ? $data['name'] . ' #' . ($i + 1) : $data['name'],
                    'created_by' => $request->user()->id,
                ]));
                $coupon->courses()->sync($courseIds);
                $coupon->exams()->sync($examIds);
                $created[] = $coupon;
            }
        });

        return redirect($bulk ? admin_coupons() : admin_coupons('' . $created[0]->id))
            ->with('message', $bulk ? count($created) . ' coupon codes created' : 'Coupon ' . $created[0]->code . ' created');
    }

    public function edit($id)
    {
        return view('admin.general.coupons.form', $this->formData(ExamCoupon::findOrFail($id)));
    }

    public function update(Request $request, $id)
    {
        $coupon = ExamCoupon::findOrFail($id);
        $request->merge(['code' => ExamCoupon::normalizeCode((string) $request->input('code', $coupon->code))]);

        // the code is printed on posters and used in orders, so it is frozen once someone used it
        $hasUses = $coupon->redemptions()->exists();
        if ($hasUses && $request->code !== $coupon->code) {
            return back()->withInput()->withErrors(['code' => 'This coupon has been used, so its code cannot change. Create a new coupon instead.']);
        }

        $data = $this->validated($request, $coupon, false);
        $courseIds = $data['course_ids'];
        $examIds = $data['exam_ids'];
        unset($data['course_ids'], $data['exam_ids']);

        DB::transaction(function () use ($coupon, $data, $courseIds, $examIds) {
            $coupon->update($data);
            $coupon->courses()->sync($courseIds);
            $coupon->exams()->sync($examIds);
        });

        return redirect(admin_coupons('' . $coupon->id))->with('message', 'Coupon updated');
    }

    public function destroy($id)
    {
        $coupon = ExamCoupon::findOrFail($id);

        if ($coupon->redemptions()->exists()) {
            return back()->with('error', 'This coupon has been used, so it is kept for the records. Pause it instead.');
        }

        $coupon->delete();

        return redirect(admin_coupons())->with('message', 'Coupon deleted');
    }

    public function toggle($id)
    {
        $coupon = ExamCoupon::findOrFail($id);
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return back()->with('message', $coupon->is_active ? 'Coupon resumed' : 'Coupon paused. Nobody can use it until you resume it.');
    }

    /** Same rules, new code. Handy for repeating an offer. */
    public function duplicate($id)
    {
        $src = ExamCoupon::findOrFail($id);

        $copy = DB::transaction(function () use ($src) {
            $copy = $src->replicate(['created_at', 'updated_at']);
            $copy->code = $this->generateCodes(1, Str::before($src->code, '-') === $src->code ? 'COPY' : Str::before($src->code, '-'))[0];
            $copy->is_active = false;   // review before it goes live
            $copy->created_by = auth()->id();
            $copy->save();
            $copy->courses()->sync($src->courses()->pluck('exam_courses.id')->all());
            $copy->exams()->sync($src->exams()->pluck('competitive_exams.id')->all());

            return $copy;
        });

        return redirect(admin_coupons('' . $copy->id . '/edit'))->with('message', 'Copied as ' . $copy->code . ' (paused). Review it, then resume.');
    }

    // -------------------------------------------------------------- analytics

    /** One coupon: numbers, trend, courses, students and every redemption. */
    public function show(Request $request, $id)
    {
        $coupon = ExamCoupon::findOrFail($id);
        $redeemed = fn () => DB::table('exam_coupon_redemptions')->where('exam_coupon_redemptions.coupon_id', $coupon->id)->where('exam_coupon_redemptions.status', 'redeemed');

        $kpi = $redeemed()->selectRaw('COUNT(*) as uses, COUNT(DISTINCT user_id) as students, COALESCE(SUM(original_amount),0) as gross, COALESCE(SUM(discount_amount),0) as discount, COALESCE(SUM(final_amount),0) as revenue')->first();
        $reversed = DB::table('exam_coupon_redemptions')->where('coupon_id', $coupon->id)->where('status', 'reversed')->count();

        // last 30 days, empty days filled with zero
        $perDay = $redeemed()->where('created_at', '>=', Carbon::today()->subDays(29))
            ->selectRaw('DATE(created_at) as d, COUNT(*) as uses, SUM(discount_amount) as discount')->groupBy('d')->get()->keyBy('d');
        $trend = collect(range(29, 0))->map(function ($i) use ($perDay) {
            $d = Carbon::today()->subDays($i);
            $r = $perDay->get($d->toDateString());
            return ['label' => $d->format('d M'), 'uses' => (int) optional($r)->uses, 'discount' => (float) optional($r)->discount];
        });

        $byCourse = $redeemed()->leftJoin('exam_courses as c', 'c.id', '=', 'exam_coupon_redemptions.course_id')
            ->selectRaw('exam_coupon_redemptions.course_id, COALESCE(c.title, "(deleted course)") as title, COUNT(*) as uses, SUM(discount_amount) as discount, SUM(final_amount) as revenue')
            ->groupBy('exam_coupon_redemptions.course_id', 'c.title')->orderByDesc('uses')->get();

        $perStudent = $redeemed()->whereNotNull('user_id')->select('user_id', DB::raw('COUNT(*) as uses'), DB::raw('SUM(discount_amount) as discount'))->groupBy('user_id');
        $topStudents = DB::query()->fromSub($perStudent, 's')->join('users as u', 'u.id', '=', 's.user_id')
            ->select('u.id', 'u.name', 'u.email', 'u.mobile', 's.uses', 's.discount')->orderByDesc('s.uses')->orderByDesc('s.discount')->limit(10)->get();
        $distribution = DB::query()->fromSub($perStudent, 's')->selectRaw('CASE WHEN uses >= 3 THEN "3+" ELSE CAST(uses AS CHAR) END as bucket, COUNT(*) as students')->groupBy('bucket')->orderBy('bucket')->pluck('students', 'bucket');

        // all redemptions, filterable
        $rows = ExamCouponRedemption::with(['user:id,name,email,mobile', 'course:id,title'])
            ->where('coupon_id', $coupon->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', $request->course))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('q'), fn ($q) => $q->whereIn('user_id', fn ($s) => $s->select('id')->from('users')
                ->where(fn ($w) => $w->where('name', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%')->orWhere('mobile', 'like', '%' . $request->q . '%'))))
            ->latest('id')->paginate(per_page(20))->withQueryString();

        return view('admin.general.coupons.show', [
            'coupon' => $coupon->loadCount(['courses']),
            'kpi' => $kpi,
            'reversed' => $reversed,
            'trend' => $trend,
            'byCourse' => $byCourse,
            'topStudents' => $topStudents,
            'distribution' => $distribution,
            'rows' => $rows,
            'state' => $coupon->state((int) $kpi->uses),
            'appliesTo' => $coupon->applies_to === 'selected' ? $coupon->courses()->pluck('title', 'exam_courses.id') : null,
            'examTypes' => $coupon->exam_scope === 'selected' ? $coupon->exams()->get() : null,
            'byExam' => $redeemed()->leftJoin('competitive_exams as e', 'e.id', '=', 'exam_coupon_redemptions.exam_id')
                ->selectRaw('COALESCE(e.short_name, e.name, "(unknown)") as label, COUNT(*) as uses, SUM(discount_amount) as discount, SUM(final_amount) as revenue')->groupBy('e.short_name', 'e.name')->orderByDesc('uses')->get(),
            'courseFilter' => ExamCourse::whereIn('id', DB::table('exam_coupon_redemptions')->where('coupon_id', $coupon->id)->pluck('course_id'))->pluck('title', 'id'),
            'payable' => ExamCourse::with('exam:id,short_name,name')->where('course_type', 'paid')->where('status', 'published')->orderBy('title')->get()
                ->filter(fn ($c) => $coupon->exam_scope === 'all' || $coupon->exams()->where('competitive_exams.id', $c->exam_id)->exists())
                ->mapWithKeys(fn ($c) => [$c->id => $c->title . ' (' . ($c->exam->short_name ?: $c->exam->name) . ')']),
        ]);
    }

    /** All coupons together: totals, trend, top coupons, usage per course, what needs attention. */
    public function analytics(Request $request)
    {
        $days = in_array($request->days, ['7', '30', '90']) ? (int) $request->days : null;   // null = all time
        $since = $days ? Carbon::today()->subDays($days - 1) : null;
        $base = fn () => DB::table('exam_coupon_redemptions as r')->where('r.status', 'redeemed')->when($since, fn ($q) => $q->where('r.created_at', '>=', $since));

        $kpi = $base()->selectRaw('COUNT(*) as uses, COUNT(DISTINCT r.user_id) as students, COUNT(DISTINCT r.coupon_id) as coupons, COALESCE(SUM(r.original_amount),0) as gross, COALESCE(SUM(r.discount_amount),0) as discount, COALESCE(SUM(r.final_amount),0) as revenue')->first();
        $reversed = DB::table('exam_coupon_redemptions as r')->where('r.status', 'reversed')->when($since, fn ($q) => $q->where('r.created_at', '>=', $since))->count();

        $trendDays = $days ?: 30;
        $perDay = $base()->where('r.created_at', '>=', Carbon::today()->subDays($trendDays - 1))
            ->selectRaw('DATE(r.created_at) as d, COUNT(*) as uses, SUM(r.discount_amount) as discount')->groupBy('d')->get()->keyBy('d');
        $trend = collect(range($trendDays - 1, 0))->map(function ($i) use ($perDay) {
            $d = Carbon::today()->subDays($i);
            $r = $perDay->get($d->toDateString());
            return ['label' => $d->format('d M'), 'uses' => (int) optional($r)->uses, 'discount' => (float) optional($r)->discount];
        });

        $topCoupons = $base()->join('exam_coupons as c', 'c.id', '=', 'r.coupon_id')
            ->selectRaw('c.id, c.code, c.name, COUNT(*) as uses, COUNT(DISTINCT r.user_id) as students, SUM(r.discount_amount) as discount, SUM(r.final_amount) as revenue')
            ->groupBy('c.id', 'c.code', 'c.name')->orderByDesc('uses')->limit(10)->get();

        // coupon use per course: how many paid with a coupon, discount given, revenue
        $byCourse = $base()->leftJoin('exam_courses as k', 'k.id', '=', 'r.course_id')
            ->selectRaw('r.course_id, COALESCE(k.title, "(deleted course)") as title, COUNT(*) as uses, COUNT(DISTINCT r.coupon_id) as coupons, SUM(r.original_amount) as gross, SUM(r.discount_amount) as discount, SUM(r.final_amount) as revenue')
            ->groupBy('r.course_id', 'k.title')->orderByDesc('uses')->limit(15)->get();

        $byExam = $base()->leftJoin('competitive_exams as e', 'e.id', '=', 'r.exam_id')
            ->selectRaw('COALESCE(e.short_name, e.name, "(unknown)") as label, COUNT(*) as uses, COUNT(DISTINCT r.coupon_id) as coupons, SUM(r.original_amount) as gross, SUM(r.discount_amount) as discount, SUM(r.final_amount) as revenue')
            ->groupBy('e.short_name', 'e.name')->orderByDesc('uses')->get();

        // coupons that need a look
        $withUse = ExamCoupon::withCount(['redemptions as used_count' => fn ($q) => $q->where('status', 'redeemed')])->get();
        $expiring = $withUse->filter(fn ($c) => $c->is_active && $c->expires_at && $c->expires_at->isFuture() && $c->expires_at->lte(now()->addDays(7)))->sortBy('expires_at')->values();
        $almostGone = $withUse->filter(fn ($c) => $c->is_active && $c->total_limit && $c->used_count < $c->total_limit && $c->used_count / $c->total_limit >= 0.8)->values();
        $unused = $withUse->filter(fn ($c) => $c->state((int) $c->used_count)[2] && $c->used_count === 0 && $c->created_at->lte(now()->subDays(7)))->values();

        return view('admin.general.coupons.analytics', compact('kpi', 'reversed', 'trend', 'topCoupons', 'byCourse', 'byExam', 'expiring', 'almostGone', 'unused', 'days'));
    }

    // ------------------------------------------------------------ redemptions

    /** Record a use by hand (offline / manual enrolment). Goes through exactly the same checks as the app. */
    public function redeem(Request $request, $id)
    {
        $coupon = ExamCoupon::findOrFail($id);

        $request->validate([
            'student' => 'required|string|max:100',
            'course_id' => 'required|integer|exists:exam_courses,id',
            'reference' => 'nullable|string|max:100',
        ]);

        $needle = trim($request->student);
        $student = User::where('type', 'Student')->where(fn ($q) => $q->where('id', ctype_digit($needle) ? $needle : 0)->orWhere('email', $needle)->orWhere('mobile', $needle))->first();

        if (!$student) {
            return back()->withInput()->withErrors(['student' => 'No student found with that ID, email or mobile number.']);
        }

        try {
            $r = $this->coupons->redeem($coupon->code, $student, ExamCourse::findOrFail($request->course_id), 'admin', $request->reference, $request->user()->id);
        } catch (CouponException $e) {
            return back()->withInput()->withErrors(['student' => $e->getMessage()]);
        }

        return back()->with('message', "Recorded: {$student->name} paid ₹" . number_format($r->final_amount, 2) . ' (saved ₹' . number_format($r->discount_amount, 2) . ').');
    }

    public function reverse(Request $request, $id)
    {
        $redemption = ExamCouponRedemption::findOrFail($id);
        $request->validate(['reason' => 'nullable|string|max:200']);

        $this->coupons->reverse($redemption, $request->reason);

        return back()->with('message', 'Redemption reversed. That use is free again.');
    }

    public function export(Request $request, $id)
    {
        $coupon = ExamCoupon::findOrFail($id);
        $safe = fn ($v) => is_string($v) && $v !== '' && strpbrk($v[0], '=+-@') ? "'" . $v : $v;

        return response()->streamDownload(function () use ($coupon, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Date', 'Status', 'Student ID', 'Student', 'Mobile', 'Email', 'Course', 'Course price', 'Discount', 'Paid', 'Source', 'Reference', 'Reversed reason']);

            ExamCouponRedemption::with(['user:id,name,email,mobile', 'course:id,title'])->where('coupon_id', $coupon->id)->orderBy('id')
                ->chunkById(500, function ($rows) use ($out, $safe) {
                    foreach ($rows as $r) {
                        fputcsv($out, [$r->id, $r->created_at->format('Y-m-d H:i'), $r->status, $r->user_id, $safe(optional($r->user)->name), $safe(optional($r->user)->mobile), $safe(optional($r->user)->email),
                            $safe(optional($r->course)->title), $r->original_amount, $r->discount_amount, $r->final_amount, $r->source, $safe($r->reference), $safe($r->reversed_reason)]);
                    }
                });
            fclose($out);
        }, 'coupon-' . $coupon->code . '-redemptions-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------------------------------------------------------------- helpers

    private function formData(?ExamCoupon $coupon): array
    {
        return [
            'coupon' => $coupon,
            'courses' => ExamCourse::with('exam:id,short_name,name')->orderBy('exam_id')->orderBy('course_type')->orderBy('title')->get(['id', 'exam_id', 'title', 'course_type', 'price', 'discount_price', 'status']),
            'exams' => CompetitiveExam::orderBy('position')->get(),
            'selectedExams' => $coupon ? $coupon->exams()->pluck('competitive_exams.id')->all() : [],
            'selected' => $coupon ? $coupon->courses()->pluck('exam_courses.id')->all() : [],
            'hasUses' => $coupon ? $coupon->redemptions()->exists() : false,
            'used' => $coupon ? $coupon->activeRedemptions()->count() : 0,
        ];
    }

    private function validated(Request $request, ?ExamCoupon $coupon, bool $bulk): array
    {
        $data = $request->validate([
            'code' => [$bulk ? 'nullable' : 'required', 'regex:/^[A-Z0-9_\-]{3,30}$/', Rule::unique('exam_coupons', 'code')->ignore($coupon?->id)],
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'gt:0', $request->discount_type === 'percent' ? 'max:100' : 'max:9999999'],
            'max_discount' => 'nullable|numeric|gt:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_purchase' => 'nullable|numeric|gt:0|gte:min_purchase',
            'total_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'exam_scope' => ['required', Rule::in(['all', 'selected'])],
            'exam_ids' => [$request->exam_scope === 'selected' ? 'required' : 'nullable', 'array'],
            'exam_ids.*' => 'integer|exists:competitive_exams,id',
            'applies_to' => ['required', Rule::in(['all', 'selected'])],
            'course_ids' => [$request->applies_to === 'selected' ? 'required' : 'nullable', 'array'],
            'course_ids.*' => 'integer|exists:exam_courses,id',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'bulk_count' => 'nullable|integer|min:2|max:200',
            'bulk_prefix' => ['nullable', 'regex:/^[A-Za-z0-9]{0,12}$/'],
        ], [
            'code.regex' => 'Use 3-30 letters, numbers, dashes or underscores.',
            'discount_value.max' => 'A percentage cannot be more than 100.',
            'course_ids.required' => 'Tick at least one course, or choose "All courses".',
            'exam_ids.required' => 'Tick at least one exam type, or choose "All exam types".',
            'max_purchase.gte' => 'The maximum purchase must be at least the minimum purchase.',
            'expires_at.after' => 'The expiry must be after the start.',
        ]);

        $data['code'] = $data['code'] ?? null;
        $data['name'] = $data['name'] ?? null;
        $data['description'] = $data['description'] ?? null;
        $data['max_discount'] = $data['discount_type'] === 'percent' ? ($data['max_discount'] ?? null) : null;
        $data['min_purchase'] = $data['min_purchase'] ?? 0;
        $data['max_purchase'] = $data['max_purchase'] ?? null;
        $data['total_limit'] = $data['total_limit'] ?? null;
        $data['per_user_limit'] = $data['per_user_limit'] ?? null;
        $data['starts_at'] = $data['starts_at'] ?? null;
        $data['expires_at'] = $data['expires_at'] ?? null;
        $data['exam_ids'] = $data['exam_scope'] === 'selected' ? array_values(array_unique(array_map('intval', $data['exam_ids'] ?? []))) : [];
        $data['course_ids'] = $data['applies_to'] === 'selected' ? array_values(array_unique($data['course_ids'] ?? [])) : [];

        // ticked courses must belong to the exam types the coupon covers
        if ($data['exam_scope'] === 'selected' && $data['course_ids']) {
            $outside = ExamCourse::whereIn('id', $data['course_ids'])->whereNotIn('exam_id', $data['exam_ids'])->count();
            if ($outside) {
                throw \Illuminate\Validation\ValidationException::withMessages(['course_ids' => $outside . ' ticked course(s) belong to exam types this coupon does not cover. Tick those exam types too, or untick the courses.']);
            }
        }
        $data['is_active'] = $request->boolean('is_active');
        unset($data['bulk_count'], $data['bulk_prefix']);

        return $data;
    }

    /** Random, easy to read codes (no 0/O or 1/I) that are not used yet. */
    private function generateCodes(int $count, string $prefix = ''): array
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $prefix));
        $codes = [];

        while (count($codes) < $count) {
            $part = '';
            for ($i = 0; $i < 8; $i++) {
                $part .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = $prefix !== '' ? $prefix . '-' . $part : $part;

            if (!in_array($code, $codes) && !ExamCoupon::where('code', $code)->exists()) {
                $codes[] = $code;
            }
        }

        return $codes;
    }
}
