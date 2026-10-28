@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h3 class="mb-0"><i class="bi bi-file-earmark-richtext me-1"></i>Pages</h3>
        <div class="d-flex gap-2">
            <a href="{{ url('admin/pages/create?kind=prebuilt') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-link-45deg me-1"></i>Add prebuilt page</a>
            <a href="{{ url('admin/pages/create') }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-plus-lg me-1"></i>New custom page</a>
        </div>
    </div>
    <div class="small text-muted mb-3">Custom pages are written here and open at <code>/p/your-slug</code>. Prebuilt pages are addresses the website already has (Free classes, FAQ ...). Every shown page can be chosen as the target of a banner, story, push notification or menu item.</div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif

    @foreach ([['Custom pages', $custom, true], ['Prebuilt pages', $prebuilt, false]] as [$heading, $rows, $isCustom])
        <h5 class="mt-3">{{ $heading }}</h5>
        <div class="table-responsive"><table class="table table-dark-gray align-middle mb-0">
            <thead><tr><th>Title</th><th>{{ $isCustom ? 'Address' : 'Opens' }}</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($rows as $p)
                    <tr>
                        <td class="fw-bold">{{ $p->title }}<div class="small text-muted">key: {{ $p->slug }}</div></td>
                        <td><a href="{{ $p->url() }}" target="_blank" rel="noopener">{{ $isCustom ? '/p/' . $p->slug : $p->path }}</a></td>
                        <td><form method="POST" action="{{ url('admin/pages/' . $p->id . '/toggle') }}">@csrf<button class="btn btn-sm mb-0 {{ $p->status ? 'btn-success-soft' : 'btn-light' }}">{{ $p->status ? 'Shown' : 'Hidden' }}</button></form></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ url('admin/pages/' . $p->id . '/edit') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ url('admin/pages/' . $p->id) }}" onsubmit="return confirm('Delete this page?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft mb-0"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">None yet.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    @endforeach
</div>
@endsection
