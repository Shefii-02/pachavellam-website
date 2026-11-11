@extends('admin.exam.course.workspace')
@section('course_body')
@php
    $placeBase = ($seasons ?? collect())->count() && request()->attributes->get('series_kind') ? $exam->examUrl('series/' . request()->attributes->get('series_kind')) : null;   // exams of a series can be moved or copied into a season
@endphp
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
                        @if ($canEdit && $placeBase)<button type="button" class="btn btn-sm btn-light mb-0" title="Move / copy to a season" onclick="pvPlaceExam({{ $e->id }}, {{ (int) $e->season_id }})"><i class="bi bi-arrow-left-right"></i></button>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No exams yet.@if ($canEdit) Click "New exam" to create one.@endif</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@if ($canEdit && $placeBase)
<div class="modal fade" id="placeExamModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <form id="placeExamForm" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-arrow-left-right me-1"></i>Move / copy to a season</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Season</label>
            <select class="form-select mb-3" id="placeExamTo">@foreach ($seasons as $os)<option value="{{ $os->id }}">{{ $os->title }}</option>@endforeach</select>
            <label class="me-3"><input type="radio" name="mode" value="move" checked> <b>Move</b></label>
            <label><input type="radio" name="mode" value="copy"> <b>Copy</b> (the copy is a draft; the original stays)</label>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0">Apply</button></div>
    </form>
</div></div></div>
<script>
    function pvPlaceExam(id, currentSeason) {
        var f = document.getElementById('placeExamForm'), to = document.getElementById('placeExamTo');
        f.querySelectorAll('.pv-id').forEach(function (n) { n.remove(); });
        var i = document.createElement('input'); i.type = 'hidden'; i.className = 'pv-id'; i.name = 'course_ids[]'; i.value = id; f.appendChild(i);
        Array.prototype.forEach.call(to.options, function (o) { o.disabled = String(o.value) === String(currentSeason); });   // not into the season it is already in
        var first = Array.prototype.find.call(to.options, function (o) { return !o.disabled; });
        if (first) { to.value = first.value; }
        new bootstrap.Modal(document.getElementById('placeExamModal')).show();
    }
    document.getElementById('placeExamForm').addEventListener('submit', function () {
        this.action = @json($placeBase . '/seasons') + '/' + document.getElementById('placeExamTo').value + '/place';
    });
</script>
@endif
@endsection
