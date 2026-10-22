@extends('cms.section')
@section('content2')
@php
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Current Affairs Daily Exam · Seasons</h3>
        <div class="small text-muted">Create a season, then add its daily exams, and weekly / weekend exams built from them.</div>
    </div>
    <a href="{{ kpsc_cms('ca-daily-exam/seasons/create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New Season</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search season name..."></div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\CaSeason::STATUSES as $k => [$label])
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-3">
        <select name="tag" class="form-select form-select-sm">
            <option value="">All tags</option>
            @foreach ($allTags as $t)
                <option value="{{ $t }}" @selected(request('tag') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-light mb-0">Filter</button>
        <a href="{{ kpsc_cms('ca-daily-exam') }}" class="btn btn-sm btn-light mb-0">Reset</a>
    </div>
</form>

<div class="row g-3">
    @forelse ($seasons as $s)
        @php
            [$statusLabel, $statusColor] = \App\Models\CaSeason::STATUSES[$s->status] ?? [$s->status, 'secondary'];
            $c = $examCounts[$s->id] ?? collect();
            $other = $c->except('daily')->sum();
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card bg-transparent border rounded-3 h-100">
                @if ($s->cover_image)
                    <img src="{{ asset('storage/' . $s->cover_image) }}" class="card-img-top" alt="" style="height:110px;object-fit:cover">
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="mb-1"><a href="{{ kpsc_cms('ca-daily-exam/seasons/' . $s->id) }}">{{ $s->name }}</a></h5>
                        <div class="text-end">
                            <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            @if ($s->isOngoing())<span class="badge bg-warning text-dark">ongoing</span>@endif
                        </div>
                    </div>
                    <div class="small text-muted mb-2">
                        {{ \App\Models\CaSeason::SCHEDULES[$s->schedule_type] ?? $s->schedule_type }}
                        · {{ $s->starts_on ? $s->starts_on->format('d M Y') : 'no start date' }} → {{ $s->ends_on ? $s->ends_on->format('d M Y') : 'open' }}
                        @if ($s->is_featured)<br><span class="text-success"><i class="bi bi-phone"></i> shown in the app by default</span>@endif
                    </div>
                    @if ($s->description)<p class="small mb-2">{{ \Illuminate\Support\Str::limit($s->description, 110) }}</p>@endif
                    <div class="mb-3">
                        @foreach ($s->tags ?? [] as $tag)
                            <a href="{{ kpsc_cms('ca-daily-exam') }}?tag={{ urlencode($tag) }}" class="badge bg-light text-dark border me-1">#{{ $tag }}</a>
                        @endforeach
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-3"><div class="border rounded py-1"><div class="fw-bold">{{ $c['daily'] ?? 0 }}</div><div class="small text-muted">daily</div></div></div>
                        <div class="col-3"><div class="border rounded py-1"><div class="fw-bold">{{ $other }}</div><div class="small text-muted">week/other</div></div></div>
                        <div class="col-3"><div class="border rounded py-1"><div class="fw-bold">{{ number_format($questionCounts[$s->id] ?? 0) }}</div><div class="small text-muted">questions</div></div></div>
                        <div class="col-3"><div class="border rounded py-1"><div class="fw-bold">{{ number_format($attemptCounts[$s->id] ?? 0) }}</div><div class="small text-muted">attempts</div></div></div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top d-flex gap-2">
                    <a href="{{ kpsc_cms('ca-daily-exam/seasons/' . $s->id) }}" class="btn btn-sm btn-primary mb-0">Open</a>
                    <a href="{{ kpsc_cms('ca-daily-exam/seasons/' . $s->id . '/edit') }}" class="btn btn-sm btn-light mb-0">Edit</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="text-center text-muted py-5">No seasons yet. Create the first one to start adding exams.</div></div>
    @endforelse
</div>
@endsection
