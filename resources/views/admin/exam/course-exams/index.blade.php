@extends('admin.exam.course.workspace')
@section('course_body')
@include('admin.question-library._alerts')
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0">@include('admin.partials.feature-icon', ['feature' => $feature, 'size' => 30]){{ $feature->name }}</h4>
            <div class="small text-muted">@if ($season)Season: <b>{{ $season->title }}</b> · <a href="{{ $manageUrl }}">all exams</a> · @endif Only published exams are shown to students.</div>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\CourseExam::STATUSES as $k => [$label])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>@endforeach
                </select>
            </form>
            @if ($canEdit)<a href="{{ $manageUrl }}/create{{ request('season') && request('season') !== 'none' ? '?season=' . request('season') : '' }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New exam</a>@endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th>#</th><th>Exam</th><th>Mode</th><th class="text-end">Sections</th><th class="text-end">Questions</th><th>Time</th><th>Access</th><th>Window</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($exams as $e)
                @php [$stLabel, $stColor] = \App\Models\CourseExam::STATUSES[$e->status]; @endphp
                <tr>
                    <td>{{ $e->position }}</td>
                    <td><a href="{{ $manageUrl }}/{{ $e->id }}" class="fw-semibold">{{ $e->title }}</a>@if (($seasons ?? collect())->count() && $e->season_id)<div class="small text-muted">{{ optional($seasons->firstWhere('id', $e->season_id))->title }}</div>@endif</td>
                    <td class="small">{{ \App\Models\CourseExam::MODES[$e->mode] ?? $e->mode }}</td>
                    <td class="text-end">{{ $e->sections_count }}</td>
                    <td class="text-end">{{ $e->links_count }}</td>
                    <td class="small">{{ $e->computedDuration() ? $e->computedDuration() . ' min' : '—' }}</td>
                    <td>{!! $e->is_paid ? '<span class="badge bg-warning text-dark">Paid</span>' : '<span class="badge bg-light text-dark border">Free</span>' !!}</td>
                    <td class="small text-nowrap">{{ $e->start_at?->format('d M Y') ?? '—' }} → {{ $e->end_at?->format('d M Y') ?? '—' }}</td>
                    <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ $manageUrl }}/{{ $e->id }}" class="btn btn-sm btn-light mb-0">{{ $canEdit ? 'Questions' : 'View' }}</a>
                        @if ($canEdit)<a href="{{ $manageUrl }}/{{ $e->id }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil"></i></a>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No exams yet.@if ($canEdit) Click "New exam" to create one.@endif</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
