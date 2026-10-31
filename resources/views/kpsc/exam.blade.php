@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
@include('kpsc.partials.public-styles')
<style>
    .lp-strip { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; scrollbar-width:none; padding:2px 2px 10px; } .lp-strip::-webkit-scrollbar { display:none; } .lp-strip > * { flex:none; scroll-snap-align:start; }
    .lp-ban a { width:min(560px, 88vw); }
    @media (min-width:768px) { .lp-ban a { width:calc((100% - 16px) / 2); } }   /* tablet: 2 banners in view */
    @media (min-width:992px) { .lp-ban a { width:calc((100% - 28px) / 3); } }   /* desktop: 3 banners in view */
    .lp-ban img { width:100%; border-radius:16px; display:block; box-shadow:0 3px 12px rgba(0,0,0,.15); }
    .lp-story { width:120px; height:190px; border-radius:16px; background-size:cover; background-position:center; display:block; border:3px solid #fff; outline:2px solid #ff5a5f; box-shadow:0 3px 10px rgba(0,0,0,.18); }
    .lp-h { display:flex; justify-content:space-between; align-items:center; margin:22px 0 8px; } .lp-h h5 { margin:0; font-weight:800; }
    .lp-card { width:250px; background:var(--pv-surface,#fff); border-radius:16px; box-shadow:0 3px 12px rgba(0,0,0,.1); overflow:hidden; text-decoration:none !important; color:inherit; display:block; position:relative; } .lp-card .im { height:125px; background:#dfe6f0 center/cover; position:relative; }
    .lp-card .bd { padding:9px 12px 12px; } .lp-card .tt { font-weight:700; font-size:.92rem; line-height:1.25; height:2.5em; overflow:hidden; } .lp-card .mt { font-size:.74rem; color:#6b7a90; margin-top:3px; }
    .lp-card .pr { margin-top:5px; font-weight:800; } .lp-tag { position:absolute; left:8px; top:8px; font-size:.66rem; font-weight:700; padding:2px 9px; border-radius:10px; color:#fff; } .lp-tag.live { background:#e53935; } .lp-tag.free { background:#16a34a; } .lp-tag.paid { background:#f59e0b; } .lp-tag.mine { background:#2563eb; }
    .lp-test { width:230px; padding:12px; } .lp-test .ic { width:40px; height:40px; border-radius:12px; background:#0f766e; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:8px; }
    .ex-tabs { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:10px; }
    .ex-tab { border-radius:14px; padding:12px 8px; text-align:center; text-decoration:none !important; font-weight:700; font-size:.88rem; box-shadow:0 2px 8px rgba(0,0,0,.08); } .ex-tab img { width:34px; height:34px; object-fit:contain; display:block; margin:0 auto 6px; }
    .ex-subj { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:10px; }
    .ex-subj a { position:relative; display:block; height:78px; border-radius:14px; overflow:hidden; background:#10243e; color:#fff; font-weight:800; padding:10px 12px; text-decoration:none !important; box-shadow:0 2px 8px rgba(0,0,0,.14); }
    .ex-subj a img { position:absolute; right:-10px; bottom:-10px; width:50%; transform:rotate(18deg); border-radius:9px; } .ex-subj a span { position:relative; z-index:2; font-size:.95rem; line-height:1.15; display:block; max-width:70%; }
    .pv-grid { display:grid; gap:12px; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); }
    .pv-card { background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:18px; padding:14px; display:flex; flex-direction:column; gap:8px; }
    .pv-card header { display:flex; align-items:center; gap:10px; }
    .pv-ic { width:40px; height:40px; border-radius:12px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex:none; }
    .pv-t { min-width:0; flex:1; } .pv-t b { display:block; color:var(--pv-text,#1b2a41); line-height:1.2; } .pv-t small { color:var(--pv-muted,#6b7280); font-size:.74rem; }
    .pv-live { display:inline-flex; align-items:center; gap:5px; background:#fde8e8; color:#c0262d; border-radius:20px; padding:2px 10px; font-size:.7rem; font-weight:800; } .pv-live i { width:7px; height:7px; border-radius:50%; background:#e5484d; animation:pvp 1.2s infinite; } @keyframes pvp { 50% { opacity:.25; } }
    .pv-season { border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; padding:2px 6px 6px; } .pv-season + .pv-season { margin-top:6px; }
    .pv-season summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:6px 4px; } .pv-season summary::-webkit-details-marker { display:none; }
    .pv-sn { flex:1; min-width:0; } .pv-sn b { display:block; font-size:.86rem; color:var(--pv-text,#1b2a41); } .pv-sn small { color:var(--pv-muted,#6b7280); font-size:.72rem; }
    .pv-season summary em.act { font-style:normal; font-size:.64rem; font-weight:800; background:#e7f6ef; color:#0a6b46; border-radius:10px; padding:2px 9px; } .pv-season summary > i { transition:transform .2s; color:var(--pv-muted,#6b7280); } .pv-season[open] summary > i { transform:rotate(180deg); }
    .pv-season-link { display:flex; align-items:center; gap:8px; padding:8px 10px; text-decoration:none; } a.pv-season-link:hover { background:rgba(0,0,0,.03); }
    .pv-season ul { margin-top:2px; } .pv-none { color:var(--pv-muted,#6b7280); font-size:.8rem; padding:6px 8px; }
    .pv-card ul { list-style:none; margin:0; padding:0; display:grid; gap:2px; }
    .pv-card li a { display:flex; align-items:center; gap:8px; padding:7px 8px; border-radius:10px; text-decoration:none; color:var(--pv-text,#1b2a41); background:var(--pv-surface-2,#f4f6f8); }
    .pv-i { min-width:0; flex:1; } .pv-i b { display:block; font-size:.84rem; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; } .pv-i small { color:var(--pv-muted,#6b7280); font-size:.72rem; }
    .pv-card li em { font-style:normal; font-size:.66rem; font-weight:800; border-radius:10px; padding:2px 9px; background:#e7f6ef; color:#0a6b46; flex:none; } .pv-card li em.hot { background:#e5484d; color:#fff; }
    .pv-card p { margin:0; color:var(--pv-muted,#6b7280); font-size:.86rem; }
    .pv-card footer { margin-top:auto; font-size:.82rem; font-weight:700; } .pv-card footer a { color:var(--pv-brand,#0a8f5b); text-decoration:none; } .pv-card footer span { color:var(--pv-muted,#6b7280); font-weight:600; }
    .ft-item { background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.08); margin-bottom:10px; overflow:hidden; }
    .ft-head { display:flex; align-items:center; gap:12px; padding:12px 14px; cursor:pointer; font-weight:700; color:#1b2a41; }
    .ft-head i.ic { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; flex:none; }
    .ft-head .chev { margin-left:auto; transition:transform .2s; } .ft-item.open .chev { transform:rotate(180deg); }
    .ft-body { display:none; padding:0 14px 14px 64px; color:#44546a; font-size:.92rem; } .ft-item.open .ft-body { display:block; }
</style>
@endsection
@section('content')
@php $accent = $exam->color ?: '#1b2a41'; @endphp
<div class="page-content-wrapper"><div class="container py-3">
    <div class="rounded-4 p-4 mb-3 text-white" style="background:linear-gradient(135deg, {{ $accent }}, #1b2a41)">
        <h2 class="fw-bold mb-1">{{ $exam->name }}</h2>
        @if ($exam->description)<div style="opacity:.9">{{ $exam->description }}</div>@endif
        @if ($exam->slug === 'kpsc')<a href="{{ url('kpsc') }}" class="btn btn-light btn-sm rounded-pill mt-3">Open KPSC home</a>@endif
    </div>

    {{-- banners set up for this exam type --}}
    @if ($banners->count())
        <div class="lp-strip lp-ban mb-2">@foreach ($banners as $b)<a href="{{ $b->landing_url ?: '#' }}" target="_blank" rel="noopener"><img src="{{ Storage::url('files/' . $b->image) }}" alt="{{ $b->title }}" loading="lazy"></a>@endforeach</div>
        <div class="text-end mb-2"><a href="{{ url('banners') }}" class="small fw-semibold">View all banners</a></div>
    @endif

    @if ($stories->count())
        <div class="lp-h"><h5>Stories</h5><a href="{{ url('stories') }}" class="small fw-semibold">Show all</a></div>
        <div class="lp-strip">@foreach ($stories as $st)<a class="lp-story" href="{{ \App\Support\Landing::url($st->target_type, $st->target_value, $st->redirection) ?: '#' }}" style="background-image:url('{{ Storage::url('files/' . $st->image) }}')"></a>@endforeach</div>
    @endif

    @if ($tabs->count())
        <h5 class="mb-2">Special tabs</h5>
        <div class="ex-tabs mb-4">
            @foreach ($tabs as $w)
                <a class="ex-tab" href="{{ $w->landing_url ?: '#' }}" @if ($w->new_tab) target="_blank" rel="noopener" @endif style="background:{{ $w->bg_color ?: '#fff' }};color:{{ $w->text_color ?: '#1b2a41' }}">
                    @if ($w->image)<img src="{{ Storage::url('files/' . $w->image) }}" alt="">@endif{{ $w->title }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- upcoming: live classes, tests, courses (free or paid) --}}
    @if ($liveClasses->count())
        <div class="lp-h"><h5>Upcoming live classes</h5><a href="{{ url('free-classes') }}" class="small fw-semibold">Show all</a></div>
        <div class="lp-strip">
            @foreach ($liveClasses as $c)
                @php $v = \App\Services\ClassSource::youtubeId((string) $c->link_url); @endphp
                <a class="lp-card" href="{{ $c->publicUrl() }}"><div class="im" style="background-image:url('{{ $c->image ? Storage::url('files/' . $c->image) : ($v ? 'https://img.youtube.com/vi/' . $v . '/hqdefault.jpg' : '') }}')"><span class="lp-tag live">LIVE</span></div>
                    <div class="bd"><div class="tt">{{ $c->title }}</div><div class="mt"><i class="bi bi-clock"></i> {{ $c->scheduled_at->format('d M, h:i a') }} · Free</div></div></a>
            @endforeach
        </div>
    @endif

    @if ($tests->count())
        <div class="lp-h"><h5>Tests</h5>
            <div class="pg-chips mb-0" data-for="#lpTests"><span class="pg-chip on" data-x="all">All</span><span class="pg-chip" data-x="free">Free</span><span class="pg-chip" data-x="paid">Paid</span></div></div>
        <div class="lp-strip" id="lpTests">
            @foreach ($tests as $t)
                @php $paid = (bool) $t->is_paid; $public = $t->course && !str_starts_with((string) $t->course->slug, 'series-'); $owned = $t->course_id && in_array($t->course_id, $mine, true); @endphp
                <a class="lp-card lp-test" data-x="{{ $paid ? 'paid' : 'free' }}" href="{{ $public ? url('courses/' . ($t->course->slug ?: $t->course_id)) : url('/') . '#getapp' }}">
                    <div class="ic"><i class="bi bi-pencil-square"></i></div>
                    <span class="lp-tag {{ $owned ? 'mine' : ($paid ? 'paid' : 'free') }}" style="position:static;display:inline-block;margin-bottom:6px">{{ $owned ? 'PURCHASED' : ($paid ? 'PAID' : 'FREE') }}</span>
                    <div class="tt">{{ $t->title }}</div>
                    <div class="mt">{{ $t->start_at ? $t->start_at->format('d M Y, h:i a') : 'Available now' }}@if ($t->duration_minutes) · {{ $t->duration_minutes }} min @endif</div>
                </a>
            @endforeach
        </div>
    @endif

    @if ($courses->count())
        <div class="lp-h"><h5>Courses</h5>
            <div class="pg-chips mb-0" data-for="#lpCourses"><span class="pg-chip on" data-x="all">All</span><span class="pg-chip" data-x="free">Free</span><span class="pg-chip" data-x="paid">Paid</span>@if (count($mine))<span class="pg-chip" data-x="mine">Purchased</span>@endif</div></div>
        <div class="lp-strip" id="lpCourses">
            @foreach ($courses as $c)
                @php $p = $c->effectivePrice(); $owned = in_array($c->id, $mine, true); @endphp
                <a class="lp-card" data-x="{{ $c->isFree() ? 'free' : 'paid' }}{{ $owned ? ' mine' : '' }}" href="{{ url('courses/' . ($c->slug ?: $c->id)) }}">
                    <div class="im" style="{{ $c->cover_image ? "background-image:url('" . Storage::url($c->cover_image) . "')" : '' }}"><span class="lp-tag {{ $owned ? 'mine' : ($c->isFree() ? 'free' : 'paid') }}">{{ $owned ? 'PURCHASED' : ($c->isFree() ? 'FREE' : 'PAID') }}</span></div>
                    <div class="bd"><div class="tt">{{ $c->title }}</div><div class="mt">{{ $c->validityLabel() }}@if ($c->duration_hours) · {{ $c->duration_hours }} h @endif</div>
                        <div class="pr" style="color:#0a8f5b">{{ $c->isFree() ? 'FREE' : '₹' . number_format($p) }}@if (!$c->isFree() && $p < $c->price) <s class="text-muted fw-normal small">₹{{ number_format($c->price) }}</s>@endif</div></div>
                </a>
            @endforeach
        </div>
    @endif

    @if ($announcements->count())
        <div class="lp-h"><h5>Announcements</h5><a href="{{ url('announcements') }}" class="small fw-semibold">Show all</a></div>
        <div class="cc-grid">@foreach ($announcements as $n)@include('kpsc.partials.content-card', ['item' => $n, 'href' => url('announcements/' . $n->id), 'icon' => 'bi-megaphone'])@endforeach</div>
    @endif

    @if (!empty($provide))
        <div class="lp-h"><h5>What we provide for {{ $exam->short_name ?: $exam->name }}</h5></div>
        <div class="pv-grid mb-4">
            @foreach ($provide as $f)
                <section class="pv-card">
                    <header>
                        <span class="pv-ic" style="background:{{ $accent }}"><i class="bi {{ $f->icon }}"></i></span>
                        <div class="pv-t"><b>{{ $f->name }}</b>
                            @if ($f->total)<small>{{ number_format($f->total) }} available</small>@endif</div>
                        @if ($f->live)<span class="pv-live"><i></i>On now</span>@endif
                    </header>
                    @if (count($f->seasons))
                        @foreach ($f->seasons as $se)
                            @php $seIn = '<span class="pv-sn"><b>' . e($se->title) . '</b><small>' . e($se->sub) . ' · ' . $se->total . ' exam' . ($se->total == 1 ? '' : 's') . '</small></span>' . (!empty($se->paid) ? '<em class="act" style="background:#fff3d6;color:#8a5500">₹' . e($se->price) . '</em>' : '<em class="act">Free</em>'); @endphp
                            @if (!empty($se->url))
                                <a href="{{ $se->url }}" class="pv-season pv-season-link">{!! $seIn !!}</a>
                            @else
                                <div class="pv-season pv-season-link">{!! $seIn !!}</div>
                            @endif
                        @endforeach
                    @elseif (count($f->items))
                        <ul>
                            @foreach ($f->items as $it)
                                <li><a href="{{ $it->url }}"><span class="pv-i"><b>{{ \Illuminate\Support\Str::limit($it->title, 48) }}</b>@if ($it->sub)<small>{{ $it->sub }}</small>@endif</span><em class="{{ in_array($it->tag, ['Live now', 'Upcoming']) ? 'hot' : '' }}">{{ $it->tag }}</em></a></li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $f->description ?: 'Included for this exam type.' }}</p>
                    @endif
                    <footer>@if ($f->all)<a href="{{ $f->all }}">See all <i class="bi bi-arrow-right"></i></a>@elseif (!count($f->items))<span>Coming soon</span>@endif</footer>
                </section>
            @endforeach
        </div>
    @endif

    @if ($recorded->count())
        <div class="lp-h"><h5>Free classes</h5><a href="{{ url('free-classes') }}" class="small fw-semibold">Show all</a></div>
        <div class="lp-strip">
            @foreach ($recorded as $c)
                @php $v = \App\Services\ClassSource::youtubeId((string) $c->link_url); @endphp
                <a class="lp-card" href="{{ $c->publicUrl() }}"><div class="im" style="background-image:url('{{ $c->image ? Storage::url('files/' . $c->image) : ($v ? 'https://img.youtube.com/vi/' . $v . '/hqdefault.jpg' : '') }}')"><span class="lp-tag free">FREE</span></div>
                    <div class="bd"><div class="tt">{{ $c->title }}</div></div></a>
            @endforeach
        </div>
    @endif

    @if ($subjects->count())
        <h5 class="mb-2">Subject categories</h5>
        <div class="ex-subj mb-4">
            @foreach ($subjects as $sj)
                <a href="{{ $sj->url }}" style="{{ $sj->color ? 'background:' . $sj->color : '' }}"><span>{{ $sj->title }}</span>@if ($sj->image)<img src="{{ $sj->image }}" alt="" loading="lazy">@endif</a>
            @endforeach
        </div>
    @endif

    @if (($rankRows ?? collect())->count())
        <div class="lp-h"><h5>{{ $exam->short_name ?: $exam->name }} ranking</h5></div>
        @include('kpsc.partials.rank-list', ['rankRows' => $rankRows, 'rankTitle' => ($exam->short_name ?: $exam->name) . ' top learners', 'rankSub' => 'From ' . ($exam->short_name ?: $exam->name) . ' tests · 5 coins for every test + 1 for every right answer'])
    @endif

    @if ($articles->count())
        <div class="lp-h"><h5>Free articles</h5><a href="{{ url('articles') }}" class="small fw-semibold">Show all</a></div>
        <div class="cc-grid">@foreach ($articles as $n)@include('kpsc.partials.content-card', ['item' => $n, 'href' => url('articles/' . $n->id), 'icon' => 'bi-newspaper'])@endforeach</div>
    @endif

</div></div>
@endsection
@section('scripts')
<script>
    document.querySelectorAll('.pg-chips[data-for]').forEach(function (box) {
        box.addEventListener('click', function (e) {
            var c = e.target.closest('[data-x]'); if (!c) return;
            [].forEach.call(box.children, function (x) { x.classList.toggle('on', x === c); });
            document.querySelectorAll(box.dataset.for + ' > a').forEach(function (a) { a.style.display = (c.dataset.x === 'all' || (' ' + a.dataset.x + ' ').indexOf(' ' + c.dataset.x + ' ') >= 0) ? '' : 'none'; });
        });
    });
</script>
<script>document.querySelectorAll('.ft-head').forEach(function (h) { h.addEventListener('click', function () { h.parentNode.classList.toggle('open'); }); });</script>
@endsection
