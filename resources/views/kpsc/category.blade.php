@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')@include('kpsc.partials.public-styles')
<style>
    .cg-tabs { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin:14px 0; border-bottom:1px solid #e3e8ef; padding-bottom:8px } .cg-tabs::-webkit-scrollbar { display:none }
    .cg-tab { flex:none; border:0; background:none; padding:7px 16px; border-radius:20px; font-weight:700; font-size:.9rem; color:#1b2a41; cursor:pointer } .cg-tab span { opacity:.6; font-weight:600; margin-left:4px }
    .cg-tab.on { background:#1b2a41; color:#fff }
    .cg-card { position:relative; display:block; height:100%; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.08); color:#1b2a41; text-decoration:none !important }
    .cg-card .im { aspect-ratio:16/9; background:#10243e center/cover }
    .cg-card .bd { padding:12px }
    [data-theme="dark"] .cg-tab { color:#e5ecf6 } [data-theme="dark"] .cg-tab.on { background:#e5ecf6; color:#10243e }
</style>
@endsection
@section('content')
@php
    $ex = $cat->exam; $tgt = $page['new_tab'] ? ' target="_blank" rel="noopener"' : ''; $copy = $page['copy_link'];
    $labels = \App\Support\CategorySections::ALL;
    $secData = ['sub_categories' => $sub_categories, 'prime_tests' => $prime_tests, 'free_tests' => $free_tests, 'free_classes' => $free_classes, 'articles' => $articles];
    $count = fn ($k) => $k === 'courses' ? $courses->total() : $secData[$k]->count();
    $money = fn ($v) => '₹' . rtrim(rtrim(number_format($v, 2), '0'), '.');
    $imgOf = fn ($c) => $c->image ? Storage::url('files/' . $c->image) : (($v = \App\Services\ClassSource::youtubeId((string) $c->link_url)) ? 'https://img.youtube.com/vi/' . $v . '/hqdefault.jpg' : '');
@endphp
<div class="page-content-wrapper"><div class="container py-3">
    <div class="small mb-2"><a href="{{ url($ex->slug) }}">{{ $ex->short_name ?: $ex->name }}</a>@foreach ($trail as $t) <span class="text-muted">/</span> @if ($loop->last)<b>{{ $t->name }}</b>@else<a href="{{ $t->publicUrl() }}">{{ $t->name }}</a>@endif @endforeach</div>
    <h3 class="fw-bold mb-1">{{ $cat->name }}</h3>
    @if (trim(strip_tags((string) $cat->description)) !== '')<p class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags((string) $cat->description), 220) }}</p>@endif
    @include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'category'])

    @if (!count($sections))
        <div class="text-muted mt-3">Nothing has been added to this category yet.</div>
    @else
        @if ($page['layout'] === 'tabs' && count($sections) > 1)
            <div class="cg-tabs" role="tablist" id="cgTabs">
                @foreach ($sections as $k)<button type="button" role="tab" class="cg-tab {{ $loop->first ? 'on' : '' }}" data-sec="{{ $k }}">{{ $labels[$k] }}<span>{{ $count($k) }}</span></button>@endforeach
            </div>
        @endif

        @foreach ($sections as $k)
            <section class="cg-sec" id="sec-{{ $k }}" data-sec="{{ $k }}" @if ($page['layout'] === 'tabs' && count($sections) > 1 && !$loop->first) hidden @endif>
                @if ($page['layout'] !== 'tabs' || count($sections) === 1)<h5 class="mt-4">{{ $labels[$k] }} <span class="text-muted fs-6">({{ $count($k) }})</span></h5>@endif

                @if ($k === 'sub_categories')
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <a href="{{ $cat->publicUrl() }}" class="btn btn-sm rounded-pill {{ !$sub ? 'btn-dark' : 'btn-outline-dark' }}">All <span class="opacity-75">{{ $totalCourses }}</span></a>
                        @foreach ($sub_categories as $ch)<a href="{{ $ch->publicUrl() }}" class="btn btn-sm btn-outline-dark rounded-pill"{!! $tgt !!}>{{ $ch->name }} <span class="opacity-75">{{ $childCounts[$ch->id] ?? 0 }}</span></a>@endforeach
                    </div>

                @elseif ($k === 'courses')
                    @if ($children->count())
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a href="{{ $cat->publicUrl() }}" class="btn btn-sm rounded-pill {{ !$sub ? 'btn-dark' : 'btn-outline-dark' }}">All <span class="opacity-75">{{ $totalCourses }}</span></a>
                            @foreach ($children as $ch)<a href="{{ $cat->publicUrl() }}?sub={{ $ch->slug }}" class="btn btn-sm rounded-pill {{ $sub && $sub->id === $ch->id ? 'btn-dark' : 'btn-outline-dark' }}">{{ $ch->name }} <span class="opacity-75">{{ $childCounts[$ch->id] ?? 0 }}</span></a>@endforeach
                        </div>
                    @endif
                    <div class="row g-3" id="cgrid">
                        @foreach ($courses as $co)
                            @php $cu = url('courses/' . ($co->slug ?: $co->id)); @endphp
                            <div class="col-12 col-md-6 col-lg-4"><a href="{{ $cu }}" class="cg-card"{!! $tgt !!}>
                                <div class="im" style="{{ $co->cover_image ? "background-image:url('" . Storage::url($co->cover_image) . "')" : '' }}"></div>
                                @include('kpsc.partials.item-actions', ['url' => $cu, 'copy' => $copy])
                                <div class="bd"><div class="fw-bold" style="min-height:2.6em;line-height:1.3">{{ $co->title }}</div>
                                    <div class="d-flex justify-content-between align-items-center mt-2"><span class="small text-muted">{{ $co->validityLabel() }}</span>
                                        <span class="fw-bold" style="color:#0a8f5b">{{ $co->isFree() ? 'Free' : $money($co->effectivePrice()) }}@if (!$co->isFree() && $co->price > $co->effectivePrice()) <s class="small text-muted fw-normal">{{ $money($co->price) }}</s>@endif</span></div></div>
                            </a></div>
                        @endforeach
                    </div>
                    @include('kpsc.partials.load-more', ['paginator' => $courses, 'target' => '#cgrid'])

                @elseif ($k === 'prime_tests' || $k === 'free_tests')
                    <div class="row g-3">
                        @foreach ($secData[$k] as $t)
                            @php $tu = $k === 'free_tests' ? url('tests/' . $t->id) : url('courses/' . ($t->course->slug ?? $t->course_id)); @endphp
                            <div class="col-12 col-md-6 col-lg-4"><a href="{{ $tu }}" class="cg-card"{!! $tgt !!}>
                                @include('kpsc.partials.item-actions', ['url' => $tu, 'copy' => $copy])
                                <div class="bd" style="padding-right:84px"><i class="bi {{ $k === 'free_tests' ? 'bi-ui-checks' : 'bi-stars' }}" style="color:#0a8f5b"></i> <b>{{ $t->title }}</b>
                                    <div class="small text-muted mt-1">{{ $t->course->title ?? '' }}</div>
                                    <div class="small mt-1">{{ $t->duration_minutes ? $t->duration_minutes . ' min · ' : '' }}{{ $t->start_at ? $t->start_at->format('d M, h:i a') : 'Available now' }} · <b style="color:{{ $k === 'free_tests' ? '#0a8f5b' : '#b45309' }}">{{ $k === 'free_tests' ? 'Free' : 'Prime' }}</b></div></div>
                            </a></div>
                        @endforeach
                    </div>

                @elseif ($k === 'free_classes')
                    <div class="row g-3">
                        @foreach ($free_classes as $c)
                            <div class="col-6 col-md-4 col-lg-3"><a href="{{ $c->publicUrl() }}" class="cg-card"{!! $tgt !!}>
                                <div class="im" style="background-image:url('{{ $imgOf($c) }}')">@if ($c->mode === 'live')<span class="badge bg-danger" style="margin:8px;position:absolute;left:0;top:0">LIVE</span>@endif</div>
                                @include('kpsc.partials.item-actions', ['url' => $c->publicUrl(), 'copy' => $copy])
                                <div class="bd fw-semibold small">{{ $c->title }}</div>
                            </a></div>
                        @endforeach
                    </div>

                @elseif ($k === 'articles')
                    <div class="row g-3">
                        @foreach ($articles as $a)
                            @php $au = url('articles/' . $a->id); @endphp
                            <div class="col-12 col-md-6 col-lg-4"><a href="{{ $au }}" class="cg-card"{!! $tgt !!}>
                                @if ($a->image)<div class="im" style="background-image:url('{{ Storage::url('files/' . $a->image) }}')"></div>@endif
                                @include('kpsc.partials.item-actions', ['url' => $au, 'copy' => $copy])
                                <div class="bd"><div class="fw-bold">{{ $a->title }}</div><div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags((string) $a->description), 100) }}</div></div>
                            </a></div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        @if ($page['layout'] === 'tabs' && count($sections) > 1)
            <script>
                (function () {
                    var tabs = document.getElementById('cgTabs'); if (!tabs) return;
                    function show(k) {
                        [].forEach.call(tabs.children, function (b) { b.classList.toggle('on', b.dataset.sec === k); });
                        [].forEach.call(document.querySelectorAll('.cg-sec'), function (s) { s.hidden = s.dataset.sec !== k; });
                        if (history.replaceState) history.replaceState(null, '', '#' + k);
                    }
                    tabs.addEventListener('click', function (e) { var b = e.target.closest('.cg-tab'); if (b) show(b.dataset.sec); });
                    var h = location.hash.slice(1); if (h && document.getElementById('sec-' + h)) show(h);
                    // a sub category filter (?sub=) is about the courses
                    if (location.search.indexOf('sub=') > -1 || location.search.indexOf('page=') > -1) show('courses');
                })();
            </script>
        @endif
    @endif
</div></div>
@endsection
