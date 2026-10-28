@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')@include('kpsc.partials.public-styles')@endsection
@section('content')
<div class="page-content-wrapper"><div class="container py-3">
    @php $q = fn ($extra = []) => url('articles' . ($extra + ($exam ? ['exam' => $exam->slug] : []) ? '?' . http_build_query($extra + ($exam ? ['exam' => $exam->slug] : [])) : '')); @endphp
    <h3 class="fw-bold mb-3">Free articles{{ $exam ? ' - ' . ($exam->short_name ?: $exam->name) : '' }}</h3>
    @if ($current)
        <div class="small mb-3"><a href="{{ $q() }}"><i class="bi bi-house"></i> All</a>@foreach ($trail as $t) <span class="text-muted">/</span> @if ($loop->last)<b>{{ $t->title }}</b>@else<a href="{{ $q(['folder' => $t->id]) }}">{{ $t->title }}</a>@endif @endforeach</div>
    @endif
    <div class="pg-chips"><a class="pg-chip {{ !$exam ? 'on' : '' }}" href="{{ url('articles') }}" style="text-decoration:none">All exams</a>@foreach ($exams as $e)<a class="pg-chip {{ $exam && $exam->id === $e->id ? 'on' : '' }}" href="{{ url('articles?exam=' . $e->slug) }}" style="text-decoration:none">{{ $e->short_name ?: $e->name }}</a>@endforeach</div>
    @if ($folders->count())
        <div class="row g-3 mb-3">
            @foreach ($folders as $f)
                <div class="col-6 col-md-4 col-lg-3"><a href="{{ $q(['folder' => $f->id]) }}" class="d-flex align-items-center gap-2 p-3 rounded-4 bg-white shadow-sm text-decoration-none text-dark">
                    <i class="bi bi-folder-fill" style="font-size:1.8rem;color:#f5b301"></i><div style="min-width:0"><div class="fw-bold text-truncate">{{ $f->title }}</div><div class="small text-muted">{{ $f->items_count }} article{{ $f->items_count == 1 ? '' : 's' }}</div></div></a></div>
            @endforeach
        </div>
    @endif
    @if ($articles->currentPage() === 1)@include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'articles'])@endif
    <div class="cc-grid" id="grid">
        @forelse ($articles as $a)
            @include('kpsc.partials.content-card', ['item' => $a, 'href' => url('articles/' . $a->id), 'icon' => 'bi-newspaper', 'excerpt' => true])
            @include('partials.ad-slot', ['type' => 'web_infeed', 'page' => 'articles', 'pos' => $loop->iteration, 'pageNo' => $articles->currentPage()])
        @empty
            @if (!$folders->count())<div class="text-muted">No articles here yet.</div>@endif
        @endforelse
    </div>
    @include('kpsc.partials.load-more', ['paginator' => $articles, 'target' => '#grid'])
</div></div>
@endsection
