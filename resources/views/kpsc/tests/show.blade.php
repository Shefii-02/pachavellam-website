@extends('kpsc.tests.layout')
@section('title', $title)
@section('styles')
<style>
    body { background: var(--pv-bg); }
    .in-wrap { max-width: 860px; margin: 0 auto; padding: 0 0 110px; }
    .in-head { padding: 18px 16px; border-bottom: 1px solid var(--pv-border); background: var(--pv-surface); }
    .in-head h1 { font-size: 1.25rem; font-weight: 800; margin: 0 0 14px; color: var(--pv-text); line-height: 1.35; }
    .in-chips { display: flex; gap: 14px; flex-wrap: wrap; } .in-chips div { text-align: center; } .in-chips b { display: block; background: var(--pv-surface-2); border-radius: 10px; padding: 6px 18px; font-size: .95rem; color: var(--pv-text); }
    .in-chips small { color: var(--pv-muted); font-size: .76rem; }
    .in-body { padding: 16px; color: var(--pv-text); font-size: .95rem; line-height: 1.6; }
    .in-body h2 { font-size: 1rem; font-weight: 800; margin: 0 0 10px; }
    .in-body ol { padding-left: 20px; } .in-body li { margin-bottom: 4px; }
    .in-note { border-radius: 12px; padding: 12px 14px; margin: 12px 16px 0; font-size: .9rem; font-weight: 600; background: var(--pv-brand-soft); color: var(--pv-text); }
    .in-foot { position: fixed; left: 0; right: 0; bottom: 0; background: var(--pv-surface); border-top: 1px solid var(--pv-border); padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); z-index: 10; }
    .in-foot .row { max-width: 860px; margin: 0 auto; display: flex; gap: 10px; align-items: center; }
    .in-foot select { border: 1px solid var(--pv-border); background: var(--pv-surface); color: var(--pv-text); border-radius: 10px; padding: 11px 12px; font-weight: 600; }
    .in-foot .t-btn { flex: 1; }
    .in-prev a { display: flex; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid var(--pv-border); border-radius: 12px; margin-bottom: 8px; color: var(--pv-text); text-decoration: none; background: var(--pv-surface); }
</style>
@endsection
@section('content')
@php
    $right = $paper['sections'][0]['right'] ?? (float) $exam->right_mark;
    $neg = $paper['sections'][0]['neg'] ?? (float) $exam->negative_mark;
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $langNames = ['en' => 'English', 'ml' => 'മലയാളം', 'hi' => 'हिन्दी', 'ta' => 'தமிழ்', 'kn' => 'ಕನ್ನಡ'];
    $sectional = $paper['timing'] === 'sectional';
@endphp
<div class="t-top">
    <a class="t-ibtn" href="{{ url()->previous() !== url()->current() ? url()->previous() : url('my-tests') }}" aria-label="Back"><i class="bi bi-arrow-left"></i></a>
    <b style="color:var(--pv-text)">Instructions</b>
</div>
<div class="in-wrap">
    <div class="in-head">
        <h1>{{ $exam->title }}</h1>
        <div class="in-chips">
            <div><b>{{ $paper['count'] }}</b><small>Total questions</small></div>
            <div><b>{{ $fmt($paper['max']) }}</b><small>Maximum marks</small></div>
            <div><b>{{ $paper['minutes'] ? $paper['minutes'] . ' mins' : 'No limit' }}</b><small>Duration</small></div>
        </div>
    </div>

    @if (session('test_msg'))<div class="in-note">{{ session('test_msg') }}</div>@endif
    @if (!$isOpen && $exam->start_at)<div class="in-note"><i class="bi bi-clock"></i> This test opens on {{ $exam->start_at->format('d M Y, h:i a') }}.</div>@endif
    @if ($exam->end_at && $exam->end_at->isPast())<div class="in-note"><i class="bi bi-info-circle"></i> The live window ended on {{ $exam->end_at->format('d M Y') }} - you can still practise it.</div>@endif

    <div class="in-body">
        @if (count($paper['sections']) > 1)
            <div style="overflow-x:auto;margin-bottom:16px">
                <table class="t-table">
                    <thead><tr><th>#</th><th>Subject</th><th>Questions</th><th>Marks</th>@if ($sectional)<th>Time</th>@endif</tr></thead>
                    <tbody>
                        @foreach ($paper['sections'] as $i => $s)
                            <tr><td>{{ $i + 1 }}</td><td>{{ $s['title'] }}</td><td>{{ count($s['questions']) }}</td><td>{{ $fmt(count($s['questions']) * $s['right']) }}</td>@if ($sectional)<td>{{ $s['minutes'] }} min</td>@endif</tr>
                        @endforeach
                        <tr><td></td><td>Total</td><td>{{ $paper['count'] }}</td><td>{{ $fmt($paper['max']) }}</td>@if ($sectional)<td>{{ $paper['minutes'] }} min</td>@endif</tr>
                    </tbody>
                </table>
            </div>
        @endif

        <h2>Please read the following instructions carefully:</h2>
        <ol>
            @if ($paper['minutes'])<li>You have <b>{{ $paper['minutes'] }} minutes</b> to complete the test.</li>@endif
            <li>The test contains a total of <b>{{ $paper['count'] }} questions</b> and <b>{{ $fmt($paper['max']) }} marks</b>.</li>
            @if ($sectional)<li>Each section has its own time. When a section's time is over, the next section opens; <b>you cannot go back to a finished section</b>. You can also submit a section early.</li>@endif
            <li>You get <b>{{ $fmt($right) }}</b> mark{{ $right == 1 ? '' : 's' }} for each correct answer.</li>
            @if ($neg > 0)<li>There is a <b>{{ $fmt($neg) }}</b> penalty for each wrong answer. Unanswered questions get no penalty.</li>@else<li>There is no negative marking.</li>@endif
            <li>Choose an option to answer; tap another option to change it, or press <b>Clear Response</b> to remove your answer.</li>
            <li>Use <b>Mark for Review</b> to come back to a question. A marked question that has an answer is still evaluated.</li>
            <li>Open the question list (<i class="bi bi-grid-3x3-gap"></i>) to jump to any question{{ $sectional ? ' of the open section' : '' }}.</li>
            <li>You can <b>pause</b> the test and resume it later - the timer stops while the test is paused or closed.</li>
            <li>Do not press <b>Submit Test</b> before you finish. A submitted test cannot be resumed.</li>
        </ol>
        @if (\App\Support\SafeHtml::text($exam->instruction) !== '')<div class="mt-2">{!! \App\Support\SafeHtml::clean($exam->instruction) !!}</div>@endif

        @if (!empty($summary))
            @include('kpsc.partials.exam-summary', ['summary' => $summary])
        @endif
    </div>
</div>

<div class="in-foot"><form class="row" method="POST" action="{{ url('tests/' . $exam->id . '/start') }}">
    @csrf
    @if (count($paper['langs']) > 1)
        <label class="sr-only" for="lang">Language</label>
        <select id="lang" name="lang">@foreach ($paper['langs'] as $l)<option value="{{ $l }}" @selected(($open->lang ?? null) === $l)>{{ $langNames[$l] ?? strtoupper($l) }}</option>@endforeach</select>
    @endif
    @guest
        <a class="t-btn pri" href="{{ url('tests/' . $exam->id . '/sign-in') }}"><i class="bi bi-box-arrow-in-right"></i> Sign in to start</a>
    @else
        @if (!$access['ok'])
            <a class="t-btn pri" href="{{ $access['buy'] }}"><i class="bi bi-lock-fill"></i> Unlock this test</a>
        @elseif (!$isOpen)
            <button class="t-btn" type="button" disabled>Opens {{ $exam->start_at->format('d M, h:i a') }}</button>
        @elseif ($open)
            <button class="t-btn pri"><i class="bi bi-play-fill"></i> Resume Test</button>
        @elseif ($left === 0)
            <button class="t-btn" type="button" disabled>No attempts left</button>
        @else
            <button class="t-btn pri"><i class="bi bi-play-fill"></i> {{ $done->count() ? 'Re-attempt' : 'Start Test' }}</button>
        @endif
    @endguest
</form></div>
@endsection
