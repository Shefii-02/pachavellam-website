@extends($layout)
@section('content2')
@php
    $label = $exam->short_name ?: $exam->name;
    $oldCats = array_map('intval', old('category_ids', []));
    $sample = fn ($ext, $key) => $base . '/samples/' . $ext . '/' . $key;
    $typeSamples = ['english' => 'english', 'native' => 'native', 'image' => 'image', 'multi' => 'multi'];
@endphp
@include('admin.question-library._alerts')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Import {{ $label }} Questions</h3>
        <div class="small text-muted">Folder → categories → Word or CSV / Excel → kind of file → upload.</div>
    </div>
    <a href="{{ $base }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back to library</a>
</div>

<div class="row g-3">
<div class="col-lg-8">
<form action="{{ $base }}/import" method="POST" enctype="multipart/form-data" id="importForm">
    @csrf
    <input type="hidden" name="folder_id" id="folderId" value="{{ old('folder_id', $selectedFolder) }}">

    {{-- 1 folder --}}
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>1. Folder</strong></div>
        <div class="card-body">
            @if (!$tree)
                <div class="alert alert-warning mb-0">There is no folder yet. <a href="{{ $base }}">Create a folder</a> in the library first.</div>
            @else
                <div id="folderLevels" class="row g-2"></div>
                <div class="small text-muted mt-2">Choose a folder; if it has sub folders you can go deeper. If you choose no sub folder the questions go into the folder you chose.</div>
                <div class="mt-2">
                    <label class="form-label small mb-1">Or create a new sub folder inside the chosen folder <span class="text-muted">(optional)</span></label>
                    <input type="text" name="new_folder_name" class="form-control form-control-sm" maxlength="150" value="{{ old('new_folder_name') }}" placeholder="New sub folder name">
                </div>
                <div class="mt-2 small">Questions will be saved in: <strong id="folderPath" class="text-primary">—</strong></div>
            @endif
        </div>
    </div>

    {{-- 2 categories --}}
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2 d-flex justify-content-between">
            <strong>2. Categories and sub categories</strong><span class="small text-muted">choose one or more (optional)</span>
        </div>
        <div class="card-body" style="max-height:260px;overflow:auto">
            @forelse ($categoryTree as $c)
                <div class="form-check" style="margin-left: {{ $c['depth'] * 1.4 }}rem">
                    <input class="form-check-input cat" type="checkbox" name="category_ids[]" value="{{ $c['id'] }}" id="cat{{ $c['id'] }}"
                        data-parent="{{ $c['parent_id'] }}" @checked(in_array($c['id'], $oldCats))>
                    <label class="form-check-label" for="cat{{ $c['id'] }}">{{ $c['name'] }}</label>
                </div>
            @empty
                <span class="text-muted small">No categories yet. <a href="{{ $exam->examUrl('category') }}">Add categories</a> first, or import without.</span>
            @endforelse
            <div class="small text-muted mt-2">Ticking a category also ticks the sub categories under it.</div>
        </div>
    </div>

    {{-- 3 format, 4 kind --}}
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>3. File format</strong></div>
        <div class="card-body">
            <div class="d-flex gap-4">
                <div class="form-check"><input class="form-check-input" type="radio" name="format" id="fmWord" value="word" @checked(old('format', 'word') === 'word')><label class="form-check-label" for="fmWord"><i class="bi bi-file-earmark-word text-primary me-1"></i>Word (.docx)</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="format" id="fmSheet" value="sheet" @checked(old('format') === 'sheet')><label class="form-check-label" for="fmSheet"><i class="bi bi-file-earmark-spreadsheet text-success me-1"></i>CSV / Excel</label></div>
            </div>
        </div>
    </div>

    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>4. Kind of file</strong></div>
        <div class="card-body">
            <div class="row g-2">
                @foreach ($types as $key => $t)
                    <div class="col-md-6">
                        <label class="border rounded p-2 d-block h-100" style="cursor:pointer">
                            <input class="form-check-input me-1" type="radio" name="file_type" value="{{ $key }}" data-single="{{ $t['single'] ? 1 : 0 }}" data-fixed="{{ $t['language'] ?? '' }}" @checked(old('file_type', 'english') === $key)>
                            <strong>{{ $t['label'] }}</strong>
                            <div class="small text-muted ms-4">{{ $t['hint'] }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="mt-3" id="languageRow" style="display:none">
                <label class="form-label small mb-1">Language of the questions in this file</label>
                <select name="language" class="form-select form-select-sm w-auto">
                    @foreach ($languages as $code => $l)<option value="{{ $code }}" @selected(old('language', 'ml') === $code)>{{ $l['name'] }}</option>@endforeach
                </select>
            </div>
            <div class="small text-muted mt-3" id="kindHelp"></div>
        </div>
    </div>

    {{-- 5 upload --}}
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>5. Upload</strong></div>
        <div class="card-body">
            <label class="form-label">Questions file</label>
            <input type="file" name="file" class="form-control mb-1" required id="fileInput" accept=".docx">
            <div class="small text-muted mb-3">Up to {{ (int) ($limits['file_kb'] / 1024) }} MB. This server accepts uploads up to {{ $limits['php_upload'] }} (form limit {{ $limits['php_post'] }}).</div>

            <div id="zipRow" style="display:none" class="mb-3">
                <label class="form-label">Pictures zip <span class="text-muted">(only if the file names pictures, e.g. in "Question Image" columns)</span></label>
                <input type="file" name="images_zip" class="form-control" accept=".zip">
                <div class="small text-muted">Up to {{ (int) ($limits['zip_kb'] / 1024) }} MB. A picture named in the file is looked up by its name inside the zip (jpg, png, gif, webp, up to 3 MB each).</div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="skip_duplicates" value="1" id="dup" @checked(old('skip_duplicates', '1'))>
                <label class="form-check-label" for="dup">Skip a question that is already in this folder (same question text)</label>
            </div>
            <button class="btn btn-primary mb-0" id="submitBtn" @disabled(!$tree)><i class="bi bi-upload me-1"></i>Import questions and answers</button>
        </div>
    </div>
</form>
</div>

{{-- samples --}}
<div class="col-lg-4">
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom py-2"><strong>Sample files</strong></div>
        <div class="card-body small">
            <p class="text-muted">Download, replace the sample rows with your questions and upload. The first (header) row must stay.</p>

            <div class="fw-semibold mb-1"><i class="bi bi-file-earmark-word text-primary me-1"></i>Word</div>
            <ul class="ps-3">
                <li><a href="{{ $sample('docx', 'english') }}">English only</a> (table)</li>
                <li><a href="{{ $sample('docx', 'text') }}">English only</a> (numbered paragraphs)</li>
                <li><a href="{{ $sample('docx', 'native') }}">Native language</a> (Malayalam)</li>
                <li><a href="{{ $sample('docx', 'image') }}">Image with content</a> (pictures in cells)</li>
                <li><a href="{{ $sample('docx', 'multi') }}">One question, many languages</a></li>
            </ul>

            <div class="fw-semibold mb-1"><i class="bi bi-filetype-csv text-success me-1"></i>CSV</div>
            <ul class="ps-3">
                <li><a href="{{ $sample('csv', 'single') }}">Single language</a> (English / native)</li>
                <li><a href="{{ $sample('csv', 'multi') }}">One question, many languages</a></li>
                <li><a href="{{ $sample('csv', 'image') }}">Picture questions</a> + <a href="{{ $sample('zip', 'images') }}">sample pictures zip</a></li>
            </ul>

            <div class="fw-semibold mb-1"><i class="bi bi-file-earmark-spreadsheet text-success me-1"></i>Excel</div>
            <ul class="ps-3 mb-0">
                <li><a href="{{ $sample('xlsx', 'single') }}">Single language</a></li>
                <li><a href="{{ $sample('xlsx', 'multi') }}">One question, many languages</a></li>
                <li><a href="{{ $sample('xlsx', 'image') }}">Picture questions</a> (with the pictures zip)</li>
            </ul>
        </div>
    </div>
    <div class="card bg-transparent border rounded-3">
        <div class="card-body small">
            <div class="fw-semibold mb-1">Good to know</div>
            <ul class="ps-3 mb-0">
                <li><b>Answer</b>: A, B, C, D (or 1-4).</li>
                <li><b>Pictures in Word</b> are taken out of the file and saved. Put the picture in the cell of the question, option or explanation. Paste pictures as normal images (not EMF / WMF drawings).</li>
                <li>Maths: x² and Word equations become plain text; for figures or complex formulas use a picture.</li>
                <li>Malayalam and other languages must be Unicode text (not old ML-TT / ISM fonts).</li>
                <li>A question with a problem is skipped and listed afterwards; the rest are imported. You can undo a whole import.</li>
            </ul>
        </div>
    </div>
</div>
</div>

<script>
(function () {
    var tree = @json($tree);   // [{id, name, parent_id, depth, path}]
    var levels = document.getElementById('folderLevels');
    var hidden = document.getElementById('folderId');
    var pathEl = document.getElementById('folderPath');

    function children(pid) { return tree.filter(function (f) { return f.parent_id === pid; }); }
    function byId(id) { return tree.filter(function (f) { return f.id === id; })[0]; }

    // chain of folder ids from the top down to id
    function chain(id) {
        var out = [], f = byId(id);
        while (f) { out.unshift(f.id); f = f.parent_id ? byId(f.parent_id) : null; }
        return out;
    }

    function render(selected) {
        if (!levels) return;
        levels.innerHTML = '';
        var pid = null;
        for (var level = 0; ; level++) {
            var opts = children(pid);
            if (!opts.length) break;
            var col = document.createElement('div');
            col.className = 'col-md-6';
            var sel = document.createElement('select');
            sel.className = 'form-select form-select-sm';
            sel.dataset.level = level;
            var first = document.createElement('option');
            first.value = '';
            first.textContent = level === 0 ? 'Choose folder...' : 'Sub folder (optional - leave empty to use the folder above)';
            sel.appendChild(first);
            opts.forEach(function (o) {
                var op = document.createElement('option');
                op.value = o.id; op.textContent = o.name;
                sel.appendChild(op);
            });
            var chosen = selected[level];
            if (chosen) sel.value = chosen;
            sel.addEventListener('change', onPick);
            col.appendChild(sel);
            levels.appendChild(col);
            if (!chosen) break;
            pid = chosen;
        }
        var deepest = selected.length ? selected[selected.length - 1] : null;
        hidden.value = deepest || '';
        pathEl.textContent = deepest ? byId(deepest).path : '—';
    }

    function onPick() {
        var picked = [];
        levels.querySelectorAll('select').forEach(function (s) {
            if (parseInt(s.dataset.level, 10) <= parseInt(this.dataset.level, 10)) picked.push(s.value ? parseInt(s.value, 10) : null);
        }, this);
        while (picked.length && !picked[picked.length - 1]) picked.pop();
        render(picked.filter(Boolean));
    }

    if (levels) render(hidden.value ? chain(parseInt(hidden.value, 10)) : []);

    // categories: ticking a parent ticks everything below it
    var cats = Array.prototype.slice.call(document.querySelectorAll('.cat'));
    cats.forEach(function (c) {
        c.addEventListener('change', function () {
            (function walk(id, on) {
                cats.forEach(function (x) { if (x.dataset.parent === String(id)) { x.checked = on; walk(x.value, on); } });
            })(c.value, c.checked);
        });
    });

    // format / kind
    var fileInput = document.getElementById('fileInput');
    var help = document.getElementById('kindHelp');
    var helpText = {
        english: { word: 'A table (No, Question, Option A-D, Answer, Explanation) or numbered questions: "1. question", "A) option" ..., "Answer: B".', sheet: 'Columns: No, Question, Option A, Option B, Option C, Option D, Answer, Explanation.' },
        native: { word: 'Same layout as English only, written in your language (Unicode).', sheet: 'Same columns as English only, written in your language (Unicode, save CSV as UTF-8).' },
        image: { word: 'A table; put the picture inside the cell of the question / option / explanation. Pictures are saved with the question.', sheet: 'Add columns "Question Image", "Option A Image" ... holding the picture file name, and upload the pictures zip.' },
        multi: { word: 'A table with a Language column: one row per language; rows with the same No are the same question.', sheet: 'A Language column: one row per language; rows with the same No are the same question.' }
    };
    function refresh() {
        var word = document.getElementById('fmWord').checked;
        fileInput.accept = word ? '.docx' : '.csv,.xlsx,.xls,.txt';
        document.getElementById('zipRow').style.display = word ? 'none' : '';
        var kind = document.querySelector('input[name=file_type]:checked');
        var needsLang = kind && kind.dataset.single === '1' && !kind.dataset.fixed;
        document.getElementById('languageRow').style.display = needsLang ? '' : 'none';
        help.textContent = kind ? (helpText[kind.value] || {})[word ? 'word' : 'sheet'] || '' : '';
    }
    document.querySelectorAll('input[name=format], input[name=file_type]').forEach(function (r) { r.addEventListener('change', refresh); });
    refresh();

    document.getElementById('importForm').addEventListener('submit', function (e) {
        if (!hidden.value) { e.preventDefault(); alert('Choose the folder first.'); return; }
        var b = document.getElementById('submitBtn');
        b.disabled = true; b.innerHTML = 'Importing... please wait';
    });
})();
</script>
@endsection
