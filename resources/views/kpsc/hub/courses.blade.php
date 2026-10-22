@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
@endsection
@section('content')
@php
    $q = fn (array $p) => url('courses') . (($p = array_filter($p + ['exam' => $exam?->slug, 'price' => $price], fn ($v) => $v !== null && $v !== 'all')) ? '?' . http_build_query($p) : '');
@endphp
<div class="page-content-wrapper"><div class="container"><div class="hb-wrap">
    <div class="hb-top"><div><h1>Courses</h1><p>Free and Prime courses for every exam.</p></div><a href="{{ url('prime') }}" class="hb-btn ghost sm"><i class="bi bi-stars"></i> About Prime</a></div>

    @if (session('message'))<div class="hb-note ok"><i class="bi bi-check-circle-fill"></i><span class="sp">{{ session('message') }}</span></div>@endif

    {{-- enrolled courses first --}}
    @auth
        @if ($admissions->count())
            <section class="hb-sec">
                <h2>My courses <span class="ct">{{ $admissions->count() }}</span></h2>
                <div class="hb-grid">
                    @foreach ($admissions as $a)
                        @php $c = $a->course; $left = $a->valid_until ? max(0, (int) now()->diffInDays($a->valid_until, false)) : null; $total = $a->valid_until && $a->admitted_at ? max(1, $a->admitted_at->diffInDays($a->valid_until)) : null; @endphp
                        <div class="hb-course">
                            <a class="cv" href="{{ url('my-courses/' . $c->id) }}" @if ($c->cover_image) style="background-image:url('{{ Storage::url($c->cover_image) }}')" @endif>
                                @if ($c->exam)<span class="ex" style="background:{{ $c->exam->color ?: '#4f7d1b' }}">{{ $c->exam->short_name ?: $c->exam->name }}</span>@endif
                                <span class="bdg"><i class="bi bi-check2-circle"></i> Enrolled</span>
                            </a>
                            <div class="bd">
                                <a class="tt" href="{{ url('my-courses/' . $c->id) }}">{{ $c->title }}</a>
                                @if ($left !== null)
                                    <div class="mt">{{ $left }} days left · valid till {{ $a->valid_until->format('d M Y') }}</div>
                                    <div class="hb-bar" aria-hidden="true"><i style="width:{{ $total ? min(100, round($left / $total * 100)) : 100 }}%"></i></div>
                                @else
                                    <div class="mt">Lifetime access</div>
                                @endif
                                <div class="act"><a class="hb-btn" href="{{ url('my-courses/' . $c->id) }}"><i class="bi bi-play-fill"></i> Continue</a><a class="hb-btn ghost" href="{{ url('learn') }}">Upcoming</a></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @else
        <div class="hb-note"><i class="bi bi-person-check"></i><span class="sp">Sign in to see the courses you have joined.</span><a class="hb-btn sm" href="{{ url('sign-in') }}">Sign in</a></div>
    @endauth

    <section class="hb-sec" id="all">
        <h2>{{ $admissions->count() ? 'More courses' : 'All courses' }} <span class="ct">{{ $courses->count() }}</span></h2>
        <div class="hb-chips" aria-label="Exam">
            <a class="hb-chip {{ !$exam ? 'on' : '' }}" href="{{ $q(['exam' => 'all']) }}">All exams</a>
            @foreach ($exams as $e)<a class="hb-chip {{ $exam && $exam->id === $e->id ? 'on' : '' }}" href="{{ $q(['exam' => $e->slug]) }}">{{ $e->short_name ?: $e->name }}</a>@endforeach
        </div>
        <div class="hb-chips" aria-label="Price">
            @foreach (['all' => 'All', 'free' => 'Free', 'paid' => 'Paid'] as $k => $l)<a class="hb-chip {{ ($price ?? 'all') === $k ? 'on' : '' }}" href="{{ $q(['price' => $k]) }}">{{ $l }}</a>@endforeach
        </div>
        @if ($courses->count())
            <div class="hb-grid">
                @foreach ($courses as $c)
                    @php $p = $c->effectivePrice(); @endphp
                    <div class="hb-course">
                        <a class="cv" href="{{ url('courses/' . ($c->slug ?: $c->id)) }}" @if ($c->cover_image) style="background-image:url('{{ Storage::url($c->cover_image) }}')" @endif>
                            @if ($c->exam)<span class="ex" style="background:{{ $c->exam->color ?: '#4f7d1b' }}">{{ $c->exam->short_name ?: $c->exam->name }}</span>@endif
                            @if ($c->isFree())<span class="bdg">FREE</span>@elseif ($c->is_featured)<span class="bdg">POPULAR</span>@endif
                        </a>
                        <div class="bd">
                            <a class="tt" href="{{ url('courses/' . ($c->slug ?: $c->id)) }}">{{ $c->title }}</a>
                            <div class="mt">{{ $c->validityLabel() }}@if ($c->duration_hours) · {{ $c->duration_hours }} hours @endif</div>
                            <div class="pr">@if ($c->isFree()) Free @else ₹{{ number_format($p) }}@if ($p < $c->price)<s>₹{{ number_format($c->price) }}</s>@endif @endif</div>
                            <div class="act">
                                <a class="hb-btn ghost" href="{{ url('courses/' . ($c->slug ?: $c->id)) }}">Details</a>
                                <a class="hb-btn" href="{{ url('prime/buy/' . $c->id) }}">@if ($c->isFree())Join free @elseif (auth()->check())Buy now @else<i class="bi bi-lock-fill"></i> Buy @endif</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="hb-empty"><i class="bi bi-journal-x"></i>No {{ $price ?? '' }} course {{ $exam ? 'for ' . ($exam->short_name ?: $exam->name) : '' }} yet. <a href="{{ url('courses') }}">See all courses</a></div>
        @endif
    </section>
</div></div></div>
@endsection
