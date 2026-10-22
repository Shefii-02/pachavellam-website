@extends($layout)
@section('content2')
@include('admin.question-library._alerts')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Cloud storage</h3>
        <div class="small text-muted">Browse the folders of your storage and make classes from the files. Disk: <code>{{ $browser->disk() ?: 'not set' }}</code></div>
    </div>
    <a href="{{ $base }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Class library</a>
</div>

@if (!$browser->configured())
    <div class="alert alert-info">
        <strong>No cloud storage is set up yet.</strong> To use files from an S3 bucket:
        <ol class="mb-0">
            <li>Install the S3 driver once: <code>composer require league/flysystem-aws-s3-v3</code></li>
            <li>Add to the <code>.env</code> file: <code>AWS_ACCESS_KEY_ID</code>, <code>AWS_SECRET_ACCESS_KEY</code>, <code>AWS_DEFAULT_REGION</code>, <code>AWS_BUCKET</code> (and <code>AWS_ENDPOINT</code> for other S3-compatible storage)</li>
            <li>Add <code>CLASS_STORAGE_DISK=s3</code> and, if all classes are in one folder of the bucket, <code>CLASS_STORAGE_ROOT=classes</code></li>
        </ol>
        <div class="mt-2">Until then you can still add classes with YouTube or CDN links.</div>
    </div>
@else
    @if ($error)<div class="alert alert-danger">{{ $error }}</div>@endif

    <form action="{{ $base }}/storage/import" method="POST">
        @csrf
        <input type="hidden" name="path" value="{{ $path }}">
        <div class="card bg-transparent border rounded-3 mb-3">
            <div class="card-body py-2">
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ $base }}/storage">Storage</a></li>
                    @foreach ($crumbs as [$name, $p])
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">@if ($loop->last){{ $name }}@else<a href="{{ $base }}/storage?path={{ urlencode($p) }}">{{ $name }}</a>@endif</li>
                    @endforeach
                </ol></nav>
            </div>
        </div>

        <div class="card bg-transparent border rounded-3 mb-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll" title="Select all files"></th><th>Name</th><th>Type</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($listing['dirs'] as $d)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="dirs[]" value="{{ $d['path'] }}" title="Import everything inside this folder"></td>
                            <td><a href="{{ $base }}/storage?path={{ urlencode($d['path']) }}"><i class="bi bi-folder-fill text-warning me-1"></i>{{ $d['name'] }}</a></td>
                            <td class="small text-muted">folder · tick to import everything inside</td>
                            <td></td>
                        </tr>
                    @endforeach
                    @foreach ($listing['files'] as $f)
                        @php $has = in_array($f['full'], $known); @endphp
                        <tr class="{{ $has ? 'opacity-50' : '' }}">
                            <td><input type="checkbox" class="form-check-input file-check" name="files[]" value="{{ $f['path'] }}" @disabled($has)></td>
                            <td><i class="bi bi-play-circle me-1"></i>{{ $f['name'] }} @if ($has)<span class="badge bg-secondary">already a class</span>@endif</td>
                            <td class="small text-muted">{{ strtoupper($f['ext']) }}</td>
                            <td class="small text-muted">→ {{ \App\Services\StorageBrowser::titleFromFile($f['name']) }}</td>
                        </tr>
                    @endforeach
                    @if (!$listing['dirs'] && !$listing['files'] && !$error)
                        <tr><td colspan="4" class="text-center text-muted py-4">Nothing here (only folders and video / audio files are shown).</td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
            @if ($listing['truncated'])<div class="card-footer small text-muted">Only the first files of this folder are shown. Open a sub folder, or tick the folder to import everything inside it.</div>@endif
        </div>

        <div class="card bg-transparent border rounded-3">
            <div class="card-header bg-transparent border-bottom py-2"><strong>Make classes from the ticked files / folders</strong></div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Into library folder</label>
                        <select name="folder_id" class="form-select" required><option value="">Choose folder...</option>
                            @foreach ($tree as $f)<option value="{{ $f['id'] }}">{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach</select>
                        @if (!$tree)<div class="form-text text-danger">Create a library folder first.</div>@endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select"><option value="draft">Draft (add details first)</option><option value="published">Published</option></select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check"><input type="hidden" name="mirror" value="0"><input class="form-check-input" type="checkbox" name="mirror" value="1" id="mirror" checked><label class="form-check-label" for="mirror">Create the same folders in the library</label></div>
                    </div>
                    <div class="col-12"><button class="btn btn-success mb-0" onclick="return pick()"><i class="bi bi-plus-lg me-1"></i>Add as classes</button>
                        <span class="small text-muted ms-2">The title comes from the file name; add teacher, length and rules afterwards.</span></div>
                </div>
            </div>
        </div>
    </form>
    <script>
    var all = document.getElementById('checkAll');
    all.addEventListener('change', function () { document.querySelectorAll('.file-check:not(:disabled)').forEach(function (c) { c.checked = all.checked; }); });
    function pick() { if (!document.querySelectorAll('input[name="files[]"]:checked, input[name="dirs[]"]:checked').length) { alert('Tick at least one file or folder.'); return false; } return true; }
    </script>
@endif
@endsection
