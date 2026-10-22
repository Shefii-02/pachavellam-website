@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div><h3 class="mb-0">New joins</h3><div class="small text-muted">Students who signed up, for the sales team to contact. Mark a student once contacted; unmarked ones are still waiting for a call.</div></div>
        <a href="{{ url('admin/new-joins/export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
    <div class="row g-2 mb-3 text-center">
        <div class="col-4"><div class="border rounded py-2"><div class="small text-muted">In this list</div><div class="h5 mb-0">{{ number_format($stats['shown']) }}</div></div></div>
        <div class="col-4"><a href="{{ url('admin/new-joins') }}?{{ http_build_query(array_merge(request()->query(), ['mark' => 'unmarked'])) }}" class="text-reset text-decoration-none"><div class="border border-warning rounded py-2"><div class="small text-muted">Waiting for a call (unmarked)</div><div class="h5 mb-0 text-warning">{{ number_format($stats['unmarked']) }}</div></div></a></div>
        <div class="col-4"><a href="{{ url('admin/new-joins') }}?{{ http_build_query(array_merge(request()->query(), ['mark' => 'marked'])) }}" class="text-reset text-decoration-none"><div class="border border-success rounded py-2"><div class="small text-muted">Marked</div><div class="h5 mb-0 text-success">{{ number_format($stats['marked']) }}</div></div></a></div>
    </div>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-6 col-md-2"><select name="period" class="form-select form-select-sm">@foreach (['today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', '0' => 'Any time'] as $k => $l)<option value="{{ $k }}" @selected(request('period', '7') === (string) $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->id }}" @selected(request('exam') == $e->id)>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="mark" class="form-select form-select-sm"><option value="">Marked + unmarked</option><option value="unmarked" @selected(request('mark') === 'unmarked')>Unmarked</option><option value="marked" @selected(request('mark') === 'marked')>Marked</option></select></div>
        <div class="col-6 col-md-2"><select name="outcome" class="form-select form-select-sm"><option value="">Any outcome</option>@foreach ($outcomes as $k => $l)<option value="{{ $k }}" @selected(request('outcome') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><select name="admitted" class="form-select form-select-sm"><option value="">Admitted or not</option><option value="no" @selected(request('admitted') === 'no')>Not admitted</option><option value="yes" @selected(request('admitted') === 'yes')>Admitted</option></select></div>
        <div class="col-6 col-md-2"><select name="mobile" class="form-select form-select-sm"><option value="">Mobile or not</option><option value="yes" @selected(request('mobile') === 'yes')>Has mobile</option><option value="no" @selected(request('mobile') === 'no')>No mobile</option></select></div>
        <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Name, mobile or email..."></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-primary mb-0">Filter</button><a href="{{ url('admin/new-joins') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <form action="{{ url('admin/new-joins/bulk') }}" method="POST" id="bulkForm">
        @csrf
        @if ($canMark)
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <select name="outcome" class="form-select form-select-sm w-auto">@foreach ($outcomes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
            <input type="text" name="note" class="form-control form-control-sm w-auto" placeholder="Note (optional)" maxlength="490">
            <button name="action" value="mark" class="btn btn-sm btn-success mb-0" onclick="return pick()"><i class="bi bi-check2-circle me-1"></i>Mark selected</button>
            <button name="action" value="unmark" class="btn btn-sm btn-outline-secondary mb-0" onclick="return pick()">Unmark selected</button>
        </div>
        @endif
        <div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0 table-hover">
            <thead><tr>@if ($canMark)<th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>@endif<th>Student</th><th>Joined</th><th>Exam types</th><th>Admission</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($list as $u)
                <tr>
                    @if ($canMark)<td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $u->id }}"></td>@endif
                    <td><div class="fw-semibold">{{ $u->name ?: 'Student #' . $u->id }}</div>
                        <div class="small">@if ($u->mobile)<a href="tel:{{ $u->mobile }}">{{ $u->mobile }}</a> · <a href="https://wa.me/{{ strlen(preg_replace('/\D/', '', $u->mobile)) === 10 ? '91' : '' }}{{ preg_replace('/\D/', '', $u->mobile) }}" target="_blank" rel="noopener" class="text-success"><i class="bi bi-whatsapp"></i></a>@else<span class="text-muted">no mobile</span>@endif {{ $u->email ? ' · ' . $u->email : '' }}</div></td>
                    <td class="small text-nowrap">{{ $u->created_at?->format('d M Y, h:i A') }}<div class="text-muted">{{ $u->created_at?->diffForHumans() }}</div></td>
                    <td>@forelse (($interests[$u->id] ?? []) as $e)<span class="badge bg-light text-dark border">{{ $e->short_name ?: $e->name }}</span> @empty<span class="text-muted small">—</span>@endforelse</td>
                    <td class="small">@if (isset($admissions[$u->id]))<span class="badge bg-success">admitted</span> {{ collect($admissions[$u->id])->pluck('title')->take(2)->implode(', ') }}@else<span class="text-muted">not yet</span>@endif</td>
                    <td class="small">
                        @if ($u->marked_at)
                            <span class="badge bg-success">{{ $outcomes[$u->mark_outcome] ?? 'Marked' }}</span>
                            <div class="text-muted">{{ $markers[$u->marked_by] ?? 'staff' }} · {{ \Illuminate\Support\Carbon::parse($u->marked_at)->format('d M, h:i A') }}</div>
                            @if ($u->mark_note)<div class="text-muted" style="max-width:240px">{{ $u->mark_note }}</div>@endif
                        @else
                            <span class="badge bg-warning text-dark">Waiting for a call</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        @if ($canMark)
                            <button type="button" class="btn btn-sm btn-success mb-0" data-bs-toggle="modal" data-bs-target="#markModal" data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-outcome="{{ $u->mark_outcome }}" data-note="{{ $u->mark_note }}"><i class="bi bi-check2"></i> {{ $u->marked_at ? 'Update' : 'Mark' }}</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No new joins match.</td></tr>
            @endforelse
            </tbody>
        </table></div></div>
    </form>
    <div class="mt-3">{{ $list->links() }}</div>
</div>

@if ($canMark)
<div class="modal fade" id="markModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="markForm" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title" id="markTitle">Mark</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Result of the call</label>
            <select name="outcome" id="markOutcome" class="form-select mb-3">@foreach ($outcomes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
            <label class="form-label">Note <span class="text-muted">(optional)</span></label>
            <textarea name="note" id="markNote" rows="3" class="form-control" maxlength="490"></textarea>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="submit" form="unmarkForm" class="btn btn-outline-secondary mb-0">Unmark</button>
            <button class="btn btn-success mb-0">Save mark</button>
        </div>
    </form>
    <form id="unmarkForm" method="POST">@csrf @method('DELETE')</form>
</div></div></div>
<script>
document.getElementById('markModal').addEventListener('show.bs.modal', function (e) {
    var b = e.relatedTarget; if (!b) return;
    var url = @json(url('admin/new-joins')) + '/' + b.dataset.id + '/mark';
    document.getElementById('markForm').action = url; document.getElementById('unmarkForm').action = url;
    document.getElementById('markTitle').textContent = 'Mark ' + (b.dataset.name || 'student');
    document.getElementById('markOutcome').value = b.dataset.outcome || 'contacted'; document.getElementById('markNote').value = b.dataset.note || '';
});
var all = document.getElementById('checkAll');
if (all) all.addEventListener('change', function () { document.querySelectorAll('.row-check').forEach(function (c) { c.checked = all.checked; }); });
function pick() { if (!document.querySelectorAll('.row-check:checked').length) { alert('Tick at least one student.'); return false; } return true; }
</script>
@endif
@endsection
