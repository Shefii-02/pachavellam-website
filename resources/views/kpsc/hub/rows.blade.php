{{-- A list of classes / tests / articles on the Learn tab. $rows: objects with title, sub, thumb, starts, url, tag (and mins for tests, date for articles). --}}
@if ($rows->count())
    <div class="hb-list">
        @foreach ($rows as $r)
            <a class="hb-row" href="{{ $r->url }}">
                <span class="th" @if ($r->thumb) style="background-image:url('{{ $r->thumb }}')" @endif>@unless ($r->thumb)<i class="bi {{ $icon }}"></i>@endunless</span>
                <span class="tx">
                    <b>{{ $r->title }}</b>
                    @if ($r->sub)<small>{{ $r->sub }}</small>@endif
                    <small>
                        @if ($r->tag)<span class="hb-tag">{{ $r->tag }}</span>@endif
                        @if (!empty($r->mins))<span>{{ $r->mins }} min</span> · @endif
                        @if (empty($r->date))<span class="when" data-start="{{ $r->starts ? $r->starts->getTimestamp() * 1000 : '' }}" @if (!empty($test)) data-test="1" @endif>{{ $r->starts ? $r->starts->format('d M, h:i a') : 'Open now' }}</span>@endif
                    </small>
                </span>
            </a>
        @endforeach
    </div>
    @if (!empty($more))<div class="mt-2 text-end"><a href="{{ url($more[0]) }}" class="small fw-bold" style="color:var(--pv-brand)">{{ $more[1] }} <i class="bi bi-arrow-right"></i></a></div>@endif
@elseif ($empty)
    <div class="hb-empty"><i class="bi bi-calendar-x"></i>{{ $empty }} @if (!empty($more))<a href="{{ url($more[0]) }}">{{ $more[1] }}</a>@endif</div>
@endif
