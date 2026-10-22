@extends('admin.exam.course.workspace')
@section('course_body')
@include('admin.question-library._alerts')
<div class="card bg-transparent border rounded-3 mb-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="{{ $manageUrl }}" class="small"><i class="bi bi-arrow-left"></i> {{ $feature->name }}</a>
            <h5 class="mb-0 mt-1">Add classes from the class library</h5>
        </div>
        <a href="{{ $libraryUrl }}/storage" target="_blank" class="btn btn-sm btn-light mb-0"><i class="bi bi-cloud me-1"></i>Cloud storage</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><select name="folder" class="form-select form-select-sm"><option value="">All folders</option>
                @foreach ($tree as $f)<option value="{{ $f['id'] }}" @selected(request('folder') == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach</select></div>
            <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title or teacher..."></div>
            <div class="col-md-4 d-flex align-items-center gap-3">
                <div class="form-check mb-0"><input type="hidden" name="hide_used" value="0"><input class="form-check-input" type="checkbox" name="hide_used" value="1" id="hu" @checked(request('hide_used', '1') === '1')><label class="form-check-label small" for="hu">Hide classes already in this course</label></div>
                <button class="btn btn-sm btn-light mb-0">Filter</button>
            </div>
        </form>
    </div>
</div>

<form action="{{ $manageUrl }}/add" method="POST">
    @csrf
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2 d-flex align-items-center gap-2">
            <span class="small text-muted me-auto">{{ $classes->total() }} published class(es)</span>
            <button class="btn btn-sm btn-success mb-0" onclick="return pickCheck()"><i class="bi bi-plus-lg me-1"></i>Add selected</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Class</th><th>Folder</th><th>Source</th><th>Length</th></tr></thead>
                <tbody>
                @forelse ($classes as $c)
                    @php $used = in_array($c->id, $inCourse); @endphp
                    <tr class="{{ $used ? 'opacity-50' : '' }}">
                        <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $c->id }}" @disabled($used)></td>
                        <td style="max-width:420px"><div class="text-truncate fw-semibold">{{ $c->title }}</div><div class="small text-muted">{{ $c->instructor }} @if ($used)<span class="badge bg-secondary">already in this course</span>@endif</div></td>
                        <td class="small">{{ $c->folder?->name }}</td>
                        <td class="small">{{ $c->sourceLabel() }}</td>
                        <td class="small">{{ $c->durationLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No classes match. Add classes to the <a href="{{ $libraryUrl }}">class library</a> first.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($otherCourses->isNotEmpty())
        <div class="row">@include('admin.exam.partials.also-courses', ['otherCourses' => $otherCourses, 'what' => 'class selection'])</div>
    @endif
</form>
<div class="mt-3">{{ $classes->links() }}</div>
<script>
var all = document.getElementById('checkAll');
all.addEventListener('change', function () { document.querySelectorAll('.row-check:not(:disabled)').forEach(function (c) { c.checked = all.checked; }); });
function pickCheck() { if (!document.querySelectorAll('.row-check:checked').length) { alert('Tick at least one class.'); return false; } return true; }
</script>
@endsection
