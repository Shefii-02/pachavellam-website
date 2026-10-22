@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')@include('kpsc.partials.public-styles')@endsection
@section('content')
<div class="page-content-wrapper"><div class="container py-3">
    <h3 class="fw-bold mb-3">All stories</h3>
    <div class="pg-chips" id="chips"><span class="pg-chip on" data-x="0">All</span>@foreach ($exams as $e)<span class="pg-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
    <div class="row g-3" id="storyGrid"></div>
    @include('kpsc.partials.achievements', ['target' => 'storyGrid', 'mode' => 'grid', 'per' => 18])
</div></div>
@endsection
@section('scripts')
<script>
    document.getElementById('chips').addEventListener('click', function (e) {
        var c = e.target.closest('[data-x]'); if (!c) return;
        [].forEach.call(this.children, function (x) { x.classList.toggle('on', x === c); });
        document.getElementById('storyGrid').reload(Number(c.dataset.x));   // a stories with no exam type ticked belongs to every tab (the server does this)
    });
</script>
@endsection
