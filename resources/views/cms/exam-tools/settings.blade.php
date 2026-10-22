@extends('cms.section')
@section('content2')
@php
    $base = url('admin/kpsc/exam-tools/' . $exam->id);
    $title = trim((string) ($exam->examtitle ?: $exam->exam_title)) ?: 'Exam #' . $exam->id;
    $chosenT = array_map('intval', old('exam_type_ids', $chosenTypes));
    $chosenS = array_map('intval', old('subject_ids', $chosenSubjects));
    $chosenC = array_map('intval', old('category_ids', $chosenCategories));
    $chosenCo = array_map('intval', old('course_ids', $linked->keys()->all()));
    $access = old('access_type', $exam->access_type ?: 'free');
@endphp
@include('admin.question-library._alerts')
@if (session('skipped'))<div class="alert alert-warning py-2 small"><ul class="mb-0 ps-3">@foreach (session('skipped') as $m)<li>{{ $m }}</li>@endforeach</ul></div>@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ $back }}" class="small"><i class="bi bi-arrow-left"></i> {{ $cfg['label'] }}</a>
        <h3 class="mb-0 mt-1">{{ $title }}</h3>
        <div class="small text-muted">{{ $cfg['label'] }} · {{ $exam->exam_date ? date('d M Y', strtotime($exam->exam_date)) : 'no date' }} · {{ $questionCount }} question(s) · {{ $exam->status ? 'published' : 'draft' }}</div>
    </div>
    @if ($cfg['questions'])
        <div class="d-flex gap-2">
            <a href="{{ $base }}/library" class="btn btn-sm btn-success mb-0"><i class="bi bi-collection me-1"></i>Add questions from the question library</a>
            <a href="{{ $back }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Write questions</a>
        </div>
    @else
        <div class="small text-muted" style="max-width:340px">OMR exams use a question paper file and an answer file, so questions are not typed here.</div>
    @endif
</div>

<form action="{{ $base }}" method="POST">
    @csrf
    <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body"><div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Free or premium</label>
            <select name="access_type" class="form-select">
                <option value="free" @selected($access === 'free')>Free</option>
                <option value="premium" @selected($access === 'premium')>Premium (paid students only)</option>
            </select>
        </div>

        <div class="col-md-8">
            <label class="form-label">Exam types <span class="text-muted">(this exam is for)</span></label>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($types as $t)
                    <div class="form-check"><input class="form-check-input type-check" type="checkbox" name="exam_type_ids[]" value="{{ $t->id }}" id="t{{ $t->id }}" @checked(in_array($t->id, $chosenT))><label class="form-check-label" for="t{{ $t->id }}">{{ $t->short_name ?: $t->name }}</label></div>
                @endforeach
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Subjects <span class="text-muted">(one or more; the first one is also saved in the old subject field)</span></label>
            <div class="border rounded p-2" style="max-height:170px;overflow:auto"><div class="row">
                @foreach ($subjects as $s)
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="subject_ids[]" value="{{ $s->id }}" id="s{{ $s->id }}" @checked(in_array($s->id, $chosenS))><label class="form-check-label" for="s{{ $s->id }}">{{ $s->subject_title }}</label></div></div>
                @endforeach
            </div></div>
        </div>

        <div class="col-12">
            <label class="form-label">Categories and sub categories <span class="text-muted">(ticking a category ticks its sub categories)</span></label>
            <div class="border rounded p-2" style="max-height:230px;overflow:auto">
                @foreach ($types as $t)
                    @if (!empty($categoryTrees[$t->id]))
                        <div class="type-cats" data-type="{{ $t->id }}">
                            <div class="small fw-semibold mt-1">{{ $t->short_name ?: $t->name }}</div>
                            @foreach ($categoryTrees[$t->id] as $c)
                                <div class="form-check" style="margin-left: {{ $c['depth'] * 1.4 }}rem"><input class="form-check-input cat" type="checkbox" name="category_ids[]" value="{{ $c['id'] }}" id="c{{ $c['id'] }}" data-parent="{{ $c['parent_id'] }}" @checked(in_array($c['id'], $chosenC))><label class="form-check-label" for="c{{ $c['id'] }}">{{ $c['name'] }}</label></div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
                @if (collect($categoryTrees)->flatten(1)->isEmpty())<span class="text-muted small">No categories yet.</span>@endif
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Add to courses <span class="text-muted">({{ $feature?->name ?? 'this feature' }} must be ticked on the course)</span></label>
            <div class="border rounded p-2" style="max-height:230px;overflow:auto">
                @foreach ($types as $t)
                    @php $cs = $courses->where('exam_id', $t->id); @endphp
                    @if ($cs->isNotEmpty())
                        <div class="type-courses" data-type="{{ $t->id }}">
                            <div class="small fw-semibold mt-1">{{ $t->short_name ?: $t->name }}</div>
                            @foreach ($cs as $co)
                                @php $lk = $linked->get($co->id); @endphp
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="course_ids[]" value="{{ $co->id }}" id="co{{ $co->id }}" @checked(in_array($co->id, $chosenCo))>
                                    <label class="form-check-label" for="co{{ $co->id }}">{{ $co->title }}</label>
                                    @if ($lk && $lk->course_exam_id)<a class="small ms-2" href="{{ url('admin/exam/' . $t->slug . '/courses/' . $co->id . '/exams/' . $feature->slug . '/' . $lk->course_exam_id) }}">open the course exam ({{ $lk->status }})</a>@endif</div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
                @if ($courses->isEmpty())<span class="text-muted small">No course has this feature yet.</span>@endif
            </div>
            <div class="form-text">A draft course exam is made in each ticked course with the same questions and free / premium type. Saving again adds questions added since. Unticking only unlinks; the course exam stays.</div>
        </div>
    </div></div></div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary mb-0">Save</button>
        <a href="{{ $back }}" class="btn btn-light mb-0">Back</a>
    </div>
</form>

<script>
(function () {
    var cats = Array.prototype.slice.call(document.querySelectorAll('.cat'));
    cats.forEach(function (c) {
        c.addEventListener('change', function () {
            (function walk(id, on) { cats.forEach(function (x) { if (x.dataset.parent === String(id)) { x.checked = on; walk(x.value, on); } }); })(c.value, c.checked);
        });
    });
    function ticked() { return Array.prototype.filter.call(document.querySelectorAll('.type-check'), function (c) { return c.checked; }).map(function (c) { return c.value; }); }
    function refresh() {
        var t = ticked();
        document.querySelectorAll('.type-cats, .type-courses').forEach(function (g) {
            var on = t.indexOf(g.dataset.type) !== -1;
            g.style.display = on ? '' : 'none';
            g.querySelectorAll('input').forEach(function (i) { i.disabled = !on; });
        });
    }
    document.querySelectorAll('.type-check').forEach(function (c) { c.addEventListener('change', refresh); });
    refresh();
})();
</script>
@endsection
