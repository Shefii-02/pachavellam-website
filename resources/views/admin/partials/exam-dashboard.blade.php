@php
    $exam = $dash['exam']; $label = $exam->short_name ?: $exam->name;
    $L = $dash['learning'] ?? []; $S = $dash['students'] ?? []; $Q = $dash['library'] ?? []; $C = $dash['classes'] ?? [];
    $R = $dash['revenue'] ?? null; $T = $dash['trend'] ?? null; $rc = $dash['recent'] ?? []; $att = $dash['attention'] ?? [];
    $n = fn ($v) => number_format((int) $v);
    $money = fn ($v) => '₹' . number_format((float) $v);
    $isAdmin = auth()->user() && auth()->user()->type === 'Admin';
    $ce = $L['course_exams'] ?? []; $ro = $L['roaring'] ?? [];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0"><span class="badge text-white" style="background:{{ $exam->color ?: '#6c757d' }}">{{ $label }}</span> Dashboard</h1>
        <div class="small text-muted">{{ $exam->name }} · updated {{ $dash['asOf']->format('d M Y, h:i A') }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ $exam->examUrl('courses/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Course</a>
        <a href="{{ $exam->examUrl('question-library/import') }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-upload me-1"></i>Import questions</a>
        <a href="{{ $exam->examUrl('class-library/create') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-collection-play me-1"></i>Class</a>
        <a href="{{ url('admin/exam/' . $exam->slug . '/roaring-study/create') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-lightning-charge me-1"></i>Roaring Study</a>
        @if ($isAdmin)<a href="{{ url('admin/admissions/create') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-person-plus me-1"></i>Add student</a>@endif
    </div>
</div>

@unless ($exam->status)<div class="alert alert-warning py-2 small">This exam type is hidden, so students and the app do not see it yet.</div>@endunless
@if (!empty($dash['problems']))<div class="alert alert-warning py-2 small">Some figures could not be read right now: {{ implode(', ', $dash['problems']) }}.</div>@endif

<div class="row g-3 mb-4">
    @foreach (array_filter([
        ['Interested students', $n($S['interested'] ?? 0), '+' . $n($S['week'] ?? 0) . ' this week', 'bi-people', 'primary', $exam->interestedUrl()],
        ['Admitted students', $n($S['admitted'] ?? 0), 'active in a course', 'bi-person-check', 'success', url('admin/admissions?exam=' . $exam->slug . '&status=active')],
        ['Courses', $n($L['courses'] ?? 0), $n($L['published_courses'] ?? 0) . ' published · ' . $n($L['paid_courses'] ?? 0) . ' paid', 'bi-mortarboard', 'info', $exam->examUrl('courses')],
        ['Library questions', $n($Q['questions'] ?? 0), $n($Q['folders'] ?? 0) . ' folders · ' . $n($Q['used'] ?? 0) . ' used', 'bi-collection', 'warning', $exam->examUrl('question-library')],
        ['Classes', $n($C['total'] ?? 0), $n($C['by_status']['published'] ?? 0) . ' published · ' . $n($C['hours'] ?? 0) . ' h', 'bi-collection-play', 'danger', $exam->examUrl('class-library')],
        $isAdmin && $R ? ['Revenue this month', $money($R['this']['gross']), $n($R['this']['admissions']) . ' admissions · last month ' . $money($R['last']['gross']), 'bi-cash-coin', 'dark', url('admin/revenue?exam=' . $exam->slug)] : null,
    ]) as [$label2, $value, $sub, $icon, $color, $href])
        <div class="col-6 col-xl-2"><a href="{{ $href }}" class="text-reset text-decoration-none"><div class="card border-0 bg-{{ $color }} bg-opacity-10 h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between"><span class="small text-muted">{{ $label2 }}</span><i class="bi {{ $icon }} text-{{ $color }}"></i></div>
            <div class="h3 mb-0">{{ $value }}</div><div class="small text-muted text-truncate" title="{{ $sub }}">{{ $sub }}</div>
        </div></div></a></div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        @if ($isAdmin && $R)
            <div class="d-flex justify-content-between"><h6>Collected and admissions, last 12 months</h6><span class="small text-muted">commission {{ rtrim(rtrim(number_format($R['commission_percent'], 2), '0'), '.') }}%</span></div>
            <div id="revChart" style="min-height:280px"></div>
        @elseif ($T)
            <h6>Interested students and admissions, last 14 days</h6><div id="trendChart" style="min-height:280px"></div>
        @endif
    </div></div></div>
    <div class="col-lg-4"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Needs attention</h6>
        @forelse ($att as $a)
            <a href="{{ $a['url'] }}" class="d-flex justify-content-between align-items-center text-reset text-decoration-none py-2 border-bottom">
                <span class="small"><i class="bi bi-{{ $a['level'] === 'warning' ? 'exclamation-triangle text-warning' : 'info-circle text-info' }} me-1"></i>{{ $a['text'] }}</span>
                <span class="badge bg-{{ $a['level'] === 'warning' ? 'warning text-dark' : 'info' }}">{{ $n($a['count']) }}</span>
            </a>
        @empty<div class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>Nothing needs attention.</div>@endforelse
    </div></div></div>
</div>

@if ($isAdmin && $R)
<div class="row g-3 mb-4">
    <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <div class="d-flex justify-content-between"><h6>Revenue</h6><a class="small" href="{{ url('admin/revenue?exam=' . $exam->slug) }}">Full analytics</a></div>
        <table class="table table-sm mb-0"><thead><tr><th></th><th class="text-end">Admissions</th><th class="text-end">Collected</th><th class="text-end">Refunds</th><th class="text-end">Commission</th><th class="text-end">Left</th></tr></thead><tbody>
        @foreach ([['This month', $R['this']], ['Last month', $R['last']], ['All time', $R['all']]] as [$k, $r])
            <tr><td>{{ $k }}</td><td class="text-end">{{ $n($r['admissions']) }}</td><td class="text-end">{{ $money($r['gross']) }}</td><td class="text-end">{{ $money($r['refunds']) }}</td><td class="text-end">{{ $money($r['commission']) }}</td><td class="text-end fw-semibold">{{ $money($r['payout']) }}</td></tr>
        @endforeach
        </tbody></table>
    </div></div></div>
    <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>By channel</h6>
        <table class="table table-sm mb-0"><thead><tr><th></th><th class="text-end">Admissions</th><th class="text-end">Collected</th><th class="text-end">Commission</th></tr></thead><tbody>
        @forelse ($R['channels'] as $ch)<tr><td>{{ $ch['label'] }}</td><td class="text-end">{{ $n($ch['admissions']) }}</td><td class="text-end">{{ $money($ch['gross']) }}</td><td class="text-end">{{ $money($ch['commission']) }}</td></tr>
        @empty<tr><td colspan="4" class="text-muted text-center">No admissions yet.</td></tr>@endforelse
        </tbody></table>
    </div></div></div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6><a href="{{ $exam->examUrl('question-library') }}" class="text-reset">Question library</a></h6>
        @foreach (($Q['by_type'] ?? []) as $type => $count)<div class="d-flex justify-content-between small"><span>{{ \App\Models\LibraryQuestion::TYPES[$type] ?? $type }}</span><strong>{{ $n($count) }}</strong></div>@endforeach
        <div class="small text-muted mt-2">{{ $n($Q['imports'] ?? 0) }} imports done</div>
    </div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6><a href="{{ $exam->examUrl('class-library') }}" class="text-reset">Class library</a></h6>
        @foreach (config('class_library.statuses') as $k => [$lab, $color])<div class="d-flex justify-content-between small"><span class="badge bg-{{ $color }}">{{ $lab }}</span><strong>{{ $n($C['by_status'][$k] ?? 0) }}</strong></div>@endforeach
        <div class="small text-muted mt-2">{{ $n($C['in_courses'] ?? 0) }} used in courses</div>
    </div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Course exams &amp; entries</h6>
        @foreach (\App\Models\CourseExam::STATUSES as $k => [$lab, $color])<div class="d-flex justify-content-between small"><span class="badge bg-{{ $color }}">{{ $lab }}</span><strong>{{ $n($ce[$k] ?? 0) }}</strong></div>@endforeach
    </div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6><a href="{{ url('admin/exam/' . $exam->slug . '/roaring-study') }}" class="text-reset">Roaring Study</a></h6>
        @foreach (\App\Models\RoaringStudy::STATUSES as $k => [$lab, $color])<div class="d-flex justify-content-between small"><span class="badge bg-{{ $color }}">{{ $lab }}</span><strong>{{ $n($ro[$k] ?? 0) }}</strong></div>@endforeach
        <div class="small text-muted mt-2">Categories {{ $n($L['categories'] ?? 0) }}</div>
    </div></div></div>
</div>

<div class="row g-3 mb-4">
    @if ($isAdmin)
    <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Latest admissions</h6>
        @forelse (($rc['admissions'] ?? []) as $r)
            <a href="{{ url('admin/admissions/' . $r->id) }}" class="d-flex justify-content-between small py-1 border-bottom text-reset text-decoration-none"><span class="text-truncate" style="max-width:62%">{{ $r->name ?: 'Student' }} <span class="text-muted">· {{ $r->course }}</span></span><span>{{ $money($r->amount_paid) }} <span class="text-muted">· {{ \App\Services\Payments\AdmissionService::CHANNELS[$r->source] ?? $r->source }}</span></span></a>
        @empty<div class="text-muted small">No admissions yet.</div>@endforelse
    </div></div></div>
    @endif
    <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Latest course exams</h6>
        @forelse (($rc['course_exams'] ?? []) as $r)<div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-truncate" style="max-width:70%">{{ $r->title }}<span class="text-muted"> · {{ $r->course }}</span></span><span class="badge bg-{{ ['published' => 'success', 'draft' => 'secondary', 'archived' => 'dark'][$r->status] ?? 'secondary' }}">{{ $r->status }}</span></div>
        @empty<div class="text-muted small">None yet.</div>@endforelse
        <h6 class="mt-3">Roaring Study</h6>
        @forelse (($rc['roaring'] ?? []) as $r)<div class="d-flex justify-content-between small py-1 border-bottom"><a href="{{ url('admin/exam/' . $exam->slug . '/roaring-study/' . $r->id) }}" class="text-truncate" style="max-width:60%">{{ $r->title }}</a><span class="text-muted">{{ $r->publish_at ? \Illuminate\Support\Carbon::parse($r->publish_at)->format('d M, h:i A') : '—' }} · {{ $r->access_type === 'global' ? 'free' : 'course' }}</span></div>
        @empty<div class="text-muted small">None yet.</div>@endforelse
    </div></div></div>
</div>

@push('dash-scripts')
@endpush
@if ($T || ($R && $isAdmin))
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;
    @if ($isAdmin && $R)
    var m = @json($R['months']);
    new ApexCharts(document.querySelector('#revChart'), {
        chart: { height: 290, type: 'line', toolbar: { show: false } },
        series: [{ name: 'Collected', type: 'column', data: m.map(function (x) { return x.gross; }) }, { name: 'After commission', type: 'column', data: m.map(function (x) { return x.payout; }) }, { name: 'Admissions', type: 'line', data: m.map(function (x) { return x.admissions; }) }],
        xaxis: { categories: m.map(function (x) { return x.label; }) },
        yaxis: [{ seriesName: 'Collected', title: { text: '₹' } }, { seriesName: 'Collected', show: false }, { seriesName: 'Admissions', opposite: true, title: { text: 'Admissions' } }],
        stroke: { width: [0, 0, 3] }, dataLabels: { enabled: false }, legend: { position: 'top' }
    }).render();
    @elseif ($T)
    var t = @json($T);
    new ApexCharts(document.querySelector('#trendChart'), { chart: { type: 'area', height: 290, toolbar: { show: false } }, stroke: { curve: 'smooth', width: 2 }, dataLabels: { enabled: false },
        series: [{ name: 'Interested students', data: t.interested }, { name: 'Admissions', data: t.admissions }], xaxis: { categories: t.labels } }).render();
    @endif
})();
</script>
@endif
