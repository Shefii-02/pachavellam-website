@if (!empty($r->omr))
@php
    $omrLocked = ($r->state ?? 'free') === 'locked';
    $omrHref = $omrLocked ? ($lockUrl ?? (auth()->check() ? $r->url : url('sign-in'))) : $r->url;
    if ($omrLocked && !auth()->check()) { $omrHref = url('sign-in'); }
@endphp
<div class="col-12 col-md-6"><div class="sr-card sr-xc" @if ($r->tagc !== 'up') data-omr="{{ $r->omrId }}" @endif>   {{-- an upcoming exam has no attempts or leaderboard to load --}}
    <div class="bd">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted">{{ $r->date }}</span><span class="sr-chip {{ $r->tagc }}">{{ $r->tag }}</span>
        </div>
        <div class="fw-bold" style="line-height:1.3;min-height:2.6em">{{ \Illuminate\Support\Str::limit($r->title, 70) }}</div>
        @include('kpsc.partials.series-card-dates', ['r' => $r])
        <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
            @if ($r->tagc === 'up')
                <span class="sr-go off">Opens soon</span>
            @else
            <a class="sr-go {{ $omrLocked ? 'off' : '' }}" href="{{ $omrHref }}">{{ $omrLocked ? 'Unlock' : ($r->tagc === 'end' ? 'Practice' : 'Attempt') }}</a>
            {{-- answer key and upload marks are inside the exam page (tabs), so they are not repeated on the card --}}
            <span class="small text-muted ms-auto omr-last"></span>
            @endif
        </div>
        @if ($r->tagc !== 'up')
        <details class="omr-dr mt-2" data-w="mine"><summary><i class="bi bi-clock-history"></i> My attempts</summary><div class="omr-body small">Loading…</div></details>
        <button type="button" class="omr-lb-btn mt-2" data-lb="{{ $r->omrId }}" data-title="{{ $r->title }}"><i class="bi bi-trophy-fill"></i> Leaderboard <i class="bi bi-chevron-right ms-auto"></i></button>
        @endif
    </div>
</div></div>
@else
{{-- One exam as a card (same look as the course cards). Needs $r (title, date, tag, tagc, url, done, paid, state, inPaid); optional $n (number), $lockUrl (where a locked exam sends the student). --}}
@php
    $locked = ($r->state ?? 'free') === 'locked';
    $href = $locked ? ($lockUrl ?? (auth()->check() ? $r->url : url('sign-in'))) : $r->url;
    if ($locked && !auth()->check()) { $href = url('sign-in'); }
@endphp
<div class="col-12 col-md-6"><div class="sr-card sr-xc {{ $locked ? 'lock' : '' }}" @if (!empty($r->dailyId) && !$locked && $r->tagc !== 'up') data-omr="{{ $r->dailyId }}" data-sum="{{ url('kpsc/daily-exam-summary') }}" @endif>
    <div class="bd">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted">@isset($n)#{{ $n }} · @endisset{{ $r->date }}</span>
            @if ($locked)<span class="sr-chip lk"><i class="bi bi-lock-fill"></i> Paid</span>
            @elseif (($r->state ?? '') === 'unlocked')<span class="sr-chip unl">Unlocked</span>
            @elseif (($r->state ?? '') === 'free' && !empty($r->inPaid))<span class="sr-chip fr">Free exam</span>
            @else<span class="sr-chip {{ $r->tagc }}">{{ $r->tag }}</span>@endif
        </div>
        <div class="fw-bold" style="line-height:1.3;min-height:2.6em">{{ \Illuminate\Support\Str::limit($r->title, 70) }}@if ($r->paid)<span class="sr-paid">PAID</span>@endif</div>
        @include('kpsc.partials.series-card-dates', ['r' => $r])
        <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
            @if ($locked)<a class="sr-go off" href="{{ $href }}">Unlock</a>
            @elseif ($r->tagc === 'up')<span class="sr-go off">Opens soon</span>
            @elseif ($r->done)<a class="sr-go done" href="{{ $href }}"><i class="bi bi-check2"></i> Attempted</a>
            @elseif ($r->tagc === 'end')<a class="sr-go off" href="{{ $href }}">Practice</a>
            @else<a class="sr-go" href="{{ $href }}">Attempt</a>@endif
            @if (!empty($r->dailyId) && !$locked && $r->tagc !== 'up')<span class="small text-muted ms-auto omr-last"></span>@endif
        </div>
        @if (!empty($r->dailyId) && !$locked && $r->tagc !== 'up')
        <details class="omr-dr mt-2" data-w="mine"><summary><i class="bi bi-clock-history"></i> My attempts</summary><div class="omr-body small">Loading…</div></details>
        <button type="button" class="omr-lb-btn mt-2" data-lb="{{ $r->dailyId }}" data-lb-base="{{ url('kpsc/daily-exam-leaderboard-data') }}" data-title="{{ $r->title }}"><i class="bi bi-trophy-fill"></i> Leaderboard <i class="bi bi-chevron-right ms-auto"></i></button>
        @endif
    </div>
</div></div>

@endif
