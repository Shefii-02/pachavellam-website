{{-- One exam or class of a bought course. Needs $r, $kind (exam | classes), $state (live | upcoming | done). --}}
@if ($kind === 'classes')
    <div class="mc-row">
        <span class="mc-thumb" @if ($r->thumb) style="background-image:url('{{ $r->thumb }}')" @endif>@unless ($r->thumb)<i class="bi bi-play-fill"></i>@endunless</span>
        <span class="mc-t"><b>{{ \Illuminate\Support\Str::limit($r->title, 80) }}</b><small>{{ $r->when }}@if ($r->sub) · {{ $r->sub }}@endif</small></span>
        @if ($state === 'live')<span class="mc-chip live">Today</span>@elseif ($state === 'upcoming')<span class="mc-chip up">Upcoming</span>@else<span class="mc-chip done">Recorded</span>@endif
        @if ($r->locked)<span class="mc-go off"><i class="bi bi-lock-fill"></i> Locked</span>
        @elseif ($r->embed)<button type="button" class="mc-go" data-embed="{{ $r->embed }}"><i class="bi bi-play-fill"></i> Watch</button>
        @elseif ($r->url)<a class="mc-go" href="{{ $r->url }}" target="_blank" rel="noopener"><i class="bi bi-play-fill"></i> Watch</a>
        @else<span class="mc-go off">Soon</span>@endif
    </div>
@else
    <div class="mc-row">
        <span class="mc-ic"><i class="bi bi-pencil-square"></i></span>
        <span class="mc-t"><b>{{ \Illuminate\Support\Str::limit($r->title, 80) }}</b><small>{{ $r->when }}@if ($r->sub) · {{ $r->sub }}@endif @if ($r->opens) · {{ $r->opens }}@endif</small></span>
        @if ($state === 'live')<span class="mc-chip live">Live now</span>@elseif ($state === 'upcoming')<span class="mc-chip up">Upcoming</span>@else<span class="mc-chip done">Completed</span>@endif
        @if ($r->url && $state !== 'upcoming')<a class="mc-go" href="{{ $r->url }}">{{ $state === 'done' ? 'Practice' : 'Attempt' }}</a>
        @elseif ($r->app && $state !== 'upcoming' && !empty($app ?? null))<a class="mc-go" href="{{ $app }}" target="_blank" rel="noopener">Attempt in app</a>
        @elseif ($state === 'upcoming')<span class="mc-go off">Opens soon</span>
        @else<span class="mc-go off">In the app</span>@endif
    </div>
@endif
