@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
@php $price = $course->effectivePrice(); @endphp
<div class="page-content-wrapper"><div class="container py-3" style="max-width:860px">
    @if ($course->cover_image)<img src="{{ Storage::url($course->cover_image) }}" class="w-100 rounded-4 mb-3" alt="">@endif
    <div class="mb-1">@if ($course->exam)<span class="badge text-white" style="background:{{ $course->exam->color ?: '#2563eb' }}">{{ $course->exam->short_name ?: $course->exam->name }}</span>@endif</div>
    <h2 class="fw-bold">{{ $course->title }}</h2>
    <div class="text-muted mb-3">{{ $course->validityLabel() }}@if ($course->duration_hours) · {{ $course->duration_hours }} hours @endif</div>
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="fs-3 fw-bold" style="color:#0a8f5b">@if ($course->isFree()) FREE @else ₹{{ number_format($price) }} @endif</div>
        @if (!$course->isFree() && $price < $course->price)<s class="text-muted">₹{{ number_format($course->price) }}</s>@endif
        <a href="{{ url('prime/buy/' . $course->id) }}" class="btn btn-danger rounded-pill px-4 ms-auto">@if ($course->isFree())Join free @elseif (auth()->check())Buy now @else<i class="bi bi-lock-fill"></i> Login &amp; buy @endif</a>
    </div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
    @if ($testCount)
        <div class="d-flex gap-2 mb-3"><span class="badge rounded-pill" style="background:var(--pv-surface-2);color:var(--pv-text)">{{ $testCount }} TESTS</span>@if ($freeTests)<span class="badge rounded-pill" style="background:var(--pv-brand-soft);color:var(--pv-brand)">{{ $freeTests }} FREE TESTS</span>@endif</div>
    @endif
    @if ($course->short_description)<p class="lead">{{ $course->short_description }}</p>@endif
    @if ($groups->count())
        @php $palette = ['#8a6d3b', '#6d28d9', '#0f766e', '#1e40af', '#7c3aed', '#0e7490']; @endphp
        <style>
            .wg-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; margin-bottom:12px; } @media (min-width:768px) { .wg-grid { grid-template-columns:repeat(3, 1fr); } }
            .wg-card { border:1px solid var(--pv-border); background:var(--pv-surface); border-radius:18px; padding:16px; min-height:120px; display:flex; flex-direction:column; justify-content:space-between; text-align:left; color:var(--pv-text); }
            .wg-card[aria-expanded="true"] { border-color:var(--pv-brand); box-shadow:0 0 0 2px var(--pv-brand-soft); }
            .wg-card b { font-size:1.9rem; font-weight:800; align-self:flex-end; line-height:1; }
            .wg-list a { display:flex; justify-content:space-between; gap:10px; align-items:center; padding:11px 2px; border-bottom:1px solid var(--pv-border); color:var(--pv-text); text-decoration:none; }
            .wg-list[hidden] { display:none; }
        </style>
        <h5 class="fw-bold mt-4 mb-3">What you'll get</h5>
        <div class="wg-grid">
            @foreach ($groups as $name => $list)
                <button type="button" class="wg-card" aria-expanded="false" aria-controls="wg{{ $loop->index }}"><span>{{ $name }}</span><b style="color:{{ $palette[$loop->index % count($palette)] }}">{{ str_pad($list->count(), 2, '0', STR_PAD_LEFT) }}</b></button>
            @endforeach
        </div>
        @foreach ($groups as $name => $list)
            <div class="wg-list mb-3" id="wg{{ $loop->index }}" hidden>
                <div class="fw-bold mb-1">{{ $name }}</div>
                @foreach ($list as $t)
                    <a href="{{ url('tests/' . $t->id) }}"><span>{{ $t->title }}<small class="d-block" style="color:var(--pv-muted)">{{ $t->links_count }} Ques{{ $t->duration_minutes ? ' • ' . $t->duration_minutes . ' Mins' : '' }}</small></span>
                        @if ($t->free)<span class="badge" style="background:var(--pv-green);color:var(--pv-on-brand)">FREE</span>@else<i class="bi bi-lock" style="color:var(--pv-muted)"></i>@endif</a>
                @endforeach
            </div>
        @endforeach
        <script>
            document.querySelectorAll('.wg-card').forEach(function (b) {
                b.addEventListener('click', function () {
                    var open = b.getAttribute('aria-expanded') !== 'true';
                    document.querySelectorAll('.wg-card').forEach(function (x) { x.setAttribute('aria-expanded', 'false'); document.getElementById(x.getAttribute('aria-controls')).hidden = true; });
                    b.setAttribute('aria-expanded', open); var p = document.getElementById(b.getAttribute('aria-controls')); p.hidden = !open; if (open) p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            });
        </script>
    @endif
    @if (!empty($course->highlights))<h5>What you get</h5><ul>@foreach ($course->highlights as $h)<li>{{ is_array($h) ? ($h['text'] ?? '') : $h }}</li>@endforeach</ul>@endif
    @if ($course->description)<div style="white-space:pre-wrap">{{ $course->description }}</div>@endif
</div></div>
@endsection
