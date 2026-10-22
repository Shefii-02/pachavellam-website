@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
@endsection
@section('content')
@php
    $hasCourses = $admissions->count() > 0;
    $tabs = ['classes' => ['Classes', 'bi-play-btn', $myClasses->count() + $promo->count()], 'tests' => ['Tests', 'bi-ui-checks', $myTests->count() + $freeTests->count()], 'articles' => ['Articles', 'bi-newspaper', $forMe->count() + $moreArticles->count()]];
@endphp
<div class="page-content-wrapper"><div class="container"><div class="hb-wrap">
    <div class="hb-top"><div><h1>Learn</h1><p>{{ $hasCourses ? 'Upcoming classes and tests from your courses, plus free ones for your exams.' : 'Upcoming free classes, tests and articles.' }}</p></div></div>

    @guest
        <div class="hb-note"><i class="bi bi-person-check"></i><span class="sp">Sign in to see the classes and tests of the courses you have joined.</span><a class="hb-btn sm" href="{{ url('sign-in') }}">Sign in</a></div>
    @else
        @unless ($hasCourses)
            <div class="hb-note"><i class="bi bi-mortarboard"></i><span class="sp">You have not joined a course yet. Prime courses add live &amp; recorded classes, mock tests and a study plan.</span><a class="hb-btn sm" href="{{ url('courses') }}">See courses</a></div>
        @endunless
    @endguest

    <div class="hb-chips" role="tablist" aria-label="Learn">
        @foreach ($tabs as $k => [$label, $icon, $n])
            <button type="button" role="tab" class="hb-chip" id="lt-{{ $k }}" aria-controls="lp-{{ $k }}" aria-selected="{{ $tab === $k ? 'true' : 'false' }}" tabindex="{{ $tab === $k ? 0 : -1 }}"><i class="bi {{ $icon }}"></i> {{ $label }}<span class="n">{{ $n }}</span></button>
        @endforeach
    </div>

    {{-- classes --}}
    <div role="tabpanel" id="lp-classes" aria-labelledby="lt-classes" @if ($tab !== 'classes') hidden @endif>
        @if ($hasCourses)
            <section class="hb-sec"><h2>From my courses <span class="ct">{{ $myClasses->count() }}</span></h2>
                @include('kpsc.hub.rows', ['rows' => $myClasses, 'icon' => 'bi-play-fill', 'empty' => 'No new class in your courses this week.'])
            </section>
        @endif
        <section class="hb-sec"><h2>Free classes <span class="ct">{{ $promo->count() }}</span></h2>
            @include('kpsc.hub.rows', ['rows' => $promo, 'icon' => 'bi-play-fill', 'empty' => 'No free class right now.', 'more' => ['free-classes', 'All free classes']])
        </section>
    </div>

    {{-- tests --}}
    <div role="tabpanel" id="lp-tests" aria-labelledby="lt-tests" @if ($tab !== 'tests') hidden @endif>
        @if ($hasCourses)
            <section class="hb-sec"><h2>From my courses <span class="ct">{{ $myTests->count() }}</span></h2>
                @include('kpsc.hub.rows', ['rows' => $myTests, 'icon' => 'bi-ui-checks', 'empty' => 'No test is open in your courses right now.', 'test' => true])
            </section>
        @endif
        <section class="hb-sec"><h2>Free tests <span class="ct">{{ $freeTests->count() }}</span></h2>
            @include('kpsc.hub.rows', ['rows' => $freeTests, 'icon' => 'bi-ui-checks', 'empty' => 'No free test is open right now.', 'test' => true, 'more' => ['kpsc/daily-exam', 'Daily exams']])
        </section>
    </div>

    {{-- articles --}}
    <div role="tabpanel" id="lp-articles" aria-labelledby="lt-articles" @if ($tab !== 'articles') hidden @endif>
        @php $artRow = fn ($a) => (object) ['title' => $a->title, 'sub' => $a->exams->pluck('short_name')->filter()->implode(', '), 'thumb' => $a->image ? Storage::url('files/' . $a->image) : null, 'starts' => null, 'url' => url('articles/' . $a->id), 'tag' => optional($a->published_at)->format('d M'), 'date' => true]; @endphp
        @if ($forMe->count())
            <section class="hb-sec"><h2>For my exams <span class="ct">{{ $forMe->count() }}</span></h2>
                @include('kpsc.hub.rows', ['rows' => $forMe->map($artRow), 'icon' => 'bi-newspaper', 'empty' => ''])
            </section>
        @endif
        <section class="hb-sec"><h2>{{ $forMe->count() ? 'More articles' : 'Latest articles' }} <span class="ct">{{ $moreArticles->count() }}</span></h2>
            @include('kpsc.hub.rows', ['rows' => $moreArticles->map($artRow), 'icon' => 'bi-newspaper', 'empty' => 'No article yet.', 'more' => ['articles', 'All articles']])
        </section>
    </div>
</div></div></div>
@endsection
@section('scripts')
<script>
    (function () {
        var all = [].slice.call(document.querySelectorAll('[role="tab"]'));
        function pick(t, focus) {
            all.forEach(function (x) { var on = x === t; x.setAttribute('aria-selected', on ? 'true' : 'false'); x.tabIndex = on ? 0 : -1; document.getElementById(x.getAttribute('aria-controls')).hidden = !on; });
            if (focus) t.focus();
            history.replaceState(null, '', '?tab=' + t.id.slice(3));
        }
        all.forEach(function (t, k) {
            t.addEventListener('click', function () { pick(t); });
            t.addEventListener('keydown', function (e) { var n = { ArrowRight: k + 1, ArrowLeft: k - 1, Home: 0, End: all.length - 1 }[e.key]; if (n === undefined) return; e.preventDefault(); pick(all[(n + all.length) % all.length], true); });
        });
        // countdowns: "Starts in 02:10:05", "Live now", "Open now"
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function tick() {
            var now = Date.now();
            [].forEach.call(document.querySelectorAll('.when[data-start]'), function (el) {
                var s = Number(el.dataset.start) || 0;
                if (!s || now >= s) { el.textContent = el.dataset.test ? 'Open now' : (s ? 'Live now' : 'Watch now'); el.classList.add('now'); return; }
                var d = Math.floor((s - now) / 1000), days = Math.floor(d / 86400);
                el.textContent = (el.dataset.test ? 'Starts in ' : 'Live in ') + (days >= 1 ? days + (days > 1 ? ' days' : ' day') : pad(Math.floor(d / 3600)) + ':' + pad(Math.floor(d % 3600 / 60)) + ':' + pad(d % 60));
            });
        }
        tick(); setInterval(tick, 1000);
    })();
</script>
@endsection
