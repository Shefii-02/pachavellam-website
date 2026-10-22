@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-3" style="max-width:820px">
    <a href="{{ url('articles') }}" class="small"><i class="bi bi-arrow-left"></i> Free articles</a>
    @if ($a->image)<img src="{{ Storage::url('files/' . $a->image) }}" class="w-100 rounded-4 my-3" alt="">@endif
    <div class="mb-1">@foreach ($a->exams as $e)<span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span> @endforeach</div>
    <h2 class="fw-bold">{{ $a->title }}</h2>
    @if ($a->published_at)<div class="text-muted small mb-3">{{ $a->published_at->format('d M Y') }}</div>@endif
    <div class="art-body" style="line-height:1.7;overflow-wrap:anywhere">{!! $a->descriptionHtml() !!}</div>
    <style>.art-body img{max-width:100%;height:auto;border-radius:10px}.art-body table{max-width:100%;display:block;overflow-x:auto}</style>
    @if ($a->link_url)<a href="{{ $a->link_url }}" class="btn btn-outline-dark rounded-pill mt-3" target="_blank" rel="noopener">Read more</a>@endif
</div></div>
@endsection
