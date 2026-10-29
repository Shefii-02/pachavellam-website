@if (!empty($r->omr))
<div class="col-12 col-md-6"><div class="sr-card sr-xc" data-omr="{{ $r->omrId }}">
    <div class="bd">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted">{{ $r->date }}</span><span class="sr-chip {{ $r->tagc }}">{{ $r->tag }}</span>
        </div>
        <div class="fw-bold" style="line-height:1.3;min-height:2.6em">{{ \Illuminate\Support\Str::limit($r->title, 70) }}</div>
        <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
            <a class="sr-go" href="{{ $r->url }}">{{ $r->tagc === 'end' ? 'Practice' : ($r->tagc === 'up' ? 'Opens soon' : 'Attempt') }}</a>
            @if ($r->answerKey)<a class="sr-go off" href="{{ $r->answerKey }}"><i class="bi bi-key"></i> Answer key</a>@endif
            <span class="small text-muted ms-auto omr-last"></span>
        </div>
        <details class="omr-dr mt-2" data-w="mine"><summary><i class="bi bi-clock-history"></i> My attempts</summary><div class="omr-body small">Loading…</div></details>
        <button type="button" class="omr-lb-btn mt-2" data-lb="{{ $r->omrId }}" data-title="{{ $r->title }}"><i class="bi bi-trophy-fill"></i> Leaderboard <i class="bi bi-chevron-right ms-auto"></i></button>
    </div>
</div></div>
@else
{{-- One exam as a card (same look as the course cards). Needs $r (title, date, tag, tagc, url, done, paid, state, inPaid); optional $n (number), $lockUrl (where a locked exam sends the student). --}}
@php
    $locked = ($r->state ?? 'free') === 'locked';
    $href = $locked ? ($lockUrl ?? (auth()->check() ? $r->url : url('sign-in'))) : $r->url;
    if ($locked && !auth()->check()) { $href = url('sign-in'); }
@endphp
<div class="col-12 col-md-6"><a class="sr-card sr-xc {{ $locked ? 'lock' : '' }}" href="{{ $href }}">
    <div class="bd">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted">@isset($n)#{{ $n }} · @endisset{{ $r->date }}</span>
            @if ($locked)<span class="sr-chip lk"><i class="bi bi-lock-fill"></i> Paid</span>
            @elseif (($r->state ?? '') === 'unlocked')<span class="sr-chip unl">Unlocked</span>
            @elseif (($r->state ?? '') === 'free' && !empty($r->inPaid))<span class="sr-chip fr">Free exam</span>
            @else<span class="sr-chip {{ $r->tagc }}">{{ $r->tag }}</span>@endif
        </div>
        <div class="fw-bold" style="line-height:1.3;min-height:2.6em">{{ \Illuminate\Support\Str::limit($r->title, 70) }}@if ($r->paid)<span class="sr-paid">PAID</span>@endif</div>
        <div class="mt-2 text-end">
            @if ($locked)<span class="sr-go off">Unlock</span>
            @elseif ($r->done)<span class="sr-go done"><i class="bi bi-check2"></i> Attempted</span>
            @elseif ($r->tagc === 'up')<span class="sr-go off">Opens soon</span>
            @elseif ($r->tagc === 'end')<span class="sr-go off">Practice</span>
            @else<span class="sr-go">Attempt</span>@endif
        </div>
    </div>
</a></div>

@endif
