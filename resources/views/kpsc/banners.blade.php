@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-3">
    <h3 class="fw-bold mb-3">All banners</h3>
    <div class="row g-3">
        @forelse ($banners as $b)
            <div class="col-12 col-md-6 col-lg-4"><a href="{{ $b->landing_url ?: '#' }}" target="_blank" rel="noopener"><img src="{{ Storage::url('files/' . $b->image) }}" class="w-100 rounded-4" style="box-shadow:0 3px 12px rgba(0,0,0,.15)" alt="{{ $b->title }}" loading="lazy"></a></div>
        @empty
            <div class="text-muted">No banners right now.</div>
        @endforelse
    </div>
</div></div>
@endsection
