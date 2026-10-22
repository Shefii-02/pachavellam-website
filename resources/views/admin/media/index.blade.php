@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-images me-1"></i>Media library</h3>
            <div class="small text-muted">All pictures, files, voice and video in folders. Files uploaded anywhere in the dashboard are kept here, and every file field lets you choose from this library. Up to {{ $maxMb }} MB per file.</div>
        </div>
    </div>
    <div class="card bg-transparent border rounded-3"><div id="mediaPanel" style="min-height:70vh"></div></div>

    <div class="row g-3 mt-3">
        <div class="col-lg-8">
            <div class="card bg-transparent border rounded-3"><div class="card-header bg-transparent"><b>Recommended sizes</b> <span class="small text-muted">shown beside each picture field (change them in config/media.php)</span></div>
                <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Used for</th><th>Size (px)</th><th>Shape</th><th>Formats</th><th>Max</th></tr></thead><tbody>
                    @foreach ($specs as $k => $s)<tr><td>{{ $s['label'] }}</td><td>{{ $s['w'] ? $s['w'] . ' × ' . $s['h'] : 'any' }}</td><td>{{ $s['ratio'] ?: '-' }}</td><td>{{ $s['formats'] }}</td><td>{{ $s['max_mb'] }} MB</td></tr>@endforeach
                </tbody></table></div></div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () { new PVMedia.Panel(document.getElementById('mediaPanel'), { mode: 'manage', accept: @json($accept) }); });
</script>
@endsection
