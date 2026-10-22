@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .mc-wrap { max-width: 900px; margin: 0 auto; padding: 14px 0 20px; }
        .mc-head { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:20px; overflow:hidden; }
        .mc-cover { height:130px; background:linear-gradient(135deg,#1c3a0c,#4f7d1b) center/cover; }
        .mc-hb { padding:14px 16px; } .mc-hb h1 { font-size:1.25rem; font-weight:800; margin:0 0 6px; color:var(--pv-text); }
        .mc-meta { display:flex; flex-wrap:wrap; gap:6px; } .mc-meta span { background:var(--pv-chip); border:1px solid var(--pv-border); border-radius:20px; padding:3px 12px; font-size:.76rem; font-weight:700; color:var(--pv-text); }
        .mc-meta span.g { background:#e7f6ef; color:#0a6b46; border-color:transparent; } .mc-meta span.y { background:#fff3d6; color:#8a5500; border-color:transparent; }
        .mc-tabs { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin:14px 0 6px; padding-bottom:2px; position:sticky; top:0; z-index:5; background:var(--pv-bg, transparent); } .mc-tabs::-webkit-scrollbar { display:none; }
        .mc-tab { flex:none; display:flex; align-items:center; gap:6px; border:1px solid var(--pv-border); background:var(--pv-chip); color:var(--pv-text); border-radius:22px; padding:7px 14px; font-size:.84rem; font-weight:700; cursor:pointer; white-space:nowrap; }
        .mc-tab.on { background:var(--pv-text); color:var(--pv-surface); border-color:var(--pv-text); } .mc-tab .n { background:rgba(127,127,127,.25); border-radius:10px; padding:0 7px; font-size:.7rem; }
        .mc-panel { display:none; } .mc-panel.on { display:block; }
        .mc-sec { margin:16px 0 6px; display:flex; align-items:center; gap:8px; font-size:1rem; font-weight:800; color:var(--pv-text); } .mc-sec .ct { background:var(--pv-surface-2); color:var(--pv-muted); font-size:.72rem; border-radius:10px; padding:1px 9px; font-weight:700; }
        .mc-sec i.dot { width:9px; height:9px; border-radius:50%; background:#e5484d; animation:mcp 1.2s infinite; } @keyframes mcp { 50% { opacity:.3; } }
        .mc-list { display:grid; gap:6px; }
        .mc-row { display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:14px; background:var(--pv-surface); border:1px solid var(--pv-border); color:var(--pv-text) !important; text-decoration:none !important; }
        .mc-thumb { width:84px; height:48px; border-radius:9px; background:#10243e center/cover; flex:none; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.3rem; }
        .mc-ic { width:40px; height:40px; border-radius:11px; background:var(--pv-brand-soft); color:var(--pv-brand); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex:none; }
        .mc-t { flex:1; min-width:0; } .mc-t b { display:block; font-size:.9rem; line-height:1.25; } .mc-t small { color:var(--pv-muted); font-size:.74rem; display:block; }
        .mc-chip { font-size:.66rem; font-weight:800; border-radius:10px; padding:2px 10px; flex:none; background:#eef0f3; color:#4b5563; } .mc-chip.live { background:#e53935; color:#fff; } .mc-chip.up { background:#fff1d6; color:#9a5b00; } .mc-chip.done { background:#e7f6ef; color:#0a6b46; }
        .mc-go { flex:none; border:0; border-radius:20px; padding:6px 16px; font-size:.78rem; font-weight:800; background:var(--pv-green); color:var(--pv-on-brand) !important; text-decoration:none !important; cursor:pointer; } .mc-go.off { background:var(--pv-surface-2); color:var(--pv-muted) !important; border:1px solid var(--pv-border); }
        .mc-none { border:1px dashed var(--pv-border); border-radius:14px; padding:18px 12px; text-align:center; color:var(--pv-muted); font-size:.88rem; }
        .mc-folder { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:14px; margin-bottom:6px; } .mc-folder > summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:10px; padding:10px 12px; font-weight:700; color:var(--pv-text); } .mc-folder > summary::-webkit-details-marker { display:none; }
        .mc-folder > summary span { flex:1; } .mc-folder > summary small { color:var(--pv-muted); } .mc-folder > summary .ch { transition:transform .2s; color:var(--pv-muted); } .mc-folder[open] > summary .ch { transform:rotate(180deg); }
        .mc-kids { padding:0 10px 10px 18px; display:grid; gap:4px; }
        .mc-file { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px; background:var(--pv-surface-2); color:var(--pv-text) !important; text-decoration:none !important; font-size:.88rem; } .mc-file span { flex:1; } .mc-file small { color:var(--pv-muted); } .mc-file .go { color:var(--pv-muted); }
        .mc-article { font-size:.92rem; line-height:1.6; color:var(--pv-text); overflow-wrap:anywhere; } .mc-article img { max-width:100%; height:auto; border-radius:10px; } .mc-empty { color:var(--pv-muted); font-size:.82rem; padding:4px 6px; }
        .mc-over { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:10px; }
        .mc-card { text-align:left; border:1px solid var(--pv-border); background:var(--pv-surface); border-radius:16px; padding:12px; cursor:pointer; color:var(--pv-text); } .mc-card .mc-ic { margin-bottom:8px; } .mc-card b { display:block; } .mc-card small { color:var(--pv-muted); }
        .mc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:12px; } .mc-stats div { text-align:center; border:1px solid var(--pv-border); border-radius:14px; padding:10px 4px; background:var(--pv-surface); } .mc-stats b { display:block; font-size:1.3rem; color:var(--pv-text); } .mc-stats small { color:var(--pv-muted); font-size:.74rem; }
        .mc-modal { position:fixed; inset:0; z-index:3000; background:rgba(0,0,0,.75); display:none; align-items:center; justify-content:center; padding:12px; } .mc-modal.on { display:flex; } .mc-modal .box { width:min(900px,100%); } .mc-modal .ratio { border-radius:14px; overflow:hidden; background:#000; } .mc-modal .x { float:right; color:#fff; font-size:1.6rem; background:none; border:0; margin-bottom:6px; }
    </style>
@endsection
@section('content')
<div class="page-content-wrapper"><div class="container"><div class="mc-wrap">
    <a href="{{ url('courses') }}" class="small"><i class="bi bi-arrow-left"></i> My courses</a>
    @if (session('message'))<div class="hb-note ok mt-2"><i class="bi bi-check-circle-fill"></i><span class="sp">{{ session('message') }}</span></div>@endif

    <div class="mc-head mt-2">
        <div class="mc-cover" @if ($course->cover_image) style="background-image:url('{{ Storage::url($course->cover_image) }}')" @endif></div>
        <div class="mc-hb">
            <h1>{{ $course->title }}</h1>
            <div class="mc-meta">
                @if ($course->exam)<span style="background:{{ $course->exam->color ?: '#6c757d' }};color:#fff;border-color:transparent">{{ $course->exam->short_name ?: $course->exam->name }}</span>@endif
                @if ($preview)<span class="y">Administrator preview</span>
                @elseif ($admission->valid_until)<span class="g">Valid until {{ $admission->valid_until->format('d M Y') }}</span><span class="{{ $daysLeft <= 7 ? 'y' : '' }}">{{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }} left</span>
                @else<span class="g">Lifetime access</span>@endif
                <span>{{ count($tabs) }} feature{{ count($tabs) == 1 ? '' : 's' }}</span>
            </div>
            <div class="mc-stats"><div><b>{{ $summary['live'] }}</b><small>Live now / open</small></div><div><b>{{ $summary['upcoming'] }}</b><small>Upcoming</small></div><div><b>{{ $summary['done'] }}</b><small>Completed</small></div></div>
        </div>
    </div>

    <div class="mc-tabs" id="mcTabs" role="tablist">
        <button type="button" class="mc-tab on" data-t="overview"><i class="bi bi-grid-1x2"></i>Overview</button>
        @foreach ($tabs as $t)<button type="button" class="mc-tab" data-t="{{ $t['key'] }}"><i class="bi {{ $t['icon'] }}"></i>{{ $t['name'] }}@if (!empty($t['count']))<span class="n">{{ $t['count'] }}</span>@endif</button>@endforeach
    </div>

    {{-- overview --}}
    <div class="mc-panel on" id="p-overview">
        @if (!count($tabs))<div class="mc-none"><i class="bi bi-hourglass-split d-block fs-3 mb-1"></i>Nothing has been added to this course yet. Please check back soon.</div>@endif
        <div class="mc-over">
            @foreach ($tabs as $t)
                <button type="button" class="mc-card" data-go="{{ $t['key'] }}"><span class="mc-ic"><i class="bi {{ $t['icon'] }}"></i></span><b>{{ $t['name'] }}</b>
                    <small>@if (isset($t['live'])){{ count($t['live']) + count($t['upcoming']) }} upcoming / live · {{ count($t['done']) }} completed @elseif (!empty($t['count'])){{ $t['count'] }} item{{ $t['count'] == 1 ? '' : 's' }} @else{{ \Illuminate\Support\Str::limit($t['description'] ?: 'Included', 46) }}@endif</small></button>
            @endforeach
        </div>
    </div>

    {{-- one panel per feature --}}
    @foreach ($tabs as $t)
        <div class="mc-panel" id="p-{{ $t['key'] }}">
            @if (isset($t['live']))
                @php $now1 = array_merge($t['live'], $t['upcoming']); @endphp
                <div class="mc-sec"><i class="dot"></i>Upcoming &amp; live <span class="ct">{{ count($now1) }}</span></div>
                <div class="mc-list">
                    @forelse ($now1 as $r)
                        @include('kpsc.partials.my-course-row', ['r' => $r, 'kind' => $t['kind'], 'state' => $r->bucket])
                    @empty
                        <div class="mc-none">Nothing upcoming or live right now.</div>
                    @endforelse
                </div>
                <div class="mc-sec"><i class="bi bi-check2-circle" style="color:#0a8f5b"></i>Completed <span class="ct">{{ count($t['done']) }}</span></div>
                <div class="mc-list">
                    @forelse ($t['done'] as $r)
                        @include('kpsc.partials.my-course-row', ['r' => $r, 'kind' => $t['kind'], 'state' => 'done'])
                    @empty
                        <div class="mc-none">Completed {{ strtolower($t['kind'] === 'classes' ? 'classes' : 'exams') }} will be listed here.</div>
                    @endforelse
                </div>
            @elseif ($t['kind'] === 'tree')
                @forelse ($t['nodes'] as $n)@include('kpsc.partials.my-course-node', ['n' => $n, 'depth' => 0])@empty<div class="mc-none">Nothing has been added here yet.</div>@endforelse
            @else
                @if ($t['description'])<p class="small" style="color:var(--pv-muted)">{{ $t['description'] }}</p>@endif
                <div class="mc-list">
                    @foreach ($t['items'] as $i)<div class="mc-row"><span class="mc-ic"><i class="bi {{ $t['icon'] }}"></i></span><span class="mc-t"><b>{{ $i->title }}</b><small>{{ $i->sub }}</small></span></div>@endforeach
                </div>
                @if ($t['link'])<a class="mc-go d-inline-block mt-2" href="{{ $t['link'] }}">Open {{ $t['name'] }}</a>@endif
                @if (!count($t['items']) && !$t['link'])<div class="mc-none">Included in your course.</div>@endif
            @endif
        </div>
    @endforeach
</div></div></div>

<div class="mc-modal" id="mcPlayer"><div class="box"><button class="x" type="button" aria-label="Close">&times;</button><div class="ratio ratio-16x9"><iframe id="mcFrame" src="about:blank" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe></div></div></div>
@endsection
@section('scripts')
<script>
(function () {
    var tabs = document.getElementById('mcTabs');
    function show(key, push) {
        [].forEach.call(document.querySelectorAll('.mc-tab'), function (b) { b.classList.toggle('on', b.dataset.t === key); });
        [].forEach.call(document.querySelectorAll('.mc-panel'), function (p) { p.classList.toggle('on', p.id === 'p-' + key); });
        if (push) { try { history.replaceState(null, '', '#' + key); } catch (e) {} }
        var on = tabs.querySelector('.mc-tab.on'); if (on && on.scrollIntoView) on.scrollIntoView({ inline: 'center', block: 'nearest' });
    }
    tabs.addEventListener('click', function (e) { var b = e.target.closest('.mc-tab'); if (b) show(b.dataset.t, true); });
    document.addEventListener('click', function (e) { var c = e.target.closest('[data-go]'); if (c) { show(c.dataset.go, true); window.scrollTo({ top: 0, behavior: 'smooth' }); } });
    var h = location.hash.replace('#', ''); if (h && document.getElementById('p-' + h)) show(h, false);

    // class player
    var modal = document.getElementById('mcPlayer'), frame = document.getElementById('mcFrame');
    document.addEventListener('click', function (e) { var w = e.target.closest('[data-embed]'); if (w) { e.preventDefault(); frame.src = w.dataset.embed + (w.dataset.embed.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1&rel=0'; modal.classList.add('on'); } });
    function close() { modal.classList.remove('on'); frame.src = 'about:blank'; }
    modal.addEventListener('click', function (e) { if (e.target === modal || e.target.closest('.x')) close(); }); document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
@endsection
