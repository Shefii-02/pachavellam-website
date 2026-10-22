@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-3" style="max-width:820px">
    <a href="{{ url('announcements') }}" class="small"><i class="bi bi-arrow-left"></i> Announcements</a>
    @if ($a->image)<img src="{{ Storage::url('files/' . $a->image) }}" class="w-100 rounded-4 my-3" alt="">@endif
    <div class="mb-1 mt-2">@foreach ($a->exams as $e)<span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span> @endforeach</div>
    <h2 class="fw-bold">{{ $a->title }}</h2>
    <div class="text-muted small mb-3"><i class="bi bi-calendar3"></i> {{ optional($a->published_at ?? $a->created_at)->format('d M Y') }}</div>
    <div class="art-body" style="line-height:1.7;overflow-wrap:anywhere">{!! $a->descriptionHtml() !!}</div>
    <div class="d-flex flex-wrap gap-2 mt-3">
        @if ($a->landing_url)<a href="{{ $a->landing_url }}" class="btn btn-dark rounded-pill" target="_blank" rel="noopener">Open link</a>@endif
        @if ($a->attachment)<a href="{{ Storage::url('files/' . $a->attachment) }}" class="btn btn-outline-dark rounded-pill" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> Attachment</a>@endif
    </div>
</div></div>
@endsection
