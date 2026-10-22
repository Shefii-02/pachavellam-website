@extends($rsLayout ?? 'admin.main-layout')
@section('main')
@php
    [$stLabel, $stColor] = \App\Models\RoaringStudy::STATUSES[$study->status];
    $base = ($rsBase . '/' . $study->id);
    $multi = $study->sections->count() > 1;
@endphp
<div class="page-content-wrapper border">
    @include('admin.question-library._alerts')
    <a href="{{ ($rsBase) }}" class="small"><i class="bi bi-arrow-left"></i> Roaring Study</a>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 mt-1">
        <div>
            <h3 class="mb-1">{{ $study->title }} <span class="badge bg-{{ $stColor }} fs-6">{{ $stLabel }}</span></h3>
            <div class="small text-muted">
                @foreach ($study->exams as $e)<span class="badge bg-light text-dark border">{{ $e->short_name ?: $e->name }}</span> @endforeach
                · opens {{ $study->publish_at?->format('d M Y, h:i A') ?? 'when published' }}@if ($study->end_at), closes {{ $study->end_at->format('d M Y, h:i A') }}@endif
                ·
                @if ($study->isGlobal())<span class="text-success">Free for everyone</span>
                @else Course based: {{ $study->courses->pluck('title')->implode(', ') ?: 'no course chosen' }}@endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ $base }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil me-1"></i>Settings</a>
            @foreach (['published' => ['Publish', 'success'], 'draft' => ['Move to draft', 'secondary'], 'archived' => ['Archive', 'dark']] as $to => [$label, $color])
                @continue($study->status === $to)
                <form action="{{ $base }}/status" method="POST">@csrf<input type="hidden" name="status" value="{{ $to }}"><button class="btn btn-sm btn-outline-{{ $color }} mb-0">{{ $label }}</button></form>
            @endforeach
            <form action="{{ $base }}" method="POST" onsubmit="return confirm('Delete this roaring study with all its content?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash"></i></button></form>
        </div>
    </div>
    @if ($study->status !== 'published' && $problems)<div class="alert alert-warning py-2 small"><strong>Before publishing:</strong> {{ implode(' ', $problems) }}</div>@endif
    @if ($study->description)<p class="small">{{ $study->description }}</p>@endif

    @foreach ($study->sections as $si => $section)
        <div class="card bg-transparent border rounded-3 mb-3">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div><strong>{{ $section->title }}</strong>
                    <span class="small text-muted ms-2">{{ $section->items->count() }} item(s){{ $section->release_at ? ' · opens ' . $section->release_at->format('d M Y, h:i A') : '' }}</span></div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-light mb-0" data-bs-toggle="collapse" data-bs-target="#sec{{ $section->id }}"><i class="bi bi-pencil me-1"></i>Rename / time</button>
                    @if ($multi)
                        <form action="{{ $base }}/sections/{{ $section->id }}" method="POST" onsubmit="return confirm('Delete this section and its content?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash"></i></button></form>
                    @endif
                </div>
            </div>
            <div class="collapse" id="sec{{ $section->id }}">
                <form action="{{ $base }}/sections/{{ $section->id }}" method="POST" class="card-body border-bottom row g-2 align-items-end">@csrf @method('PUT')
                    <div class="col-md-5"><label class="form-label small mb-0">Section title</label><input type="text" name="title" class="form-control form-control-sm" required value="{{ $section->title }}"></div>
                    <div class="col-md-4"><label class="form-label small mb-0">Opens at <span class="text-muted">(optional, later than the pack)</span></label><input type="datetime-local" name="release_at" class="form-control form-control-sm" value="{{ $section->release_at?->format('Y-m-d\TH:i') }}"></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary mb-0">Save</button></div>
                </form>
            </div>

            <div class="list-group list-group-flush">
                @forelse ($section->items as $ii => $item)
                    <div class="list-group-item bg-transparent d-flex gap-3 align-items-center {{ $item->status ? '' : 'opacity-50' }}">
                        <span class="badge bg-light text-dark border" style="width:92px">{{ \App\Models\RoaringStudy::TYPES[$item->type] }}</span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $item->title }}@if (!$item->status) <span class="badge bg-secondary">hidden</span>@endif</div>
                            <div class="small text-muted text-truncate" style="max-width:640px">
                                @switch($item->type)
                                    @case('text') {{ \Illuminate\Support\Str::limit(\App\Support\SafeHtml::text($item->content), 140) }} @break
                                    @case('link') <a href="{{ $item->content }}" target="_blank" rel="noopener">{{ $item->content }}</a> @break
                                    @case('class') {{ $item->libraryClass?->title ?? '(class removed from the library)' }} @break
                                    @default <a href="{{ $item->fileUrl() }}" target="_blank">Open file</a>
                                @endswitch
                            </div>
                        </div>
                        <div class="text-nowrap">
                            <form action="{{ $base }}/items/{{ $item->id }}/move" method="POST" class="d-inline">@csrf
                                <button name="dir" value="up" class="btn btn-sm btn-light mb-0" @disabled($ii === 0)><i class="bi bi-arrow-up"></i></button>
                                <button name="dir" value="down" class="btn btn-sm btn-light mb-0" @disabled($ii === $section->items->count() - 1)><i class="bi bi-arrow-down"></i></button></form>
                            <form action="{{ $base }}/items/{{ $item->id }}/toggle" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light mb-0" title="Show / hide"><i class="bi bi-eye{{ $item->status ? '' : '-slash' }}"></i></button></form>
                            <form action="{{ $base }}/items/{{ $item->id }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this content?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-x-lg"></i></button></form>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item bg-transparent text-muted text-center py-3">Nothing here yet.</div>
                @endforelse
            </div>

            <div class="card-footer bg-transparent">
                <form action="{{ $base }}/sections/{{ $section->id }}/items" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end add-item" data-section="{{ $section->id }}">
                    @csrf
                    <div class="col-md-2"><label class="form-label small mb-0">Add</label>
                        <select name="type" class="form-select form-select-sm item-type">@foreach (\App\Models\RoaringStudy::TYPES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label small mb-0">Title <span class="text-muted">(optional)</span></label><input type="text" name="title" class="form-control form-control-sm" maxlength="250"></div>
                    <div class="col-md-5 f-text"><label class="form-label small mb-0">Text</label><textarea name="content" rows="2" class="form-control form-control-sm"></textarea></div>
                    <div class="col-md-5 f-link" style="display:none"><label class="form-label small mb-0">Link</label><input type="text" name="content" class="form-control form-control-sm" placeholder="https://…" disabled></div>
                    <div class="col-md-5 f-pdf" style="display:none"><label class="form-label small mb-0">PDF file</label><input type="file" name="file" accept="application/pdf" class="form-control form-control-sm" disabled></div>
                    <div class="col-md-5 f-voice" style="display:none"><label class="form-label small mb-0">Voice file (mp3, m4a, aac, wav)</label><input type="file" name="file" accept="audio/*" class="form-control form-control-sm" disabled></div>
                    <div class="col-md-5 f-class" style="display:none"><label class="form-label small mb-0">Search a class in the class library</label>
                        <input type="text" class="form-control form-control-sm class-q" placeholder="type a title or teacher…" autocomplete="off" disabled>
                        <input type="hidden" name="class_id" disabled><div class="class-results list-group position-absolute shadow-sm" style="z-index:5;max-width:420px"></div>
                        <div class="small text-success class-picked"></div></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-success mb-0 w-100">Add</button></div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
        <form action="{{ $base }}/sections" method="POST" class="row g-2 align-items-end">@csrf
            <div class="col-md-5"><label class="form-label small mb-0">New section</label><input type="text" name="title" class="form-control form-control-sm" required maxlength="200" placeholder="e.g. Kerala History"></div>
            <div class="col-md-4"><label class="form-label small mb-0">Opens at <span class="text-muted">(optional)</span></label><input type="datetime-local" name="release_at" class="form-control form-control-sm"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add section</button></div>
        </form>
    </div></div>
</div>

<script>
document.querySelectorAll('.add-item').forEach(function (form) {
    var sel = form.querySelector('.item-type');
    function show() {
        ['text', 'link', 'pdf', 'voice', 'class'].forEach(function (k) {
            var col = form.querySelector('.f-' + k), on = sel.value === k;
            col.style.display = on ? '' : 'none';
            col.querySelectorAll('input,textarea').forEach(function (i) { i.disabled = !on; });
        });
    }
    sel.addEventListener('change', show); show();

    var q = form.querySelector('.class-q'), hid = form.querySelector('.f-class [name=class_id]'), box = form.querySelector('.class-results'), picked = form.querySelector('.class-picked'), timer;
    q.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch(@json(($rsBase . '/' . $study->id . '/classes')) + '?q=' + encodeURIComponent(q.value), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); }).then(function (rows) {
                    box.innerHTML = '';
                    rows.forEach(function (c) {
                        var a = document.createElement('a');
                        a.href = '#'; a.className = 'list-group-item list-group-item-action py-1 small';
                        a.innerHTML = '<strong></strong><div class="text-muted"></div>';
                        a.firstChild.textContent = c.title; a.lastChild.textContent = c.meta;
                        a.addEventListener('click', function (e) { e.preventDefault(); hid.value = c.id; picked.textContent = 'Chosen: ' + c.title; box.innerHTML = ''; q.value = ''; });
                        box.appendChild(a);
                    });
                    if (!rows.length) box.innerHTML = '<div class="list-group-item small text-muted">No published class found.</div>';
                });
        }, 250);
    });
});
</script>
@endsection
@section('content2')@yield('main')@endsection
