@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0">Capsule</h3>
            <div class="small text-muted">Picture or text cards in the app feed. Each one appears only for the exam types you tick.</div>
        </div>
        @can('Capsule add')
            <a href="{{ url('admin/capsule/create') }}{{ request('exam') ? '?exam=' . request('exam') : '' }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New Capsule</a>
        @endcan
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search text or tag..."></div>
        <div class="col-6 col-md-2">
            <select name="exam" class="form-select form-select-sm">
                <option value="">All exam types</option>
                @foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="type" class="form-select form-select-sm"><option value="">Image + text</option><option value="Image" @selected(request('type') === 'Image')>Image</option><option value="Content" @selected(request('type') === 'Content')>Text</option></select>
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select form-select-sm"><option value="">Shown + hidden</option><option value="1" @selected(request('status') === '1')>Shown</option><option value="0" @selected(request('status') === '0')>Hidden</option></select>
        </div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ url('admin/capsule') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    <div class="card bg-transparent border rounded-3">
        <div class="card-body p-0 table-responsive">
            @can('Capsule list')
                <table class="table align-middle mb-0 table-hover">
                    <thead><tr><th style="width:150px">Card</th><th>Type</th><th>Exam types</th><th>Likes</th><th>Comments</th><th>Updated</th><th>Status</th><th>Added by</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @forelse ($list as $c)
                            <tr>
                                <td>
                                    @if ($c->type === 'Image')
                                        <img src="{{ asset('storage/files/' . $c->image) }}" alt="" class="rounded" style="max-width:110px;max-height:110px">
                                    @else
                                        <div class="small" style="max-width:260px;max-height:110px;overflow:hidden">{!! \Illuminate\Support\Str::limit(strip_tags((string) $c->content), 160) !!}</div>
                                    @endif
                                    @if ($c->tag)<div class="small text-muted mt-1">#{{ $c->tag }}</div>@endif
                                </td>
                                <td>{{ $c->type === 'Content' ? 'Text' : 'Image' }}</td>
                                <td>
                                    @forelse ($c->exams as $e)<span class="badge text-white me-1" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>@empty<span class="text-danger small">none — not shown anywhere</span>@endforelse
                                </td>
                                <td>{{ number_format($c->likes_count) }}</td>
                                <td>{{ number_format($c->comments_count) }}</td>
                                <td class="text-nowrap small">{{ $c->updated_at ? $c->updated_at->format('d M Y') : '—' }}</td>
                                <td>
                                    @can('Capsule edit')
                                        <form action="{{ url('admin/capsule/' . $c->id . '/toggle') }}" method="POST">@csrf
                                            <button class="badge border-0 bg-{{ $c->status ? 'success' : 'secondary' }}" title="Click to {{ $c->status ? 'hide' : 'show' }}">{{ $c->status ? 'Shown' : 'Hidden' }}</button>
                                        </form>
                                    @else
                                        <span class="badge bg-{{ $c->status ? 'success' : 'secondary' }}">{{ $c->status ? 'Shown' : 'Hidden' }}</span>
                                    @endcan
                                </td>
                                <td class="text-capitalize small">{{ optional($c->author)->name ?: '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @can('Capsule edit')<a href="{{ url('admin/capsule/' . $c->id . '/edit') }}" class="btn btn-sm btn-circle btn-info" title="Edit"><i class="bi bi-pencil"></i></a>@endcan
                                    @can('Capsule delete')
                                        <form action="{{ url('admin/capsule/' . $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this capsule with its likes and comments?')">@csrf @method('DELETE')
                                            <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-5">No capsules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endcan
        </div>
    </div>
    <div class="mt-3">{{ $list->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
