@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <h3 class="mb-1"><i class="bi bi-send me-1"></i>Push notifications</h3>
    <div class="small text-muted mb-3">Delivered to <b>every device</b> of each person - Android, iOS and the website. One person with three devices gets it on all three.</div>

    <div class="row g-2 mb-3">
        @foreach (['android' => 'Android', 'ios' => 'iOS', 'web' => 'Web'] as $k => $label)
            <div class="col-4 col-md-2"><div class="border rounded p-2 text-center"><div class="h4 mb-0">{{ $devices[$k]->n ?? 0 }}</div><div class="small text-muted">{{ $label }} devices</div></div></div>
        @endforeach
        <div class="col-4 col-md-2"><div class="border rounded p-2 text-center"><div class="h4 mb-0">{{ $people }}</div><div class="small text-muted">people reachable</div></div></div>
    </div>

    <div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
        <form method="POST" action="{{ url('admin/push') }}">@csrf
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Title <span class="text-danger">*</span></label><input class="form-control" name="title" maxlength="120" required value="{{ old('title') }}">@error('title')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-7"><label class="form-label">Message <span class="text-danger">*</span></label><input class="form-control" name="body" maxlength="300" required value="{{ old('body') }}">@error('body')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-12">@include('admin.content._landing-picker', ['type' => 'none', 'value' => ''])</div>
                <div class="col-md-8" data-landing-link><label class="form-label">Custom link</label><input class="form-control" id="landingLink" name="link_url" maxlength="500" placeholder="https://…" value="{{ old('link_url') }}"></div>
                <div class="col-md-6">
                    <label class="form-label">Send to</label>
                    <select name="audience" id="pa" class="form-select"><option value="interested">All exam types - students interested in any exam type</option><option value="all">Everyone who has the app or web push on</option><option value="exams" {{ old('audience') === 'exams' ? 'selected' : '' }}>Specific exam types only…</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Platforms</label>
                    <div class="d-flex gap-3 pt-1">@foreach (['android' => 'Android', 'ios' => 'iOS', 'web' => 'Web'] as $k => $label)<div class="form-check"><input class="form-check-input pf" type="checkbox" name="platforms[]" value="{{ $k }}" id="pf{{ $k }}" checked><label class="form-check-label" for="pf{{ $k }}">{{ $label }}</label></div>@endforeach</div>
                </div>
                <div class="col-12" id="examBox" style="display:none">
                    <div class="d-flex flex-wrap gap-3 border rounded p-2">@foreach ($exams as $e)<div class="form-check"><input class="form-check-input ex" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="e{{ $e->id }}"><label class="form-check-label" for="e{{ $e->id }}"><span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span></label></div>@endforeach</div>
                    @error('exam_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3 d-flex align-items-center gap-3"><button class="btn btn-success mb-0" onclick="return confirm('Send this push notification now?')"><i class="bi bi-send me-1"></i>Send now</button><span class="small text-muted" id="reach"></span></div>
        </form>
    </div></div>

    <h5>History</h5>
    <div class="table-responsive"><table class="table table-dark-gray align-middle mb-0">
        <thead><tr><th>When</th><th>Title</th><th>To</th><th>People / devices</th><th>Delivered</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($history as $h)
            <tr><td class="small">{{ $h->created_at->format('d M, h:i a') }}</td><td><b>{{ $h->title }}</b><div class="small text-muted">{{ \Illuminate\Support\Str::limit($h->body, 70) }}</div></td>
                <td class="small">{{ $h->audience === 'all' ? 'Everyone' : 'Exam interest' }}@if ($h->platforms) · {{ implode('/', $h->platforms) }}@endif</td>
                <td>{{ $h->users }} / {{ $h->devices }}</td><td>{{ $h->delivered }}@if ($h->failed)<span class="text-danger"> ({{ $h->failed }} failed)</span>@endif</td>
                <td><span class="badge bg-{{ ['sent' => 'success', 'queued' => 'warning text-dark', 'no_devices' => 'secondary', 'no_credentials' => 'danger'][$h->status] ?? 'secondary' }}">{{ ['no_credentials' => 'Firebase not set up', 'no_devices' => 'No devices'][$h->status] ?? ucfirst($h->status) }}</span></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-3">Nothing sent yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="mt-3">@include('admin.partials.per-page', ['p' => $history])
{{ $history->links() }}</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var pa = document.getElementById('pa'), box = document.getElementById('examBox'), reach = document.getElementById('reach');
        function q() { var p = new URLSearchParams(); p.set('audience', pa.value); document.querySelectorAll('.ex:checked').forEach(function (e) { p.append('exam_ids[]', e.value); }); document.querySelectorAll('.pf:checked').forEach(function (e) { p.append('platforms[]', e.value); }); return p; }
        function count() { fetch(@json(url('admin/push/preview')) + '?' + q(), { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (j) { reach.textContent = 'Will reach ' + j.people + ' people on ' + j.devices + ' devices'; }); }
        function paint() { box.style.display = pa.value === 'exams' ? '' : 'none'; count(); }
        pa.addEventListener('change', paint); document.querySelectorAll('.ex,.pf').forEach(function (e) { e.addEventListener('change', count); }); paint();
    });
</script>
@endsection
