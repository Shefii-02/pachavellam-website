@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .ex-list { display:grid; gap:12px; }
        @media (min-width:900px) { .ex-list { grid-template-columns:1fr 1fr; align-items:start; } }
        .ex-card { --ex:#5b8a1f; background:var(--pv-surface); border:1.5px solid var(--pv-border); border-radius:16px; overflow:hidden; scroll-margin-top:16px; }
        .ex-card.mine { border-color:var(--ex); }
        .ex-hd { display:flex; align-items:center; gap:12px; padding:12px; }
        .ex-hd .im { width:56px; height:56px; border-radius:12px; flex:none; overflow:hidden; background:color-mix(in srgb, var(--ex) 14%, var(--pv-surface)); color:var(--ex); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.85rem; }
        .ex-hd .im img { width:100%; height:100%; object-fit:cover; }
        .ex-hd .nm { flex:1; min-width:0; } .ex-hd .nm b { display:block; color:var(--pv-text); line-height:1.25; } .ex-hd .nm small { color:var(--pv-muted); font-size:.76rem; }
        .ex-more { width:100%; border:0; border-top:1px solid var(--pv-border); background:var(--pv-surface-2); color:var(--pv-text); font-size:.8rem; font-weight:700; padding:8px; display:flex; align-items:center; justify-content:center; gap:6px; }
        .ex-more i { transition:transform .2s; } .ex-more[aria-expanded="true"] i { transform:rotate(180deg); }
        .ex-cats { padding:6px 12px 12px; display:grid; gap:8px; }
        .ex-cat { display:flex; align-items:center; gap:10px; border:1px solid var(--pv-border); border-radius:12px; padding:9px 10px; }
        .ex-cat .ic { width:38px; height:38px; border-radius:10px; flex:none; overflow:hidden; background:color-mix(in srgb, var(--ex) 12%, var(--pv-surface)); color:var(--ex); display:flex; align-items:center; justify-content:center; }
        .ex-cat .ic img { width:100%; height:100%; object-fit:cover; }
        .ex-cat .tx { flex:1; min-width:0; } .ex-cat .tx a { font-weight:700; font-size:.9rem; color:var(--pv-text); text-decoration:none; display:block; }
        .ex-cat .tx small { color:var(--pv-muted); font-size:.74rem; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .fav { width:38px; height:38px; flex:none; border-radius:50%; border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-muted); display:flex; align-items:center; justify-content:center; font-size:1.05rem; text-decoration:none !important; cursor:pointer; }
        .fav.on { color:#e5484d; border-color:#e5484d; background:color-mix(in srgb, #e5484d 10%, var(--pv-surface)); }
        .fav .bi-heart-fill { display:none; } .fav.on .bi-heart-fill { display:inline; } .fav.on .bi-heart { display:none; }
        .pick .yes { display:none; } .pick.on .yes { display:inline; } .pick.on .no { display:none; }
    </style>
@endsection
@section('content')
@php
    $catIcons = ['bi-bank', 'bi-train-front', 'bi-building', 'bi-mortarboard', 'bi-gear', 'bi-heart-pulse', 'bi-book', 'bi-shield-check'];
    $colors = ['#5b8a1f', '#d97706', '#2563eb', '#0f766e', '#7c3aed', '#0369a1', '#4d7c0f'];
@endphp
<div class="page-content-wrapper"><div class="container"><div class="hb-wrap">
    <div class="hb-top"><div><h1>Exams</h1><p>Choose the exams you are preparing for. Tap ♡ on a category to keep it in your favourites.</p></div></div>

    @if (session('hub_msg'))<div class="hb-note ok"><i class="bi bi-check-circle-fill"></i><span class="sp">{{ session('hub_msg') }}</span></div>@endif
    @guest
        <div class="hb-note"><i class="bi bi-person-check"></i><span class="sp">Sign in to save your exams - your courses, classes and tests are then picked for you.</span><a class="hb-btn sm" href="{{ url('sign-in') }}">Sign in</a></div>
    @else
        @if (!$myExams->count())
            <div class="hb-note"><i class="bi bi-stars"></i><span class="sp"><b>Welcome, {{ \Illuminate\Support\Str::before($user->name ?: 'there', ' ') }}!</b> Tap <b>Select</b> on the exams you are preparing for - we will show their courses, classes and tests first.</span></div>
        @endif
    @endguest

    @foreach ([['My exams', $myExams, true], [$myExams->count() ? 'Other exams' : 'All exams', $otherExams, false]] as [$heading, $list, $isMine])
        @if ($list->count())
            <section class="hb-sec">
                <h2>{{ $heading }} <span class="ct">{{ $list->count() }}</span></h2>
                <div class="ex-list">
                    @foreach ($list as $i => $ex)
                        @php $color = $ex->color ?: $colors[$i % count($colors)]; @endphp
                        <article class="ex-card {{ $isMine ? 'mine' : '' }}" id="exam-{{ $ex->id }}" style="--ex:{{ $color }}">
                            <div class="ex-hd">
                                <div class="im">@if ($ex->image)<img src="{{ Storage::url($ex->image) }}" alt="" loading="lazy">@else{{ \Illuminate\Support\Str::limit($ex->short_name ?: $ex->name, 5, '') }}@endif</div>
                                <div class="nm"><b>{{ $ex->name }}</b><small>{{ $ex->categories->count() }} categories · {{ $courseCounts[$ex->id] ?? 0 }} courses</small></div>
                                @auth
                                    <button type="button" class="hb-btn sm pick {{ $isMine ? 'on' : '' }}" data-post="{{ url('exams/interest/' . $ex->id) }}" aria-pressed="{{ $isMine ? 'true' : 'false' }}" data-name="{{ $ex->short_name ?: $ex->name }}"><span class="no"><i class="bi bi-plus-lg"></i> Select</span><span class="yes"><i class="bi bi-check-lg"></i> Preparing</span></button>
                                @else
                                    <a class="hb-btn sm pick" href="{{ url('exams/interest/' . $ex->id) }}" title="Sign in to select"><span class="no"><i class="bi bi-plus-lg"></i> Select</span></a>
                                @endauth
                            </div>
                            <button type="button" class="ex-more" aria-expanded="{{ $isMine ? 'true' : 'false' }}" aria-controls="cats-{{ $ex->id }}">{{ $ex->categories->count() ? 'Categories' : 'Details' }} <i class="bi bi-chevron-down"></i></button>
                            <div class="ex-cats" id="cats-{{ $ex->id }}" @unless ($isMine) hidden @endunless>
                                @forelse ($ex->categories as $k => $cat)
                                    @php $isFav = in_array($cat->id, $favs); @endphp
                                    <div class="ex-cat">
                                        <span class="ic">@if ($cat->image)<img src="{{ Storage::url($cat->image) }}" alt="" loading="lazy">@else<i class="bi {{ $cat->icon && str_starts_with($cat->icon, 'bi-') ? $cat->icon : $catIcons[$k % count($catIcons)] }}"></i>@endif</span>
                                        <div class="tx"><a href="{{ url('prime?exam=' . $ex->slug . '&category=' . $cat->id) }}#courses">{{ $cat->name }}</a><small>{{ $cat->children->count() ? $cat->children->pluck('name')->take(8)->implode(' • ') : 'See courses' }}</small></div>
                                        @auth
                                            <button type="button" class="fav {{ $isFav ? 'on' : '' }}" data-post="{{ url('exams/favourite/' . $cat->id) }}" data-name="{{ $cat->name }}" aria-pressed="{{ $isFav ? 'true' : 'false' }}" aria-label="Favourite {{ $cat->name }}"><i class="bi bi-heart"></i><i class="bi bi-heart-fill"></i></button>
                                        @else
                                            <a class="fav" href="{{ url('exams/favourite/' . $cat->id) }}" aria-label="Sign in to favourite {{ $cat->name }}"><i class="bi bi-heart"></i><i class="bi bi-heart-fill"></i></a>
                                        @endauth
                                    </div>
                                @empty
                                    <div class="small" style="color:var(--pv-muted)">{{ \Illuminate\Support\Str::limit((string) ($ex->description ?: 'Classes, tests and study materials for ' . $ex->name . '.'), 160) }}</div>
                                @endforelse
                                <div class="d-flex gap-2 flex-wrap mt-1">
                                    <a class="hb-btn sm" href="{{ url('courses?exam=' . $ex->slug) }}"><i class="bi bi-mortarboard"></i> Courses</a>
                                    <a class="hb-btn sm ghost" href="{{ url($ex->slug) }}">About this exam</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
</div></div></div>
@endsection
@section('scripts')
<script>
    (function () {
        [].forEach.call(document.querySelectorAll('.ex-more'), function (b) {
            b.addEventListener('click', function () {
                var p = document.getElementById(b.getAttribute('aria-controls')), open = b.getAttribute('aria-expanded') !== 'true';
                b.setAttribute('aria-expanded', open ? 'true' : 'false'); p.hidden = !open;
            });
        });
        // select exam / favourite category (signed in): saved at once, no reload
        [].forEach.call(document.querySelectorAll('button[data-post]'), function (b) {
            b.addEventListener('click', function () {
                b.disabled = true;
                pvPost(b.dataset.post).then(function (j) {
                    b.classList.toggle('on', j.on); b.setAttribute('aria-pressed', j.on ? 'true' : 'false');
                    var fav = b.classList.contains('fav');
                    pvToast(b.dataset.name + (j.on ? (fav ? ' added to favourites' : ' added to your exams') : (fav ? ' removed from favourites' : ' removed from your exams')));
                    if (!fav) b.closest('.ex-card').classList.toggle('mine', j.on);
                }).catch(function (e) { if (e.message !== 'login') pvToast('Could not save. Please try again.'); })
                  .finally(function () { b.disabled = false; });
            });
        });
        // came back after sign in with #exam-N: open that exam's categories
        var h = location.hash.match(/^#exam-(\d+)$/);
        if (h) { var m = document.querySelector('[aria-controls="cats-' + h[1] + '"]'); if (m && m.getAttribute('aria-expanded') !== 'true') m.click(); }
    })();
</script>
@endsection
