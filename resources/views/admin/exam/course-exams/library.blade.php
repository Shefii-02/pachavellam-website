@extends('admin.exam.course.workspace')
@section('course_body')
@include('admin.question-library._alerts')
<div class="card bg-transparent border rounded-3 mb-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="{{ $manageUrl }}/{{ $item->id }}" class="small"><i class="bi bi-arrow-left"></i> {{ $item->title }}</a>
            <h5 class="mb-0 mt-1">Add questions from the question library</h5>
        </div>
        <a href="{{ $libraryUrl }}/import" class="btn btn-sm btn-light mb-0" target="_blank"><i class="bi bi-upload me-1"></i>Import new questions (Word / Excel)</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="section" value="{{ $section->id }}">
            <div class="col-md-3">
                <select name="folder" class="form-select form-select-sm">
                    <option value="">All folders</option>
                    @foreach ($tree as $f)<option value="{{ $f['id'] }}" @selected(request('folder') == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $id => $name)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Any type</option>
                    @foreach (\App\Models\LibraryQuestion::TYPES as $k => $label)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search question text..."></div>
            <div class="col-md-12 d-flex align-items-center gap-3">
                <div class="form-check mb-0"><input type="hidden" name="hide_used" value="0"><input class="form-check-input" type="checkbox" name="hide_used" value="1" id="hu" @checked(request('hide_used', '1') === '1')><label class="form-check-label small" for="hu">Hide questions already in this exam</label></div>
                <button class="btn btn-sm btn-light mb-0">Filter</button>
                <a href="{{ $manageUrl }}/{{ $item->id }}/library?section={{ $section->id }}" class="btn btn-sm btn-light mb-0">Reset</a>
            </div>
        </form>
    </div>
</div>

<form action="{{ $manageUrl }}/{{ $item->id }}/questions" method="POST">
    @csrf
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom py-2 d-flex flex-wrap gap-2 align-items-center">
            <span class="small text-muted me-auto">{{ $questions->total() }} question(s) found</span>
            @if ($sections->count() > 1)
                <label class="small mb-0">Add to</label>
                <select name="section" class="form-select form-select-sm w-auto">
                    @foreach ($sections as $s)<option value="{{ $s->id }}" @selected($s->id === $section->id)>{{ $s->title ?: 'Section ' . ($loop->iteration) }}</option>@endforeach
                </select>
            @else
                <input type="hidden" name="section" value="{{ $section->id }}">
            @endif
            <button class="btn btn-sm btn-success mb-0" onclick="return pickCheck()"><i class="bi bi-plus-lg me-1"></i>Add selected</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Question</th><th>Type</th><th>Folder</th><th>Answer</th></tr></thead>
                <tbody>
                @forelse ($questions as $q)
                    @php $used = in_array($q->id, $inExam); @endphp
                    <tr class="{{ $used ? 'opacity-50' : '' }}">
                        <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $q->id }}" @disabled($used)></td>
                        <td style="max-width:520px"><div class="text-truncate">@if ($q->hasImages())<i class="bi bi-image text-info me-1"></i>@endif{{ $q->plainQuestion(160) }}</div>@if ($used)<span class="badge bg-secondary">already in this exam</span>@endif</td>
                        <td class="small">{{ $q->typeLabel() }}</td>
                        <td class="small">{{ $q->folder?->name }}</td>
                        <td>{{ $q->answerLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No questions match. Change the filters, or import questions into the library first.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>
<div class="mt-3">{{ $questions->links() }}</div>
<script>
var all = document.getElementById('checkAll');
all.addEventListener('change', function () { document.querySelectorAll('.row-check:not(:disabled)').forEach(function (c) { c.checked = all.checked; }); });
function pickCheck() { if (!document.querySelectorAll('.row-check:checked').length) { alert('Tick at least one question.'); return false; } return true; }
</script>
@endsection
