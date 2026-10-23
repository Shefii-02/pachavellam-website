@extends($layout)
@section('content2')
<style>
    .qlib-folder, .qlib-folder:hover { color: var(--bs-body-color) !important; }
    .qlib-folder.active, .qlib-folder.active:hover { color: #098209 !important; }
</style>
@php
    $label = $exam->short_name ?: $exam->name;
    $q = fn ($extra = []) => '?' . http_build_query(array_filter(array_merge(request()->query(), $extra), fn ($v) => $v !== null && $v !== ''));
@endphp
@include('admin.question-library._alerts')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">{{ $label }} Question Library</h3>
        <div class="small text-muted">Folders and sub folders of questions. Import from Word / CSV / Excel or add one by one.</div>
    </div>
    <div class="d-flex gap-2">
        @if ($exam->slug === 'kpsc' && auth()->user()->type === 'Admin')
            <form action="{{ $base }}/sync-daily-exams" method="POST" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Syncing...'">@csrf
                <button class="btn btn-sm btn-light mb-0" title="Copy the daily exam, CA exam series and model exam questions into the library"><i class="bi bi-arrow-repeat me-1"></i>Sync daily &amp; CA exam questions</button></form>
        @endif
        <a href="{{ $base }}/imports" class="btn btn-sm btn-light mb-0"><i class="bi bi-clock-history me-1"></i>Import history</a>
        <a href="{{ $base }}/import{{ $current ? '?folder=' . $current->id : '' }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-upload me-1"></i>Import questions</a>
        @if ($current)
            <a href="{{ $base }}/questions/create?folder={{ $current->id }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add question</a>
        @endif
    </div>
</div>

<div class="row g-3">
    {{-- folder tree --}}
    <div class="col-lg-3">
        <div class="card bg-transparent border rounded-3">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-2">
                <strong>Folders</strong>
                <button class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#folderModal" data-parent="" data-title="New folder"><i class="bi bi-folder-plus"></i></button>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ $base }}" class="qlib-folder list-group-item list-group-item-action bg-transparent {{ !$current ? 'active' : '' }}">
                    <i class="bi bi-collection me-1"></i>All questions
                </a>
                @foreach ($tree as $f)
                    <a href="{{ $base }}?folder={{ $f['id'] }}" class="qlib-folder list-group-item list-group-item-action bg-transparent d-flex justify-content-between align-items-center {{ $current?->id === $f['id'] ? 'active' : '' }}"
                        style="padding-left: {{ 1 + $f['depth'] * 1.1 }}rem">
                        <span class="text-truncate"><i class="bi bi-folder{{ $current?->id === $f['id'] ? '2-open' : '' }} me-1"></i>{{ $f['name'] }}</span>
                        <span class="badge bg-secondary ms-2" title="questions in this folder and below">{{ $total[$f['id']] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
            @if (!$tree)<div class="card-body text-muted small">No folders yet. Create the first one with the button above.</div>@endif
        </div>
    </div>

    {{-- current folder --}}
    <div class="col-lg-9">
        <div class="card bg-transparent border rounded-3 mb-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ $base }}">Library</a></li>
                    @foreach ($breadcrumb as $b)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if ($loop->last){{ $b->name }}@else<a href="{{ $base }}?folder={{ $b->id }}">{{ $b->name }}</a>@endif
                        </li>
                    @endforeach
                </ol></nav>
                @if ($current)
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#folderModal" data-parent="{{ $current->id }}" data-title="New sub folder in {{ $current->name }}"><i class="bi bi-folder-plus me-1"></i>Sub folder</button>
                        <button class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#editFolderModal"><i class="bi bi-pencil me-1"></i>Rename / move</button>
                        <button class="btn btn-sm btn-outline-danger mb-0" data-bs-toggle="modal" data-bs-target="#deleteFolderModal" title="Delete folder"><i class="bi bi-trash"></i></button>
                    </div>
                @endif
            </div>
            @if ($current && $current->description)<div class="card-body pt-0 small text-muted">{{ $current->description }}</div>@endif
        </div>

        @if ($subfolders->isNotEmpty())
            <div class="row g-2 mb-3">
                @foreach ($subfolders as $s)
                    <div class="col-6 col-md-4">
                        <a href="{{ $base }}?folder={{ $s['id'] }}" class="qlib-folder card bg-transparent border rounded-3 text-decoration-none h-100">
                            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                                <span class="text-truncate"><i class="bi bi-folder me-1 text-warning"></i>{{ $s['name'] }}</span>
                                <span class="badge bg-secondary">{{ $total[$s['id']] ?? 0 }}</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            @if ($current)<input type="hidden" name="folder" value="{{ $current->id }}">@endif
            <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search question text..."></div>
            <div class="col-6 col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $id => $name)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="lang" class="form-select form-select-sm">
                    <option value="">Any language</option>
                    @foreach ($languages as $code => $l)<option value="{{ $code }}" @selected(request('lang') === $code)>{{ $l['name'] }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto d-flex align-items-center gap-3">
                <div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="images" value="1" id="fImg" @checked(request('images') === '1')><label class="form-check-label small" for="fImg">With pictures</label></div>
                @if ($current && count($subfolders))
                    <div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="deep" value="1" id="fDeep" @checked($deep)><label class="form-check-label small" for="fDeep">Include sub folders</label></div>
                @endif
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-sm btn-light mb-0">Filter</button>
                <a href="{{ $base }}{{ $current ? '?folder=' . $current->id : '' }}" class="btn btn-sm btn-light mb-0">Reset</a>
            </div>
        </form>

        <form action="{{ $base }}/questions/bulk" method="POST" id="bulkForm">
            @csrf
            <div class="card bg-transparent border rounded-3">
                <div class="card-header bg-transparent border-bottom py-2 d-flex flex-wrap gap-2 align-items-center">
                    <span class="small text-muted me-auto">{{ $questions->total() }} question(s){{ $current && !$deep ? ' directly in this folder' : '' }}</span>
                    <select name="folder_id" class="form-select form-select-sm w-auto">
                        <option value="">Move selected to...</option>
                        @foreach ($tree as $f)<option value="{{ $f['id'] }}">{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>@endforeach
                    </select>
                    <button name="action" value="move" class="btn btn-sm btn-light mb-0" onclick="return bulkCheck(this)">Move</button>
                    <button name="action" value="delete" class="btn btn-sm btn-outline-danger mb-0" onclick="return bulkCheck(this)">Delete</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead><tr>
                            <th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                            <th>Question</th><th>Folder</th><th>Categories</th><th>Languages</th><th></th>
                        </tr></thead>
                        <tbody>
                        @forelse ($questions as $item)
                            @php $t = $item->mainText(); @endphp
                            <tr>
                                <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $item->id }}"></td>
                                <td style="max-width:380px">
                                    <div class="text-truncate">
                                        @if ($item->hasImages())<i class="bi bi-image text-info me-1" title="has pictures"></i>@endif
                                        {{ $item->plainQuestion(120) }}
                                    </div>
                                    <div class="small text-muted">{{ $item->question_type !== 'single' ? $item->typeLabel() . ' · ' : '' }}Answer: {{ $item->answerLabel() }}{{ $item->difficulty ? ' · ' . ucfirst($item->difficulty) : '' }}</div>
                                </td>
                                <td class="small"><a href="{{ $base }}?folder={{ $item->folder_id }}">{{ $item->folder?->name }}</a></td>
                                <td class="small">{{ $item->categories->pluck('name')->take(3)->implode(', ') }}{{ $item->categories->count() > 3 ? ' +' . ($item->categories->count() - 3) : '' }}</td>
                                <td>@foreach ($item->texts as $tx)<span class="badge bg-light text-dark border">{{ strtoupper($tx->lang) }}</span> @endforeach</td>
                                <td class="text-end text-nowrap"><a href="{{ $base }}/questions/{{ $item->id }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No questions here yet. Use "Import questions" or "Add question".</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <div class="mt-3">@include('admin.partials.per-page', ['p' => $questions])
{{ $questions->links() }}</div>
    </div>
</div>

{{-- new folder --}}
<div class="modal fade" id="folderModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="{{ $base }}/folders" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title" id="folderModalTitle">New folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="hidden" name="parent_id" id="folderParent">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control mb-3" required maxlength="150">
        <label class="form-label">Description <span class="text-muted">(optional)</span></label>
        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
    </div>
    <div class="modal-footer"><button class="btn btn-primary mb-0">Create</button></div>
</form></div></div>

@if ($current)
{{-- rename / move --}}
<div class="modal fade" id="editFolderModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="{{ $base }}/folders/{{ $current->id }}" method="POST">
    @csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">Rename / move folder</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control mb-3" required maxlength="150" value="{{ $current->name }}">
        <label class="form-label">Inside folder</label>
        @php $own = array_merge([$current->id], $current->descendantIds()); @endphp
        <select name="parent_id" class="form-select mb-3">
            <option value="">— top level —</option>
            @foreach ($tree as $f)
                @continue(in_array($f['id'], $own))
                <option value="{{ $f['id'] }}" @selected($current->parent_id == $f['id'])>{{ str_repeat('— ', $f['depth']) }}{{ $f['name'] }}</option>
            @endforeach
        </select>
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" maxlength="500">{{ $current->description }}</textarea>
    </div>
    <div class="modal-footer"><button class="btn btn-primary mb-0">Save</button></div>
</form></div></div>
{{-- delete folder --}}
<div class="modal fade" id="deleteFolderModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="{{ $base }}/folders/{{ $current->id }}" method="POST">
    @csrf @method('DELETE')
    <div class="modal-header"><h5 class="modal-title">Delete folder "{{ $current->name }}"</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="mb-2">This folder and its sub folders will be deleted.</p>
        @if (($total[$current->id] ?? 0) > 0)
            <div class="alert alert-warning small">It still has <b>{{ $total[$current->id] }}</b> question(s) (including sub folders). Tick the box below to delete them with the folder.</div>
        @else
            <div class="small text-muted mb-2">Questions that are only in Trash also keep a folder from being deleted.</div>
        @endif
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="clear_all" value="1" id="clearAll">
            <label class="form-check-label" for="clearAll">Also delete everything inside (all questions with their pictures, including those in Trash, and all sub folders). This cannot be undone.</label>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger mb-0">Delete folder</button></div>
</form></div></div>
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
    if (!n) { alert('Select at least one question.'); return false; }
    if (btn.value === 'move' && !document.querySelector('#bulkForm [name=folder_id]').value) { alert('Choose the folder to move to.'); return false; }
    return btn.value === 'delete' ? confirm('Delete ' + n + ' selected question(s)? This cannot be undone.') : true;
}
</script>
@endsection
