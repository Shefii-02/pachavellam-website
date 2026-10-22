@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .mt-row { display:flex; align-items:center; gap:12px; padding:14px 2px; border-bottom:1px solid var(--pv-border); color:var(--pv-text); }
        .mt-row[hidden] { display:none; }
        .mt-row .tx { flex:1; min-width:0; } .mt-row .tx b { display:block; line-height:1.35; font-size:.98rem; }
        .mt-row .tx small { color:var(--pv-muted); font-size:.8rem; }
        .mt-lvl { display:inline-block; font-size:.7rem; font-weight:700; border-radius:6px; padding:1px 8px; margin-bottom:4px; background:color-mix(in srgb, #1aa260 14%, var(--pv-surface)); color:#1aa260; }
        .mt-lvl.moderate { background:color-mix(in srgb, #f5b301 16%, var(--pv-surface)); color:#b07d00; } .mt-lvl.difficult, .mt-lvl.advanced { background:color-mix(in srgb, #e5484d 12%, var(--pv-surface)); color:#e5484d; }
        .mt-act { width:62px; flex:none; text-align:center; text-decoration:none !important; font-size:.62rem; font-weight:700; letter-spacing:.04em; color:var(--pv-muted); }
        .mt-act i { display:flex; align-items:center; justify-content:center; width:40px; height:40px; margin:0 auto 3px; border-radius:9px; font-size:1.3rem; color:#fff; }
        .mt-search { width:100%; border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-text); border-radius:12px; padding:10px 14px; margin-bottom:10px; }
    </style>
@endsection
@section('content')
@php $q = fn (array $p) => url('my-tests') . '?' . http_build_query(array_filter($p + ['exam' => $examType?->slug, 'tab' => $tab], fn ($v) => $v && $v !== 'all')); @endphp
<div class="page-content-wrapper"><div class="container"><div class="hb-wrap" style="max-width:820px">
    <div class="hb-top"><div><h1>My tests</h1><p>Every test you can take, and where you left off.</p></div><a class="hb-btn ghost sm" href="{{ url('my-tests/analysis') }}"><i class="bi bi-graph-up"></i> Analysis</a></div>

    <div class="hb-chips" aria-label="Exam">
        <a class="hb-chip {{ !$examType ? 'on' : '' }}" href="{{ $q(['exam' => null]) }}">My exams</a>
        @foreach ($exams as $e)<a class="hb-chip {{ $examType && $examType->id === $e->id ? 'on' : '' }}" href="{{ $q(['exam' => $e->slug]) }}">{{ $e->short_name ?: $e->name }}</a>@endforeach
    </div>
    <div class="hb-chips" aria-label="Status">
        @foreach (['all' => 'All Tests', 'completed' => 'Completed', 'incomplete' => 'Incomplete', 'unattempted' => 'Unattempted'] as $k => $l)
            <a class="hb-chip {{ $tab === $k ? 'on' : '' }}" href="{{ $q(['tab' => $k]) }}">{{ $l }}<span class="n">{{ $counts[$k] }}</span></a>
        @endforeach
    </div>
    <input class="mt-search" type="search" id="mtSearch" placeholder="Search tests" aria-label="Search tests">

    @forelse ($tests as $t)
        @php
            $mins = $t->duration_minutes ?: null;
            [$label, $icon, $color, $href] = $t->state === 'completed' ? ['RESULT', 'bi-check-lg', '#1aa260', url('tests/attempt/' . $t->last->id . '/result')]
                : ($t->state === 'incomplete' ? ['RESUME', 'bi-pause-fill', '#f5b301', url('tests/' . $t->id)]
                : (!$t->access['ok'] ? ['UNLOCK', 'bi-lock-fill', '#66755d', $t->access['buy']]
                : ($t->start_at && $t->start_at->isFuture() ? ['UPCOMING', 'bi-clock', '#2f80ed', url('tests/' . $t->id)] : ['START', 'bi-play-fill', 'var(--pv-text)', url('tests/' . $t->id)])));
        @endphp
        <div class="mt-row" data-name="{{ strtolower($t->title) }}">
            <div class="tx">
                <span class="mt-lvl {{ $t->mode }}">{{ \App\Models\CourseExam::MODES[$t->mode] ?? 'Moderate' }}</span>
                <b>{{ $t->title }}</b>
                <small>{{ $t->links_count }} Ques{{ $mins ? ' • ' . $mins . ' Mins' : '' }}{{ $t->feature ? ' • ' . $t->feature->name : '' }}{{ $t->last ? ' • Scored ' . rtrim(rtrim(number_format($t->last->score, 2), '0'), '.') . '/' . rtrim(rtrim(number_format($t->last->max_score, 2), '0'), '.') : '' }}{{ $t->start_at && $t->start_at->isFuture() ? ' • Opens ' . $t->start_at->format('d M, h:i a') : '' }}</small>
            </div>
            <a class="mt-act" href="{{ $href }}"><i style="background:{{ $color }}" class="bi {{ $icon }}"></i>{{ $label }}</a>
        </div>
    @empty
        <div class="hb-empty"><i class="bi bi-ui-checks"></i>No tests here yet. <a href="{{ url('exams') }}">Choose your exams</a> to see their tests.</div>
    @endforelse
</div></div></div>
@endsection
@section('scripts')
<script>
    document.getElementById('mtSearch').addEventListener('input', function () {
        var v = this.value.trim().toLowerCase();
        [].forEach.call(document.querySelectorAll('.mt-row'), function (r) { r.hidden = v && r.dataset.name.indexOf(v) < 0; });
    });
</script>
@endsection
