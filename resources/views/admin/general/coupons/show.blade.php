@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
@php
    [$stLabel, $stColor, $usable] = $state;
    $base = admin_coupons('' . $coupon->id);
    $used = (int) $kpi->uses;
    $maxUses = max(1, $trend->max('uses'));
    $left = $coupon->total_limit !== null ? max(0, $coupon->total_limit - $used) : null;
    $avgPct = $kpi->gross > 0 ? round($kpi->discount / $kpi->gross * 100, 1) : 0;
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ admin_coupons() }}" class="small"><i class="bi bi-arrow-left"></i> All coupons</a>
        <h3 class="mb-1"><code class="fs-3">{{ $coupon->code }}</code>
            <span class="badge bg-{{ $stColor }} fs-6">{{ $stLabel }}</span></h3>
        <div class="text-muted">{{ $coupon->name }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ $base }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form action="{{ $base }}/toggle" method="POST">@csrf
            <button class="btn btn-sm btn-{{ $coupon->is_active ? 'warning' : 'success' }} mb-0">{{ $coupon->is_active ? 'Pause' : 'Resume' }}</button></form>
        <a href="{{ $base }}/export" class="btn btn-sm btn-success mb-0"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
</div>

{{-- rules at a glance --}}
<div class="card bg-transparent border rounded-3 mb-4"><div class="card-body py-2"><div class="row g-3 small">
    <div class="col-6 col-lg-3"><span class="text-muted">Discount</span><div class="fw-bold">{{ $coupon->discountLabel() }}</div></div>
    <div class="col-6 col-lg-3"><span class="text-muted">Course price</span><div class="fw-bold">₹{{ number_format($coupon->min_purchase) }}{{ $coupon->max_purchase ? ' – ₹' . number_format($coupon->max_purchase) : ' and above' }}</div></div>
    <div class="col-6 col-lg-3"><span class="text-muted">Limits</span><div class="fw-bold">{{ $coupon->total_limit ? number_format($coupon->total_limit) . ' uses' : 'unlimited uses' }} · {{ $coupon->per_user_limit ? $coupon->per_user_limit . '× per student' : 'unlimited per student' }}</div></div>
    <div class="col-6 col-lg-3"><span class="text-muted">Valid</span><div class="fw-bold">{{ $coupon->starts_at ? $coupon->starts_at->format('d M Y') : 'now' }} → {{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'no expiry' }}</div></div>
    <div class="col-12"><span class="text-muted">Exam types</span>
        @if ($examTypes === null) <span class="fw-bold">All exam types</span>
        @else @foreach ($examTypes as $e)<span class="badge text-white me-1" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>@endforeach @endif
    </div>
    <div class="col-12"><span class="text-muted">Applies to</span>
        @if ($appliesTo === null) <span class="fw-bold">All courses</span>
        @else @foreach ($appliesTo as $t)<span class="badge bg-light text-dark border me-1">{{ $t }}</span>@endforeach @endif
    </div>
</div></div></div>

{{-- numbers --}}
<div class="row g-3 mb-4">
    @foreach ([
        ['Times used', number_format($used) . ($coupon->total_limit ? ' / ' . number_format($coupon->total_limit) : '')],
        ['Uses left', $left === null ? '∞' : number_format($left)],
        ['Students', number_format($kpi->students) . ($kpi->students ? ' · avg ' . number_format($used / max(1, $kpi->students), 1) . '×' : '')],
        ['Discount given', '₹' . number_format($kpi->discount)],
        ['Revenue (paid)', '₹' . number_format($kpi->revenue)],
        ['Avg discount', $avgPct . '%'],
    ] as [$label, $value])
        <div class="col-6 col-lg-2"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body py-2">
            <div class="small text-muted">{{ $label }}</div><div class="h5 mb-0">{{ $value }}</div>
        </div></div></div>
    @endforeach
</div>
@if ($reversed)<p class="small text-muted">{{ $reversed }} redemption(s) were reversed (refunds) and are not counted above.</p>@endif

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Uses, last 30 days</h5></div>
            <div class="card-body">
                <div class="d-flex align-items-end" style="height:130px;gap:3px">
                    @foreach ($trend as $d)
                        <div class="flex-fill bg-primary" title="{{ $d['label'] }}: {{ $d['uses'] }} use(s), ₹{{ number_format($d['discount']) }} discount"
                            style="height:{{ round($d['uses'] / $maxUses * 100) }}%;min-height:{{ $d['uses'] ? 3 : 0 }}px"></div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between small text-muted mt-1"><span>{{ $trend->first()['label'] }}</span><span>peak {{ $trend->max('uses') }}/day</span><span>{{ $trend->last()['label'] }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Students by number of uses</h5></div>
            <div class="card-body">
                @forelse ($distribution as $bucket => $students)
                    <div class="d-flex justify-content-between small"><span>Used {{ $bucket }} time(s)</span><strong>{{ $students }} student(s)</strong></div>
                    <div class="progress mb-2" style="height:6px"><div class="progress-bar" style="width:{{ round($students / max(1, $distribution->sum()) * 100) }}%"></div></div>
                @empty
                    <span class="text-muted small">Nobody has used it yet.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Used on which courses</h5></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Course</th><th>Uses</th><th>Discount</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($byCourse as $r)
                            <tr><td>{{ $r->title }}</td><td>{{ number_format($r->uses) }}</td><td>₹{{ number_format($r->discount) }}</td><td>₹{{ number_format($r->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-3">No uses yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Top students</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        @forelse ($topStudents as $s)
                            <tr>
                                <td><a href="{{ student_cms('student-list/' . $s->id) }}">{{ $s->name ?: '(no name)' }}</a><div class="small text-muted">{{ $s->mobile ?: $s->email }}</div></td>
                                <td class="text-end">{{ $s->uses }}× <div class="small text-muted">₹{{ number_format($s->discount) }}</div></td>
                            </tr>
                        @empty
                            <tr><td class="text-muted text-center py-3">No students yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- by exam type --}}
@if ($byExam->count() > 1)
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Used on which exam types</h5></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Exam type</th><th>Uses</th><th>Discount</th><th>Revenue</th></tr></thead>
            <tbody>@foreach ($byExam as $r)<tr><td>{{ $r->label }}</td><td>{{ number_format($r->uses) }}</td><td>₹{{ number_format($r->discount) }}</td><td>₹{{ number_format($r->revenue) }}</td></tr>@endforeach</tbody>
        </table>
    </div>
</div>
@endif

{{-- manual redemption --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Record a use</h5>
        <div class="small text-muted">For an offline or manual enrolment. It goes through the same checks and limits as the app.</div></div>
    <div class="card-body">
        <form action="{{ $base }}/redeem" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-3"><label class="form-label small mb-1">Student ID, email or mobile</label><input class="form-control form-control-sm" name="student" value="{{ old('student') }}" required>
                @error('student')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-4"><label class="form-label small mb-1">Course</label>
                <select class="form-control form-control-sm" name="course_id" required>
                    @foreach ($payable as $id => $title)<option value="{{ $id }}" @selected(old('course_id') == $id)>{{ $title }}</option>@endforeach
                </select></div>
            <div class="col-md-3"><label class="form-label small mb-1">Payment reference (optional)</label><input class="form-control form-control-sm" name="reference" value="{{ old('reference') }}"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary mb-0 w-100">Record</button></div>
        </form>
    </div>
</div>

{{-- every redemption --}}
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-lg-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Student name, email, mobile..."></div>
            <div class="col-6 col-lg-2"><select name="status" class="form-select form-select-sm"><option value="">All</option><option value="redeemed" @selected(request('status') === 'redeemed')>Used</option><option value="reversed" @selected(request('status') === 'reversed')>Reversed</option></select></div>
            <div class="col-6 col-lg-3"><select name="course" class="form-select form-select-sm"><option value="">All courses</option>@foreach ($courseFilter as $id => $t)<option value="{{ $id }}" @selected(request('course') == $id)>{{ $t }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
            <div class="col-6 col-lg-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
            <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ $base }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm align-middle mb-0 table-hover">
            <thead><tr><th>When</th><th>Student</th><th>Course</th><th>Price</th><th>Discount</th><th>Paid</th><th>Source</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr class="{{ $r->status === 'reversed' ? 'text-muted' : '' }}">
                        <td class="text-nowrap">{{ $r->created_at->format('d M Y, h:i A') }}</td>
                        <td>@if ($r->user)<a href="{{ student_cms('student-list/' . $r->user_id) }}">{{ $r->user->name ?: '(no name)' }}</a><div class="small text-muted">{{ $r->user->mobile ?: $r->user->email }}</div>@else — @endif</td>
                        <td>{{ optional($r->course)->title ?? '—' }}</td>
                        <td>₹{{ number_format($r->original_amount, 2) }}</td>
                        <td>₹{{ number_format($r->discount_amount, 2) }}</td>
                        <td><strong>₹{{ number_format($r->final_amount, 2) }}</strong></td>
                        <td>{{ ucfirst($r->source) }}@if ($r->reference)<div class="small text-muted">{{ $r->reference }}</div>@endif</td>
                        <td>@if ($r->status === 'reversed')<span class="badge bg-secondary" title="{{ $r->reversed_reason }}">Reversed</span>@else<span class="badge bg-success">Used</span>@endif</td>
                        <td class="text-end">
                            @if ($r->status === 'redeemed')
                                <form action="{{ admin_coupons('redemptions/' . $r->id . '/reverse') }}" method="POST" onsubmit="var r = prompt('Reason for reversing (refund / cancelled order)?'); if (r === null) return false; this.reason.value = r; return true;">
                                    @csrf <input type="hidden" name="reason">
                                    <button class="btn btn-sm btn-outline-secondary mb-0" title="Give this use back">Reverse</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No redemptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">@include('admin.partials.per-page', ['p' => $rows])
{{ $rows->links('pagination::bootstrap-5') }}</div>
</div>
</div>
@endsection
