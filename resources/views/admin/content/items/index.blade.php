@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0"><i class="bi {{ $m['icon'] }} me-1"></i>{{ $m['label'] }}</h3>
            <div class="small text-muted">{{ !empty($m['global']) ? 'Shown on the website for everyone.' : 'Common for all exam types. Choose the exam types when you create one.' }}</div>
        </div>
        @if ($m['section'] === 'achievements')<a href="{{ url('admin/content/achievements/import') }}" class="btn btn-sm btn-outline-danger mb-0 me-1"><i class="bi bi-instagram me-1"></i>Import from Instagram</a>@endif
        <a href="{{ url('admin/content/' . $m['section'] . '/create') }}{{ request('exam') ? '?exam=' . request('exam') . (request('folder') ? '&folder=' . request('folder') : '') : '' }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add {{ $m['one'] }}</a>
    </div>

    <ul class="nav nav-pills mb-3 flex-wrap gap-1">
        @foreach (\App\Models\ContentItem::KINDS as $seg => $k)
            <li class="nav-item"><a class="nav-link py-1 {{ $seg === $m['section'] ? 'active' : '' }}" href="{{ url('admin/content/' . $seg) }}"><i class="bi {{ $k['icon'] }} me-1"></i>{{ $k['label'] }}</a></li>
        @endforeach
    </ul>


    @if (!empty($m['folders']) && !$folderMode)
        <div class="alert alert-info py-2 small">These are kept in folders for each exam type. Choose an exam type below and press Filter to open its folders.</div>
    @endif

    @if ($folderMode)
        @php $trail = $current ? $current->trail() : []; $base = url('admin/content/' . $m['section']); $q = fn ($fid = null) => $base . '?exam=' . $exam->slug . ($fid ? '&folder=' . $fid : ''); @endphp
        <div class="d-flex align-items-center flex-wrap gap-1 mb-2 small">
            <i class="bi bi-folder2-open"></i>
            <a href="{{ $q() }}" class="fw-semibold">{{ $exam->short_name ?: $exam->name }}</a>
            @foreach ($trail as $t)<span class="text-muted">/</span> @if ($loop->last)<b>{{ $t->title }}</b>@else<a href="{{ $q($t->id) }}">{{ $t->title }}</a>@endif @endforeach
        </div>

        <div class="row g-2 mb-3">
            @foreach ($subfolders as $f)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="border rounded-3 p-2 h-100 d-flex align-items-start gap-2">
                        <a href="{{ $q($f->id) }}" class="text-decoration-none flex-grow-1 text-reset"><i class="bi bi-folder-fill text-warning fs-4"></i> <b>{{ $f->title }}</b>
                            <div class="small text-muted">{{ $f->items_count }} item(s) · {{ $f->folders_count }} folder(s)</div></a>
                        <div class="dropdown"><button class="btn btn-sm btn-light mb-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:230px">
                                <li><form action="{{ $base . '/folders/' . $f->id }}" method="POST">@csrf @method('PUT')
                                    <input class="form-control form-control-sm mb-1" name="title" value="{{ $f->title }}" required maxlength="150">
                                    <select name="parent_id" class="form-select form-select-sm mb-1"><option value="">Top level</option>@foreach ($allFolders->where('id', '!=', $f->id) as $af)<option value="{{ $af->id }}" {{ $f->parent_id == $af->id ? 'selected' : '' }}>{{ $af->path() }}</option>@endforeach</select>
                                    <button class="btn btn-sm btn-success w-100 mb-0">Rename / move</button></form></li>
                                <li><form action="{{ $base . '/folders/' . $f->id }}" method="POST" onsubmit="return confirm('Delete this folder? Its content moves up one level.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger w-100 mt-1 mb-0">Delete folder</button></form></li>
                            </ul></div>
                    </div>
                </div>
            @endforeach
            <div class="col-6 col-md-4 col-lg-3">
                <form action="{{ $base . '/folders' }}" method="POST" class="border border-dashed rounded-3 p-2 h-100">@csrf
                    <input type="hidden" name="exam" value="{{ $exam->slug }}"><input type="hidden" name="parent_id" value="{{ $current?->id }}">
                    <div class="small text-muted mb-1"><i class="bi bi-folder-plus"></i> New {{ $current ? 'sub ' : '' }}folder</div>
                    <div class="input-group input-group-sm"><input class="form-control" name="title" placeholder="Folder name" required maxlength="150"><button class="btn btn-primary mb-0">Add</button></div>
                    @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                </form>
            </div>
        </div>
        <div class="border rounded-3 p-3 mb-3" id="upBox" data-url="{{ $base . '/upload' }}" data-exam="{{ $exam->slug }}" data-folder="{{ $current?->id }}" data-csrf="{{ csrf_token() }}">
            <div id="upDrop" class="text-center rounded-3 p-4" style="border:2px dashed #9aa5b5;cursor:pointer">
                <i class="bi bi-cloud-arrow-up fs-1 d-block"></i>
                <b>Drop files here or click to browse</b>
                <div class="small text-muted">Uploading into <b>{{ $current ? $current->path() : $exam->short_name . ' (top level)' }}</b> · PDF, Word, Excel, JPG, PNG · up to 10 MB each, 30 at a time</div>
                <input type="file" id="upFiles" multiple hidden accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            </div>
            <div id="upList" class="row g-2 mt-1"></div>
            <div id="upBar" class="progress mt-2 d-none" style="height:8px"><div class="progress-bar" style="width:0"></div></div>
            <div id="upErr" class="text-danger small mt-1"></div>
            <div class="mt-2 d-none" id="upActions"><button type="button" id="upGo" class="btn btn-sm btn-primary mb-0"><i class="bi bi-upload me-1"></i>Upload <span id="upCount"></span></button> <button type="button" id="upClear" class="btn btn-sm btn-light mb-0">Clear</button>
                <a href="{{ url('admin/content/' . $m['section'] . '/create') }}?exam={{ $exam->slug }}{{ $current ? '&folder=' . $current->id : '' }}" class="btn btn-sm btn-outline-secondary mb-0 float-end">Or add one with details / library / link</a></div>
        </div>
        <script>
        (function () {
            var box = document.getElementById('upBox'), drop = document.getElementById('upDrop'), inp = document.getElementById('upFiles'), list = document.getElementById('upList'),
                acts = document.getElementById('upActions'), err = document.getElementById('upErr'), bar = document.getElementById('upBar'), files = [];
            var ok = /\.(pdf|docx?|xlsx?|jpe?g|png)$/i;
            function size(n) { return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.ceil(n / 1024) + ' KB'; }
            function icon(f) { return /\.pdf$/i.test(f.name) ? 'bi-file-earmark-pdf text-danger' : /\.docx?$/i.test(f.name) ? 'bi-file-earmark-word text-primary' : /\.xlsx?$/i.test(f.name) ? 'bi-file-earmark-excel text-success' : 'bi-file-earmark-image'; }
            function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
            function draw() {
                list.innerHTML = files.map(function (f, i) {
                    var th = /^image\//.test(f.type) ? '<img src="' + URL.createObjectURL(f) + '" style="width:100%;height:80px;object-fit:cover" class="rounded">' : '<i class="bi ' + icon(f) + '" style="font-size:2.6rem"></i>';
                    return '<div class="col-6 col-md-3 col-lg-2"><div class="border rounded-3 p-2 text-center position-relative h-100"><button type="button" data-rm="' + i + '" class="btn-close position-absolute top-0 end-0 m-1" style="font-size:.6rem"></button>'
                        + '<div class="d-flex align-items-center justify-content-center" style="height:80px">' + th + '</div><div class="small fw-semibold text-truncate mt-1" title="' + esc(f.name) + '">' + esc(f.name) + '</div><div class="small text-muted">' + size(f.size) + '</div></div></div>';
                }).join('');
                acts.classList.toggle('d-none', !files.length); document.getElementById('upCount').textContent = files.length ? '(' + files.length + ' file' + (files.length > 1 ? 's' : '') + ')' : '';
                list.querySelectorAll('[data-rm]').forEach(function (b) { b.onclick = function () { files.splice(+b.dataset.rm, 1); draw(); }; });
            }
            function add(fl) {
                err.textContent = '';
                Array.prototype.forEach.call(fl, function (f) {
                    if (!ok.test(f.name)) { err.textContent = f.name + ': file type not allowed.'; return; }
                    if (f.size > 10485760) { err.textContent = f.name + ' is over 10 MB.'; return; }
                    if (files.length < 30) files.push(f);
                });
                draw();
            }
            drop.onclick = function () { inp.click(); };
            inp.onchange = function () { add(inp.files); inp.value = ''; };
            ['dragover', 'dragenter'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.style.background = 'rgba(13,110,253,.08)'; }); });
            ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.style.background = ''; }); });
            drop.addEventListener('drop', function (e) { add(e.dataTransfer.files); });
            document.getElementById('upClear').onclick = function () { files = []; draw(); };
            document.getElementById('upGo').onclick = function () {
                var fd = new FormData(); fd.append('_token', box.dataset.csrf); fd.append('exam', box.dataset.exam); if (box.dataset.folder) fd.append('folder_id', box.dataset.folder);
                files.forEach(function (f) { fd.append('files[]', f); });
                var x = new XMLHttpRequest(); x.open('POST', box.dataset.url); x.setRequestHeader('Accept', 'application/json');
                bar.classList.remove('d-none'); document.getElementById('upGo').disabled = true;
                x.upload.onprogress = function (e) { if (e.lengthComputable) bar.firstElementChild.style.width = (e.loaded * 100 / e.total) + '%'; };
                x.onload = function () {
                    if (x.status === 200) { location.reload(); return; }
                    document.getElementById('upGo').disabled = false; bar.classList.add('d-none');
                    try { var j = JSON.parse(x.responseText); err.textContent = Object.values(j.errors || {}).flat().join(' ') || j.message || 'Upload failed.'; } catch (e) { err.textContent = 'Upload failed (' + x.status + ').'; }
                };
                x.onerror = function () { document.getElementById('upGo').disabled = false; err.textContent = 'Network error.'; };
                x.send(fd);
            };
        })();
        </script>
    @endif

    <form method="GET" class="row g-2 mb-3">@if (request('folder'))<input type="hidden" name="folder" value="{{ request('folder') }}">@endif
        <div class="col-md-3"><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Search title…"></div>
        @if (empty($m['global']))<div class="col-md-3"><select name="exam" class="form-select form-select-sm"><option value="">All exam types</option>@foreach ($exams as $e)<option value="{{ $e->slug }}" {{ request('exam') === $e->slug ? 'selected' : '' }}>{{ $e->short_name ?: $e->name }}</option>@endforeach</select></div>@endif
        <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option><option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Shown</option><option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Hidden</option></select></div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ url('admin/content/' . $m['section']) }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <div class="table-responsive">
        <table class="table table-dark-gray align-middle table-hover mb-0">
            <thead><tr><th>#</th>@if (in_array('image', $m['fields']))<th>Image</th>@endif<th>Title / details</th><th>Exam types</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
            @forelse ($list as $it)
                <tr>
                    <td>{{ $it->position }}</td>
                    @if (in_array('image', $m['fields']))<td style="width:110px">@if ($it->image)<img src="{{ Storage::url('files/' . $it->image) }}" class="rounded" style="max-width:100px;max-height:60px" alt="">@endif</td>@endif
                    <td>
                        <div class="fw-semibold">@if ($it->folder)<span class="badge bg-light text-dark border"><i class="bi bi-folder"></i> {{ $it->folder->path() }}</span> @endif @if ($it->is_pinned)<i class="bi bi-pin-angle-fill text-warning me-1"></i>@endif{{ $it->title ?: '—' }}</div>
                        <div class="small text-muted">
                            @if ($it->published_at){{ $it->published_at->format('d M Y') }} · @endif
                            @if ($it->last_date)Last date {{ $it->last_date->format('d M Y') }} · @endif
                            @if ($it->link_url)<a href="{{ $it->link_url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($it->link_url, 40) }}</a>@endif
                        </div>
                    </td>
                    <td>@foreach ($it->exams as $e)<span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span> @endforeach</td>
                    <td>
                        <form action="{{ url('admin/content/' . $m['section'] . '/' . $it->id . '/toggle') }}" method="POST">@csrf
                            <button class="badge border-0 {{ $it->status ? 'bg-success' : 'bg-secondary' }}">{{ $it->status ? 'Shown' : 'Hidden' }}</button>
                        </form>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ url('admin/content/' . $m['section'] . '/' . $it->id . '/edit') }}" class="btn btn-sm btn-circle btn-info" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form action="{{ url('admin/content/' . $m['section'] . '/' . $it->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this {{ $m['one'] }}?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Nothing here yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
</div>
@endsection
