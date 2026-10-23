@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div><h3 class="mb-0">Trash</h3><div class="small text-muted">Deleted items wait here with their pictures and files. Restore them, or remove them for good.</div></div>
        @if ($list->total() > 0)
            <form action="{{ url('admin/trash/' . $type . '/purge-old') }}" method="POST" onsubmit="return confirm('Remove for good everything in this tab that has been in Trash for more than 30 days?')">@csrf<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-fire me-1"></i>Remove items older than 30 days</button></form>
        @endif
    </div>
    <ul class="nav nav-pills mb-3 gap-1">
        @foreach ($types as $k => [$class, $label])
            <li class="nav-item"><a class="nav-link py-1 {{ $type === $k ? 'active' : '' }}" href="{{ url('admin/trash?type=' . $k) }}">{{ $label }} <span class="badge bg-secondary">{{ $counts[$k] }}</span></a></li>
        @endforeach
    </ul>
    <div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Item</th><th>Deleted</th><th>By</th><th></th></tr></thead>
        <tbody>
        @forelse ($list as $item)
            <tr>
                <td style="max-width:520px"><div class="text-truncate fw-semibold">{{ $item instanceof \App\Models\LibraryQuestion ? $item->plainQuestion(140) : $item->title }}</div>
                    <div class="small text-muted">{{ $item instanceof \App\Models\LibraryQuestion ? $item->typeLabel() : ($item instanceof \App\Models\LibraryClass ? $item->sourceLabel() : ($item instanceof \App\Models\ExamCourse ? 'Course' : '#' . $item->id)) }}</div></td>
                <td class="small text-nowrap">{{ $item->deleted_at->format('d M Y, h:i A') }}<div class="text-muted">{{ $item->deleted_at->diffForHumans() }}</div></td>
                <td class="small">{{ $who[$item->deleted_by] ?? '—' }}</td>
                <td class="text-end text-nowrap">
                    <form action="{{ url('admin/trash/' . $type . '/' . $item->id . '/restore') }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success mb-0"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
                    <form action="{{ url('admin/trash/' . $type . '/' . $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this for good? Its pictures and files are deleted too.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-x-lg"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Trash is empty here.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
</div>
@endsection
