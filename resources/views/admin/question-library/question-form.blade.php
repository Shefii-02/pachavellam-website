@extends($layout)
@section('content2')
@php
    $editing = (bool) $question;
    $max = \App\Models\LibraryQuestion::MAX_OPTIONS;
    $texts = $editing ? $question->texts->keyBy('lang') : collect();
    $selectedCats = old('category_ids', $editing ? $question->categories->pluck('id')->all() : []);
    $imgUrl = fn ($p) => $p ? asset('storage/' . $p) : null;
    $optImgs = $editing ? ($question->option_images ?? []) : [];
    $openLangs = collect(old('texts') ? array_keys(array_filter(old('texts'), fn ($t) => implode('', $t) !== '')) : $texts->keys()->all());
    if ($openLangs->isEmpty()) { $openLangs = collect(['en']); }
    $type = old('question_type', $question->question_type ?? 'single');
    $count = (int) old('option_count', $editing ? max(2, collect(range(1, $max))->filter(fn ($n) => $texts->contains(fn ($t) => !empty($t->{"option_$n"})) || !empty($optImgs[$n]))->max() ?: 4) : 4);
    $count = max(2, min($max, $count));
    $correct = (int) old('correct_option', $question->correct_option ?? 1);
    $multiRight = array_map('intval', old('correct_options', $editing ? ($question->correct_options ?? []) : []));
    // text typed before the editor existed is plain: show it as paragraphs
    $html = fn ($v) => ($editing && !$question->is_rich) ? '<p>' . nl2br(e((string) $v)) . '</p>' : (string) $v;
@endphp
@include('admin.question-library._alerts')
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} Question</h3>
        @if ($attach)<div class="small text-muted">It will be saved in the question library and added to the exam <strong>{{ $attach->title }}</strong>.</div>@endif
    </div>
    <div class="card-body">
        <form action="{{ $editing ? $base . '/questions/' . $question->id : $base . '/questions' }}" method="POST" enctype="multipart/form-data" id="qForm">
            @csrf
            @if ($editing) @method('PUT') @endif
            @if ($attach)<input type="hidden" name="attach" value="{{ $attach->id }}"><input type="hidden" name="section" value="{{ $sectionId }}">@endif
            @if ($back)<input type="hidden" name="back" value="{{ $back }}">@endif

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Folder @if ($attach)<span class="text-muted">(optional)</span>@endif</label>
                    <select name="folder_id" class="form-select" @required(!$attach)>
                        <option value="">{{ $attach ? 'Course Exams › ' . $attach->title . ' (automatic)' : 'Choose folder...' }}</option>
                        @foreach ($tree as $f)
                            <option value="{{ $f['id'] }}" @selected(old('folder_id', $folderId) == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Question type</label>
                    <select name="question_type" id="qType" class="form-select">
                        @foreach (\App\Models\LibraryQuestion::TYPES as $k => $label)<option value="{{ $k }}" @selected($type === $k)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2" id="countBox">
                    <label class="form-label">Options</label>
                    <select name="option_count" id="optCount" class="form-select">
                        @for ($n = 2; $n <= $max; $n++)<option value="{{ $n }}" @selected($count === $n)>{{ $n }}</option>@endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-select">
                        <option value="">Not set</option>
                        @foreach (['easy', 'medium', 'hard'] as $d)<option value="{{ $d }}" @selected(old('difficulty', $question->difficulty ?? '') === $d)>{{ ucfirst($d) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Categories <span class="text-muted">(one or more)</span></label>
                    <div class="border rounded p-2" style="max-height:150px;overflow:auto">
                        @forelse ($categories as $id => $name)
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $id }}" id="c{{ $id }}" @checked(in_array($id, $selectedCats))><label class="form-check-label" for="c{{ $id }}">{{ $name }}</label></div>
                        @empty
                            <span class="text-muted small">No categories yet.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <h5>Text <span class="text-muted fs-6">(write in one or more languages)</span></h5>
            <div class="mb-2 d-flex flex-wrap gap-3">
                @foreach ($languages as $code => $l)
                    <div class="form-check"><input class="form-check-input lang-toggle" type="checkbox" id="lt{{ $code }}" data-lang="{{ $code }}" @checked($openLangs->contains($code))><label class="form-check-label" for="lt{{ $code }}">{{ $l['name'] }}</label></div>
                @endforeach
            </div>

            @foreach ($languages as $code => $l)
                @php $t = $texts->get($code); @endphp
                <div class="border rounded p-3 mb-3 lang-block" id="lb{{ $code }}" data-lang="{{ $code }}" style="{{ $openLangs->contains($code) ? '' : 'display:none' }}">
                    <div class="fw-semibold mb-2">{{ $l['name'] }}</div>
                    <label class="form-label small">Question</label>
                    <textarea class="form-control mb-2 rich" rows="3" name="texts[{{ $code }}][question]">{{ old("texts.$code.question", $html($t?->question)) }}</textarea>
                    <div class="row g-2 option-rows">
                        @foreach (range(1, $max) as $n)
                            <div class="col-md-6 opt-row" data-n="{{ $n }}">
                                <label class="form-label small">Option {{ chr(64 + $n) }}</label>
                                <input type="text" class="form-control" name="texts[{{ $code }}][option_{{ $n }}]" value="{{ old("texts.$code.option_$n", $t?->{"option_$n"}) }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="small text-muted tf-note" style="display:none">True / False questions use the options "True" and "False" automatically.</div>
                    <label class="form-label small mt-2">Explanation <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control rich" rows="2" name="texts[{{ $code }}][explanation]">{{ old("texts.$code.explanation", $html($t?->explanation)) }}</textarea>
                </div>
            @endforeach

            <div class="border rounded p-3 mb-3">
                <label class="form-label fw-semibold">Right answer</label>
                <div id="rightSingle">
                    @foreach (range(1, $max) as $n)
                        <div class="form-check form-check-inline right-opt" data-n="{{ $n }}"><input class="form-check-input" type="radio" name="correct_option" id="ro{{ $n }}" value="{{ $n }}" @checked($correct === $n)><label class="form-check-label" for="ro{{ $n }}"><span class="ro-label">{{ chr(64 + $n) }}</span></label></div>
                    @endforeach
                </div>
                <div id="rightMulti" style="display:none">
                    @foreach (range(1, $max) as $n)
                        <div class="form-check form-check-inline right-opt" data-n="{{ $n }}"><input class="form-check-input" type="checkbox" name="correct_options[]" id="rm{{ $n }}" value="{{ $n }}" @checked(in_array($n, $multiRight))><label class="form-check-label" for="rm{{ $n }}">{{ chr(64 + $n) }}</label></div>
                    @endforeach
                    <div class="form-text">Tick every right option (two or more).</div>
                </div>
            </div>

            <h5>Pictures <span class="text-muted fs-6">(optional)</span></h5>
            <div class="row g-3 mb-3">
                @php
                    $slots = ['question_image' => ['Question', $editing ? $question->question_image : null], 'explanation_image' => ['Explanation', $editing ? $question->explanation_image : null]];
                    foreach (range(1, $max) as $n) { $slots["option_$n"] = ['Option ' . chr(64 + $n), $optImgs[$n] ?? null]; }
                @endphp
                @foreach ($slots as $key => [$name, $current])
                    <div class="col-6 col-md-4 {{ str_starts_with($key, 'option_') ? 'opt-pic' : '' }}" @if (str_starts_with($key, 'option_')) data-n="{{ substr($key, -1) }}" @endif>
                        <label class="form-label small">{{ $name }} picture</label>
                        @if ($current)
                            <div class="mb-1"><img src="{{ $imgUrl($current) }}" alt="" style="max-height:70px" class="border rounded"></div>
                            <div class="form-check small"><input class="form-check-input" type="checkbox" name="remove[{{ $key }}]" value="1" id="rm_{{ $key }}"><label class="form-check-label" for="rm_{{ $key }}">Remove this picture</label></div>
                        @endif
                        <input type="file" class="form-control form-control-sm" accept="image/*"
                            name="{{ str_starts_with($key, 'option_') ? 'option_images[' . substr($key, -1) . ']' : $key }}">
                    </div>
                @endforeach
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary mb-0">{{ $editing ? 'Save' : 'Add question' }}</button>
                <a href="{{ $back ?: $base . (($folderId ?? null) ? '?folder=' . $folderId : '') }}" class="btn btn-light mb-0">Cancel</a>
            </div>
        </form>

        @if ($editing)
            <form action="{{ $base }}/questions/{{ $question->id }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this question? It is also removed from every exam that uses it.')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash me-1"></i>Delete question</button>
            </form>
        @endif
    </div>
</div>

<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
(function () {
    var editors = {};
    function makeEditors(block) {
        if (typeof CKEDITOR === 'undefined') return;
        block.querySelectorAll('textarea.rich').forEach(function (ta) {
            var id = ta.name;
            if (editors[id]) return;
            editors[id] = CKEDITOR.replace(ta, {
                height: ta.rows > 2 ? 140 : 100, removePlugins: 'elementspath', resize_enabled: true,
                toolbar: [['Bold', 'Italic', 'Underline', 'Subscript', 'Superscript'], ['NumberedList', 'BulletedList'], ['Table'], ['RemoveFormat', 'Source']],
                extraAllowedContent: 'sub sup'
            });
        });
    }

    var type = document.getElementById('qType'), count = document.getElementById('optCount');

    function applyType() {
        var t = type.value, n = t === 'truefalse' ? 2 : parseInt(count.value, 10);
        document.getElementById('countBox').style.display = t === 'truefalse' ? 'none' : '';
        document.getElementById('rightSingle').style.display = t === 'multiple' ? 'none' : '';
        document.getElementById('rightMulti').style.display = t === 'multiple' ? '' : 'none';
        // the radio / checkbox controls and the option inputs follow the number of options
        document.querySelectorAll('.right-opt').forEach(function (el) { el.style.display = parseInt(el.dataset.n, 10) <= n ? '' : 'none'; });
        document.querySelectorAll('.lang-block .opt-row').forEach(function (el) { el.style.display = (t !== 'truefalse' && parseInt(el.dataset.n, 10) <= n) ? '' : 'none'; });
        document.querySelectorAll('.opt-pic').forEach(function (el) { el.style.display = (t !== 'truefalse' && parseInt(el.dataset.n, 10) <= n) ? '' : 'none'; });
        document.querySelectorAll('.tf-note').forEach(function (el) { el.style.display = t === 'truefalse' ? '' : 'none'; });
        if (t === 'truefalse') {
            document.querySelectorAll('#rightSingle .ro-label').forEach(function (el, i) { if (i < 2) el.textContent = i === 0 ? 'True' : 'False'; });
        } else {
            document.querySelectorAll('#rightSingle .ro-label').forEach(function (el, i) { el.textContent = String.fromCharCode(65 + i); });
        }
        // an option hidden by a lower count must not be sent
        document.querySelectorAll('.lang-block .opt-row input').forEach(function (inp) {
            var row = inp.closest('.opt-row');
            inp.disabled = row.style.display === 'none' || inp.closest('.lang-block').style.display === 'none';
        });
    }
    type.addEventListener('change', applyType);
    count.addEventListener('change', applyType);

    document.querySelectorAll('.lang-toggle').forEach(function (c) {
        function apply() {
            var block = document.getElementById('lb' + c.dataset.lang);
            block.style.display = c.checked ? '' : 'none';
            block.querySelectorAll('textarea').forEach(function (i) { i.disabled = !c.checked; });
            if (c.checked) makeEditors(block);
            applyType();
        }
        c.addEventListener('change', apply);
        apply();
    });

    // editors write back to their textareas before the form is sent
    document.getElementById('qForm').addEventListener('submit', function () {
        if (typeof CKEDITOR !== 'undefined') { for (var k in CKEDITOR.instances) { CKEDITOR.instances[k].updateElement(); } }
    });
})();
</script>
@endsection
