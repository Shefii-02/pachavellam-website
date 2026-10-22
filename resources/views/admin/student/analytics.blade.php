@extends('admin.student.section')
@section('content2')
@php
    $bar = function ($items, $color = 'primary') {
        // items: [label => count]; renders simple horizontal bars
        $items = collect($items);
        $max = max(1, $items->max());
        $sum = max(1, $items->sum());
        return $items->map(fn ($c, $l) => [$l, $c, round($c / $max * 100), round($c / $sum * 100)]);
    };
    $maxDaily = max(1, $daily->max('count'));
    $maxMonthly = max(1, $monthly->max('count'));
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Student Analytics</h3>
    <span class="text-muted small">Updated {{ $generated_at->diffForHumans() }} · refreshes every 10 minutes</span>
</div>

<div class="row g-3 mb-4">
    @foreach ([['Total students', $total], ['Joined today', $new_today], ['Joined last 7 days', $new_7], ['Joined last 30 days', $new_30]] as [$label, $value])
        <div class="col-6 col-lg-3">
            <div class="card bg-transparent border rounded-3 h-100"><div class="card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="h3 mb-0">{{ number_format($value) }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">New students, last 30 days</h5></div>
            <div class="card-body">
                <div class="d-flex align-items-end" style="height:150px;gap:3px">
                    @foreach ($daily as $d)
                        <div class="flex-fill bg-primary" title="{{ $d['label'] }}: {{ $d['count'] }}"
                            style="height:{{ round($d['count'] / $maxDaily * 100) }}%;min-height:{{ $d['count'] ? 2 : 0 }}px"></div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between small text-muted mt-1">
                    <span>{{ $daily->first()['label'] }}</span><span>peak {{ $maxDaily }}/day</span><span>{{ $daily->last()['label'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">New students, last 12 months</h5></div>
            <div class="card-body">
                <div class="d-flex align-items-end" style="height:150px;gap:6px">
                    @foreach ($monthly as $m)
                        <div class="flex-fill text-center" title="{{ $m['label'] }}: {{ $m['count'] }}">
                            <div class="bg-info" style="height:{{ round($m['count'] / $maxMonthly * 120) }}px;min-height:{{ $m['count'] ? 2 : 0 }}px"></div>
                            <div class="small text-muted" style="font-size:10px">{{ \Illuminate\Support\Str::before($m['label'], ' ') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Activity</h5></div>
            <div class="card-body">
                @foreach ($activity as [$label, $value, $color, $filter])
                    <a href="{{ student_cms('student-list') }}?activity={{ $filter }}" class="text-reset d-block mb-3">
                        <div class="d-flex justify-content-between small"><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></div>
                        <div class="progress" style="height:8px"><div class="progress-bar bg-{{ $color }}" style="width:{{ round($value / max(1, $total) * 100) }}%"></div></div>
                    </a>
                @endforeach
                <p class="small text-muted mb-0">Based on exam attempts, capsule activity, discussions and app API use.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Signup method</h5></div>
            <div class="card-body">
                @foreach ($bar($signup) as [$label, $c, $w, $pct])
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small"><span>{{ $label }}</span><span>{{ number_format($c) }} · {{ $pct }}%</span></div>
                        <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $w }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Mobile numbers</h5></div>
            <div class="card-body">
                @foreach ($bar($mobile) as [$label, $c, $w, $pct])
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small"><span>{{ $label }}</span><span>{{ number_format($c) }} · {{ $pct }}%</span></div>
                        <div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $w }}%"></div></div>
                    </div>
                @endforeach
                @if ($gender->isNotEmpty())
                    <hr><div class="small text-muted mb-1">Gender (where given)</div>
                    @foreach ($gender as $g => $c)<span class="badge bg-secondary me-1">{{ ucfirst($g) }} {{ number_format($c) }}</span>@endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
        <h5 class="mb-0">Exam interests</h5>
        <span class="small text-muted">{{ number_format($with_interest) }} students chose at least one · <a href="{{ student_cms('exams') }}">manage exams</a></span>
    </div>
    <div class="card-body">
        @php $maxI = max(1, $interests->max('c')); @endphp
        <div class="row g-3">
            @foreach ($interests as $i)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ student_cms('student-list') }}?interest={{ $i->slug }}" class="text-reset">
                        <div class="d-flex justify-content-between small">
                            <span><span class="badge text-white" style="background:{{ $i->color ?: '#6c757d' }}">{{ $i->short_name ?: $i->name }}</span></span>
                            <strong>{{ number_format($i->c) }}</strong>
                        </div>
                        <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ round($i->c / $maxI * 100) }}%;background:{{ $i->color ?: '#0d6efd' }}"></div></div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Top states</h5></div>
            <div class="card-body">
                @forelse ($bar($states) as [$label, $c, $w, $pct])
                    <div class="d-flex justify-content-between small"><span>{{ $label }}</span><strong>{{ number_format($c) }}</strong></div>
                    <div class="progress mb-2" style="height:6px"><div class="progress-bar bg-info" style="width:{{ $w }}%"></div></div>
                @empty <span class="text-muted small">No state data.</span> @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Top cities</h5></div>
            <div class="card-body">
                @forelse ($bar($cities) as [$label, $c, $w, $pct])
                    <div class="d-flex justify-content-between small"><span>{{ $label }}</span><strong>{{ number_format($c) }}</strong></div>
                    <div class="progress mb-2" style="height:6px"><div class="progress-bar bg-info" style="width:{{ $w }}%"></div></div>
                @empty <span class="text-muted small">No city data.</span> @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Most exam attempts</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        @forelse ($top_students as $s)
                            <tr>
                                <td><a href="{{ student_cms('student-list/' . $s->id) }}">{{ $s->name ?: '(no name)' }}</a>
                                    <div class="small text-muted">{{ $s->mobile ?: $s->email }}</div></td>
                                <td class="text-end"><strong>{{ number_format($s->attempts) }}</strong><div class="small text-muted">attempts</div></td>
                            </tr>
                        @empty
                            <tr><td class="text-muted small p-3">No attempts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
