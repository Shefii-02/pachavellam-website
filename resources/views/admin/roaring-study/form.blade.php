@extends($rsLayout ?? 'admin.main-layout')
@section('main')
@php
    $editing = (bool) $study;
    $v = fn ($k, $d = null) => old($k, $editing ? ($study->{$k} ?? $d) : $d);
    $dt = fn ($k) => old($k, $editing && $study->{$k} ? $study->{$k}->format('Y-m-d\TH:i') : '');
    $chosen = array_map('intval', old('exam_ids', $chosenExams));
    $chosenC = array_map('intval', old('course_ids', $chosenCourses));
    $access = old('access_type', $editing ? $study->access_type : 'global');
@endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <h3 class="mb-3">{{ $editing ? 'Edit' : 'New' }} Roaring Study</h3>
    <form action="{{ $editing ? ($rsBase . '/' . $study->id) : ($rsBase) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body"><div class="row g-3">
            <div class="col-md-8"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required maxlength="200" value="{{ $v('title') }}"></div>
            <div class="col-md-4"><label class="form-label">Sort order</label><input type="number" min="0" name="position" class="form-control" value="{{ $v('position', 0) }}"></div>
            <div class="col-12"><label class="form-label">Description <span class="text-muted">(optional)</span></label><textarea name="description" rows="2" class="form-control" maxlength="2000">{{ $v('description') }}</textarea></div>

            <div class="col-md-4"><label class="form-label">Opens at</label><input type="datetime-local" name="publish_at" class="form-control" value="{{ $dt('publish_at') }}"><div class="form-text">Every pack has its own time. Empty = as soon as it is published.</div></div>
            <div class="col-md-4"><label class="form-label">Closes at <span class="text-muted">(optional)</span></label><input type="datetime-local" name="end_at" class="form-control" value="{{ $dt('end_at') }}"></div>
            <div class="col-md-4">
                <label class="form-label">Cover picture <span class="text-muted">(optional)</span></label>
                <input type="file" name="cover_image" accept="image/*" class="form-control">
                @if ($editing && $study->cover_image)<div class="mt-1"><img src="{{ $study->coverUrl() }}" alt="" style="height:44px" class="border rounded"> <label class="small ms-2"><input type="checkbox" name="remove_cover" value="1"> remove</label></div>@endif
            </div>

            <div class="col-12">
                <label class="form-label">Exam types <span class="text-muted">(shown in each ticked exam type)</span></label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($exams as $e)
                        <div class="form-check"><input class="form-check-input exam-check" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="ex{{ $e->id }}" @checked(in_array($e->id, $chosen))><label class="form-check-label" for="ex{{ $e->id }}">{{ $e->short_name ?: $e->name }}</label></div>
                    @endforeach
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Who can open it</label>
                <div class="d-flex gap-4 mb-2">
                    <div class="form-check"><input class="form-check-input" type="radio" name="access_type" id="acG" value="global" @checked($access === 'global')><label class="form-check-label" for="acG"><strong>Free for everyone</strong> (global)</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="access_type" id="acC" value="course" @checked($access === 'course')><label class="form-check-label" for="acC"><strong>Course based</strong> (only students of the chosen courses)</label></div>
                </div>
                <div id="coursePick" class="border rounded p-2" style="display:none;max-height:220px;overflow:auto">
                    @foreach ($exams as $e)
                        @php $cs = $courses->where('exam_id', $e->id); @endphp
                        @if ($cs->isNotEmpty())
                            <div class="course-group" data-exam="{{ $e->id }}">
                                <div class="small fw-semibold mt-1">{{ $e->short_name ?: $e->name }}</div>
                                @foreach ($cs as $c)
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="course_ids[]" value="{{ $c->id }}" id="co{{ $c->id }}" @checked(in_array($c->id, $chosenC))><label class="form-check-label" for="co{{ $c->id }}">{{ $c->title }}</label></div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                    <div class="small text-muted mt-1">Only courses of the ticked exam types are listed.</div>
                </div>
            </div>
        </div></div></div>
        <div class="d-flex gap-2 mb-4">
            <button class="btn btn-primary mb-0">{{ $editing ? 'Save' : 'Create and add content' }}</button>
            <a href="{{ $editing ? ($rsBase . '/' . $study->id) : ($rsBase) }}" class="btn btn-light mb-0">Cancel</a>
        </div>
    </form>
</div>
<script>
(function () {
    var box = document.getElementById('coursePick');
    function ticked() { return Array.prototype.filter.call(document.querySelectorAll('.exam-check'), function (c) { return c.checked; }).map(function (c) { return c.value; }); }
    function refresh() {
        var course = document.getElementById('acC').checked, exams = ticked();
        box.style.display = course ? '' : 'none';
        document.querySelectorAll('.course-group').forEach(function (g) {
            var show = exams.indexOf(g.dataset.exam) !== -1;
            g.style.display = show ? '' : 'none';
            g.querySelectorAll('input').forEach(function (i) { i.disabled = !show; });
        });
    }
    document.querySelectorAll('input[name=access_type], .exam-check').forEach(function (i) { i.addEventListener('change', refresh); });
    refresh();
})();
</script>
@endsection
@section('content2')@yield('main')@endsection
