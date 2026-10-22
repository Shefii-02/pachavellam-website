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
    <div class="mt-3">{{ $list->links() }}</div>
</div>
@endsection
