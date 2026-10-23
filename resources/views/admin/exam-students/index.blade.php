@extends($layout)
@section('content2')
@php
    $exportUrl = $baseUrl . '/export' . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
    $fmtActivity = fn ($v) => $v ? \Carbon\Carbon::parse($v)->diffForHumans() : '—';
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">
            <span class="badge text-white" style="background:{{ $exam->color ?: '#6c757d' }}">{{ $exam->short_name ?: $exam->name }}</span>
            Interested Students
        </h3>
        <div class="small text-muted">{{ $exam->name }}</div>
    </div>
    <a href="{{ $exportUrl }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-download me-1"></i>Export CSV ({{ number_format($summary['interested']) }})</a>
</div>

<div class="row g-3 mb-3">
    @foreach ([
        ['Interested students', number_format($summary['interested'])],
        ['Attempted exams', $hasPerformance ? number_format($summary['with_attempts']) : '—'],
        ['Average accuracy', $summary['avg_accuracy'] !== null ? $summary['avg_accuracy'] . '%' : '—'],
        ['Total attempts', $hasPerformance ? number_format($summary['attempts']) : '—'],
    ] as [$label, $value])
        <div class="col-6 col-lg-3">
            <div class="card bg-transparent border rounded-3 h-100"><div class="card-body py-2">
                <div class="small text-muted">{{ $label }}</div><div class="h4 mb-0">{{ $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>

@unless ($hasPerformance)
    <div class="alert alert-info py-2 small">
        No graded exams are connected to <strong>{{ $exam->name }}</strong> yet, so rank and performance are empty.
        Students and their activity are still listed. A data source can be set under
        <a href="{{ student_cms('exams/' . $exam->id . '/edit') }}">Students → Exams</a> once this exam has practice tests.
    </div>
@endunless

<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-lg-3"><input type="text" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" placeholder="Name, email or mobile..."></div>
            @if ($hasPerformance)
                <div class="col-6 col-lg-2">
                    <select name="period" class="form-select form-select-sm">
                        @foreach (\App\Services\StudentPerformance::PERIODS as $k => $label)
                            <option value="{{ $k }}" @selected($f['period'] === (string) $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="sort" class="form-select form-select-sm" title="Rank by">
                        @foreach (\App\Services\StudentPerformance::METRICS as $k => $label)
                            <option value="{{ $k }}" @selected($f['sort'] === $k)>Rank by {{ strtolower($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select name="show" class="form-select form-select-sm">
                        <option value="">All students</option>
                        <option value="ranked" @selected($f['show'] === 'ranked')>Attempted exams</option>
                        <option value="no_attempts" @selected($f['show'] === 'no_attempts')>No attempts yet</option>
                    </select>
                </div>
            @endif
            <div class="col-6 col-lg-2">
                <select name="mobile" class="form-select form-select-sm">
                    <option value="">Mobile: any</option>
                    <option value="with" @selected($f['mobile'] === 'with')>Mobile filled</option>
                    <option value="without" @selected($f['mobile'] === 'without')>No mobile</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-sm btn-primary mb-0">Filter</button>
                <a href="{{ $baseUrl }}" class="btn btn-sm btn-light mb-0">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                <thead>
                    <tr>
                        @if ($hasPerformance)<th class="border-0">Rank</th>@endif
                        <th class="border-0">Student</th>
                        <th class="border-0">Mobile</th>
                        @if ($hasPerformance)
                            <th class="border-0">Attempts</th>
                            <th class="border-0">Accuracy</th>
                            <th class="border-0">Marks</th>
                            <th class="border-0">Best</th>
                        @endif
                        <th class="border-0">Last activity</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Interested since</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list as $r)
                        @php
                            $s = $r->stats;
                            [$stLabel, $stColor] = \App\Services\StudentFilter::activityStatus($r->last_activity);
                        @endphp
                        <tr>
                            @if ($hasPerformance)
                                <td>
                                    @if ($s && isset($s->rank))
                                        <span class="badge bg-{{ $s->rank <= 3 ? 'warning text-dark' : 'secondary' }}">#{{ $s->rank }}</span>
                                    @else — @endif
                                </td>
                            @endif
                            <td>
                                <a href="{{ student_cms('student-list/' . $r->id) }}">{{ $r->name ?: '(no name)' }}</a>
                                <div class="small text-muted">{{ $r->email }}</div>
                            </td>
                            <td>{{ $r->mobile ?: '—' }}</td>
                            @if ($hasPerformance)
                                <td>{{ $s ? number_format($s->attempts) : '—' }}</td>
                                <td>
                                    @if ($s)
                                        <div class="progress" style="height:6px;width:70px"><div class="progress-bar bg-{{ $s->accuracy >= 60 ? 'success' : ($s->accuracy >= 35 ? 'warning' : 'danger') }}" style="width:{{ min(100, $s->accuracy) }}%"></div></div>
                                        <small>{{ $s->accuracy }}%</small>
                                    @else — @endif
                                </td>
                                <td>{{ $s ? $s->marks : '—' }}</td>
                                <td>{{ $s ? $s->best : '—' }}</td>
                            @endif
                            <td class="text-nowrap">{{ $fmtActivity($r->last_activity) }}</td>
                            <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                            <td class="text-nowrap small">{{ \Carbon\Carbon::parse($r->interested_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">No students have chosen {{ $exam->name }} yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links('pagination::bootstrap-5') }}</div>
        @if ($hasPerformance)
            <p class="small text-muted mb-0 mt-2">Rank is among this exam's interested students, by the selected measure over the selected period. Accuracy = right ÷ (right + wrong + skipped). Marks include negative marking. Only finished attempts count.</p>
        @endif
    </div>
</div>
@endsection
