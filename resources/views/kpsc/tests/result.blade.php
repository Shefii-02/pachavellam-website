@extends('kpsc.tests.layout')
@section('title', $title)
@section('styles')
<style>
    body { background: var(--pv-bg); }
    .r-wrap { max-width: 760px; margin: 0 auto; padding: 14px 14px 110px; display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; }
    .r-wrap > *, .r-three > *, .r-acts > * { min-width: 0; }
    .r-card { background: var(--pv-surface); border: 1px solid var(--pv-border); border-radius: 18px; padding: 18px; color: var(--pv-text); }
    .r-card h2 { font-size: 1.05rem; font-weight: 800; margin: 0 0 14px; }
    .r-hd { min-width: 0; } .r-hd b { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--pv-text); } .r-hd small { color: var(--pv-muted); }
    .r-tile { border-radius: 14px; padding: 14px; display: flex; align-items: center; gap: 14px; position: relative; }
    .r-tile .ring { width: 54px; height: 54px; flex: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; background: var(--pv-surface); }
    .r-tile b { font-size: 1.2rem; } .r-tile b span { color: var(--pv-muted); font-weight: 500; font-size: 1rem; } .r-tile small { display: block; color: var(--pv-muted); }
    .r-neg { position: absolute; right: 12px; top: 12px; background: #e5484d; color: #fff; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; }
    .r-three { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
    .r-three .r-tile { flex-direction: column; align-items: flex-start; gap: 8px; padding: 12px 10px; }
    @media (max-width: 420px) { .r-three b { font-size: .95rem; } .r-three b span { font-size: .8rem; } .r-three .ring { width: 42px; height: 42px; font-size: 1.05rem; } .r-neg { position: static; display: inline-block; margin-left: auto; align-self: flex-start; } }
    .r-acts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px; }
    .r-stars { display: flex; gap: 8px; justify-content: center; margin-top: 10px; }
    .r-stars button { border: 0; background: none; font-size: 2rem; color: #d6dbe1; padding: 0 2px; line-height: 1; } .r-stars button.on { color: #f5b301; }
    .r-chips { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; margin-bottom: 10px; } .r-chips::-webkit-scrollbar { display: none; }
    .r-chip { flex: none; border: 1px solid var(--pv-border); background: var(--pv-surface); color: var(--pv-text); border-radius: 10px; padding: 6px 14px; font-size: .84rem; }
    .r-chip[aria-pressed="true"] { background: color-mix(in srgb, #2f80ed 12%, var(--pv-surface)); color: #2f80ed; border-color: #2f80ed; }
    .r-legend { display: flex; flex-wrap: wrap; gap: 8px 16px; font-size: .82rem; margin-bottom: 10px; } .r-legend i { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
    .r-bar { height: 10px; border-radius: 6px; background: var(--pv-surface-2); display: flex; overflow: hidden; margin: 12px 0 14px; } .r-bar i { display: block; height: 100%; }
    .r-dist { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; } .r-dist div { display: flex; align-items: center; gap: 10px; }
    .r-dist b { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .9rem; flex: none; }
    .r-scale { position: relative; height: 10px; border-radius: 6px; background: var(--pv-surface-2); margin: 26px 0 8px; }
    .r-scale .fill { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 6px; background: color-mix(in srgb, #2f80ed 55%, var(--pv-surface)); }
    .r-scale .dot { position: absolute; top: 50%; width: 20px; height: 20px; margin: -10px 0 0 -10px; border-radius: 50%; background: #2f80ed; border: 4px solid var(--pv-surface); box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    .r-ticks { display: flex; justify-content: space-between; font-size: .72rem; color: var(--pv-muted); }
    .r-lb { display: flex; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--pv-border); }
    .r-lb .n { width: 26px; height: 26px; border-radius: 50%; background: var(--pv-surface-2); font-size: .78rem; display: flex; align-items: center; justify-content: center; flex: none; }
    .r-lb .av { width: 40px; height: 40px; border-radius: 10px; background: color-mix(in srgb, #2f80ed 12%, var(--pv-surface)); color: #2f80ed; font-weight: 800; display: flex; align-items: center; justify-content: center; flex: none; overflow: hidden; }
    .r-lb .av { position: relative; } .r-lb .av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }   /* the initial shows if the photo is missing */
    .r-lb .nm small { white-space: normal; }
    .r-lb .nm { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .r-lb .sc { background: var(--pv-surface-2); border-radius: 8px; padding: 3px 10px; font-size: .82rem; white-space: nowrap; }
    .r-lb.me { background: color-mix(in srgb, #2f80ed 10%, var(--pv-surface)); border-radius: 12px; padding: 10px; border: 0; margin-top: 8px; } .r-lb.me .nm { color: #2f80ed; font-weight: 700; }
    .r-foot { position: fixed; left: 0; right: 0; bottom: 0; background: var(--pv-surface); border-top: 1px solid var(--pv-border); padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); }
    .r-foot .t-btn { width: 100%; max-width: 760px; margin: 0 auto; display: flex; }
</style>
@endsection
@section('content')
@php
    $f = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $mmss = fn ($s) => sprintf($s >= 3600 ? '%02d:%02d:%02d' : '%02d:%02d', ...($s >= 3600 ? [intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60] : [intdiv($s, 60), $s % 60]));
    $total = $paper['minutes'] ? $paper['minutes'] * 60 : null;
    $colors = ['#2f80ed', '#e5484d', '#f5c451', '#1aa260', '#7c3aed', '#0891b2'];
    $multi = $sections->count() > 1;
    $tile = function ($score, $max, $neg, $time, $timeMax, $rank, $tot, $pct, $acc) use ($f, $mmss) {
        return view('kpsc.tests.result-tiles', compact('score', 'max', 'neg', 'time', 'timeMax', 'rank', 'tot', 'pct', 'acc') + ['f' => $f, 'mmss' => $mmss])->render();
    };
    $chart = [
        'labels' => ['You', 'Average', 'Topper'], 'sections' => $sections->map(fn ($s, $i) => ['name' => $s->title, 'color' => $colors[$i % count($colors)],
            'score' => [$s->mine['score'], $s->avg['score'], $s->top['score']], 'correct' => [$s->mine['correct'], $s->avg['correct'], $s->top['correct']],
            'wrong' => [$s->mine['wrong'], $s->avg['wrong'], $s->top['wrong']], 'accuracy' => [$s->accuracy, $s->avg['accuracy'], $s->top['accuracy']],
            'time' => [round($s->mine['time'] / 60, 1), round($s->avg['time'] / 60, 1), round($s->top['time'] / 60, 1)]])->values(),
        'dist' => ['overall' => [$a->correct, $a->wrong, $a->skipped]] + $sections->mapWithKeys(fn ($s) => [$s->id => [$s->mine['correct'], $s->mine['wrong'], $s->mine['skipped']]])->all(),
    ];
    $pos = $a->max_score ? max(0, min(100, $a->score / $a->max_score * 100)) : 0;
@endphp
<div class="t-top">
    <a class="t-ibtn" href="{{ url('my-tests') }}" aria-label="Back to my tests"><i class="bi bi-arrow-left"></i></a>
    <div class="r-hd"><b>{{ $exam->title }}</b><small>Attempted on: {{ optional($a->submitted_at)->format('d M Y | h:i a') }}{{ $a->attempt_no > 1 ? ' · Attempt ' . $a->attempt_no : '' }}</small></div>
</div>

<div class="r-wrap">
    <section class="r-card">
        <h2>Overall Performance Summary</h2>
        {!! $tile($a->score, $a->max_score, $a->negative, $a->time_used, $total, $rank['rank'], $rank['total'], $rank['percentile'], $a->accuracy()) !!}
        <div class="r-acts">
            <button class="t-btn" id="shareBtn"><i class="bi bi-share" style="color:#e5484d"></i> Share</button>
            @if ($canRetry)
                <form method="POST" action="{{ url('tests/' . $exam->id . '/start') }}">@csrf<button class="t-btn pri" style="width:100%"><i class="bi bi-arrow-repeat"></i> Re-Attempt</button></form>
            @else
                <button class="t-btn" disabled>No attempts left</button>
            @endif
        </div>
        @if ($a->attempt_no > 1)<p style="font-size:.8rem;color:var(--pv-muted);margin:10px 0 0">Rank is worked out against everyone's first attempt.</p>@endif
        @if ($others->count() > 1)
            <div class="r-chips" style="margin:12px 0 0">@foreach ($others as $o)<a class="r-chip" style="text-decoration:none" href="{{ url('tests/attempt/' . $o->id . '/result') }}" aria-pressed="{{ $o->id === $a->id ? 'true' : 'false' }}">Attempt {{ $o->attempt_no }}: {{ $f($o->score) }}</a>@endforeach</div>
        @endif
    </section>

    @if ($multi)
        <section class="r-card">
            <h2>Sectional Summary</h2>
            <div class="t-tabs" role="tablist" style="margin:0 -18px 14px">
                @foreach ($sections as $i => $s)<button class="t-tab" role="tab" aria-selected="{{ $i ? 'false' : 'true' }}" aria-controls="ss{{ $s->id }}" id="sst{{ $s->id }}">{{ $s->title }}</button>@endforeach
            </div>
            @foreach ($sections as $i => $s)
                <div role="tabpanel" id="ss{{ $s->id }}" aria-labelledby="sst{{ $s->id }}" @if ($i) hidden @endif>
                    {!! $tile($s->mine['score'], $s->mine['max'], $s->mine['negative'], $s->mine['time'], $s->minutes ? $s->minutes * 60 : null, $s->rank, $s->total, $s->percentile, $s->accuracy) !!}
                </div>
            @endforeach
        </section>
    @endif

    <section class="r-card" style="text-align:center">
        <h2 style="margin-bottom:4px">Rate this test</h2>
        <div style="color:var(--pv-muted);font-size:.9rem">How was your experience with this test?</div>
        <div class="r-stars" role="radiogroup" aria-label="Rating">@for ($i = 1; $i <= 5; $i++)<button role="radio" aria-checked="{{ $a->rating == $i ? 'true' : 'false' }}" aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}" data-r="{{ $i }}" class="{{ $a->rating >= $i ? 'on' : '' }}">★</button>@endfor</div>
    </section>

    @if ($boardCount > 0)
        <section class="r-card">
            <h2>Comparison Chart</h2>
            <div class="r-legend">@foreach ($chart['sections'] as $s)<span><i style="background:{{ $s['color'] }}"></i>{{ $s['name'] }}</span>@endforeach</div>
            <div class="r-chips" role="group" aria-label="Measure">
                @foreach (['score' => 'Your Score', 'correct' => 'Correct', 'wrong' => 'Incorrect', 'accuracy' => 'Accuracy', 'time' => 'Time Spent'] as $k => $l)<button class="r-chip" data-m="{{ $k }}" aria-pressed="{{ $k === 'score' ? 'true' : 'false' }}">{{ $l }}</button>@endforeach
            </div>
            <div style="height:280px"><canvas id="cmp" aria-label="You, average and topper by section"></canvas></div>
            <div style="display:flex;justify-content:space-around;font-size:.8rem;color:var(--pv-muted);margin-top:6px"><span>You {{ $f($a->score) }}</span><span>Average {{ $f($avgScore) }}</span><span>Topper {{ $f($topScore) }}</span></div>
        </section>

        <section class="r-card">
            <div style="display:flex;justify-content:space-between;align-items:center"><h2 style="margin:0">Marks vs Rank</h2><span class="t-pill">Your Rank : {{ $rank['rank'] }}</span></div>
            <div class="r-scale" role="img" aria-label="Your marks {{ $f($a->score) }} of {{ $f($a->max_score) }}"><div class="fill" style="width:{{ $pos }}%"></div><div class="dot" style="left:{{ $pos }}%"></div></div>
            <div class="r-ticks">@for ($i = 0; $i <= 5; $i++)<span>{{ $f($a->max_score * $i / 5) }}</span>@endfor</div>
            <div style="font-size:.72rem;color:var(--pv-muted);margin-top:2px">MARKS</div>
        </section>
    @endif

    <section class="r-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px"><h2 style="margin:0">Question Distribution</h2>
            @if ($multi)<select id="distSel" class="r-chip" aria-label="Section"><option value="overall">Overall</option>@foreach ($sections as $s)<option value="{{ $s->id }}">{{ $s->title }}</option>@endforeach</select>@endif
        </div>
        <div class="r-bar" id="distBar"><i style="background:#2f80ed"></i><i style="background:#e5484d"></i></div>
        <div class="r-dist"><div><b style="background:#2f80ed" id="dC">0</b>Correct</div><div><b style="background:#e5484d" id="dW">0</b>Wrong</div><div><b style="background:var(--pv-surface-2);color:var(--pv-text)" id="dU">0</b>Unattempted</div></div>
    </section>

    @if ($leaders->count())
        <section class="r-card">
            <h2>Leaderboard</h2>
            @foreach ($leaders as $i => $l)
                <div class="r-lb"><span class="n">{{ $i + 1 }}</span><span class="av">{{ strtoupper(mb_substr($l->user->name ?? 'U', 0, 1)) }}@if ($l->user && $l->user->photoUrl())<img src="{{ $l->user->photoUrl() }}" alt="" loading="lazy" onerror="this.remove()">@endif</span><span class="nm">{{ $l->user->name ?? 'Student' }}</span><span class="sc">{{ number_format($l->score, 2) }}/{{ $f($a->max_score) }}</span></div>
            @endforeach
            <div class="r-lb me"><span class="av" style="background:#2f80ed;color:#fff">{{ strtoupper(mb_substr(auth()->user()->name ?? 'Y', 0, 1)) }}</span><span class="nm">{{ auth()->user()->name ?: 'You' }}<small style="display:block;color:var(--pv-muted);font-weight:500">{{ $rank['rank'] <= 10 ? 'Great work - you are in the top 10!' : 'Way to go! Keep practising.' }}</small></span><span style="text-align:right"><span class="sc" style="border:1px solid var(--pv-text)">{{ number_format($a->score, 2) }}/{{ $f($a->max_score) }}</span><small style="display:block;color:var(--pv-muted);font-size:.72rem;margin-top:3px">Rank : {{ $rank['rank'] }}</small></span></div>
        </section>
    @endif
</div>

<div class="r-foot"><a class="t-btn pri" href="{{ url('tests/attempt/' . $a->id . '/solutions') }}"><i class="bi bi-eye"></i> View Solutions</a></div>
<script type="application/json" id="chartData">@json($chart)</script>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    var C = JSON.parse(document.getElementById('chartData').textContent), CSRF = document.querySelector('meta[name="csrf-token"]').content;
    // section tabs
    var tabs = [].slice.call(document.querySelectorAll('[role="tab"]'));
    tabs.forEach(function (t, k) {
        function pick(x) { tabs.forEach(function (o) { var on = o === x; o.setAttribute('aria-selected', on); document.getElementById(o.getAttribute('aria-controls')).hidden = !on; }); x.focus(); }
        t.addEventListener('click', function () { pick(t); });
        t.addEventListener('keydown', function (e) { var n = { ArrowRight: k + 1, ArrowLeft: k - 1 }[e.key]; if (n !== undefined) pick(tabs[(n + tabs.length) % tabs.length]); });
    });
    // rating
    [].forEach.call(document.querySelectorAll('[data-r]'), function (b) {
        b.addEventListener('click', function () {
            var r = +b.dataset.r;
            [].forEach.call(document.querySelectorAll('[data-r]'), function (x) { x.classList.toggle('on', +x.dataset.r <= r); x.setAttribute('aria-checked', +x.dataset.r === r); });
            fetch(@json(url('tests/attempt/' . $a->id . '/rate')), { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ rating: r }) });
        });
    });
    // share
    document.getElementById('shareBtn').addEventListener('click', function () {
        var text = @json('I scored ' . $f($a->score) . '/' . $f($a->max_score) . ' in "' . $exam->title . '" on Pachavellam. Try it: ' . url('tests/' . $exam->id));
        if (navigator.share) navigator.share({ title: 'My test result', text: text }).catch(function () {});
        else { navigator.clipboard && navigator.clipboard.writeText(text); alert('Copied - paste it anywhere to share.'); }
    });
    // question distribution
    function dist(k) {
        var d = C.dist[k] || [0, 0, 0], t = Math.max(1, d[0] + d[1] + d[2]), bar = document.getElementById('distBar').children;
        bar[0].style.width = (d[0] / t * 100) + '%'; bar[1].style.width = (d[1] / t * 100) + '%';
        document.getElementById('dC').textContent = d[0]; document.getElementById('dW').textContent = d[1]; document.getElementById('dU').textContent = d[2];
    }
    var ds = document.getElementById('distSel'); if (ds) ds.addEventListener('change', function () { dist(ds.value); }); dist('overall');
    // comparison chart
    var cv = document.getElementById('cmp');
    if (cv && window.Chart) {
        var css = getComputedStyle(document.documentElement), grid = css.getPropertyValue('--pv-border').trim(), txt = css.getPropertyValue('--pv-muted').trim();
        var units = { score: 'Marks', correct: 'Questions', wrong: 'Questions', accuracy: '%', time: 'Minutes' };
        var chart = new Chart(cv, { type: 'bar', data: { labels: C.labels, datasets: [] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false }, ticks: { color: txt } }, y: { beginAtZero: true, grid: { color: grid }, ticks: { color: txt }, title: { display: true, text: 'Marks', color: txt } } } } });
        function draw(m) {
            chart.data.datasets = C.sections.map(function (s) { return { label: s.name, data: s[m], backgroundColor: s.color, borderRadius: 4, maxBarThickness: 22 }; });
            chart.options.scales.y.title.text = units[m]; chart.update();
            [].forEach.call(document.querySelectorAll('[data-m]'), function (b) { b.setAttribute('aria-pressed', b.dataset.m === m); });
        }
        [].forEach.call(document.querySelectorAll('[data-m]'), function (b) { b.addEventListener('click', function () { draw(b.dataset.m); }); });
        draw('score');
    }
})();
</script>
@endsection
