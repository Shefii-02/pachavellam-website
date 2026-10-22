@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
<style>
    .fc-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; margin-bottom:14px; } .fc-chips::-webkit-scrollbar { display:none; }
    .fc-chip { flex:none; border:1px solid #d5dbe6; background:#fff; border-radius:20px; padding:5px 16px; font-size:.85rem; font-weight:600; cursor:pointer; color:#1b2a41; } .fc-chip.on { background:#1b2a41; color:#fff; border-color:#1b2a41; }
    .fc-card { display:block; background:#fff; border-radius:14px; overflow:hidden; text-decoration:none !important; color:#1b2a41; box-shadow:0 2px 10px rgba(0,0,0,.1); height:100%; }
    .fc-card .im { aspect-ratio:16/9; background:#10243e center/cover; position:relative; } .fc-card .im i { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:2.2rem; text-shadow:0 2px 8px rgba(0,0,0,.5); }
    .fc-card .tx { padding:8px 10px; font-weight:600; font-size:.9rem; height:3.6em; overflow:hidden; }
    .fc-live { position:absolute; left:8px; top:8px; background:#e53935; color:#fff; font-size:.68rem; font-weight:700; padding:2px 8px; border-radius:10px; }
    .fc-when { position:absolute; right:8px; bottom:8px; background:rgba(0,0,0,.7); color:#fff; font-size:.7rem; padding:2px 8px; border-radius:10px; }
</style>
@endsection
@section('content')
<div class="page-content-wrapper"><div class="container py-3">
    <h3 class="fw-bold mb-2">Free classes</h3>
    @if ($current)
        <div class="small mb-3"><a href="{{ url('free-classes') }}"><i class="bi bi-house"></i> All</a>@foreach ($trail as $t) <span class="text-muted">/</span> @if ($loop->last)<b>{{ $t->title }}</b>@else<a href="{{ url('free-classes?folder=' . $t->id) }}">{{ $t->title }}</a>@endif @endforeach</div>
    @endif
    <div class="fc-chips" id="chips"><span class="fc-chip on" data-x="all">All exams</span>@foreach ($exams as $e)<span class="fc-chip" data-x="{{ $e->id }}">{{ $e->short_name ?: $e->name }}</span>@endforeach</div>
    @if ($folders->count())
        <div class="row g-3 mb-3" id="folders">
            @foreach ($folders as $f)
                <div class="col-6 col-md-4 col-lg-3" data-x="{{ implode(' ', $f->exam_ids) }}"><a class="fc-card" href="{{ url('free-classes?folder=' . $f->id) }}" style="padding:12px 14px;display:flex;align-items:center;gap:10px">
                    <i class="bi bi-folder-fill" style="font-size:1.8rem;color:#f5b301"></i><div style="min-width:0"><div class="fw-bold text-truncate">{{ $f->title }}</div><div class="small text-muted">{{ $f->classes_count }} class{{ $f->classes_count == 1 ? '' : 'es' }}</div></div></a></div>
            @endforeach
        </div>
    @endif
    <div class="row g-3" id="grid">
        @forelse ($classes as $c)
            @php $vid = \App\Services\ClassSource::youtubeId((string) $c->link_url); @endphp
            <div class="col-6 col-md-4 col-lg-3" data-x="{{ $c->exams->pluck('id')->implode(' ') }}"><a class="fc-card" href="{{ $c->publicUrl() }}">
                <div class="im" style="background-image:url('{{ $c->image ? Storage::url('files/' . $c->image) : ($vid ? 'https://img.youtube.com/vi/' . $vid . '/hqdefault.jpg' : '') }}')">
                    @if ($c->mode === 'live')<span class="fc-live">LIVE</span>@if ($c->scheduled_at)<span class="fc-when">{{ $c->scheduled_at->format('d M, h:i a') }}</span>@endif @endif
                    <i class="bi bi-play-circle-fill"></i></div>
                <div class="tx">{{ $c->title }}</div></a></div>
        @empty
            @if (!$folders->count())<div class="text-muted">No free classes yet.</div>@endif
        @endforelse
    </div>
</div></div>
@endsection
@section('scripts')
<script>
    document.getElementById('chips').addEventListener('click', function (e) {
        var c = e.target.closest('[data-x]'); if (!c) return;
        [].forEach.call(this.children, function (x) { x.classList.toggle('on', x === c); });
        [].forEach.call(document.querySelectorAll('#grid [data-x], #folders [data-x]'), function (a) { a.style.display = (c.dataset.x === 'all' || (' ' + a.dataset.x + ' ').indexOf(' ' + c.dataset.x + ' ') >= 0) ? '' : 'none'; });
    });
</script>
@endsection
