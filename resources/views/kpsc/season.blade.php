@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .ss-wrap { max-width: 820px; margin: 0 auto; padding: 14px 0 20px; }
        .ss-hero { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:20px; overflow:hidden; }
        .ss-cover { height:150px; background:#10243e center/cover; }
        .ss-body { padding:16px; }
        .ss-body h1 { font-size:1.3rem; font-weight:800; margin:0 0 4px; color:var(--pv-text); } .ss-body p { margin:0 0 8px; color:var(--pv-muted); font-size:.9rem; }
        .ss-chips { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px; } .ss-chips span { background:var(--pv-chip); border:1px solid var(--pv-border); border-radius:20px; padding:3px 12px; font-size:.76rem; font-weight:700; color:var(--pv-text); }
        .ss-chips span.g { background:#e7f6ef; color:#0a6b46; border-color:transparent; } .ss-chips span.y { background:#fff3d6; color:#8a5500; border-color:transparent; }
        .ss-buy { display:flex; align-items:center; gap:14px; flex-wrap:wrap; border-top:1px solid var(--pv-border); padding-top:14px; }
        .ss-price { font-size:1.6rem; font-weight:800; color:var(--pv-text); } .ss-price s { font-size:.95rem; color:var(--pv-muted); font-weight:500; margin-left:8px; } .ss-price small { display:block; font-size:.74rem; font-weight:500; color:var(--pv-muted); }
        .ss-btn { margin-left:auto; border:0; border-radius:24px; padding:11px 28px; font-weight:800; background:var(--pv-green); color:var(--pv-on-brand) !important; text-decoration:none !important; font-size:.95rem; }
        .ss-msg { border-radius:12px; padding:10px 14px; margin-bottom:12px; font-size:.88rem; } .ss-msg.err { background:#fde8e8; color:#a4242a; } .ss-msg.ok { background:#e7f6ef; color:#0a6b46; }
        .ss-list { display:grid; gap:4px; margin-top:6px; }
        .ss-row { display:flex; align-items:center; gap:10px; padding:11px 12px; border-radius:12px; background:var(--pv-surface); border:1px solid var(--pv-border); color:var(--pv-text) !important; text-decoration:none !important; }
        .ss-row .n { width:26px; text-align:center; color:var(--pv-muted); font-weight:800; font-size:.8rem; } .ss-row .t { flex:1; min-width:0; } .ss-row .t b { display:block; font-size:.9rem; } .ss-row .t small { color:var(--pv-muted); font-size:.74rem; }
        .ss-row.lock { opacity:.85; background:var(--pv-surface-2); cursor:pointer; }
        .ss-st { font-size:.68rem; font-weight:800; border-radius:10px; padding:2px 10px; flex:none; } .ss-st.free { background:#e7f6ef; color:#0a6b46; } .ss-st.unlocked { background:#dbeafe; color:#1d4ed8; } .ss-st.locked { background:#fde8e8; color:#a4242a; }
        .ss-go { flex:none; border-radius:20px; padding:5px 14px; font-size:.78rem; font-weight:800; background:var(--pv-green); color:var(--pv-on-brand) !important; } .ss-go.lk { background:transparent; border:1px solid var(--pv-border); color:var(--pv-muted) !important; }
    </style>
@endsection
@section('content')
<div class="page-content-wrapper"><div class="container"><div class="ss-wrap">
    <a href="{{ $back }}" class="small"><i class="bi bi-arrow-left"></i> {{ $exam->short_name ?: $exam->name }} exams</a>
    @if (season('season_msg'))<div class="ss-msg err mt-2"><i class="bi bi-lock-fill me-1"></i>{{ season('season_msg') }}</div>@endif
    @if (season('season_ok'))<div class="ss-msg ok mt-2">{{ season('season_ok') }}</div>@endif

    <div class="ss-hero mt-2">
        @if ($season->cover_image)<div class="ss-cover" style="background-image:url('{{ Storage::url($season->cover_image) }}')"></div>@endif
        <div class="ss-body">
            <h1>{{ $season->title }}</h1>
            @if ($season->tagline)<p>{{ $season->tagline }}</p>@endif
            <div class="ss-chips">
                @if ($season->basis)<span>{{ \App\Models\ExamSeason::BASES[$season->basis] ?? $season->basis }}@if ($season->basis_name): {{ $season->basis_name }}@endif</span>@endif
                @if ($season->starts_on || $season->ends_on)<span><i class="bi bi-calendar3"></i> {{ $season->starts_on?->format('d M Y') ?? '…' }} - {{ $season->ends_on?->format('d M Y') ?? 'running' }}</span>@endif
                <span>{{ count($rows) }} exam{{ count($rows) == 1 ? '' : 's' }}</span>
                @if ($paid)<span class="y">Paid season</span>@if ($freeCount)<span class="g">{{ $freeCount }} free exam{{ $freeCount == 1 ? '' : 's' }}</span>@endif<span>{{ $access->validityLabel($season) }}</span>@else<span class="g">Free - all exams</span>@endif
            </div>
            @if ($season->description)<p style="white-space:pre-line">{{ strip_tags($season->description) }}</p>@endif

            @if ($paid)
                <div class="ss-buy">
                    @php $eff = $access->effectivePrice($season); @endphp
                    <div class="ss-price">₹{{ rtrim(rtrim(number_format($eff, 2), '0'), '.') }}@if ($eff < $season->price)<s>₹{{ rtrim(rtrim(number_format($season->price, 2), '0'), '.') }}</s>@endif<small>one payment · {{ $access->validityLabel($season) }}</small></div>
                    @if ($owns)
                        <span class="ss-btn" style="background:#0a6b46">✓ You have this season</span>
                    @elseif (auth()->check())
                        <a class="ss-btn" href="{{ url('seasons/' . $season->id . '/buy') }}">Buy this season</a>
                    @else
                        <a class="ss-btn" href="{{ url('sign-in') }}">Sign in to buy</a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="hb-sec"><h2 style="font-size:1.05rem;font-weight:800;color:var(--pv-text)">Exams in this season</h2></div>
    <div class="ss-list">
        @forelse ($rows as $i => $r)
            @if ($r->state === 'locked')
                <a class="ss-row lock" href="{{ auth()->check() ? url('seasons/' . $season->id . '/buy') : url('sign-in') }}">
                    <span class="n">{{ $i + 1 }}</span><span class="t"><b>{{ \Illuminate\Support\Str::limit($r->title, 70) }}</b><small>{{ $r->date }}</small></span>
                    <span class="ss-st locked"><i class="bi bi-lock-fill"></i> Paid</span><span class="ss-go lk">Unlock</span>
                </a>
            @else
                <a class="ss-row" href="{{ $r->url }}">
                    <span class="n">{{ $i + 1 }}</span><span class="t"><b>{{ \Illuminate\Support\Str::limit($r->title, 70) }}</b><small>{{ $r->date }}</small></span>
                    <span class="ss-st {{ $r->state }}">{{ $r->state === 'free' ? ($paid ? 'Free exam' : 'Free') : 'Unlocked' }}</span>
                    <span class="ss-go">{{ $r->tagc === 'up' ? 'Opens soon' : ($r->tagc === 'end' ? 'Practice' : 'Attempt') }}</span>
                </a>
            @endif
        @empty
            <div class="hb-empty"><i class="bi bi-hourglass-split"></i>No exams in this season yet.</div>
        @endforelse
    </div>
</div></div></div>
@endsection
