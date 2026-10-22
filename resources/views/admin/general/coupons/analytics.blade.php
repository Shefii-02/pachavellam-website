@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
@php
    $maxUses = max(1, $trend->max('uses'));
    $avgPct = $kpi->gross > 0 ? round($kpi->discount / $kpi->gross * 100, 1) : 0;
    $period = $days ? "last {$days} days" : 'all time';
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ admin_coupons() }}" class="small"><i class="bi bi-arrow-left"></i> All coupons</a>
        <h3 class="mb-0">Coupon analytics</h3>
    </div>
    <form method="GET"><select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">All time</option>
        @foreach (['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days'] as $k => $label)<option value="{{ $k }}" @selected((string) $days === (string) $k)>{{ $label }}</option>@endforeach
    </select></form>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Coupon uses', number_format($kpi->uses)],
        ['Students', number_format($kpi->students)],
        ['Coupons used', number_format($kpi->coupons)],
        ['Discount given', '₹' . number_format($kpi->discount)],
        ['Revenue (paid)', '₹' . number_format($kpi->revenue)],
        ['Avg discount', $avgPct . '%'],
    ] as [$label, $value])
        <div class="col-6 col-lg-2"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body py-2">
            <div class="small text-muted">{{ $label }}</div><div class="h5 mb-0">{{ $value }}</div>
        </div></div></div>
    @endforeach
</div>
<p class="small text-muted">Showing {{ $period }}. @if ($reversed){{ $reversed }} reversed redemption(s) are not counted.@endif</p>

<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Uses per day</h5></div>
    <div class="card-body">
        <div class="d-flex align-items-end" style="height:140px;gap:3px">
            @foreach ($trend as $d)
                <div class="flex-fill bg-primary" title="{{ $d['label'] }}: {{ $d['uses'] }} use(s), ₹{{ number_format($d['discount']) }} discount"
                    style="height:{{ round($d['uses'] / $maxUses * 100) }}%;min-height:{{ $d['uses'] ? 3 : 0 }}px"></div>
            @endforeach
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1"><span>{{ $trend->first()['label'] }}</span><span>peak {{ $trend->max('uses') }}/day</span><span>{{ $trend->last()['label'] }}</span></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Top coupons</h5></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Coupon</th><th>Uses</th><th>Students</th><th>Discount</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($topCoupons as $c)
                            <tr><td><a href="{{ admin_coupons('' . $c->id) }}"><code>{{ $c->code }}</code></a><div class="small text-muted">{{ $c->name }}</div></td>
                                <td>{{ number_format($c->uses) }}</td><td>{{ number_format($c->students) }}</td><td>₹{{ number_format($c->discount) }}</td><td>₹{{ number_format($c->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No coupon has been used {{ $days ? 'in this period' : 'yet' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Coupon use by course</h5></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Course</th><th>Uses</th><th>Coupons</th><th>Discount</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($byCourse as $r)
                            <tr><td>{{ $r->title }}</td><td>{{ number_format($r->uses) }}</td><td>{{ $r->coupons }}</td><td>₹{{ number_format($r->discount) }}</td><td>₹{{ number_format($r->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Nothing yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Coupon use by exam type</h5></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Exam type</th><th>Uses</th><th>Coupons</th><th>Course value</th><th>Discount</th><th>Revenue</th></tr></thead>
            <tbody>
                @forelse ($byExam as $r)
                    <tr><td>{{ $r->label }}</td><td>{{ number_format($r->uses) }}</td><td>{{ $r->coupons }}</td><td>₹{{ number_format($r->gross) }}</td><td>₹{{ number_format($r->discount) }}</td><td>₹{{ number_format($r->revenue) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Nothing yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    @foreach ([
        ['Expiring in 7 days', $expiring, fn ($c) => 'expires ' . $c->expires_at->diffForHumans()],
        ['Almost used up (80%+)', $almostGone, fn ($c) => $c->used_count . ' / ' . $c->total_limit . ' used'],
        ['Active but never used (7+ days old)', $unused, fn ($c) => 'created ' . $c->created_at->diffForHumans()],
    ] as [$title, $items, $note])
        <div class="col-lg-4">
            <div class="card bg-transparent border rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom"><h6 class="mb-0">{{ $title }}</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse ($items as $c)
                        <li class="list-group-item d-flex justify-content-between"><a href="{{ admin_coupons('' . $c->id) }}"><code>{{ $c->code }}</code></a><span class="small text-muted">{{ $note($c) }}</span></li>
                    @empty
                        <li class="list-group-item small text-muted">None</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endforeach
</div>
</div>
@endsection
