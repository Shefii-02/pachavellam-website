@extends($layout)
@section('content2')
@include('admin.exam._drawer-style')
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
        <a href="{{ $season->publicUrl() }}" target="_blank" class="btn btn-sm btn-light mb-0"><i class="bi bi-box-arrow-up-right me-1"></i>See on website</a>
        <button type="button" class="btn btn-sm btn-outline-success mb-0 js-share" @disabled($season->status !== 'published') data-url="{{ $season->publicUrl() }}" data-title="{{ $season->title }}"
            data-text="{{ $season->title . ($season->tagline ? ' - ' . $season->tagline : '') . "\n" . ($season->is_paid ? 'Practise on Pachavellam:' : 'Practise free on Pachavellam:') }}"><i class="bi bi-share me-1"></i>Share</button>
        <button type="button" class="btn btn-sm btn-outline-primary mb-0" data-bs-toggle="modal" data-bs-target="#editSession"><i class="bi bi-pencil me-1"></i>Edit season</button>
        <form action="{{ $base }}/seasons/{{ $season->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete the season &quot;{{ addslashes($season->title) }}&quot;? Its {{ $courseExams->count() + $legacy->count() }} exam(s) are kept without a season.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash me-1"></i>Delete season</button></form>
    </div>
</div>
@if (session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

{{-- exams of the season --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <strong><i class="bi bi-list-check me-1"></i>Exams in this season <span class="badge bg-secondary">{{ $courseExams->count() + $legacy->count() }}</span></strong>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ $addUrl }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-plus-lg me-1"></i>Add exam</a>
            @if ($legacyAdd)<a href="{{ $legacyAdd }}" class="btn btn-sm btn-outline-success mb-0" title="The old exam form of KPSC"><i class="bi bi-plus-lg me-1"></i>Add exam (old form)</a>@endif
            @if ($attachable->count() || $otherCourse->count())<button type="button" class="btn btn-sm btn-outline-secondary mb-0" data-bs-toggle="modal" data-bs-target="#attachExams"><i class="bi bi-link-45deg me-1"></i>Add from Other exams</button>@endif
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 pb-2">
        <div class="small text-muted"><i class="bi bi-grip-vertical"></i> Drag <b>⠿</b> to set the order of the exams (students see this order). <span id="orderNote"></span></div>
        <form action="{{ $base }}/seasons/{{ $season->id }}/exams-order/reset" method="POST" onsubmit="return confirm('Go back to the default order (newest first)?')">@csrf<button class="btn btn-sm btn-outline-secondary mb-0"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset to date order</button></form>
    </div>
    <div class="d-none align-items-center flex-wrap gap-2 px-3 pb-2" id="bulkBar">
        <form action="{{ $base }}/seasons/{{ $season->id }}/bulk" method="POST" class="d-flex align-items-center flex-wrap gap-2" id="bulkForm">@csrf
            <span class="badge bg-primary"><span id="bulkN">0</span> selected</span>
            <select name="action" class="form-select form-select-sm w-auto" id="bulkAction" required>
                <option value="">Action…</option>
                <option value="publish">Publish</option>
                <option value="unpublish">Unpublish (hide)</option>
                @if ($paid)<option value="free">Make free access</option><option value="paid">Follow season (paid)</option>@endif
                <option value="remove">Take out of the season</option>
            </select>
            <button class="btn btn-sm btn-success mb-0">Apply</button>
            <button type="button" class="btn btn-sm btn-light mb-0" id="bulkClear">Clear</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th style="width:30px"></th><th style="width:30px"><input type="checkbox" class="form-check-input" id="bulkAll" title="Select all"></th><th>Exam</th><th>Date</th><th>Start - End</th><th class="text-center">Attempts</th><th>Status</th>@if ($paid)<th class="text-center">Free access</th>@endif<th class="text-end">Actions</th></tr></thead>
            <tbody id="examRows">
            @foreach ($items as $it)
              @if ($it['type'] === 'course')
                @php $e = $it['model']; [$esL, $esC] = \App\Models\CourseExam::STATUSES[$e->status] ?? [$e->status, 'secondary']; @endphp
                <tr data-type="course" data-id="{{ $e->id }}">
                    <td class="pv-drag" title="Drag to change the order">⠿</td>
                    <td><input type="checkbox" class="form-check-input bulk-cb" data-type="course" value="{{ $e->id }}"></td>
                    <td><a href="{{ $builder }}/{{ $e->id }}" class="fw-semibold">{{ $e->title }}</a><div class="small text-muted">{{ $e->sections_count }} section(s) · {{ $e->links_count }} question(s)@if ($e->sort_no !== null) · sort no. {{ $e->sort_no }}@endif</div></td>
                    <td class="small">{{ $e->start_at ? $e->start_at->format('d M Y') : ($e->entry_date ? \Illuminate\Support\Carbon::parse($e->entry_date)->format('d M Y') : '-') }}</td>
                    <td class="small text-nowrap">@if ($e->start_at || $e->end_at){{ $e->start_at?->format('d M, h:i a') ?? '…' }}<br>{{ $e->end_at?->format('d M, h:i a') ?? '…' }}@else - @endif</td>
                    <td class="text-center"><b>{{ (int) ($courseAttempts[$e->id] ?? 0) }}</b></td>
                    <td><span class="badge bg-{{ $esC }}">{{ $esL }}</span></td>
                    @if ($paid)<td class="text-center"><form action="{{ $base }}/seasons/{{ $season->id }}/free/course/{{ $e->id }}" method="POST">@csrf<button class="btn btn-sm {{ $e->free_access ? 'btn-success' : 'btn-outline-secondary' }} mb-0 py-0" title="Open for everyone without buying">{{ $e->free_access ? 'Free' : 'Paid' }}</button></form></td>@endif
                    <td class="text-end text-nowrap">
                        <a href="{{ $builder }}/{{ $e->id }}/edit" class="btn btn-sm btn-light mb-0" title="Edit"><i class="bi bi-pencil"></i></a>
                        <a href="{{ $builder }}/{{ $e->id }}" class="btn btn-sm btn-light mb-0" title="Questions"><i class="bi bi-question-circle"></i></a>
                        <button type="button" class="btn btn-sm btn-light mb-0 js-lb" title="Leaderboard ({{ (int) ($courseAttempts[$e->id] ?? 0) }} attempt(s))" data-type="course" data-id="{{ $e->id }}" data-n="{{ (int) ($courseAttempts[$e->id] ?? 0) }}" data-title="{{ $e->title }}"><i class="bi bi-trophy"></i></button>
                        <button type="button" class="btn btn-sm btn-light mb-0" title="Move / copy to another season" onclick="pvPlace('course',{{ $e->id }})"><i class="bi bi-arrow-left-right"></i></button>
                        <form class="d-inline" action="{{ $builder }}/{{ $e->id }}/status" method="POST">@csrf<input type="hidden" name="status" value="{{ $e->status === 'published' ? 'draft' : 'published' }}"><button class="btn btn-sm btn-light mb-0" title="{{ $e->status === 'published' ? 'Unpublish' : 'Publish' }}"><i class="bi {{ $e->status === 'published' ? 'bi-eye-slash' : 'bi-eye' }}"></i></button></form>
                        <form class="d-inline" action="{{ $builder }}/{{ $e->id }}" method="POST" onsubmit="return confirm('Move this exam to the Trash?')">@csrf @method('DELETE')<input type="hidden" name="back" value="{{ urldecode($back) }}"><button class="btn btn-sm btn-outline-danger mb-0" title="Delete"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
              @else
                @php $d = $it['model']; @endphp
                <tr data-type="legacy" data-id="{{ $d->id }}">
                    <td class="pv-drag" title="Drag to change the order">⠿</td>
                    <td><input type="checkbox" class="form-check-input bulk-cb" data-type="legacy" value="{{ $d->id }}"></td>
                    <td><span class="fw-semibold">{{ $d->exam_title ?: ($d->examtitle ?: 'Exam') }}</span> <span class="badge bg-dark">old KPSC</span>@if ($d->sort_no !== null)<div class="small text-muted">sort no. {{ $d->sort_no }}</div>@endif</td>
                    @php $att = $legacyAttempts->get($d->id); $n = (int) ($att->c ?? 0); @endphp
                    <td class="small">{{ $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '-' }}</td>
                    <td class="small text-nowrap">@if ($d->started_at || $d->ended_at){{ $d->started_at ? \Illuminate\Support\Carbon::parse($d->started_at)->format('d M, h:i a') : '…' }}<br>{{ $d->ended_at ? \Illuminate\Support\Carbon::parse($d->ended_at)->format('d M, h:i a') : '…' }}@else - @endif</td>
                    <td class="text-center"><b>{{ $n }}</b>@if ($kind !== 'omr' && $att && $att->u != $n)<div class="small text-muted">{{ (int) $att->u }} student(s)</div>@endif</td>
                    <td><span class="badge bg-{{ $d->status ? 'success' : 'secondary' }}">{{ $d->status ? 'Published' : 'Draft' }}</span></td>
                    @if ($paid)<td class="text-center"><form action="{{ $base }}/seasons/{{ $season->id }}/free/legacy/{{ $d->id }}" method="POST">@csrf<button class="btn btn-sm {{ $d->free_access ? 'btn-success' : 'btn-outline-secondary' }} mb-0 py-0">{{ $d->free_access ? 'Free' : 'Paid' }}</button></form></td>@endif
                    <td class="text-end text-nowrap">
                        @if ($kind === 'omr')<button type="button" class="btn btn-sm btn-light mb-0 js-omr-edit" title="Edit exam details" data-id="{{ $d->id }}" data-date="{{ $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('Y-m-d') : '' }}"
                            data-started="{{ $d->started_at ? \Illuminate\Support\Carbon::parse($d->started_at)->format('Y-m-d\TH:i') : '' }}" data-ended="{{ $d->ended_at ? \Illuminate\Support\Carbon::parse($d->ended_at)->format('Y-m-d\TH:i') : '' }}"
                            data-title="{{ $d->examtitle }}" data-category="{{ $d->category }}" data-cutoff="{{ $d->cutoff_mark }}" data-sort="{{ $d->sort_no }}" data-instruction="{{ $d->instruction_id }}" data-free="{{ $d->free_access ? 1 : 0 }}"
                            data-qp="{{ $d->model_exam_details_one?->qp_file ? url(Storage::url('model-exam/' . $d->model_exam_details_one->qp_file)) : '' }}" data-ak="{{ $d->model_exam_details_one?->answer_file ? url(Storage::url('model-exam/' . $d->model_exam_details_one->answer_file)) : '' }}"><i class="bi bi-pencil"></i></button>
                        @elseif ($legacyBase)<a href="{{ url($legacyBase . '/edit/' . $d->id) }}" class="btn btn-sm btn-light mb-0" title="Edit"><i class="bi bi-pencil"></i></a>@endif
                        <button type="button" class="btn btn-sm btn-light mb-0 js-lb" title="Leaderboard ({{ $n }} attempt(s))" data-id="{{ $d->id }}" data-n="{{ $n }}" data-title="{{ $d->examtitle ?: ($d->exam_title ?: 'Exam') }}"><i class="bi bi-trophy"></i></button>
                        <button type="button" class="btn btn-sm btn-light mb-0" title="Move / copy to another season" onclick="pvPlace('legacy',{{ $d->id }})"><i class="bi bi-arrow-left-right"></i></button>
                        <form class="d-inline" action="{{ $base }}/seasons/{{ $season->id }}/legacy-status/{{ $d->id }}" method="POST">@csrf<button class="btn btn-sm btn-light mb-0" title="{{ $d->status ? 'Unpublish' : 'Publish' }}"><i class="bi {{ $d->status ? 'bi-eye-slash' : 'bi-eye' }}"></i></button></form>
                        <form class="d-inline" action="{{ $base }}/seasons/{{ $season->id }}/detach/{{ $d->id }}" method="POST" onsubmit="return confirm('Take this exam out of the season? The exam itself is kept.')">@csrf<button class="btn btn-sm btn-light mb-0" title="Take out of the season"><i class="bi bi-box-arrow-right"></i></button></form>
                        @if ($legacyBase)<a href="{{ url($legacyBase . '/delete/' . $d->id) }}" class="btn btn-sm btn-outline-danger mb-0" title="Delete the exam" onclick="return confirm('Delete this exam and its questions?')"><i class="bi bi-trash"></i></a>@endif
                    </td>
                </tr>
              @endif
            @endforeach
            @if (!count($items))
                <tr><td colspan="10" class="text-center text-muted py-5"><i class="bi bi-journal-plus fs-1 d-block mb-2"></i>No exams in this season yet. Press <b>Add exam</b>.</td></tr>
            @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Edit season --}}
<div class="modal fade modal-drawer" id="editSession" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form action="{{ $base }}/seasons/{{ $season->id }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')<input type="hidden" name="back" value="session">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit season</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-2">@include('admin.exam._season-fields', ['s' => $season, 'p' => 'ed', 'special' => $special])</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0"><i class="bi bi-check-lg me-1"></i>Save</button></div>
    </form>
</div></div></div>

@if ($kind === 'omr')
{{-- Edit an OMR exam here (saves and comes back to this season, no redirect to the old OMR list) --}}
<div class="modal fade modal-drawer" id="editOmrExam" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content">
    <form action="{{ url('admin/kpsc/model-exam/edit-details') }}" method="POST" enctype="multipart/form-data">@csrf
        <input type="hidden" name="exam_id" id="oe_id"><input type="hidden" name="series_season_id" value="{{ $season->id }}"><input type="hidden" name="back" value="{{ url()->current() }}">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit exam details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-2">
            <div class="col-12"><label class="form-label small mb-0">Exam title <span class="text-danger">*</span></label><input class="form-control" name="examtitle" id="oe_title" required autocomplete="off"></div>
            <div class="col-12"><label class="form-label small mb-0">Exam date <span class="text-danger">*</span></label><input type="date" class="form-control" name="exam_date" id="oe_date" required></div>
            <div class="col-12"><label class="form-label small mb-0">Starts at <span class="text-danger">*</span></label><input type="datetime-local" class="form-control" name="exam_started" id="oe_started" required></div>
            <div class="col-12"><label class="form-label small mb-0">Ends at <span class="text-danger">*</span></label><input type="datetime-local" class="form-control" name="exam_ended" id="oe_ended" required></div>
            <div class="col-12"><label class="form-label small mb-0">Sort number</label><input type="number" min="0" class="form-control" name="sort_no" id="oe_sort" placeholder="optional - smallest shows first on the OMR exams page"></div>
            <div class="col-12"><label class="form-label small mb-0">Cutoff mark</label><input type="number" step="0.01" min="0" class="form-control" name="cutoff_mark" id="oe_cutoff" placeholder="optional"></div>
            <div class="col-12"><label class="form-label small mb-0">Instructions</label><select class="form-select" name="instruction_id" id="oe_instruction"><option value="">Default (OMR)</option>@foreach (\App\Models\ExamInstruction::where('kind', 'omr')->orderBy('name')->get() as $ins)<option value="{{ $ins->id }}">{{ $ins->name }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label small mb-0">Category <span class="text-danger">*</span></label><select class="form-select" name="category" id="oe_category" required><option value=""></option>@foreach (['10th level' => '10th level', 'Plus two level' => 'Plus two level', 'Degree level' => 'Degree level', 'Programmer' => 'Programmer', 'Typest' => 'Typest'] as $cv => $cl)<option value="{{ $cv }}">{{ $cl }}</option>@endforeach</select></div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="free_access" id="oe_free" value="1"><label class="form-check-label" for="oe_free">Free access - open for everyone even when the season is paid</label></div></div>
            <div class="col-12"><label class="form-label small mb-0">Question paper (PDF)</label><input class="form-control" accept=".pdf" type="file" name="qstn_file"><a href="#" target="_blank" id="oe_qp" class="small">View current question paper</a></div>
            <div class="col-12"><label class="form-label small mb-0">Answer key (PDF)</label><input class="form-control" accept=".pdf" type="file" name="ans_file"><a href="#" target="_blank" id="oe_ak" class="small">View current answer key</a></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0"><i class="bi bi-check-lg me-1"></i>Save changes</button></div>
    </form>
</div></div></div>
<script>
    document.querySelectorAll('.js-omr-edit').forEach(function (b) {
        b.addEventListener('click', function () {
            var d = b.dataset, set = function (id, v) { document.getElementById(id).value = v || ''; };
            set('oe_id', d.id); set('oe_title', d.title); set('oe_date', d.date); set('oe_started', d.started); set('oe_ended', d.ended); set('oe_cutoff', d.cutoff); set('oe_sort', d.sort); set('oe_instruction', d.instruction); set('oe_category', d.category);
            document.getElementById('oe_free').checked = d.free === '1';
            [['oe_qp', d.qp], ['oe_ak', d.ak]].forEach(function (x) { var a = document.getElementById(x[0]); a.style.display = x[1] ? '' : 'none'; a.href = x[1] || '#'; });
            new bootstrap.Modal(document.getElementById('editOmrExam')).show();
        });
    });
</script>
@endif

{{-- Leaderboard of an old exam, with the clear action inside --}}
<div class="modal fade modal-drawer modal-drawer-wide" id="lbDrawer" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-trophy me-1"></i><span id="lbTitle">Leaderboard</span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="small text-muted mb-2" id="lbSub"></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>#</th><th>Student</th><th class="text-end">Marks</th><th class="text-center">✓ / ✗</th><th>Time</th><th>Submitted</th></tr></thead><tbody id="lbRows"></tbody></table></div>
    </div>
    <div class="modal-footer justify-content-between">
        <form id="lbClear" method="POST" onsubmit="return confirm('Clear this leaderboard? Every attempt of this exam is deleted. This cannot be undone.')" style="display:none">@csrf<button class="btn btn-danger mb-0"><i class="bi bi-trash me-1"></i>Clear leaderboard</button></form>
        <button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Close</button>
    </div>
</div></div></div>
<style>
    .pv-drag { cursor:grab; color:#8b95a1; font-size:1.2rem; user-select:none; text-align:center; } .pv-drag:active { cursor:grabbing; }
    #examRows tr.sortable-ghost { opacity:.4; background:#e8f0fe; } #examRows tr.sortable-chosen { background:#f1f6ff; }
</style>
<script>
    (function () {
        var cbs = function () { return [].slice.call(document.querySelectorAll('.bulk-cb')); }, all = document.getElementById('bulkAll'),
            bar = document.getElementById('bulkBar'), form = document.getElementById('bulkForm');
        if (!all || !form) return;
        var refresh = function () {
            var on = cbs().filter(function (c) { return c.checked; });
            document.getElementById('bulkN').textContent = on.length;
            bar.classList.toggle('d-none', !on.length); bar.classList.toggle('d-flex', !!on.length);
            all.checked = on.length > 0 && on.length === cbs().length;
        };
        all.addEventListener('change', function () { cbs().forEach(function (c) { c.checked = all.checked; }); refresh(); });
        document.addEventListener('change', function (e) { if (e.target.classList && e.target.classList.contains('bulk-cb')) refresh(); });
        document.getElementById('bulkClear').addEventListener('click', function () { cbs().forEach(function (c) { c.checked = false; }); refresh(); });
        form.addEventListener('submit', function (e) {
            var a = document.getElementById('bulkAction'), on = cbs().filter(function (c) { return c.checked; });
            if (!a.value || !on.length) { e.preventDefault(); return; }
            if (!confirm(a.options[a.selectedIndex].text + ' - ' + on.length + ' exam(s)?')) { e.preventDefault(); return; }
            form.querySelectorAll('.bulk-id').forEach(function (n) { n.remove(); });
            on.forEach(function (c) { var i = document.createElement('input'); i.type = 'hidden'; i.className = 'bulk-id'; i.name = c.dataset.type === 'legacy' ? 'legacy_ids[]' : 'course_ids[]'; i.value = c.value; form.appendChild(i); });
        });
    })();
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
    (function () {
        var tb = document.getElementById('examRows'), note = document.getElementById('orderNote');
        if (!tb || !window.Sortable) return;
        var say = function (t, ok) { if (!note) return; note.textContent = t; note.className = 'small ' + (ok ? 'text-success' : 'text-danger'); if (ok) setTimeout(function () { note.textContent = ''; }, 2500); };
        Sortable.create(tb, {
            handle: '.pv-drag', animation: 150, draggable: 'tr[data-id]',
            onEnd: function () {
                var items = [].map.call(tb.querySelectorAll('tr[data-id]'), function (r) { return { type: r.dataset.type, id: +r.dataset.id }; });
                say('Saving the order…', true);
                fetch(@json($base . '/seasons/' . $season->id . '/exams-order'), { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) }, body: JSON.stringify({ items: items }) })
                    .then(function (r) { if (!r.ok) throw 0; say('Order saved.', true); })
                    .catch(function () { say('Could not save the order. Refresh and try again.', false); });
            }
        });
    })();
</script>
<script>
    (function () {
        var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
        var clock = function (s) { s = Math.max(0, s | 0); return Math.floor(s / 60) + 'm ' + (s % 60) + 's'; };
        var el = document.getElementById('lbDrawer'), rows = document.getElementById('lbRows'), form = document.getElementById('lbClear'), modal = null;
        document.querySelectorAll('.js-lb').forEach(function (b) {
            b.addEventListener('click', function () {
                document.getElementById('lbTitle').textContent = b.dataset.title; document.getElementById('lbSub').textContent = 'Loading…'; rows.innerHTML = ''; form.style.display = 'none';
                var qs = b.dataset.type === 'course' ? '?type=course' : '';
                form.action = @json($base . '/seasons/' . $season->id . '/clear-leaderboard/') + b.dataset.id + qs;
                modal = modal || new bootstrap.Modal(el); modal.show();
                fetch(@json($base . '/seasons/' . $season->id . '/leaderboard/') + b.dataset.id + qs, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw 0; return r.json(); })
                    .then(function (d) {
                        document.getElementById('lbSub').textContent = d.total + ' attempt(s)' + (d.total > d.rows.length ? ' · showing the top ' + d.rows.length : '') + (d.cutoff != null ? ' · cutoff ' + d.cutoff : '');
                        rows.innerHTML = d.rows.length ? d.rows.map(function (r) {
                            return '<tr' + (d.cutoff != null && r.mark < d.cutoff ? ' class="table-danger"' : '') + '><td>' + r.rank + '</td><td><b>' + esc(r.name) + '</b>' + (r.mobile ? '<div class="small text-muted">' + esc(r.mobile) + '</div>' : '') + '</td><td class="text-end"><b>' + r.mark + '</b></td><td class="text-center small">' + (r.ok == null ? '-' : r.ok + ' / ' + r.bad) + '</td><td class="small">' + (r.secs != null ? clock(r.secs) : '-') + '</td><td class="small text-nowrap">' + esc(r.at) + '</td></tr>';
                        }).join('') : '<tr><td colspan="6" class="text-center text-muted py-4">No attempts yet.</td></tr>';
                        form.style.display = d.total ? '' : 'none';
                    })
                    .catch(function () { document.getElementById('lbSub').textContent = 'Could not load the leaderboard.'; });
            });
        });
    })();
</script>

{{-- Add exams of the "Other exams" list (no season): move or copy them into this season --}}
@if ($attachable->count() || $otherCourse->count())
<div class="modal fade modal-drawer modal-drawer-wide" id="attachExams" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form action="{{ $base }}/seasons/{{ $season->id }}/place" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-link-45deg me-1"></i>Add from Other exams</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="small text-muted mb-2">Exams that are not in any season (the "Other exams" list students see). Tick the ones to add.</div>
            <div class="mb-3">
                <label class="me-3"><input type="radio" name="mode" value="move" checked> <b>Move</b> here (leaves "Other exams")</label>
                <label><input type="radio" name="mode" value="copy"> <b>Copy</b> here (original stays; the copy is a draft)</label>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control" id="attachSearch" placeholder="Search exam by name" autocomplete="off">
            </div>
            <div class="small text-muted mb-2 d-none" id="attachNone">No exam matches your search.</div>
            <div class="list-group" id="attachList">
                @foreach ($otherCourse as $e)
                    <label class="list-group-item d-flex gap-2 align-items-center"><input class="form-check-input mt-0" type="checkbox" name="course_ids[]" value="{{ $e->id }}"><span class="flex-fill">{{ $e->title }}</span><span class="badge bg-{{ \App\Models\CourseExam::STATUSES[$e->status][1] ?? 'secondary' }}">{{ \App\Models\CourseExam::STATUSES[$e->status][0] ?? $e->status }}</span></label>
                @endforeach
                @foreach ($attachable as $d)
                    <label class="list-group-item d-flex gap-2 align-items-center"><input class="form-check-input mt-0" type="checkbox" name="legacy_ids[]" value="{{ $d->id }}"><span class="flex-fill">{{ $d->exam_title ?: ($d->examtitle ?: 'Exam') }}</span><span class="badge bg-dark">old KPSC</span><span class="small text-muted">{{ $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '' }}</span></label>
                @endforeach
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0">Apply</button></div>
    </form>
</div></div></div>
<script>
    (function () {
        var box = document.getElementById('attachSearch');
        if (!box) return;
        box.addEventListener('input', function () {
            var q = box.value.trim().toLowerCase(), shown = 0;
            document.querySelectorAll('#attachList > label').forEach(function (row) {
                var hit = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                row.classList.toggle('d-none', !hit);
                if (hit) shown++;
            });
            document.getElementById('attachNone').classList.toggle('d-none', shown > 0);
        });
        box.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        document.getElementById('attachExams').addEventListener('shown.bs.modal', function () { box.focus(); });
    })();
</script>
@endif

{{-- Move / copy one exam of this season to another season --}}
@if ($otherSeasons->count())
<div class="modal fade modal-drawer" id="placeExam" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <form id="placeForm" method="POST">@csrf
        <input type="hidden" id="placeId">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-arrow-left-right me-1"></i>Move / copy to another season</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Season</label>
            <select class="form-select mb-3" id="placeTo">@foreach ($otherSeasons as $os)<option value="{{ $os->id }}">{{ $os->title }}</option>@endforeach</select>
            <label class="me-3"><input type="radio" name="mode" value="move" checked> <b>Move</b></label>
            <label><input type="radio" name="mode" value="copy"> <b>Copy</b> (draft)</label>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0">Apply</button></div>
    </form>
</div></div></div>
<script>
    function pvPlace(type, id) {
        var f = document.getElementById('placeForm');
        f.querySelectorAll('.pv-id').forEach(function (n) { n.remove(); });
        var i = document.createElement('input'); i.type = 'hidden'; i.className = 'pv-id'; i.name = (type === 'legacy' ? 'legacy_ids[]' : 'course_ids[]'); i.value = id; f.appendChild(i);
        new bootstrap.Modal(document.getElementById('placeExam')).show();
    }
    document.getElementById('placeForm').addEventListener('submit', function () {
        this.action = @json($base . '/seasons') + '/' + document.getElementById('placeTo').value + '/place';
    });
</script>
@endif
@include('admin.exam._season-form-js')
@include('admin.exam._share-drawer')
<script>
    function pvSeasonPaid(p) { var on = document.getElementById(p + 'p').checked; document.getElementById(p + 'box').style.display = on ? '' : 'none'; }
    function pvSeasonValidity(p) {
        var box = document.getElementById(p + 'box'); if (!box) return; var t = box.querySelector('[name=validity_type]').value;
        document.getElementById(p + 'vd').style.display = t === 'days' ? '' : 'none'; document.getElementById(p + 'vt').style.display = t === 'date' ? '' : 'none';
    }
    document.querySelectorAll('[id$="box"]').forEach(function (b) { pvSeasonValidity(b.id.replace(/box$/, '')); });
</script>
@endsection
