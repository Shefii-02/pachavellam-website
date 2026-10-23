@extends($layout)
@section('content2')
@php $label = $exam->short_name ?: $exam->name; @endphp
@include('admin.question-library._alerts')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">{{ $label }} Class Library</h3>
        <div class="small text-muted">Recorded classes by link (YouTube, CDN) or from cloud storage, in folders. Courses pick their free / paid classes from here.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if ($exam->slug === 'kpsc' && auth()->user()->type === 'Admin')
            <form action="{{ $base }}/sync-legacy" method="POST" onsubmit="this.querySelector('button').disabled=true">@csrf
                <button class="btn btn-sm btn-light mb-0" title="Copy the old free / paid class links into the library"><i class="bi bi-arrow-repeat me-1"></i>Sync old class links</button></form>
        @endif
        <a href="{{ $base }}/storage" class="btn btn-sm btn-success mb-0"><i class="bi bi-cloud me-1"></i>Browse cloud storage</a>
        <a href="{{ $base }}/create{{ $current ? '?folder=' . $current->id : '' }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add class</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-3">
        <div class="card bg-transparent border rounded-3">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-2">
                <strong>Folders</strong>
                <button class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#folderModal" data-parent="" data-title="New folder"><i class="bi bi-folder-plus"></i></button>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ $base }}" class="list-group-item list-group-item-action bg-transparent {{ !$current ? 'active' : '' }}"><i class="bi bi-collection-play me-1"></i>All classes</a>
                @foreach ($tree as $f)
                    <a href="{{ $base }}?folder={{ $f['id'] }}" class="list-group-item list-group-item-action bg-transparent d-flex justify-content-between align-items-center {{ $current?->id === $f['id'] ? 'active' : '' }}" style="padding-left: {{ 1 + $f['depth'] * 1.1 }}rem">
                        <span class="text-truncate"><i class="bi bi-folder me-1"></i>{{ $f['name'] }}</span>
                        <span class="badge bg-secondary ms-2">{{ $total[$f['id']] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
            @if (!$tree)<div class="card-body text-muted small">No folders yet.</div>@endif
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card bg-transparent border rounded-3 mb-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ $base }}">Library</a></li>
                    @foreach ($breadcrumb as $b)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">@if ($loop->last){{ $b->name }}@else<a href="{{ $base }}?folder={{ $b->id }}">{{ $b->name }}</a>@endif</li>
                    @endforeach
                </ol></nav>
                @if ($current)
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#folderModal" data-parent="{{ $current->id }}" data-title="New sub folder in {{ $current->name }}"><i class="bi bi-folder-plus me-1"></i>Sub folder</button>
                        <button class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#editFolderModal"><i class="bi bi-pencil me-1"></i>Rename / move</button>
                        <form action="{{ $base }}/folders/{{ $current->id }}" method="POST" onsubmit="return confirm('Delete this folder and its empty sub folders?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash"></i></button></form>
                    </div>
                @endif
            </div>
        </div>

        @if ($subfolders->isNotEmpty())
            <div class="row g-2 mb-3">
                @foreach ($subfolders as $s)
                    <div class="col-6 col-md-4"><a href="{{ $base }}?folder={{ $s['id'] }}" class="card bg-transparent border rounded-3 text-decoration-none h-100"><div class="card-body py-2 d-flex justify-content-between align-items-center">
                        <span class="text-truncate"><i class="bi bi-folder me-1 text-warning"></i>{{ $s['name'] }}</span><span class="badge bg-secondary">{{ $total[$s['id']] ?? 0 }}</span></div></a></div>
                @endforeach
            </div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            @if ($current)<input type="hidden" name="folder" value="{{ $current->id }}">@endif
            <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title or teacher..."></div>
            <div class="col-6 col-md-3"><select name="source" class="form-select form-select-sm"><option value="">Any source</option>
                @foreach (config('class_library.sources') as $k => $l)<option value="{{ $k }}" @selected(request('source') === $k)>{{ $l }}</option>@endforeach</select></div>
            <div class="col-6 col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>
                @foreach (config('class_library.statuses') as $k => [$l])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select></div>
            @if ($current && count($subfolders))
                <div class="col-auto d-flex align-items-center"><div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="deep" value="1" id="fDeep" @checked($deep)><label class="form-check-label small" for="fDeep">Include sub folders</label></div></div>
            @endif
            <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ $base }}{{ $current ? '?folder=' . $current->id : '' }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
        </form>

        <form action="{{ $base }}/bulk" method="POST" id="bulkForm">
            @csrf
            <div class="card bg-transparent border rounded-3">
                <div class="card-header bg-transparent border-bottom py-2 d-flex flex-wrap gap-2 align-items-center">
                    <span class="small text-muted me-auto">{{ $classes->total() }} class(es)</span>
                    <select name="folder_id" class="form-select form-select-sm w-auto"><option value="">Move selected to...</option>
                        @foreach ($tree as $f)<option value="{{ $f['id'] }}">{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach</select>
                    <button name="action" value="move" class="btn btn-sm btn-light mb-0" onclick="return bulkCheck(this)">Move</button>
                    <button name="action" value="publish" class="btn btn-sm btn-light mb-0" onclick="return bulkCheck(this)">Publish</button>
                    <button name="action" value="draft" class="btn btn-sm btn-light mb-0" onclick="return bulkCheck(this)">Draft</button>
                    <button name="action" value="delete" class="btn btn-sm btn-outline-danger mb-0" onclick="return bulkCheck(this)">Delete</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Class</th><th>Source</th><th>Length</th><th>Watch rules</th><th class="text-end">In courses</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($classes as $c)
                            @php [$stLabel, $stColor] = config('class_library.statuses.' . $c->status, [$c->status, 'secondary']); @endphp
                            <tr>
                                <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $c->id }}"></td>
                                <td style="max-width:340px">
                                    <div class="d-flex gap-2 align-items-center">
                                        @if ($t = $c->thumbnailUrl())<img src="{{ $t }}" alt="" style="width:64px;height:36px;object-fit:cover" class="rounded border">@else<span class="d-inline-flex align-items-center justify-content-center border rounded text-muted" style="width:64px;height:36px"><i class="bi bi-play-circle"></i></span>@endif
                                        <div class="text-truncate"><a href="{{ $base }}/{{ $c->id }}/edit" class="fw-semibold">{{ $c->title }}</a>
                                            <div class="small text-muted text-truncate">{{ $c->folder?->name }}{{ $c->instructor ? ' · ' . $c->instructor : '' }}</div></div>
                                    </div>
                                </td>
                                <td class="small">{{ $c->sourceLabel() }}</td>
                                <td class="small">{{ $c->durationLabel() }}</td>
                                <td class="small text-muted" style="max-width:180px">{{ $c->rulesSummary() }}</td>
                                <td class="text-end">{{ $c->course_links_count }}</td>
                                <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                                <td class="text-end"><a href="{{ $base }}/{{ $c->id }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No classes here yet. Use "Add class", or "Browse cloud storage" to make classes from files.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <div class="mt-3">@include('admin.partials.per-page', ['p' => $classes])
{{ $classes->links() }}</div>
    </div>
</div>

<div class="modal fade" id="folderModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="{{ $base }}/folders" method="POST">@csrf
    <div class="modal-header"><h5 class="modal-title" id="folderModalTitle">New folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><input type="hidden" name="parent_id" id="folderParent">
        <label class="form-label">Name</label><input type="text" name="name" class="form-control mb-3" required maxlength="150">
        <label class="form-label">Description <span class="text-muted">(optional)</span></label><textarea name="description" class="form-control" rows="2" maxlength="500"></textarea></div>
    <div class="modal-footer"><button class="btn btn-primary mb-0">Create</button></div></form></div></div>

@if ($current)
<div class="modal fade" id="editFolderModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="{{ $base }}/folders/{{ $current->id }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">Rename / move folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">Name</label><input type="text" name="name" class="form-control mb-3" required maxlength="150" value="{{ $current->name }}">
        <label class="form-label">Inside folder</label>
        @php $own = array_merge([$current->id], $current->descendantIds()); @endphp
        <select name="parent_id" class="form-select mb-3"><option value="">— top level —</option>
            @foreach ($tree as $f)@continue(in_array($f['id'], $own))<option value="{{ $f['id'] }}" @selected($current->parent_id == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach</select>
        <label class="form-label">Description</label><textarea name="description" class="form-control" rows="2" maxlength="500">{{ $current->description }}</textarea></div>
    <div class="modal-footer"><button class="btn btn-primary mb-0">Save</button></div></form></div></div>
@endif

<script>
document.getElementById('folderModal').addEventListener('show.bs.modal', function (e) {
    var b = e.relatedTarget; if (!b) return;
    document.getElementById('folderParent').value = b.getAttribute('data-parent') || '';
    document.getElementById('folderModalTitle').textContent = b.getAttribute('data-title') || 'New folder';
});
var all = document.getElementById('checkAll');
if (all) all.addEventListener('change', function () { document.querySelectorAll('.row-check').forEach(function (c) { c.checked = all.checked; }); });
function bulkCheck(btn) {
    var n = document.querySelectorAll('.row-check:checked').length;
    if (!n) { alert('Select at least one class.'); return false; }
    if (btn.value === 'move' && !document.querySelector('#bulkForm [name=folder_id]').value) { alert('Choose the folder to move to.'); return false; }
    return btn.value === 'delete' ? confirm('Delete ' + n + ' class(es)? They are also removed from every course.') : true;
}
</script>
@endsection
