@extends('admin.main-layout')
@section('main')
@php
    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    $qs = http_build_query(request()->query());
@endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div><h3 class="mb-0">Admissions &amp; Revenue</h3><div class="small text-muted">{{ $from->format('M Y') }} – {{ $to->format('M Y') }} · money is counted in the month the student was admitted.</div></div>
        <a href="{{ url('admin/revenue/export') }}?{{ $qs }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-download me-1"></i>Monthly CSV</a>
    </div>
    @include('admin.finance._tabs')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-6 col-md-2"><input type="month" name="from" value="{{ $from->format('Y-m') }}" class="form-control form-control-sm" title="From month"></div>
        <div class="col-6 col-md-2"><input type="month" name="to" value="{{ $to->format('Y-m') }}" class="form-control form-control-sm" title="To month"></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-3"><select name="course" class="form-select form-select-sm"><option value="">All courses</option>@foreach ($courses as $c)<option value="{{ $c->id }}" @selected(request('course') == $c->id)>{{ $c->title }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="gateway" class="form-select form-select-sm"><option value="">Any payment</option>@foreach (\App\Models\PaymentTransaction::GATEWAYS as $k => $l)<option value="{{ $k }}" @selected(request('gateway') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-1"><select name="source" class="form-select form-select-sm"><option value="">Any</option>@foreach (\App\Services\Payments\AdmissionService::CHANNELS as $k => $l)<option value="{{ $k }}" @selected(request('source') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-primary mb-0">Apply</button><a href="{{ url('admin/revenue') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <div class="row g-3 mb-3">
        @foreach ([
            ['Admissions', number_format($kpi['admissions']), number_format($kpi['cancelled']) . ' cancelled · fee value ' . $money($kpi['value']), 'bi-person-check', 'primary'],
            ['Collected', $money($kpi['gross']), 'balance due ' . $money($kpi['due']), 'bi-cash-stack', 'success'],
            ['Refunded', $money($kpi['refunds']), 'net ' . $money($kpi['net']), 'bi-arrow-counterclockwise', 'danger'],
            ['Commission ' . rtrim(rtrim(number_format($commissionPercent, 2), '0'), '.') . '%', $money($kpi['commission']), 'on each admission\'s fee', 'bi-percent', 'warning'],
            ['After commission', $money($kpi['payout']), 'collected − refunds − commission', 'bi-wallet2', 'info'],
            ['Payments not completed', number_format($kpi['failed'] + $kpi['abandoned']), $kpi['failed'] . ' failed · ' . $kpi['abandoned'] . ' left open', 'bi-exclamation-circle', 'secondary'],
        ] as [$label, $value, $sub, $icon, $color])
            <div class="col-6 col-xl-2"><div class="card border-0 bg-{{ $color }} bg-opacity-10 h-100"><div class="card-body py-3">
                <div class="d-flex justify-content-between"><span class="small text-muted">{{ $label }}</span><i class="bi {{ $icon }} text-{{ $color }}"></i></div>
                <div class="h4 mb-0">{{ $value }}</div><div class="small text-muted text-truncate" title="{{ $sub }}">{{ $sub }}</div>
            </div></div></div>
        @endforeach
    </div>
    @if ($commissionPercent <= 0)<div class="alert alert-info py-2 small">No commission is set. Add <code>COMMISSION_PERCENT=10</code> (your percentage) to the .env file; it applies to payments received from then on.</div>@endif

    <div class="row g-3 mb-3">
        <div class="col-lg-8"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body"><h6>Revenue and admissions by month</h6><div id="revChart" style="min-height:300px"></div></div></div></div>
        <div class="col-lg-4">
            <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
                <h6>Payment gateway</h6>
                <form action="{{ url('admin/revenue/gateway') }}" method="POST">@csrf
                    @foreach (['razorpay' => 'Razorpay', 'phonepe' => 'PhonePe'] as $k => $l)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="gateway" id="g{{ $k }}" value="{{ $k }}" @checked($activeGateway === $k) @disabled(!$configured[$k])>
                            <label class="form-check-label" for="g{{ $k }}">{{ $l }} @if ($configured[$k])<span class="badge bg-success">keys set</span>@else<span class="badge bg-secondary">no keys in .env</span>@endif @if ($activeGateway === $k)<span class="badge bg-primary">in use</span>@endif</label>
                        </div>
                    @endforeach
                    <button class="btn btn-sm btn-primary mb-0 mt-1">Switch</button>
                    <div class="form-text mt-2">New payments use the chosen gateway. Payments already started finish on the gateway they began with. Keys and the commission % are set in the .env file.</div>
                </form>
            </div></div>
        </div>
    </div>

    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>Monthly</strong></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Month</th><th class="text-end">Admissions</th><th class="text-end">Cancelled</th><th class="text-end">Fee value</th><th class="text-end">Collected</th><th class="text-end">Due</th><th class="text-end">Refunds</th><th class="text-end">Commission</th><th class="text-end">Collected − refunds − commission</th></tr></thead>
            <tbody>
            @foreach (array_reverse($months) as $m)
                <tr class="{{ $m['admissions'] ? '' : 'text-muted' }}"><td>{{ $m['label'] }}</td><td class="text-end">{{ $m['admissions'] }}</td><td class="text-end">{{ $m['cancelled'] }}</td><td class="text-end">{{ $money($m['value']) }}</td><td class="text-end">{{ $money($m['gross']) }}</td><td class="text-end">{{ $money($m['due']) }}</td><td class="text-end">{{ $money($m['refunds']) }}</td><td class="text-end">{{ $money($m['commission']) }}</td><td class="text-end fw-semibold">{{ $money($m['payout']) }}</td></tr>
            @endforeach
            <tr class="fw-bold border-top"><td>Total</td><td class="text-end">{{ $kpi['admissions'] }}</td><td class="text-end">{{ $kpi['cancelled'] }}</td><td class="text-end">{{ $money($kpi['value']) }}</td><td class="text-end">{{ $money($kpi['gross']) }}</td><td class="text-end">{{ $money($kpi['due']) }}</td><td class="text-end">{{ $money($kpi['refunds']) }}</td><td class="text-end">{{ $money($kpi['commission']) }}</td><td class="text-end">{{ $money($kpi['payout']) }}</td></tr>
            </tbody>
        </table></div>
    </div>

    {{-- channels --}}
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2 d-flex justify-content-between flex-wrap gap-2">
            <strong>By channel <span class="small text-muted fw-normal">- where the student bought from</span></strong>
            <span class="small text-muted">Payment links: {{ (int) $linkStats->made }} made · {{ (int) $linkStats->paid }} paid · {{ (int) $linkStats->expired }} expired · {{ (int) $linkStats->cancelled }} cancelled</span>
        </div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Channel</th><th class="text-end">Admissions</th><th class="text-end">Collected</th><th class="text-end">Refunds</th><th class="text-end">Commission</th><th class="text-end">After commission</th><th class="text-end">Avg / admission</th><th class="text-end">Payments started</th><th class="text-end">Completed</th><th class="text-end">Failed</th><th class="text-end">Conversion</th></tr></thead>
            <tbody>
            @forelse ($channels as $c)
                <tr><td class="fw-semibold"><a href="{{ url('admin/revenue') }}?{{ http_build_query(array_merge(request()->query(), ['source' => $c['key']])) }}" class="text-reset">{{ $c['label'] }}</a></td><td class="text-end">{{ $c['admissions'] }}</td><td class="text-end">{{ $money($c['gross']) }}</td><td class="text-end">{{ $money($c['refunds']) }}</td><td class="text-end">{{ $money($c['commission']) }}</td><td class="text-end">{{ $money($c['payout']) }}</td><td class="text-end">{{ $money($c['avg']) }}</td>
                    <td class="text-end">{{ $c['key'] === 'admin' ? '—' : $c['started'] }}</td><td class="text-end">{{ $c['key'] === 'admin' ? '—' : $c['paid'] }}</td><td class="text-end">{{ $c['key'] === 'admin' ? '—' : $c['failed'] }}</td><td class="text-end">{{ $c['conversion'] !== null ? $c['conversion'] . '%' : '—' }}</td></tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-3">Nothing in this period.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if (count($channelSeries) > 0)<div class="card-body pt-2"><div id="channelChart" style="min-height:260px"></div></div>@endif
    </div>

    <div class="row g-3 mb-3">
        @foreach ([['By course (top 10)', $byCourse], ['By exam type', $byExam], ['By payment method', $byGateway]] as [$title, $rows])
            <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
                <h6>{{ $title }}</h6>
                <table class="table table-sm mb-0"><thead><tr><th></th><th class="text-end">Admissions</th><th class="text-end">Collected</th><th class="text-end">Commission</th><th class="text-end">After</th></tr></thead><tbody>
                @forelse ($rows as $r)<tr><td class="text-truncate" style="max-width:220px">{{ $r['key'] }}</td><td class="text-end">{{ $r['admissions'] }}</td><td class="text-end">{{ $money($r['gross']) }}</td><td class="text-end">{{ $money($r['commission']) }}</td><td class="text-end">{{ $money($r['payout']) }}</td></tr>
                @empty<tr><td colspan="5" class="text-muted text-center">Nothing in this period.</td></tr>@endforelse
                </tbody></table>
            </div></div></div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;
    var m = @json($months);
    var cs = @json($channelSeries), ch = document.querySelector('#channelChart');
    if (ch && cs.length) {
        new ApexCharts(ch, { chart: { type: 'bar', stacked: true, height: 270, toolbar: { show: false } }, series: cs, xaxis: { categories: m.map(function (x) { return x.label; }) },
            dataLabels: { enabled: false }, legend: { position: 'top' }, title: { text: 'Money kept per month, by channel (₹)', style: { fontSize: '13px' } } }).render();
    }
    new ApexCharts(document.querySelector('#revChart'), {
        chart: { height: 310, type: 'line', toolbar: { show: false } },
        series: [
            { name: 'Collected', type: 'column', data: m.map(function (x) { return x.gross; }) },
            { name: 'After commission', type: 'column', data: m.map(function (x) { return x.payout; }) },
            { name: 'Admissions', type: 'line', data: m.map(function (x) { return x.admissions; }) }
        ],
        xaxis: { categories: m.map(function (x) { return x.label; }) },
        yaxis: [{ seriesName: 'Collected', title: { text: '₹' } }, { seriesName: 'Collected', show: false }, { seriesName: 'Admissions', opposite: true, title: { text: 'Admissions' } }],
        stroke: { width: [0, 0, 3] }, dataLabels: { enabled: false }, legend: { position: 'top' }
    }).render();
})();
</script>
@endsection
