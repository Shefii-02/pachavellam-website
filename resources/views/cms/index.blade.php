@extends('cms.section')
@section('content2')
@php
    $s = $dash['students'] ?? []; $l = $dash['learning'] ?? []; $q = $dash['library'] ?? []; $c = $dash['classes'] ?? [];
    $e = $dash['exams'] ?? []; $ct = $dash['content'] ?? []; $tr = $dash['trend'] ?? null; $top = $dash['topExams'] ?? [];
    $rc = $dash['recent'] ?? []; $att = $dash['attention'] ?? [];
    $n = fn ($v) => number_format((int) $v);
    $kp = fn ($path = '') => url('admin/exam/kpsc/' . $path);
    $ce = $l['course_exams'] ?? []; $ro = $l['roaring'] ?? [];
    $statusBadge = ['published' => 'success', 'draft' => 'secondary', 'archived' => 'dark', 'done' => 'success', 'undone' => 'secondary'];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">KPSC Dashboard</h1>
        <div class="small text-muted">Updated {{ $dash['asOf']->format('d M Y, h:i A') }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ $kp('courses/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Course</a>
        <a href="{{ $kp('question-library/import') }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-upload me-1"></i>Import questions</a>
        <a href="{{ $kp('class-library/create') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-collection-play me-1"></i>Class</a>
        <a href="{{ url('admin/exam/kpsc/roaring-study/create') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-lightning-charge me-1"></i>Roaring Study</a>
        <a href="{{ url('admin/kpsc/daily-exam') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-list-task me-1"></i>Daily exams</a>
    </div>
</div>

@if (!empty($dash['problems']))
    <div class="alert alert-warning py-2 small">Some figures could not be read right now: {{ implode(', ', $dash['problems']) }}. The rest is correct.</div>
@endif

{{-- headline numbers --}}
<div class="row g-3 mb-4">
    @foreach ([
        ['Students', $n($s['total'] ?? 0), '+' . $n($s['week'] ?? 0) . ' this week', 'bi-people', 'primary', url('admin/students/student-list')],
        ['Interested in KPSC', $n($s['interested'] ?? 0), $n($s['with_mobile'] ?? 0) . ' students with a mobile', 'bi-star', 'warning', url('admin/kpsc/interested-students')],
        ['Courses', $n($l['courses'] ?? 0), $n($l['published_courses'] ?? 0) . ' published · ' . $n($l['paid_courses'] ?? 0) . ' paid', 'bi-mortarboard', 'success', $kp('courses')],
        ['Library questions', $n($q['questions'] ?? 0), $n($q['folders'] ?? 0) . ' folders · ' . $n($q['used'] ?? 0) . ' used in exams', 'bi-collection', 'info', $kp('question-library')],
        ['Classes', $n($c['total'] ?? 0), $n(($c['by_status']['published'] ?? 0)) . ' published · ' . $n(round(($c['minutes'] ?? 0) / 60)) . ' hours', 'bi-collection-play', 'danger', $kp('class-library')],
        ['Exam attempts', $n($e['attempts_week'] ?? 0), 'last 7 days · ' . $n($e['attempts_today'] ?? 0) . ' today', 'bi-bar-chart', 'dark', url('admin/kpsc/daily-exam')],
    ] as [$label, $value, $sub, $icon, $color, $href])
        <div class="col-6 col-xl-2">
            <a href="{{ $href }}" class="text-reset text-decoration-none">
                <div class="card border-0 bg-{{ $color }} bg-opacity-10 h-100"><div class="card-body py-3">
                    <div class="d-flex justify-content-between"><span class="small text-muted">{{ $label }}</span><i class="bi {{ $icon }} text-{{ $color }}"></i></div>
                    <div class="h3 mb-0">{{ $value }}</div>
                    <div class="small text-muted text-truncate" title="{{ $sub }}">{{ $sub }}</div>
                </div></div>
            </a>
        </div>
    @endforeach
</div>

{{-- chart + attention --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Exam attempts, last 14 days</h6>
                <span class="small text-muted">{{ $n($e['attempts_total'] ?? 0) }} attempts in all</span></div>
            @if ($tr)<div id="attemptChart" style="min-height:260px"></div>@else<div class="text-muted small">Not available right now.</div>@endif
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <h6>Needs attention</h6>
            @forelse ($att as $a)
                <a href="{{ $a['url'] }}" class="d-flex justify-content-between align-items-center text-reset text-decoration-none py-2 border-bottom">
                    <span class="small"><i class="bi bi-{{ $a['level'] === 'warning' ? 'exclamation-triangle text-warning' : 'info-circle text-info' }} me-1"></i>{{ $a['text'] }}</span>
                    <span class="badge bg-{{ $a['level'] === 'warning' ? 'warning text-dark' : 'info' }}">{{ $n($a['count']) }}</span>
                </a>
            @empty
                <div class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>Nothing needs attention.</div>
            @endforelse
        </div></div>
    </div>
</div>

{{-- students + top exams --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <div class="d-flex justify-content-between"><h6>New students per day</h6>
                <span class="small text-muted">today {{ $n($s['today'] ?? 0) }} · 7 days {{ $n($s['week'] ?? 0) }} · 30 days {{ $n($s['month'] ?? 0) }}</span></div>
            @if ($tr)<div id="studentChart" style="min-height:200px"></div>@endif
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <h6>Most attempted exams, last 30 days</h6>
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Exam</th><th class="text-end">Attempts</th><th class="text-end">Avg mark</th></tr></thead>
                <tbody>
                @forelse ($top as $t)
                    <tr><td class="text-truncate" style="max-width:260px"><a href="{{ url('admin/kpsc/' . ($t->section === 'Model Exam' ? 'model-exam' : ($t->section === 'CA-Daily-Exam' ? 'ca-daily-exam' : 'daily-exam'))) }}">{{ $t->title ?: 'Exam #' . $t->id }}</a>
                        <div class="small text-muted">{{ $t->section }}</div></td>
                        <td class="text-end">{{ $n($t->attempts) }}</td><td class="text-end">{{ $t->avg_mark }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center py-3">No attempts in the last 30 days.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>

{{-- library / classes / course exams / roaring --}}
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <h6><a href="{{ $kp('question-library') }}" class="text-reset">Question library</a></h6>
            <div class="small text-muted mb-2">By type</div>
            @foreach (($q['by_type'] ?? []) as $type => $count)
                <div class="d-flex justify-content-between small"><span>{{ \App\Models\LibraryQuestion::TYPES[$type] ?? $type }}</span><strong>{{ $n($count) }}</strong></div>
            @endforeach
            <div class="small text-muted mt-2 mb-1">By source</div>
            @foreach (($q['by_source'] ?? []) as $src => $count)
                <div class="d-flex justify-content-between small"><span>{{ ['daily' => 'Daily / CA exams (synced)', 'manual' => 'Typed in', 'docx' => 'Word import', 'csv' => 'CSV import', 'xlsx' => 'Excel import'][$src] ?? ucfirst($src) }}</span><strong>{{ $n($count) }}</strong></div>
            @endforeach
            <div class="small text-muted mt-2">{{ $n($q['imports'] ?? 0) }} imports done · {{ $n($q['uncategorised'] ?? 0) }} without a category</div>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <h6><a href="{{ $kp('class-library') }}" class="text-reset">Class library</a></h6>
            @foreach (config('class_library.statuses') as $k => [$label, $color])
                <div class="d-flex justify-content-between small"><span><span class="badge bg-{{ $color }}">{{ $label }}</span></span><strong>{{ $n($c['by_status'][$k] ?? 0) }}</strong></div>
            @endforeach
            <div class="small text-muted mt-2 mb-1">By source</div>
            @foreach (($c['by_source'] ?? []) as $src => $count)
                <div class="d-flex justify-content-between small"><span>{{ config('class_library.sources.' . $src, $src) }}</span><strong>{{ $n($count) }}</strong></div>
            @endforeach
            <div class="small text-muted mt-2">{{ $n(round(($c['minutes'] ?? 0) / 60)) }} h of video · {{ $n($c['in_courses'] ?? 0) }} used in courses · {{ $n(round(($c['watched_minutes'] ?? 0) / 60)) }} h watched</div>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <h6><a href="{{ $kp('courses') }}" class="text-reset">Course exams &amp; entries</a></h6>
            @foreach (\App\Models\CourseExam::STATUSES as $k => [$label, $color])
                <div class="d-flex justify-content-between small"><span><span class="badge bg-{{ $color }}">{{ $label }}</span></span><strong>{{ $n($ce[$k] ?? 0) }}</strong></div>
            @endforeach
            <h6 class="mt-3"><a href="{{ url('admin/exam/kpsc/roaring-study') }}" class="text-reset">Roaring Study</a></h6>
            @foreach (\App\Models\RoaringStudy::STATUSES as $k => [$label, $color])
                <div class="d-flex justify-content-between small"><span><span class="badge bg-{{ $color }}">{{ $label }}</span></span><strong>{{ $n($ro[$k] ?? 0) }}</strong></div>
            @endforeach
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
            <h6>Exam series</h6>
            @foreach (($e['series'] ?? []) as $row)
                @php $pct = $row['total'] ? round($row['live'] / $row['total'] * 100) : 0; @endphp
                <div class="mb-2"><div class="d-flex justify-content-between small"><span>{{ $row['label'] }}</span><span><strong>{{ $n($row['live']) }}</strong> live / {{ $n($row['total']) }}</span></div>
                    <div class="progress" style="height:6px"><div class="progress-bar" style="width:{{ $pct }}%"></div></div></div>
            @endforeach
            <div class="small text-muted">Categories {{ $n($l['categories'] ?? 0) }} · {{ $n($e['attempts_total'] ?? 0) }} attempts in all</div>
        </div></div>
    </div>
</div>

@if (auth()->user() && auth()->user()->type === 'Admin' && !empty($dash['revenue']))
@php $R = $dash['revenue']; $money = fn ($v) => '₹' . number_format((float) $v); @endphp
<div class="row g-3 mb-4">
    <div class="col-lg-7"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <div class="d-flex justify-content-between"><h6>Revenue</h6><a class="small" href="{{ url('admin/revenue?exam=kpsc') }}">Full analytics &amp; admissions</a></div>
        <table class="table table-sm mb-2"><thead><tr><th></th><th class="text-end">Admissions</th><th class="text-end">Collected</th><th class="text-end">Refunds</th><th class="text-end">Commission {{ rtrim(rtrim(number_format($R['commission_percent'], 2), '0'), '.') }}%</th><th class="text-end">Left</th></tr></thead><tbody>
        @foreach ([['This month', $R['this']], ['Last month', $R['last']], ['All time', $R['all']]] as [$k, $r])
            <tr><td>{{ $k }}</td><td class="text-end">{{ $n($r['admissions']) }}</td><td class="text-end">{{ $money($r['gross']) }}</td><td class="text-end">{{ $money($r['refunds']) }}</td><td class="text-end">{{ $money($r['commission']) }}</td><td class="text-end fw-semibold">{{ $money($r['payout']) }}</td></tr>
        @endforeach
        </tbody></table>
        <div id="kpscRevChart" style="min-height:220px"></div>
    </div></div></div>
    <div class="col-lg-5"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Admissions by channel</h6>
        <table class="table table-sm mb-0"><thead><tr><th></th><th class="text-end">Admissions</th><th class="text-end">Collected</th><th class="text-end">Commission</th></tr></thead><tbody>
        @forelse ($R['channels'] as $ch)<tr><td>{{ $ch['label'] }}</td><td class="text-end">{{ $n($ch['admissions']) }}</td><td class="text-end">{{ $money($ch['gross']) }}</td><td class="text-end">{{ $money($ch['commission']) }}</td></tr>
        @empty<tr><td colspan="4" class="text-muted text-center py-3">No admissions yet.</td></tr>@endforelse
        </tbody></table>
        <div class="d-flex gap-2 mt-3"><a href="{{ url('admin/admissions/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-person-plus me-1"></i>Add student</a><a href="{{ url('admin/payment-links/create') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-link-45deg me-1"></i>Payment link</a></div>
    </div></div></div>
</div>
@endif

{{-- content counts --}}
<div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
    <h6>Content at a glance</h6>
    <div class="row g-2">
        @php $links = ['Study material' => 'material', 'PYQ' => 'prev-qstn', 'PSC bulletins' => 'psc-bulletin', 'Daily CA' => 'daily-ca', 'PSC news' => 'psc-news', 'PSC results' => 'psc-results', 'Syllabus' => 'syllabus', 'Subjects' => 'subjects', 'Notifications' => 'notifications']; @endphp
        @foreach ($ct as $label => $count)
            <div class="col-6 col-md-3 col-xl-2">
                @if (isset($links[$label]))<a href="{{ url('admin/kpsc/' . $links[$label]) }}" class="text-reset text-decoration-none">@elseif ($label === 'Capsules')<a href="{{ url('admin/capsule?exam=kpsc') }}" class="text-reset text-decoration-none">@elseif ($label === 'Stories')<a href="{{ url('admin/stories?exam=kpsc') }}" class="text-reset text-decoration-none">@else<span>@endif
                <div class="border rounded px-3 py-2 h-100"><div class="small text-muted">{{ $label }}</div><div class="h5 mb-0">{{ $n($count) }}</div></div>
                @if (isset($links[$label]) || in_array($label, ['Capsules', 'Stories']))</a>@else</span>@endif
            </div>
        @endforeach
    </div>
</div></div>

{{-- recent --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Latest course exams / entries</h6>
        @forelse (($rc['course_exams'] ?? []) as $r)
            <div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-truncate" style="max-width:70%">{{ $r->title }}<span class="text-muted"> · {{ $r->course }}</span></span><span class="badge bg-{{ $statusBadge[$r->status] ?? 'secondary' }}">{{ $r->status }}</span></div>
        @empty<div class="text-muted small">None yet.</div>@endforelse
        <h6 class="mt-3">Roaring Study</h6>
        @forelse (($rc['roaring'] ?? []) as $r)
            <div class="d-flex justify-content-between small py-1 border-bottom"><a href="{{ url('admin/exam/kpsc/roaring-study/' . $r->id) }}" class="text-truncate" style="max-width:60%">{{ $r->title }}</a>
                <span class="text-muted">{{ $r->publish_at ? \Illuminate\Support\Carbon::parse($r->publish_at)->format('d M, h:i A') : '—' }} · {{ $r->access_type === 'global' ? 'free' : 'course' }}</span></div>
        @empty<div class="text-muted small">None yet.</div>@endforelse
    </div></div></div>
    <div class="col-lg-6"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
        <h6>Latest question imports</h6>
        @forelse (($rc['imports'] ?? []) as $r)
            <div class="d-flex justify-content-between small py-1 border-bottom"><a href="{{ $kp('question-library/imports/' . $r->id) }}" class="text-truncate" style="max-width:55%">{{ $r->file_name }}</a>
                <span><span class="text-success">{{ $r->imported }}</span> / <span class="text-danger">{{ $r->skipped }}</span> <span class="badge bg-{{ $statusBadge[$r->status] ?? 'secondary' }}">{{ $r->status }}</span></span></div>
        @empty<div class="text-muted small">No imports yet.</div>@endforelse
        <h6 class="mt-3">Newest students</h6>
        @forelse (($rc['students'] ?? []) as $r)
            <div class="d-flex justify-content-between small py-1 border-bottom"><span>{{ $r->name ?: 'Student #' . $r->id }}</span><span class="text-muted">{{ $r->mobile ?: '—' }} · {{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d M') }}</span></div>
        @empty<div class="text-muted small">None.</div>@endforelse
    </div></div></div>
</div>
@endsection

@section('scripts')
@if (!empty($dash['trend']))
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    var t = @json($dash['trend']);
    if (typeof ApexCharts === 'undefined') return;
    new ApexCharts(document.querySelector('#attemptChart'), {
        chart: { type: 'bar', height: 270, stacked: true, toolbar: { show: false } },
        series: [{ name: 'Daily exams', data: t.daily }, { name: 'OMR exams', data: t.omr }, { name: 'CA exams', data: t.ca }],
        xaxis: { categories: t.labels }, legend: { position: 'top' }, dataLabels: { enabled: false }, plotOptions: { bar: { columnWidth: '55%' } }
    }).render();
    @if (!empty($dash['revenue']) && auth()->user() && auth()->user()->type === 'Admin')
    var rm = @json($dash['revenue']['months']), rc = document.querySelector('#kpscRevChart');
    if (rc) { new ApexCharts(rc, { chart: { height: 230, type: 'bar', toolbar: { show: false } }, series: [{ name: 'Collected', data: rm.map(function (x) { return x.gross; }) }, { name: 'After commission', data: rm.map(function (x) { return x.payout; }) }],
        xaxis: { categories: rm.map(function (x) { return x.label; }) }, dataLabels: { enabled: false }, legend: { position: 'top' } }).render(); }
    @endif
    new ApexCharts(document.querySelector('#studentChart'), {
        chart: { type: 'area', height: 210, toolbar: { show: false } }, stroke: { curve: 'smooth', width: 2 },
        series: [{ name: 'New students', data: t.students }], xaxis: { categories: t.labels }, dataLabels: { enabled: false }
    }).render();
})();
</script>
@endif
@endsection
