@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Coupons</h3>
        <div class="small text-muted">Discount codes for courses of any exam type, with usage limits and analytics.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ admin_coupons('analytics') }}" class="btn btn-sm btn-outline-primary mb-0"><i class="bi bi-graph-up me-1"></i>Analytics</a>
        <a href="{{ admin_coupons('create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New Coupon</a>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach ([['Active coupons', $activeNow], ['Times used', number_format($totals->uses)], ['Discount given', '₹' . number_format($totals->discount)], ['Revenue after discount', '₹' . number_format($totals->revenue)]] as [$label, $value])
        <div class="col-6 col-lg-3"><div class="card bg-transparent border rounded-3"><div class="card-body py-2">
            <div class="small text-muted">{{ $label }}</div><div class="h4 mb-0">{{ $value }}</div>
        </div></div></div>
    @endforeach
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search code or name..."></div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $k => $label)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select name="exam" class="form-select form-select-sm">
            <option value="">Any exam type</option>
            @foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4">
        <select name="course" class="form-select form-select-sm">
            <option value="">Works on any course</option>
            @foreach ($courses as $id => $title)<option value="{{ $id }}" @selected(request('course') == $id)>{{ $title }}</option>@endforeach
        </select>
    </div>
    <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-light mb-0">Filter</button>
        <a href="{{ admin_coupons() }}" class="btn btn-sm btn-light mb-0">Reset</a>
    </div>
</form>

<div class="card bg-transparent border rounded-3">
    <div class="card-body p-0 table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead>
                <tr><th>Code</th><th>Discount</th><th>Exam types</th><th>Courses</th><th>Used</th><th>Per student</th><th>Valid</th><th>Status</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
                @forelse ($list as $c)
                    @php
                        [$stLabel, $stColor] = $c->state((int) $c->used_count);
                        $pct = $c->total_limit ? min(100, round($c->used_count / $c->total_limit * 100)) : null;
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ admin_coupons('' . $c->id) }}"><code class="fs-6">{{ $c->code }}</code></a>
                            @if ($c->name)<div class="small text-muted">{{ $c->name }}</div>@endif
                        </td>
                        <td>
                            <strong>{{ $c->discountLabel() }}</strong>
                            @if ($c->min_purchase > 0 || $c->max_purchase)
                                <div class="small text-muted">
                                    for ₹{{ number_format($c->min_purchase) }}@if ($c->max_purchase) – ₹{{ number_format($c->max_purchase) }}@else + @endif
                                </div>
                            @endif
                        </td>
                        <td>@if ($c->exam_scope === 'all')<span class="text-muted">All exam types</span>@else @foreach ($c->exams as $e)<span class="badge text-white me-1" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>@endforeach @endif</td>
                        <td>{{ $c->applies_to === 'all' ? 'All courses' : $c->courses_count . ' course(s)' }}</td>
                        <td style="min-width:120px">
                            {{ number_format($c->used_count) }}@if ($c->total_limit) / {{ number_format($c->total_limit) }}@else <span class="text-muted">/ ∞</span>@endif
                            @if ($pct !== null)<div class="progress mt-1" style="height:5px"><div class="progress-bar bg-{{ $pct >= 100 ? 'danger' : ($pct >= 80 ? 'warning' : 'success') }}" style="width:{{ $pct }}%"></div></div>@endif
                            <div class="small text-muted">₹{{ number_format($c->discount_total ?? 0) }} given</div>
                        </td>
                        <td>{{ $c->per_user_limit ? $c->per_user_limit . '×' : 'unlimited' }}</td>
                        <td class="small text-nowrap">
                            {{ $c->starts_at ? $c->starts_at->format('d M Y') : 'now' }}<br>→ {{ $c->expires_at ? $c->expires_at->format('d M Y') : 'no expiry' }}
                        </td>
                        <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ admin_coupons('' . $c->id) }}" class="btn btn-sm btn-circle btn-primary" title="Analytics"><i class="bi bi-bar-chart"></i></a>
                            <a href="{{ admin_coupons('' . $c->id . '/edit') }}" class="btn btn-sm btn-circle btn-info" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ admin_coupons('' . $c->id . '/toggle') }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-circle btn-{{ $c->is_active ? 'warning' : 'success' }}" title="{{ $c->is_active ? 'Pause' : 'Resume' }}"><i class="bi bi-{{ $c->is_active ? 'pause-fill' : 'play-fill' }}"></i></button>
                            </form>
                            <form action="{{ admin_coupons('' . $c->id . '/duplicate') }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-circle btn-light" title="Duplicate"><i class="bi bi-files"></i></button>
                            </form>
                            @if ($c->used_count == 0 && !\Illuminate\Support\Facades\DB::table('exam_coupon_redemptions')->where('coupon_id', $c->id)->exists())
                                <form action="{{ admin_coupons('' . $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete coupon {{ $c->code }}?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $list->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
