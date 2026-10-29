@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .sr-wrap { max-width: 860px; margin: 0 auto; padding: 14px 0 20px; }
        .sr-top { display:flex; align-items:center; gap:12px; margin-bottom:6px; }
        .sr-ic { width:46px; height:46px; border-radius:14px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex:none; }
        .sr-top h1 { font-size:1.4rem; font-weight:800; margin:0; color:var(--pv-text); } .sr-top p { margin:0; color:var(--pv-muted); font-size:.85rem; }
        .sr-tabs { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin:12px 0 4px; } .sr-tabs::-webkit-scrollbar { display:none; }
        .sr-tabs a { flex:none; border:1px solid var(--pv-border); background:var(--pv-chip); color:var(--pv-text) !important; border-radius:20px; padding:6px 15px; font-size:.84rem; font-weight:600; text-decoration:none !important; } .sr-tabs a.on { background:var(--pv-text); color:var(--pv-surface) !important; border-color:var(--pv-text); }
        .sr-h { display:flex; align-items:center; gap:8px; margin:20px 0 8px; font-size:1.05rem; font-weight:800; color:var(--pv-text); } .sr-h .ct { background:var(--pv-surface-2); color:var(--pv-muted); font-size:.72rem; border-radius:10px; padding:1px 9px; font-weight:700; }
        .sr-season { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; margin-bottom:10px; overflow:hidden; }
        .sr-season > summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:10px; padding:12px 14px; } .sr-season > summary::-webkit-details-marker { display:none; }
        .sr-season .sn { flex:1; min-width:0; } .sr-season .sn b { display:block; color:var(--pv-text); } .sr-season .sn small { color:var(--pv-muted); font-size:.76rem; }
        .sr-season .act { font-size:.66rem; font-weight:800; background:#e7f6ef; color:#0a6b46; border-radius:10px; padding:2px 10px; } .sr-season > summary > i { color:var(--pv-muted); transition:transform .2s; } .sr-season[open] > summary > i { transform:rotate(180deg); }
        .sr-list { display:grid; gap:2px; padding:0 8px 8px; }
        .sr-row { display:flex; align-items:center; gap:10px; padding:10px 10px; border-radius:12px; text-decoration:none !important; color:var(--pv-text) !important; background:var(--pv-surface-2); }
        .sr-row .t { flex:1; min-width:0; } .sr-row .t b { display:block; font-size:.9rem; line-height:1.25; } .sr-row .t small { color:var(--pv-muted); font-size:.74rem; }
        .sr-chip { font-size:.66rem; font-weight:800; border-radius:10px; padding:2px 9px; flex:none; background:#eef0f3; color:#4b5563; } .sr-chip.live { background:#e53935; color:#fff; } .sr-chip.up { background:#fff1d6; color:#9a5b00; } .sr-chip.end { background:#eef0f3; color:#6b7280; }
        .sr-go { flex:none; border-radius:20px; padding:5px 14px; font-size:.78rem; font-weight:800; background:var(--pv-green); color:var(--pv-on-brand) !important; } .sr-go.done { background:transparent; border:1px solid var(--pv-border); color:var(--pv-muted) !important; } .sr-go.off { background:var(--pv-surface); border:1px solid var(--pv-border); color:var(--pv-muted) !important; }
        .sr-chip.lk { background:#fde8e8; color:#a4242a; } .sr-chip.unl { background:#dbeafe; color:#1d4ed8; } .sr-chip.fr { background:#e7f6ef; color:#0a6b46; }
        .sr-sbar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:0 14px 10px; font-size:.78rem; color:var(--pv-muted); } .sr-sbar b { color:var(--pv-text); }
        .sr-buy { margin-left:auto; border-radius:20px; padding:6px 16px; font-weight:800; font-size:.8rem; background:var(--pv-green); color:var(--pv-on-brand) !important; text-decoration:none !important; } .sr-buy.own { background:#0a6b46; }
        .sr-pr { font-size:.66rem; font-weight:800; border-radius:10px; padding:2px 10px; background:#fff3d6; color:#8a5500; } .sr-pr.fr { background:#e7f6ef; color:#0a6b46; }
        .sr-paid { font-size:.62rem; font-weight:800; background:#f59e0b; color:#fff; border-radius:8px; padding:1px 7px; margin-left:6px; }
        .sr-none { border:1px dashed var(--pv-border); border-radius:14px; padding:22px 14px; text-align:center; color:var(--pv-muted); }
        .sr-card { height:100%; background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06); }
        .sr-card .im { position:relative; height:84px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.9); font-size:2rem; }
        .sr-card .im .act { position:absolute; top:10px; right:10px; font-size:.66rem; font-weight:800; font-style:normal; background:#e7f6ef; color:#0a6b46; border-radius:10px; padding:2px 10px; }
        .sr-card .bd { padding:12px; }
        .sr-pg { display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:.85rem; color:var(--pv-muted); }
    </style>
@endsection
@section('content')
@php
    $tabs = ['daily-exams' => ['Daily exams', 'bi-calendar-check'], 'omr-exams' => ['OMR exams', 'bi-ui-checks-grid'], 'gk-exams' => ['GK exam series', 'bi-lightbulb'], 'ca-exams' => ['CA exam series', 'bi-newspaper'], 'special-exams' => ['Special exams', 'bi-stars']];
    $item = function ($r) {
        return $r; // rows are already shaped by the controller
    };
@endphp
<div class="page-content-wrapper"><div class="container"><div class="sr-wrap">
    <div class="sr-top">
        <span class="sr-ic" style="background:{{ $accent }}"><i class="bi {{ $icon }}"></i></span>
        <div><h1>{{ $label }}</h1><p><a href="{{ url($exam->slug) }}" style="color:var(--pv-brand)">{{ $exam->short_name ?: $exam->name }}</a> · active seasons first, then every other exam</p></div>
    </div>
    <div class="sr-tabs">@foreach ($tabs as $k => [$l, $ic])<a class="{{ $k === $series ? 'on' : '' }}" href="{{ url($exam->slug . '/' . $k) }}"><i class="bi {{ $ic }} me-1"></i>{{ $l }}</a>@endforeach</div>

    {{-- active seasons --}}
    <div class="sr-h"><i class="bi bi-lightning-charge-fill" style="color:#0a8f5b"></i>Active seasons <span class="ct">{{ count($active) }}</span></div>
    @if (count($active))
    <div class="row g-3">
    @foreach ($active as $se)
        <div class="col-12 col-md-6"><div class="sr-card">
            <div class="im" style="background:linear-gradient(135deg,{{ $accent }},#10243e)"><i class="bi {{ $icon }}"></i><em class="act">Active</em></div>
            <div class="bd">
                <div class="fw-bold" style="line-height:1.3">@if ($se->url)<a href="{{ $se->url }}" style="color:inherit;text-decoration:none">{{ $se->title }}</a>@else{{ $se->title }}@endif</div>
                <div class="small text-muted">{{ $se->sub }} · {{ $se->total }} exam{{ $se->total == 1 ? '' : 's' }}@if ($se->basis) · {{ $se->basis }}@endif</div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    @if ($se->paid)
                        <span class="fw-bold" style="color:#0a8f5b">₹{{ $se->price }}@if ($se->oldPrice) <s class="small text-muted fw-normal">₹{{ $se->oldPrice }}</s>@endif <span class="small text-muted fw-normal">· {{ $se->validity }}</span></span>
                        @if ($se->owns)<span class="sr-buy own">✓ You have this</span>@else<a class="sr-buy" href="{{ auth()->check() ? url('seasons/' . $se->id . '/buy') : url('sign-in') }}">Buy season</a>@endif
                    @else
                        <span class="fw-bold" style="color:#0a8f5b">Free</span>
                        @if ($se->url)<a class="sr-buy" href="{{ $se->url }}">Open</a>@endif
                    @endif
                </div>
                @if ($se->paid && $se->freeN)<div class="small text-muted mt-1"><b>{{ $se->freeN }}</b> free exam{{ $se->freeN == 1 ? '' : 's' }}</div>@endif
                @if (count($se->items))
                    <div class="sr-list" style="padding:8px 0 0">
                        @foreach (array_slice($se->items, 0, 3) as $r)
                            @include('kpsc.partials.series-row', ['r' => $r])
                        @endforeach
                    </div>
                    @if ($se->url && $se->total > 3)<a class="small d-block mt-2" href="{{ $se->url }}">All {{ $se->total }} exams <i class="bi bi-arrow-right"></i></a>@endif
                @else
                    <div class="small text-muted mt-2">No exams in this season yet.</div>
                @endif
            </div>
        </div></div>
    @endforeach
    </div>
    @else
        <div class="sr-none"><i class="bi bi-hourglass-split d-block fs-3 mb-1"></i>No season is running right now. The exams are listed below.</div>
    @endif

    {{-- every other exam --}}
    <div class="sr-h"><i class="bi bi-list-ul"></i>{{ count($active) ? 'Other exams' : 'All exams' }} <span class="ct">{{ $others->total() }}</span></div>
    @if (count($rows))
        <div class="sr-list" id="srOthers" style="padding:0">
            @foreach ($rows as $r)
                @include('kpsc.partials.series-row', ['r' => $r])
                @include('partials.ad-slot', ['type' => 'web_infeed', 'page' => 'series', 'pos' => $loop->iteration, 'pageNo' => $others->currentPage()])
            @endforeach
        </div>
        @include('kpsc.partials.load-more', ['paginator' => $others, 'target' => '#srOthers'])
    @else
        <div class="sr-none">{{ count($active) ? 'No other exams yet.' : 'No exams here yet - they will appear as soon as they are published.' }}</div>
    @endif
</div></div></div>
@endsection
