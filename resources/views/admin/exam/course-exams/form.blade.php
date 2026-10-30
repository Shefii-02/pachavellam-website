@extends('admin.exam.course.workspace')
@section('course_body')
@php
    $editing = (bool) $item;
    $v = fn ($k, $d = null) => old($k, $editing ? ($item->{$k} ?? $d) : $d);
    $dt = fn ($k) => old($k, $editing && $item->{$k} ? $item->{$k}->format('Y-m-d\TH:i') : '');
    $rows = old('sections') ? array_values(old('sections')) : $sections->map(fn ($s) => [
        'id' => $s->id, 'title' => $s->title, 'duration_minutes' => $s->duration_minutes, 'right_mark' => $s->right_mark,
        'negative_mark' => $s->negative_mark, 'instruction' => strip_tags(str_replace(['<br>', '</p>'], "\n", (string) $s->instruction)),
        'pdf' => $s->instruction_pdf, 'questions' => $s->links_count ?? 0,
        'instruction_type' => $s->instruction_pdf ? 'pdf' : (\App\Support\SafeHtml::text($s->instruction) !== '' ? 'text' : 'none'),
    ])->all();
    $examPdf = $editing ? $item->instruction_pdf : null;
    $instrType = old('instruction_type', $examPdf ? 'pdf' : ($editing && \App\Support\SafeHtml::text($item->instruction) !== '' ? 'text' : 'none'));
    $instruction = old('instruction', $editing ? strip_tags(str_replace(['<br>', '</p>'], "\n", (string) $item->instruction)) : '');
@endphp
@include('admin.question-library._alerts')

<form action="{{ $editing ? $manageUrl . '/' . $item->id : $manageUrl }}" method="POST" enctype="multipart/form-data" id="examForm">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom"><h4 class="mb-0">{{ $editing ? 'Edit' : 'New' }} {{ \Illuminate\Support\Str::singular($feature->name) }}</h4></div>
        <div class="card-body">
            <div class="row g-3">
                @if (($seasons ?? collect())->count())
                    <div class="col-12">
                        <label class="form-label">Season</label>
                        <select name="season_id" class="form-select"><option value="">No season</option>
                            @foreach ($seasons as $se)<option value="{{ $se->id }}" {{ (string) old('season_id', $editing ? $item->season_id : ($preSeason ?? '')) === (string) $se->id ? 'selected' : '' }}>{{ $se->title }}@if ($se->starts_on) ({{ $se->starts_on->format('M Y') }})@endif</option>@endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-8">
                    <label class="form-label">Exam name</label>
                    <input type="text" name="title" class="form-control" required maxlength="200" value="{{ $v('title') }}">
                </div>
                <div class="col-md-4">
                    @if ($isCa)
                        <label class="form-label">Date of the entry</label>
                        <input type="date" name="entry_date" class="form-control" required value="{{ old('entry_date', $editing && $item->entry_date ? $item->entry_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    @else
                        <label class="form-label">Exam mode</label>
                        <select name="mode" class="form-select">
                            @foreach (\App\Models\CourseExam::MODES as $k => $label)<option value="{{ $k }}" @selected($v('mode', 'moderate') === $k)>{{ $label }}</option>@endforeach
                        </select>
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label">Description <span class="text-muted">(optional)</span></label>
                    <textarea name="description" rows="2" class="form-control" maxlength="2000">{{ $v('description') }}</textarea>
                </div>
                @if ($isCa)
                    <div class="col-12">
                        <label class="form-label">Content <span class="text-muted">(text of the entry - questions are optional and are added after saving)</span></label>
                        <textarea name="body" id="caBody" rows="10" class="form-control">{{ old('body', $editing ? $item->body : '') }}</textarea>
                    </div>
                @endif

                @unless ($isCa)
                <div class="col-md-3">
                    <label class="form-label">Number of sections</label>
                    <input type="number" id="sectionCount" min="1" max="12" class="form-control" value="{{ max(1, count($rows)) }}">
                    <div class="form-text">1 = no sections, just questions.</div>
                </div>
                @endunless
                <div class="col-md-3">
                    <label class="form-label">{{ $isCa ? 'Time for the questions (minutes, optional)' : 'Total duration (minutes)' }}</label>
                    <input type="number" name="duration_minutes" min="1" max="1440" class="form-control" value="{{ $v('duration_minutes') }}">
                    <div class="form-text" id="durHelp">With sections you may leave this empty and set each section's time.</div>
                </div>
                @unless ($isCa)
                <div class="col-md-3">
                    <label class="form-label">Avg. time per question (sec)</label>
                    <input type="number" name="avg_seconds_per_question" min="1" class="form-control" value="{{ $v('avg_seconds_per_question') }}" placeholder="auto">
                    <div class="form-text">Empty = total time ÷ questions.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total mark</label>
                    <input type="number" step="0.01" name="total_mark" min="0" class="form-control" value="{{ $v('total_mark') }}" placeholder="auto">
                    <div class="form-text">Empty = worked out from the questions.</div>
                </div>
                @endunless

                <div class="col-md-3">
                    <label class="form-label">Right answer mark</label>
                    <input type="number" step="0.01" name="right_mark" min="0" required class="form-control" value="{{ $v('right_mark', 1) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Negative mark <span class="text-muted">(per wrong answer)</span></label>
                    <input type="number" step="0.01" name="negative_mark" min="0" class="form-control" value="{{ $v('negative_mark', 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Free / paid</label>
                    <select name="access" class="form-select">
                        @php $access = old('access', $editing && $item->is_paid ? 'paid' : 'free'); @endphp
                        <option value="free" @selected($access === 'free')>Free</option>
                        <option value="paid" @selected($access === 'paid')>Paid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort number</label>
                    <input type="number" min="0" class="form-control" name="sort_no" value="{{ old('sort_no', $editing ? $item->sort_no : '') }}" placeholder="optional">
                    <div class="form-text">Smallest shows first on the exams page.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Free access</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="free_access" id="free_access" value="1" @checked(old('free_access', $editing ? $item->free_access : false))>
                        <label class="form-check-label small" for="free_access">Open for everyone, even when its season is paid</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Attempts per user</label>
                    <input type="number" name="max_attempts" min="0" class="form-control" value="{{ $v('max_attempts', 1) }}">
                    <div class="form-text">0 = unlimited.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start date</label>
                    <input type="datetime-local" name="start_at" class="form-control" value="{{ $dt('start_at') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End date</label>
                    <input type="datetime-local" name="end_at" class="form-control" value="{{ $dt('end_at') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unlock time</label>
                    <input type="datetime-local" name="unlock_at" class="form-control" value="{{ $dt('unlock_at') }}">
                    <div class="form-text">Students can open it from this time.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="position" min="0" class="form-control" value="{{ $v('position', 0) }}">
                </div>

                @unless ($isCa)
                <div class="col-md-3">
                    <label class="form-label">Instructions</label>
                    <select name="instruction_type" id="instrType" class="form-select">
                        <option value="none" @selected($instrType === 'none')>None</option>
                        <option value="text" @selected($instrType === 'text')>Write as text</option>
                        <option value="pdf" @selected($instrType === 'pdf')>Upload a PDF</option>
                    </select>
                    <div class="form-text">Choose one: text or PDF.</div>
                </div>
                <div class="col-md-9" id="instrText" style="display:none">
                    <label class="form-label">Instructions text</label>
                    <textarea name="instruction" rows="3" class="form-control">{{ $instruction }}</textarea>
                </div>
                <div class="col-md-9" id="instrPdf" style="display:none">
                    <label class="form-label">Instructions PDF</label>
                    <input type="file" name="instruction_pdf" accept="application/pdf" class="form-control">
                    @if ($examPdf)
                        <div class="form-text"><a href="{{ asset('storage/' . $examPdf) }}" target="_blank">Current PDF</a> (upload another to replace it)</div>
                    @endif
                </div>
                @endunless
            </div>
        </div>
    </div>

    {{-- sections --}}
    @unless ($isCa)
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom">
            <strong>Sections</strong>
            <span class="small text-muted ms-2" id="sectionHelp"></span>
        </div>
        <div class="card-body" id="sectionList"></div>
    </div>
    @endunless

    @unless ($editing)
        <div class="row mb-3">@include('admin.exam.partials.also-courses', ['otherCourses' => $otherCourses, 'what' => $isCa ? 'entry' : 'exam', 'note' => 'A separate copy (draft) is made in each ticked course, with the same settings and questions. Edit each copy on its own.'])</div>
    @endunless

    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-primary mb-0">{{ $editing ? 'Save exam' : 'Create exam and sections' }}</button>
        <a href="{{ $editing ? $manageUrl . '/' . $item->id : $manageUrl }}" class="btn btn-light mb-0">Cancel</a>
    </div>
</form>

@unless ($isCa)
<template id="sectionTpl">
    <div class="border rounded p-3 mb-3 section-box">
        <input type="hidden" data-f="id">
        <div class="d-flex justify-content-between mb-2"><strong class="section-no"></strong><span class="small text-muted section-info"></span></div>
        <div class="row g-2">
            <div class="col-md-6 title-col"><label class="form-label small">Section title</label><input type="text" data-f="title" class="form-control form-control-sm" maxlength="150"></div>
            <div class="col-md-2"><label class="form-label small">Time limit (min)</label><input type="number" data-f="duration_minutes" min="1" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Right mark</label><input type="number" step="0.01" min="0" data-f="right_mark" class="form-control form-control-sm" placeholder="exam"></div>
            <div class="col-md-2"><label class="form-label small">Negative mark</label><input type="number" step="0.01" min="0" data-f="negative_mark" class="form-control form-control-sm" placeholder="exam"></div>
            <div class="col-md-3"><label class="form-label small">Section instructions</label>
                <select data-f="instruction_type" class="form-select form-select-sm instr-type"><option value="none">None</option><option value="text">Write as text</option><option value="pdf">Upload a PDF</option></select></div>
            <div class="col-md-9 instr-text" style="display:none"><label class="form-label small">Instructions text</label><textarea data-f="instruction" rows="2" class="form-control form-control-sm"></textarea></div>
            <div class="col-md-9 instr-pdf" style="display:none"><label class="form-label small">Instructions PDF</label><input type="file" data-pdf accept="application/pdf" class="form-control form-control-sm"><div class="form-text pdf-current"></div></div>
        </div>
    </div>
</template>

<script>
(function () {
    var rows = @json($rows);
    var list = document.getElementById('sectionList');
    var count = document.getElementById('sectionCount');
    var tpl = document.getElementById('sectionTpl');
    var base = @json(asset('storage') . '/');

    function build(i, row) {
        var node = tpl.content.firstElementChild.cloneNode(true);
        node.querySelectorAll('[data-f]').forEach(function (el) {
            var f = el.dataset.f;
            el.name = 'sections[' + i + '][' + f + ']';
            if (row && row[f] !== null && row[f] !== undefined) el.value = row[f];
        });
        var pdf = node.querySelector('[data-pdf]');
        pdf.name = 'section_pdf[' + i + ']';
        if (row && row.pdf) {
            node.querySelector('.pdf-current').innerHTML = '<a target="_blank" href="' + base + row.pdf + '">Current PDF</a> (upload another to replace it)';
        }
        var type = node.querySelector('.instr-type');
        function showType() {
            node.querySelector('.instr-text').style.display = type.value === 'text' ? '' : 'none';
            node.querySelector('.instr-pdf').style.display = type.value === 'pdf' ? '' : 'none';
        }
        type.addEventListener('change', showType);
        showType();
        if (row && row.questions) node.querySelector('.section-info').textContent = row.questions + ' question(s)';
        return node;
    }

    function render() {
        var n = Math.max(1, Math.min(12, parseInt(count.value, 10) || 1));
        count.value = n;
        // keep what was typed
        var current = [];
        list.querySelectorAll('.section-box').forEach(function (box) {
            var r = {};
            box.querySelectorAll('[data-f]').forEach(function (el) { r[el.dataset.f] = el.value; });
            current.push(r);
        });
        var source = current.length ? current.map(function (c, i) { return Object.assign({}, rows[i] || {}, c); }) : rows;
        list.innerHTML = '';
        for (var i = 0; i < n; i++) {
            var node = build(i, source[i] || null);
            node.querySelector('.section-no').textContent = n > 1 ? 'Section ' + (i + 1) : 'Exam time and marks';
            node.querySelector('.title-col').style.display = n > 1 ? '' : 'none';
            list.appendChild(node);
        }
        document.getElementById('sectionHelp').textContent = n > 1
            ? 'Each section has its own title and time limit; students attend them one by one. Questions are added to each section after you create the exam.'
            : 'One section: the questions are listed together. Time and marks come from the exam above (optional overrides here).';
    }
    count.addEventListener('change', render);
    var et = document.getElementById('instrType');
    function showExamInstr() {
        document.getElementById('instrText').style.display = et.value === 'text' ? '' : 'none';
        document.getElementById('instrPdf').style.display = et.value === 'pdf' ? '' : 'none';
    }
    et.addEventListener('change', showExamInstr); showExamInstr();
    rows = rows.length ? rows : [{}];
    count.value = rows.length;
    render();
})();
</script>
@endunless
@if ($isCa)
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('caBody', { height: 320, removePlugins: 'elementspath', toolbar: [['Bold', 'Italic', 'Underline', 'Subscript', 'Superscript'], ['NumberedList', 'BulletedList', 'Blockquote'], ['Table'], ['Format'], ['RemoveFormat', 'Source']] });
}
</script>
@endif
@endsection
