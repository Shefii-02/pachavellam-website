@extends($layout)
@section('content2')
@php
    $label = $exam->short_name ?: $exam->name;
    $special = $kind === 'special';
    $acc = app(\App\Services\SeasonAccess::class);
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h3 class="mb-0">{{ $label }} - {{ $title }}</h3><div class="small text-muted">Create a season first (free or paid), then open it to add and manage its exams.</div></div>
    <div class="d-flex gap-2">
        <a href="{{ $builder }}?season=none" class="btn btn-sm btn-light mb-0"><i class="bi bi-list-ul me-1"></i>Exams without a season ({{ (int) optional($counts->get(''))->c }})</a>
        @if ($exam->slug === 'kpsc' && in_array($kind, ['daily', 'omr']))
            <a href="{{ url($kind === 'daily' ? 'admin/kpsc/daily-exam/view' : 'admin/kpsc/model-exam/view') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-archive me-1"></i>All old exams</a>
        @endif
        <button type="button" class="btn btn-sm btn-primary mb-0" data-bs-toggle="modal" data-bs-target="#newSession"><i class="bi bi-plus-lg me-1"></i>New season</button>
    </div>
</div>
@if (session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<div class="row g-3">
    @forelse ($seasons as $s)
        @php [$stL, $stC] = \App\Models\ExamSeason::STATUSES[$s->status]; $c = $counts[$s->id] ?? null; $paid = $acc->isPaid($s); $legacyN = (int) ($legacyCounts[$s->id] ?? 0); @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 border rounded-3 overflow-hidden">
                <a href="{{ $base }}/seasons/{{ $s->id }}" style="display:block;height:110px;background:#10243e center/cover {{ $s->cover_image ? "url('" . Storage::url($s->cover_image) . "')" : '' }}"></a>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h5 class="mb-1"><span class="badge bg-dark me-1" title="Show order">#{{ $loop->iteration }}</span><a href="{{ $base }}/seasons/{{ $s->id }}" class="text-reset">{{ $s->title }}</a></h5>
                        <span class="badge bg-{{ $stC }}">{{ $stL }}</span>
                    </div>
                    @if ($s->tagline)<div class="small text-muted">{{ $s->tagline }}</div>@endif
                    <div class="small mt-1">
                        @if ($s->starts_on || $s->ends_on)<i class="bi bi-calendar3"></i> {{ $s->starts_on?->format('d M Y') ?? '…' }} - {{ $s->ends_on?->format('d M Y') ?? '…' }}<br>@endif
                        @if ($s->basis)<span class="badge bg-info text-dark">{{ \App\Models\ExamSeason::BASES[$s->basis] ?? $s->basis }}@if ($s->basis_name): {{ $s->basis_name }}@endif</span>@endif
                        @if ($paid)
                            <span class="badge bg-warning text-dark">Paid ₹{{ rtrim(rtrim(number_format($acc->effectivePrice($s), 2), '0'), '.') }}</span>
                            <span class="badge bg-light text-dark border">{{ $acc->validityLabel($s) }}</span>
                            @if ($s->free_exams)<span class="badge bg-success">first {{ $s->free_exams }} free</span>@endif
                        @else<span class="badge bg-success">Free</span>@endif
                    </div>
                    <div class="mt-2"><b>{{ (int) ($c->c ?? 0) + $legacyN }}</b> exam(s) · {{ (int) ($c->p ?? 0) }} published</div>
                </div>
                <div class="card-footer bg-transparent d-flex gap-2">
                    <form action="{{ $base }}/seasons/{{ $s->id }}/move/up" method="POST">@csrf<button class="btn btn-sm btn-outline-secondary mb-0" title="Show earlier" @disabled($loop->first)><i class="bi bi-arrow-left"></i></button></form>
                    <form action="{{ $base }}/seasons/{{ $s->id }}/move/down" method="POST">@csrf<button class="btn btn-sm btn-outline-secondary mb-0" title="Show later" @disabled($loop->last)><i class="bi bi-arrow-right"></i></button></form>
                    <a href="{{ $base }}/seasons/{{ $s->id }}" class="btn btn-sm btn-primary mb-0 flex-fill"><i class="bi bi-list-check me-1"></i>Manage exams</a>
                    <form action="{{ $base }}/seasons/{{ $s->id }}" method="POST" onsubmit="return confirm('Delete this season? Its exams are kept without a season.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-0" title="Delete season"><i class="bi bi-trash"></i></button></form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5"><i class="bi bi-collection fs-1 d-block mb-2"></i>No seasons yet. Press <b>New season</b> to create the first one.</div>
    @endforelse
</div>

{{-- New season --}}
<div class="modal fade" id="newSession" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form action="{{ $base }}/seasons" method="POST" enctype="multipart/form-data">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i>New {{ $special ? 'special exam' : '' }} season</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-2">@include('admin.exam._season-fields', ['s' => null, 'p' => 'new', 'special' => $special])</div>
            <div class="alert alert-info small mt-3 mb-0">After you save, the season opens and you can add its exams.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light mb-0" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success mb-0"><i class="bi bi-check-lg me-1"></i>Create season</button></div>
    </form>
</div></div></div>
@if ($errors->any() && old('title') && !request()->routeIs('*seasons.update'))<script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('newSession')).show(); });</script>@endif
<script>
    function pvSeasonPaid(p) { var on = document.getElementById(p + 'p').checked; document.getElementById(p + 'box').style.display = on ? '' : 'none'; }
    function pvSeasonValidity(p) {
        var box = document.getElementById(p + 'box'); if (!box) return; var t = box.querySelector('[name=validity_type]').value;
        document.getElementById(p + 'vd').style.display = t === 'days' ? '' : 'none'; document.getElementById(p + 'vt').style.display = t === 'date' ? '' : 'none';
    }
    document.querySelectorAll('[id$="box"]').forEach(function (b) { pvSeasonValidity(b.id.replace(/box$/, '')); });
</script>
@endsection
