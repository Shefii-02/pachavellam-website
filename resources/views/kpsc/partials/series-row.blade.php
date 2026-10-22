{{-- One exam of a series: title, date, state and the attempt button. Needs $r (title, date, tag, tagc, url, done, paid, state). --}}
@php $locked = ($r->state ?? 'free') === 'locked'; @endphp
<a class="sr-row {{ $locked ? 'lock' : '' }}" href="{{ $locked && !auth()->check() ? url('sign-in') : $r->url }}">
    <span class="t"><b>{{ \Illuminate\Support\Str::limit($r->title, 70) }}@if ($r->paid)<span class="sr-paid">PAID</span>@endif</b><small>{{ $r->date }}</small></span>
    @if ($locked)
        <span class="sr-chip lk"><i class="bi bi-lock-fill"></i> Paid</span><span class="sr-go off">Unlock</span>
    @else
        @if (($r->state ?? '') === 'unlocked')<span class="sr-chip unl">Unlocked</span>@elseif (($r->state ?? '') === 'free' && !empty($r->inPaid))<span class="sr-chip fr">Free exam</span>@else<span class="sr-chip {{ $r->tagc }}">{{ $r->tag }}</span>@endif
        @if ($r->done)<span class="sr-go done"><i class="bi bi-check2"></i> Attempted</span>
        @elseif ($r->tagc === 'up')<span class="sr-go off">Opens soon</span>
        @elseif ($r->tagc === 'end')<span class="sr-go off">Practice</span>
        @else<span class="sr-go">Attempt</span>@endif
    @endif
</a>
