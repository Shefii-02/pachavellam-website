@extends($layout)
@section('content2')
@include('admin.question-library._alerts')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Import #{{ $batch->id }}</h3>
        <div class="small text-muted">{{ $batch->file_name }} · {{ $batch->created_at?->format('d M Y H:i') }}{{ $batch->user ? ' · by ' . $batch->user->name : '' }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $base }}/imports" class="btn btn-sm btn-light mb-0">History</a>
        @if ($batch->folder)<a href="{{ $base }}?folder={{ $batch->folder_id }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-folder2-open me-1"></i>Open folder</a>@endif
        @if ($batch->status === 'done' && $liveCount > 0)
            <form action="{{ $base }}/imports/{{ $batch->id }}/undo" method="POST" onsubmit="return confirm('Delete the {{ $liveCount }} question(s) this import created, with their pictures?')">
                @csrf
                <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-arrow-counterclockwise me-1"></i>Undo import</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach ([['Found', $batch->total_found, 'secondary'], ['Imported', $batch->imported, 'success'], ['Skipped', $batch->skipped, $batch->skipped ? 'danger' : 'secondary'], ['Pictures saved', $batch->images_stored, 'info']] as [$name, $n, $color])
        <div class="col-6 col-md-3"><div class="card bg-transparent border rounded-3"><div class="card-body py-3">
            <div class="small text-muted">{{ $name }}</div><div class="fs-3 text-{{ $color }}">{{ $n }}</div>
        </div></div></div>
    @endforeach
</div>

<div class="card bg-transparent border rounded-3 mb-3">
    <div class="card-body small">
        <div><b>Folder:</b> {{ $folderPath ?: '(folder deleted)' }}</div>
        <div><b>Kind:</b> {{ config("question_library.types.{$batch->file_type}.label") }} · {{ strtoupper($batch->file_format) }}{{ $batch->language ? ' · ' . (config("question_library.languages.{$batch->language}.name") ?? $batch->language) : '' }}</div>
        <div><b>Categories:</b> {{ $categoryNames->isNotEmpty() ? $categoryNames->implode(', ') : 'none' }}</div>
        <div><b>Status:</b> {{ $batch->status === 'undone' ? 'Undone ' . $batch->undone_at?->format('d M Y H:i') : 'Done - ' . $liveCount . ' question(s) still in the library' }}</div>
    </div>
</div>

<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom py-2"><strong>Problems</strong> <span class="text-muted small">({{ count($batch->problems ?? []) }})</span></div>
    @if (!empty($batch->problems))
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th style="width:200px">Where</th><th>What happened</th></tr></thead>
                <tbody>
                @foreach ($batch->problems as $p)
                    <tr>
                        <td class="small text-nowrap">{{ $p['row'] ?? '' }}</td>
                        <td><span class="badge bg-{{ ($p['level'] ?? 'error') === 'error' ? 'danger' : 'warning text-dark' }} me-1">{{ ($p['level'] ?? 'error') === 'error' ? 'Skipped' : 'Notice' }}</span>{{ $p['message'] ?? '' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card-body text-muted">No problems. Everything in the file was imported.</div>
    @endif
</div>
@endsection
