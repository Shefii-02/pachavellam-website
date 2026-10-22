@extends($layout)
@section('content2')
@include('admin.question-library._alerts')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h3 class="mb-0">{{ $exam->short_name ?: $exam->name }} Import History</h3>
    <div class="d-flex gap-2">
        <a href="{{ $base }}" class="btn btn-sm btn-light mb-0">Library</a>
        <a href="{{ $base }}/import" class="btn btn-sm btn-success mb-0"><i class="bi bi-upload me-1"></i>Import questions</a>
    </div>
</div>
<div class="card bg-transparent border rounded-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th>#</th><th>File</th><th>Folder</th><th>Kind</th><th class="text-end">Found</th><th class="text-end">Imported</th><th class="text-end">Skipped</th><th>By</th><th>When</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($batches as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td class="text-truncate" style="max-width:220px">{{ $b->file_name }}</td>
                    <td>{{ $b->folder?->name ?? '—' }}</td>
                    <td class="small">{{ config("question_library.types.{$b->file_type}.label") }} · {{ strtoupper($b->file_format) }}</td>
                    <td class="text-end">{{ $b->total_found }}</td>
                    <td class="text-end text-success">{{ $b->imported }}</td>
                    <td class="text-end {{ $b->skipped ? 'text-danger' : '' }}">{{ $b->skipped }}</td>
                    <td class="small">{{ $b->user?->name ?? '—' }}</td>
                    <td class="small text-nowrap">{{ $b->created_at?->format('d M Y H:i') }}</td>
                    <td>@if ($b->status === 'undone')<span class="badge bg-secondary">Undone</span>@else<span class="badge bg-success">Done</span>@endif</td>
                    <td class="text-end"><a href="{{ $base }}/imports/{{ $b->id }}" class="btn btn-sm btn-light mb-0">Details</a></td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-4">No imports yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $batches->links() }}</div>
@endsection
