@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')@include('kpsc.partials.public-styles')@endsection
@section('content')
@php $ex = $cat->exam; @endphp
<div class="page-content-wrapper"><div class="container py-3">
    <div class="small mb-2"><a href="{{ url($ex->slug) }}">{{ $ex->short_name ?: $ex->name }}</a>@foreach ($trail as $t) <span class="text-muted">/</span> @if ($loop->last)<b>{{ $t->name }}</b>@else<a href="{{ $t->publicUrl() }}">{{ $t->name }}</a>@endif @endforeach</div>
    <h3 class="fw-bold mb-1">{{ $cat->name }}</h3>
    @if (trim(strip_tags((string) $cat->description)) !== '')<p class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags((string) $cat->description), 220) }}</p>@endif

    @if ($children->count())
        <div class="row g-3 mb-3">@foreach ($children as $ch)<div class="col-6 col-md-4 col-lg-3"><a href="{{ $ch->publicUrl() }}" class="d-flex align-items-center gap-2 p-3 rounded-4 bg-white shadow-sm text-decoration-none text-dark"><i class="bi bi-folder-fill" style="font-size:1.6rem;color:#f5b301"></i><b class="text-truncate">{{ $ch->name }}</b></a></div>@endforeach</div>
    @endif

    @include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'category'])
    {{-- sections with nothing in them are not shown --}}
    @if ($videos->count())
        <h5 class="mt-4">Free videos</h5>
        <div class="row g-3">@foreach ($videos as $c)@php $vid = \App\Services\ClassSource::youtubeId((string) $c->link_url); @endphp
            <div class="col-6 col-md-4 col-lg-3"><a href="{{ $c->publicUrl() }}" class="text-decoration-none text-dark d-block bg-white rounded-4 shadow-sm overflow-hidden h-100"><div style="aspect-ratio:16/9;background:#10243e center/cover url('{{ $c->image ? Storage::url('files/' . $c->image) : ($vid ? 'https://img.youtube.com/vi/' . $vid . '/hqdefault.jpg' : '') }}')"></div><div class="p-2 fw-semibold small">{{ $c->title }}</div></a></div>
        @endforeach</div>
    @endif
    @if ($articles->count())
        <h5 class="mt-4">Articles</h5>
        <div class="cc-grid">@foreach ($articles as $a)@include('kpsc.partials.content-card', ['item' => $a, 'href' => url('articles/' . $a->id), 'icon' => 'bi-newspaper', 'excerpt' => true])@endforeach</div>
    @endif
    @if ($courses->count())
        <h5 class="mt-4">Courses</h5>
        <div class="row g-3">@foreach ($courses as $co)<div class="col-12 col-md-6 col-lg-4"><a href="{{ url('courses/' . ($co->slug ?: $co->id)) }}" class="d-flex justify-content-between align-items-center p-3 bg-white rounded-4 shadow-sm text-decoration-none text-dark"><b>{{ $co->title }}</b><span class="text-success fw-bold ms-2">{{ $co->isFree() ? 'Free' : '₹' . rtrim(rtrim(number_format($co->effectivePrice(), 2), '0'), '.') }}</span></a></div>@endforeach</div>
    @endif
    @if (!$videos->count() && !$articles->count() && !$courses->count() && !$children->count())
        <div class="text-muted mt-3">Nothing has been added to this category yet.</div>
    @endif
</div></div>
@endsection
