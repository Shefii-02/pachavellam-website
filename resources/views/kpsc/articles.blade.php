@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')@include('kpsc.partials.public-styles')@endsection
@section('content')
<div class="page-content-wrapper"><div class="container py-3">
    <h3 class="fw-bold mb-3">Free articles</h3>
    <div class="pg-chips" id="chips"><span class="pg-chip on" data-x="all">All exams</span>@foreach ($exams as $e)<span class="pg-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
    <div class="cc-grid" id="grid">
        @forelse ($articles as $a)
            @include('kpsc.partials.content-card', ['item' => $a, 'href' => url('articles/' . $a->id), 'icon' => 'bi-newspaper', 'excerpt' => true])
        @empty
            <div class="text-muted">No articles yet.</div>
        @endforelse
    </div>
</div></div>
@endsection
@section('scripts')
<script>
    document.getElementById('chips').addEventListener('click', function (e) {
        var c = e.target.closest('[data-x]'); if (!c) return;
        [].forEach.call(this.children, function (x) { x.classList.toggle('on', x === c); });
        [].forEach.call(document.querySelectorAll('#grid > a'), function (a) { a.style.display = (c.dataset.x === 'all' || (' ' + a.dataset.x + ' ').indexOf(' ' + c.dataset.x + ' ') >= 0) ? '' : 'none'; });
    });
</script>
@endsection
