@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .an-card { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:18px; padding:16px; margin-bottom:14px; color:var(--pv-text); }
        .an-hd { display:flex; align-items:center; gap:10px; margin-bottom:6px; } .an-hd .ic { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
        .an-hd b { font-size:1.05rem; flex:1; }
        .an-sub { color:var(--pv-muted); font-size:.8rem; margin-bottom:6px; }
        .an-lv { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
        .an-lv button { border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-text); border-radius:10px; padding:5px 14px; font-size:.84rem; }
        .an-lv button[aria-pressed="true"] { background:color-mix(in srgb, #2f80ed 12%, var(--pv-surface)); color:#2f80ed; border-color:transparent; }
        .an-legend { display:flex; gap:16px; font-size:.8rem; justify-content:flex-end; } .an-legend i { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; }
        .an-ring { display:flex; align-items:center; gap:16px; padding:10px 0; } .an-ring b { font-size:1rem; } .an-ring small { display:block; color:var(--pv-muted); }
        .an-ring .r { width:84px; height:84px; border-radius:50%; flex:none; display:flex; align-items:center; justify-content:center; font-weight:800; }
        .an-ring .r span { width:62px; height:62px; border-radius:50%; background:var(--pv-surface); display:flex; align-items:center; justify-content:center; }
        .an-table { width:100%; border-collapse:collapse; font-size:.88rem; } .an-table th, .an-table td { padding:10px; border-bottom:1px solid var(--pv-border); text-align:center; } .an-table th { background:var(--pv-surface-2); }
        .an-st { font-weight:700; } .an-st.Strong { color:#1aa260; } .an-st.Average { color:#b07d00; } .an-st.Weak { color:#e5484d; } .an-st.Unidentified { color:var(--pv-muted); font-weight:500; }
    </style>
@endsection
@section('content')
<div class="page-content-wrapper"><div class="container"><div class="hb-wrap" style="max-width:760px">
    <div class="hb-top"><div><h1>Test performance analysis</h1><p>{{ $count }} test{{ $count === 1 ? '' : 's' }} in the last {{ $range === 365 ? 'year' : $range . ' days' }}</p></div>
        <div class="hb-chips" style="margin:0">@foreach ([30 => '30 days', 90 => '90 days', 365 => '1 year'] as $k => $l)<a class="hb-chip {{ $range === $k ? 'on' : '' }}" href="{{ url('my-tests/analysis?range=' . $k) }}">{{ $l }}</a>@endforeach</div></div>

    @if (!$count)
        <div class="hb-empty"><i class="bi bi-graph-up"></i>Take a test to see your analysis here. <a href="{{ url('my-tests') }}">Go to my tests</a></div>
    @else
        <div class="an-card">
            <div class="an-hd"><span class="ic" style="background:color-mix(in srgb,#2f80ed 12%,var(--pv-surface));color:#2f80ed"><i class="bi bi-graph-up-arrow"></i></span><b>Marks in each test</b></div>
            <div style="height:220px"><canvas id="chMarks" aria-label="Marks percentage of each test"></canvas></div>
        </div>
        @foreach ([['att', 'Attempt Percentage', 'bi-percent', '#f5b301', 'Attempted questions %'], ['acc', 'Accuracy Percentage', 'bi-bullseye', '#1aa260', 'Accuracy %'], ['time', 'Average Time Per Question', 'bi-stopwatch', '#2f80ed', 'Minutes']] as [$k, $label, $icon, $color, $axis])
            <div class="an-card" data-chart="{{ $k }}">
                <div class="an-hd"><span class="ic" style="background:color-mix(in srgb,{{ $color }} 14%,var(--pv-surface));color:{{ $color }}"><i class="bi {{ $icon }}"></i></span><b>{{ $label }}</b></div>
                <div class="an-sub">Question difficulty level</div>
                <div class="an-lv" role="group" aria-label="Difficulty">@foreach (['overall' => 'Overall', 'easy' => 'Easy', 'moderate' => 'Moderate', 'difficult' => 'Difficult'] as $lv => $ll)<button data-lv="{{ $lv }}" aria-pressed="{{ $lv === 'overall' ? 'true' : 'false' }}">{{ $ll }}</button>@endforeach</div>
                @if ($k === 'att')<div class="an-legend"><span><i style="background:#1aa260"></i>Attempted %</span><span><i style="background:#e5484d"></i>Unattempted %</span></div>@endif
                <div style="height:240px"><canvas aria-label="{{ $label }} by week"></canvas></div>
            </div>
        @endforeach

        <div class="an-card">
            <div class="an-hd"><span class="ic" style="background:color-mix(in srgb,#7c3aed 12%,var(--pv-surface));color:#7c3aed"><i class="bi bi-clipboard-data"></i></span><b>Sectional Report</b></div>
            @if ($subjects->count())
                <div class="t-tabs" role="tablist" style="display:flex;overflow-x:auto;border-bottom:1px solid var(--pv-border);margin-bottom:6px">
                    @foreach ($subjects as $i => $s)<button class="hb-chip" style="border:0;border-radius:0;border-bottom:3px solid {{ $i ? 'transparent' : 'var(--pv-brand)' }}" role="tab" aria-selected="{{ $i ? 'false' : 'true' }}" data-subj="{{ $i }}">{{ $s->name }}</button>@endforeach
                </div>
                @foreach ($subjects as $i => $s)
                    <div data-subj-panel="{{ $i }}" @if ($i) hidden @endif>
                        @foreach ([['Cumulative Accuracy', $s->cumulative, '#2f80ed', 'Right answers out of all questions'], ['Attempt Accuracy', $s->accuracy, '#1aa260', 'Right answers out of the ones you answered'], ['Time taken', $s->time, '#f5b301', 'Time used out of the time allowed']] as [$l, $v, $c, $hint])
                            @if ($v !== null)<div class="an-ring"><div class="r" style="background:conic-gradient({{ $c }} {{ $v * 3.6 }}deg, var(--pv-surface-2) 0)"><span>{{ $v }}%</span></div><div><b>{{ $l }}</b><small>{{ $hint }}</small></div></div>@endif
                        @endforeach
                    </div>
                @endforeach
                <h3 style="font-size:1rem;font-weight:800;margin:14px 0 8px">Subject strength</h3>
                <table class="an-table"><thead><tr><th>Subject</th><th>Accuracy</th><th>Strength</th></tr></thead><tbody>
                    @foreach ($subjects as $s)<tr><td style="text-align:left">{{ $s->name }}</td><td>{{ $s->accuracy }}%</td><td class="an-st {{ $s->strength }}">{{ $s->strength }}</td></tr>@endforeach
                </tbody></table>
                <p style="font-size:.76rem;color:var(--pv-muted);margin:8px 0 0">Strong: 75%+ accuracy · Average: 50-74% · Weak: below 50% · Unidentified: fewer than 5 answers yet.</p>
            @else
                <div class="hb-empty">No section data yet.</div>
            @endif
        </div>
    @endif
</div></div></div>
<script type="application/json" id="anData">@json(['series' => $series, 'marks' => $marks])</script>
@endsection
@section('scripts')
@if ($count)
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    var D = JSON.parse(document.getElementById('anData').textContent), css = getComputedStyle(document.documentElement);
    var grid = css.getPropertyValue('--pv-border').trim(), txt = css.getPropertyValue('--pv-muted').trim();
    var scale = function (title, max) { return { x: { grid: { display: false }, ticks: { color: txt } }, y: { beginAtZero: true, max: max, grid: { color: grid }, ticks: { color: txt }, title: { display: true, text: title, color: txt } } }; };
    new Chart(document.getElementById('chMarks'), { type: 'line', data: { labels: D.marks.map(function (m) { return m.t; }), datasets: [{ data: D.marks.map(function (m) { return m.pct; }), borderColor: '#4f7d1b', backgroundColor: '#a7d04f', pointRadius: 4, tension: .25 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { title: function (i) { return D.marks[i[0].dataIndex].title; }, label: function (i) { return i.raw + '%'; } } } }, scales: scale('Marks %', 100) } });
    [].forEach.call(document.querySelectorAll('[data-chart]'), function (card) {
        var k = card.dataset.chart, chart = new Chart(card.querySelector('canvas'), { type: 'bar', data: { labels: [], datasets: [] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: scale(k === 'time' ? 'Minutes' : (k === 'att' ? 'Attempt %' : 'Accuracy %'), k === 'time' ? undefined : 100) } });
        function draw(lv) {
            var rows = D.series[lv] || [];
            chart.data.labels = rows.map(function (r) { return r.w; });
            chart.data.datasets = k === 'att'
                ? [{ data: rows.map(function (r) { return r.att; }), backgroundColor: '#1aa260', borderRadius: 6, maxBarThickness: 16 }, { data: rows.map(function (r) { return r.un; }), backgroundColor: '#e5484d', borderRadius: 6, maxBarThickness: 16 }]
                : [{ data: rows.map(function (r) { return r[k]; }), backgroundColor: k === 'acc' ? '#2f80ed' : '#a7d04f', borderRadius: 6, maxBarThickness: 22 }];
            chart.update();
            [].forEach.call(card.querySelectorAll('[data-lv]'), function (b) { b.setAttribute('aria-pressed', b.dataset.lv === lv); });
        }
        [].forEach.call(card.querySelectorAll('[data-lv]'), function (b) { b.addEventListener('click', function () { draw(b.dataset.lv); }); });
        draw('overall');
    });
    [].forEach.call(document.querySelectorAll('[data-subj]'), function (b) {
        b.addEventListener('click', function () {
            [].forEach.call(document.querySelectorAll('[data-subj]'), function (x) { var on = x === b; x.setAttribute('aria-selected', on); x.style.borderBottomColor = on ? 'var(--pv-brand)' : 'transparent'; document.querySelector('[data-subj-panel="' + x.dataset.subj + '"]').hidden = !on; });
        });
    });
})();
</script>
@endif
@endsection
