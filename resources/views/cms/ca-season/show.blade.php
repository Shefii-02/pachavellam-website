@extends('cms.section')
@section('content2')
@php
    [$statusLabel, $statusColor] = \App\Models\CaSeason::STATUSES[$season->status] ?? [$season->status, 'secondary'];
    $base = kpsc_cms('ca-daily-exam/seasons/' . $season->id);
    $questions = $daily->sum('exam_details_count') + $others->sum('exam_details_count');
    $attempts = $daily->sum('ca_exam_attened_count') + $others->sum('ca_exam_attened_count');
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ kpsc_cms('ca-daily-exam') }}" class="small"><i class="bi bi-arrow-left"></i> All seasons</a>
        <h3 class="mb-1">
            {{ $season->name }}
            <span class="badge bg-{{ $statusColor }} fs-6">{{ $statusLabel }}</span>
            @if ($season->isOngoing())<span class="badge bg-warning text-dark fs-6">ongoing</span>@endif
        </h3>
        <div class="small text-muted">
            {{ \App\Models\CaSeason::SCHEDULES[$season->schedule_type] ?? $season->schedule_type }}
            · {{ $season->starts_on ? $season->starts_on->format('d M Y') : 'no start date' }} → {{ $season->ends_on ? $season->ends_on->format('d M Y') : 'open' }}
            @foreach ($season->tags ?? [] as $tag)<span class="badge bg-light text-dark border ms-1">#{{ $tag }}</span>@endforeach
            @if ($season->is_featured)<span class="text-success ms-2"><i class="bi bi-phone"></i> default season in the app</span>@endif
        </div>
        @if ($season->description)<div class="mt-1">{{ $season->description }}</div>@endif
    </div>
    <a href="{{ $base }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil me-1"></i>Edit season</a>
</div>

@if ($notice)
    <div class="alert alert-warning py-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ $notice }}
        <a href="{{ $base }}/exams/create?kind=daily" class="alert-link ms-1">Add it now</a></div>
@endif

<div class="row g-3 mb-4">
    @foreach ([['Daily exams', $daily->count()], ['Weekly / weekend / other', $others->count()], ['Questions', number_format($questions)], ['Attempts', number_format($attempts)]] as [$label, $value])
        <div class="col-6 col-lg-3"><div class="card bg-transparent border rounded-3"><div class="card-body py-2">
            <div class="small text-muted">{{ $label }}</div><div class="h4 mb-0">{{ $value }}</div>
        </div></div></div>
    @endforeach
</div>

{{-- daily --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daily exams</h5>
        <a href="{{ $base }}/exams/create?kind=daily" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add daily exam</a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th>#</th><th>Exam</th><th>Date</th><th>Questions</th><th>Attempts</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse ($daily as $e)
                    @include('cms.ca-season._exam-row', ['e' => $e, 'sources' => collect()])
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No daily exams yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- weekly / weekend / other --}}
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Weekly · Weekend · Other exams</h5>
            <div class="small text-muted">Add one by hand, or build it from the questions of the daily exams.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $base }}/build?kind=weekend" class="btn btn-sm btn-success mb-0"><i class="bi bi-magic me-1"></i>Build from daily exams</a>
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle mb-0" data-bs-toggle="dropdown"><i class="bi bi-plus me-1"></i>Add exam</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach (['weekend', 'weekly', 'monthly', 'other'] as $k)
                        <li><a class="dropdown-item" href="{{ $base }}/exams/create?kind={{ $k }}">{{ \App\Models\CaSeason::KINDS[$k] }} exam</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th>#</th><th>Exam</th><th>Date</th><th>Questions</th><th>Attempts</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse ($others as $e)
                    @include('cms.ca-season._exam-row', ['e' => $e, 'sources' => $sources->get($e->id, collect())])
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No weekly or weekend exams yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
