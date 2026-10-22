@extends('cms.section')
@section('content2')
@php $base = url('admin/kpsc/exam-tools/' . $exam->id); $title = trim((string) ($exam->examtitle ?: $exam->exam_title)) ?: 'Exam #' . $exam->id; @endphp
@include('admin.question-library._alerts')
<div class="card bg-transparent border rounded-3 mb-3">
    <div class="card-header bg-transparent border-bottom">
        <a href="{{ $back }}" class="small"><i class="bi bi-arrow-left"></i> {{ $title }}</a>
        <h5 class="mb-0 mt-1">Add questions from the question library</h5>
        <div class="small text-muted">Only one-right-answer questions with exactly four options fit the {{ $cfg['label'] }}. Pictures are kept inside the question and options.</div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><select name="folder" class="form-select form-select-sm"><option value="">All folders</option>
                @foreach ($tree as $f)<option value="{{ $f['id'] }}" @selected(request('folder') == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach</select></div>
            <div class="col-md-3"><select name="category" class="form-select form-select-sm"><option value="">All categories</option>
                @foreach ($categories as $cid => $name)<option value="{{ $cid }}" @selected(request('category') == $cid)>{{ $name }}</option>@endforeach</select></div>
            <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search question text..."></div>
            <div class="col-md-3 d-flex align-items-center gap-2">
                <div class="form-check mb-0"><input type="hidden" name="hide_used" value="0"><input class="form-check-input" type="checkbox" name="hide_used" value="1" id="hu" @checked(request('hide_used', '1') === '1')><label class="form-check-label small" for="hu">Hide already added</label></div>
                <button class="btn btn-sm btn-light mb-0">Filter</button>
            </div>
        </form>
    </div>
</div>

<form action="{{ $base }}/library" method="POST">
    @csrf
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom py-2 d-flex align-items-center gap-2">
            <span class="small text-muted me-auto">{{ $questions->total() }} question(s)</span>
            <button class="btn btn-sm btn-success mb-0" onclick="return pickCheck()"><i class="bi bi-plus-lg me-1"></i>Add selected</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Question</th><th>Folder</th><th>Answer</th></tr></thead>
                <tbody>
                @forelse ($questions as $q)
                    @php $used = in_array($q->id, $have); @endphp
                    <tr class="{{ $used ? 'opacity-50' : '' }}">
                        <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $q->id }}" @disabled($used)></td>
                        <td style="max-width:560px"><div class="text-truncate">@if ($q->hasImages())<i class="bi bi-image text-info me-1"></i>@endif{{ $q->plainQuestion(170) }}</div>@if ($used)<span class="badge bg-secondary">already in this exam</span>@endif</td>
                        <td class="small">{{ $q->folder?->name }}</td>
                        <td>{{ $q->answerLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No questions match.</td></tr>
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
