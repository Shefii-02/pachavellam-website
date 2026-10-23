@extends($layout)
@section('content2')
@php
    $label = $exam->short_name ?: $exam->name;
    $paid = $access->isPaid($season);
    [$stL, $stC] = \App\Models\ExamSeason::STATUSES[$season->status];
    $addUrl = $builder . '/create?season=' . $season->id;
    $back = urlencode($exam->examUrl('series/' . $kind . '/seasons/' . $season->id));
    $legacyAdd = ['daily' => url('admin/kpsc/daily-exam') . '?session=' . $season->id, 'omr' => url('admin/kpsc/model-exam') . '?session=' . $season->id][$kind] ?? null;
@endphp
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ $base }}" class="small"><i class="bi bi-arrow-left"></i> {{ $title }}</a>
        <h3 class="mb-1">{{ $season->title }} <span class="badge bg-{{ $stC }} fs-6 align-middle">{{ $stL }}</span></h3>
        <div class="small">
            @if ($season->basis)<span class="badge bg-info text-dark">{{ \App\Models\ExamSeason::BASES[$season->basis] ?? $season->basis }}@if ($season->basis_name): {{ $season->basis_name }}@endif</span>@endif
            @if ($paid)
                <span class="badge bg-warning text-dark">Paid ₹{{ rtrim(rtrim(number_format($access->effectivePrice($season), 2), '0'), '.') }}@if ($season->discount_price) <s class="opacity-75">₹{{ rtrim(rtrim(number_format($season->price, 2), '0'), '.') }}</s>@endif</span>
                <span class="badge bg-light text-dark border">{{ $access->validityLabel($season) }}</span>
                @if ($season->free_exams)<span class="badge bg-success">first {{ $season->free_exams }} exam(s) free</span>@endif
            @else<span class="badge bg-success">Free</span>@endif
            @if ($season->starts_on || $season->ends_on)<span class="text-muted ms-1"><i class="bi bi-calendar3"></i> {{ $season->starts_on?->format('d M Y') ?? '…' }} - {{ $season->ends_on?->format('d M Y') ?? '…' }}</span>@endif
        </div>
        @if ($season->tagline)<div class="text-muted small mt-1">{{ $season->tagline }}</div>@endif
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ url('seasons/' . $season->id) }}" target="_blank" class="btn btn-sm btn-light mb-0"><i class="bi bi-box-arrow-up-right me-1"></i>See on website</a>
        <button type="button" class="btn btn-sm btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#editSession"><i class="bi bi-pencil me-1"></i>Edit season</button>
    </div>
</div>
@if (season('success'))<div class="alert alert-success py-2">{{ season('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

{{-- exams of the season --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <strong><i class="bi bi-list-check me-1"></i>Exams in this season <span class="badge bg-secondary">{{ $courseExams->count() + $legacy->count() }}</span></strong>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ $addUrl }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-plus-lg me-1"></i>Add exam</a>
            @if ($legacyAdd)<a href="{{ $legacyAdd }}" class="btn btn-sm btn-outline-success mb-0" title="The old exam form of KPSC"><i class="bi bi-plus-lg me-1"></i>Add exam (old form)</a>@endif
            @if ($attachable->count())<button type="button" class="btn btn-sm btn-outline-secondary mb-0" data-bs-toggle="modal" data-bs-target="#attachExams"><i class="bi bi-link-45deg me-1"></i>Add existing exams</button>@endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th>Exam</th><th>Date</th><th>Status</th>@if ($paid)<th class="text-center">Free access</th>@endif<th class="text-end">Actions</th></tr></thead>
            <tbody>
            @foreach ($courseExams as $e)
                @php [$esL, $esC] = \App\Models\CourseExam::STATUSES[$e->status] ?? [$e->status, 'secondary']; @endphp
                <tr>
                    <td><a href="{{ $builder }}/{{ $e->id }}" class="fw-semibold">{{ $e->title }}</a><div class="small text-muted">{{ $e->sections_count }} section(s) · {{ $e->links_count }} question(s)</div></td>
                    <td class="small">{{ $e->start_at ? $e->start_at->format('d M Y, h:i a') : ($e->entry_date ? \Illuminate\Support\Carbon::parse($e->entry_date)->format('d M Y') : '-') }}</td>
                    <td><span class="badge bg-{{ $esC }}">{{ $esL }}</span></td>
                    @if ($paid)<td class="text-center"><form action="{{ $base }}/seasons/{{ $season->id }}/free/course/{{ $e->id }}" method="POST">@csrf<button class="btn btn-sm {{ $e->free_access ? 'btn-success' : 'btn-outline-secondary' }} mb-0 py-0" title="Open for everyone without buying">{{ $e->free_access ? 'Free' : 'Paid' }}</button></form></td>@endif
                    <td class="text-end text-nowrap">
                        <a href="{{ $builder }}/{{ $e->id }}/edit" class="btn btn-sm btn-light mb-0" title="Edit"><i class="bi bi-pencil"></i></a>
                        <a href="{{ $builder }}/{{ $e->id }}" class="btn btn-sm btn-light mb-0" title="Questions"><i class="bi bi-question-circle"></i></a>
                        <form class="d-inline" action="{{ $builder }}/{{ $e->id }}/status" method="POST">@csrf<input type="hidden" name="status" value="{{ $e->status === 'published' ? 'draft' : 'published' }}"><button class="btn btn-sm btn-light mb-0" title="{{ $e->status === 'published' ? 'Unpublish' : 'Publish' }}"><i class="bi {{ $e->status === 'published' ? 'bi-eye-slash' : 'bi-eye' }}"></i></button></form>
                        <form class="d-inline" action="{{ $builder }}/{{ $e->id }}" method="POST" onsubmit="return confirm('Move this exam to the Trash?')">@csrf @method('DELETE')<input type="hidden" name="back" value="{{ urldecode($back) }}"><button class="btn btn-sm btn-outline-danger mb-0" title="Delete"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @endforeach
            @foreach ($legacy as $d)
                <tr>
                    <td><span class="fw-semibold">{{ $d->exam_title ?: ($d->examtitle ?: 'Exam') }}</span> <span class="badge bg-dark">old KPSC</span></td>
                    <td class="small">{{ $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '-' }}</td>
                    <td><span class="badge bg-{{ $d->status ? 'success' : 'secondary' }}">{{ $d->status ? 'Published' : 'Draft' }}</span></td>
                    @if ($paid)<td class="text-center"><form action="{{ $base }}/seasons/{{ $season->id }}/free/legacy/{{ $d->id }}" method="POST">@csrf<button class="btn btn-sm {{ $d->free_access ? 'btn-success' : 'btn-outline-secondary' }} mb-0 py-0">{{ $d->free_access ? 'Free' : 'Paid' }}</button></form></td>@endif
                    <td class="text-end text-nowrap">
                        @if ($kind === 'omr')<a href="{{ url('admin/kpsc/model-exam/view') }}" class="btn btn-sm btn-light mb-0" title="Edit in the OMR exam list"><i class="bi bi-pencil"></i></a>
                        @elseif ($legacyBase)<a href="{{ url($legacyBase . '/edit/' . $d->id) }}" class="btn btn-sm btn-light mb-0" title="Edit"><i class="bi bi-pencil"></i></a>@endif
                        <form class="d-inline" action="{{ $base }}/seasons/{{ $season->id }}/legacy-status/{{ $d->id }}" method="POST">@csrf<button class="btn btn-sm btn-light mb-0" title="{{ $d->status ? 'Unpublish' : 'Publish' }}"><i class="bi {{ $d->status ? 'bi-eye-slash' : 'bi-eye' }}"></i></button></form>
                        <form class="d-inline" action="{{ $base }}/seasons/{{ $season->id }}/detach/{{ $d->id }}" method="POST" onsubmit="return confirm('Take this exam out of the season? The exam itself is kept.')">@csrf<button class="btn btn-sm btn-light mb-0" title="Take out of the season"><i class="bi bi-box-arrow-right"></i></button></form>
                        @if ($legacyBase)<a href="{{ url($legacyBase . '/delete/' . $d->id) }}" class="btn btn-sm btn-outline-danger mb-0" title="Delete the exam" onclick="return confirm('Delete this exam and its questions?')"><i class="bi bi-trash"></i></a>@endif
                    </td>
                </tr>
            @endforeach
            @if (!$courseExams->count() && !$legacy->count())
                <tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-journal-plus fs-1 d-block mb-2"></i>No exams in this season yet. Press <b>Add exam</b>.</td></tr>
            @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Edit season --}}
<div class="modal fade" id="editSession" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form action="{{ $base }}/seasons/{{ $season->id }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')<input type="hidden" name="back" value="season">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit season</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-2">@include('admin.exam._season-fields', ['s' => $season, 'p' => 'ed', 'special' => $special])</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
</div></div></div>

{{-- Add existing old exams --}}
@if ($attachable->count())
<div class="modal fade" id="attachExams" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form action="{{ $base }}/seasons/{{ $season->id }}/attach" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-link-45deg me-1"></i>Add existing exams to this season</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="small text-muted mb-2">Old exams that are not in any season. Tick the ones to add.</div>
            <div class="list-group">
                @foreach ($attachable as $d)
                    <label class="list-group-item d-flex gap-2 align-items-center"><input class="form-check-input mt-0" type="checkbox" name="ids[]" value="{{ $d->id }}"><span class="flex-fill">{{ $d->exam_title ?: ($d->examtitle ?: 'Exam') }}</span><span class="small text-muted">{{ $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '' }}</span></label>
                @endforeach
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0">Add to season</button></div>
    </form>
</div></div></div>
@endif
<script>
    function pvSeasonPaid(p) { var on = document.getElementById(p + 'p').checked; document.getElementById(p + 'box').style.display = on ? '' : 'none'; }
    function pvSeasonValidity(p) {
        var box = document.getElementById(p + 'box'); if (!box) return; var t = box.querySelector('[name=validity_type]').value;
        document.getElementById(p + 'vd').style.display = t === 'days' ? '' : 'none'; document.getElementById(p + 'vt').style.display = t === 'date' ? '' : 'none';
    }
    document.querySelectorAll('[id$="box"]').forEach(function (b) { pvSeasonValidity(b.id.replace(/box$/, '')); });
</script>
@endsection
