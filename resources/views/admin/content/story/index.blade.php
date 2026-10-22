@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0">Stories</h3>
            <div class="small text-muted">The picture slider on the app's home screen. Each story appears only for the exam types you tick. Stories synced from the Instagram highlight appear here too (red badge).</div>
        </div>
        <div class="d-flex gap-2">
            @can('Banner Slider Add')
                <button type="button" class="btn btn-sm btn-outline-danger mb-0" data-bs-toggle="modal" data-bs-target="#syncModal"><i class="bi bi-instagram me-1"></i>Sync Instagram highlight</button>
            @endcan
        @can('Banner Slider Add')
            <a href="{{ url('admin/stories/create') }}{{ request('exam') ? '?exam=' . request('exam') : '' }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New Story</a>
        @endcan
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <select name="exam" class="form-select form-select-sm">
                <option value="">All exam types</option>
                @foreach ($exams as $e)<option value="{{ $e->slug }}" @selected(request('exam') === $e->slug)>{{ $e->short_name ?: $e->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select form-select-sm"><option value="">Shown + hidden</option><option value="1" @selected(request('status') === '1')>Shown</option><option value="0" @selected(request('status') === '0')>Hidden</option></select>
        </div>
        <div class="col-auto d-flex gap-2"><button class="btn btn-sm btn-light mb-0">Filter</button><a href="{{ url('admin/stories') }}" class="btn btn-sm btn-light mb-0">Reset</a></div>
    </form>

    @can('Banner Slider list')
        <div class="row g-3">
            @forelse ($list as $s)
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card bg-transparent border rounded-3 h-100 {{ $s->status ? '' : 'opacity-75' }}">
                        <img src="{{ asset('storage/files/' . $s->image) }}" class="card-img-top" alt="" style="height:170px;object-fit:cover">
                        <div class="card-body p-3">
                            <div class="mb-2">
                                @forelse ($s->exams as $e)<span class="badge text-white me-1" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>@empty<span class="text-danger small">none — not shown anywhere</span>@endforelse
                            </div>
                            <div class="small text-muted text-truncate" title="{{ $s->redirection }}"><i class="bi bi-link-45deg"></i> {{ $s->redirection ?: '—' }}</div>
                            <div class="small text-muted">Position {{ $s->position }}</div>
                        </div>
                        <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center p-2">
                            @can('Banner Slider Edit')
                                <form action="{{ url('admin/stories/' . $s->id . '/toggle') }}" method="POST">@csrf
                                    <button class="badge border-0 bg-{{ $s->status ? 'success' : 'secondary' }}" title="Click to {{ $s->status ? 'hide' : 'show' }}">{{ $s->status ? 'Shown' : 'Hidden' }}</button>
                                </form>
                            @else
                                <span class="badge bg-{{ $s->status ? 'success' : 'secondary' }}">{{ $s->status ? 'Shown' : 'Hidden' }}</span>
                            @endcan
                            <div>
                                @can('Banner Slider Edit')<a href="{{ url('admin/stories/' . $s->id . '/edit') }}" class="btn btn-sm btn-circle btn-info" title="Edit"><i class="bi bi-pencil"></i></a>@endcan
                                @can('Banner Slider Delete')
                                    <form action="{{ url('admin/stories/' . $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this story?')">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                @if (!$achievements->count())<div class="col-12 text-center text-muted py-5">No stories found.</div>@endif
            @endforelse
            @foreach ($achievements as $a)
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card bg-transparent border rounded-3 h-100 {{ $a->status ? '' : 'opacity-75' }}">
                        <div style="position:relative">
                            <img src="{{ asset('storage/files/' . $a->image) }}" class="card-img-top" alt="" style="height:170px;object-fit:cover">
                            <span class="badge bg-danger position-absolute" style="left:8px;top:8px"><i class="bi bi-instagram"></i> {{ $a->media_type === 'video' ? 'Video' : 'Picture' }}@if ($a->duration) · {{ $a->duration }}s @endif</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-2">@forelse ($a->exams as $e)<span class="badge text-white me-1" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>@empty<span class="badge bg-light text-dark border">All exam types</span>@endforelse</div>
                            <div class="small text-muted">Synced {{ $a->created_at?->format('d M Y') }}</div>
                        </div>
                        <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center p-2">
                            @can('Banner Slider Edit')
                                <form action="{{ url('admin/content/achievements/' . $a->id . '/toggle') }}" method="POST">@csrf
                                    <button class="badge border-0 bg-{{ $a->status ? 'success' : 'secondary' }}" title="Click to {{ $a->status ? 'hide' : 'show' }}">{{ $a->status ? 'Shown' : 'Hidden' }}</button>
                                </form>
                            @endcan
                            @can('Banner Slider Delete')
                                <form action="{{ url('admin/content/achievements/' . $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this synced story?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-circle btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $list->links('pagination::bootstrap-5') }}</div>
    @endcan
</div>

<div class="modal fade" id="syncModal" tabindex="-1" data-bs-backdrop="static"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="syncForm" method="POST" action="{{ url('admin/content/achievements/import') }}" novalidate>@csrf
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-instagram me-1"></i>Sync Instagram highlight</h5><button type="button" class="btn-close" data-bs-dismiss="modal" id="syncX"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Highlight link</label>
            <div class="input-group"><input class="form-control" id="igUrl" name="highlight_url" placeholder="https://www.instagram.com/stories/highlights/...">
                <button type="button" class="btn btn-outline-secondary mb-0" id="igOpen">1. Open downloader</button></div>
            <div class="form-text">Instagram's own feed has no highlights, and the downloader refuses calls from our server, so the one step that needs your browser is: press <b>1</b> (the link is copied - paste it in the downloader and press Download), then copy its answer and paste it in <b>2</b>.</div></div>
        <div class="mb-3"><label class="form-label">2. Paste the downloader's answer <span class="text-muted">(F12 &rarr; Network &rarr; the <code>dl?url=</code> request &rarr; Response)</span></label>
            <textarea class="form-control font-monospace" id="igPayload" name="payload" rows="7" required placeholder='{"code":200,"msg":"SUCCESS","result":{...}}'></textarea></div>
        <div><label class="form-label mb-1">Show for exam types <span class="text-muted">(none ticked = shown for every exam type)</span></label>
            <div class="d-flex flex-wrap gap-3">@foreach ($exams as $e)<div class="form-check"><input class="form-check-input" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="sx{{ $e->id }}"><label class="form-check-label" for="sx{{ $e->id }}"><span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span></label></div>@endforeach</div></div>
        <div id="syncProgress" class="mt-3" style="display:none">
            <div class="d-flex justify-content-between small"><span id="syncStatus">Working…</span><span id="syncCount"></span></div>
            <div class="progress" style="height:8px"><div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="syncBar" style="width:5%"></div></div>
        </div>
        <div id="syncError" class="alert alert-danger py-2 mt-3 small" style="display:none"></div>
        <div class="small text-muted mt-3">Stories already synced are skipped, and a story whose picture or video went missing is downloaded again. Keep this window open until it finishes.</div>
    </div>
    <div class="modal-footer"><button class="btn btn-success mb-0" id="syncGo"><i class="bi bi-arrow-repeat me-1"></i>Sync now</button></div>
    </form>
</div></div></div>
<script>
(function () {
    var form = document.getElementById('syncForm'), modalEl = document.getElementById('syncModal'), go = document.getElementById('syncGo');
    var prog = document.getElementById('syncProgress'), bar = document.getElementById('syncBar'), status = document.getElementById('syncStatus'), count = document.getElementById('syncCount'), err = document.getElementById('syncError');
    document.getElementById('igOpen').addEventListener('click', function () {
        var u = document.getElementById('igUrl').value.trim();
        if (u && navigator.clipboard) navigator.clipboard.writeText(u);
        window.open('https://www.snapgram.io/', '_blank', 'noopener');
    });
    function reset() { form.reset(); prog.style.display = 'none'; err.style.display = 'none'; bar.style.width = '5%'; go.disabled = false; document.getElementById('syncX').disabled = false; go.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Sync now'; }
    modalEl.addEventListener('hidden.bs.modal', reset);
    function fail(msg) { err.textContent = msg; err.style.display = 'block'; go.disabled = false; document.getElementById('syncX').disabled = false; go.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Try again'; bar.classList.remove('progress-bar-animated'); }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        err.style.display = 'none'; prog.style.display = 'block'; go.disabled = true; document.getElementById('syncX').disabled = true; go.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Syncing…';
        var total = { added: 0, skipped: 0, repaired: 0, failed: 0 }, firstRemaining = null, round = 0, last = null;
        try {
            while (round < 60) {
                round++;
                var res = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form), credentials: 'same-origin' });
                var j = await res.json().catch(function () { return {}; });
                if (!res.ok) { return fail(j.message || (j.errors && Object.values(j.errors)[0][0]) || 'The server could not finish (' + res.status + '). Press Try again - what was done is kept.'); }
                // rounds after the first see earlier stories as "already there", so only the new / restored counts add up
                total.added += j.added || 0; total.repaired += j.repaired || 0; total.failed = j.failed || 0;   // failures are retried every round, so the latest count is the real one
                if (firstRemaining === null) { total.skipped = j.skipped || 0; firstRemaining = (j.added || 0) + (j.repaired || 0) + (j.failed || 0) + (j.remaining || 0); }
                last = j;
                var done = total.added + total.repaired + total.failed;
                bar.style.width = Math.max(5, Math.min(100, firstRemaining ? Math.round(done / firstRemaining * 100) : 100)) + '%';
                count.textContent = done + ' / ' + firstRemaining;
                status.textContent = j.remaining > 0 ? 'Downloading stories…' : 'Finishing…';
                if (!j.remaining) break;
                if (!(j.added || j.repaired)) { return fail('Nothing more could be downloaded (' + (j.errors || [])[0] + '). Open the highlight in the downloader again and paste a fresh answer.'); }
            }
        } catch (x) { return fail('The connection broke. Press Try again - what was done is kept.'); }

        bar.classList.remove('progress-bar-animated'); bar.style.width = '100%';
        status.textContent = 'Done: ' + total.added + ' new, ' + total.skipped + ' already there' + (total.repaired ? ', ' + total.repaired + ' restored' : '') + (total.failed ? ', ' + total.failed + ' failed' : '');
        // success: close the window, clear what was typed, and show the result
        setTimeout(function () {
            bootstrap.Modal.getInstance(modalEl).hide();
            var t = document.createElement('div'); t.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3 shadow'; t.style.zIndex = 3000;
            t.innerHTML = '<b>Instagram highlight synced.</b> ' + total.added + ' new stor' + (total.added === 1 ? 'y' : 'ies') + ' added' + (total.skipped ? ', ' + total.skipped + ' already there' : '') + (total.failed ? ', ' + total.failed + ' failed' : '') + '. <a href="" onclick="location.reload();return false">Refresh to see them</a>';
            document.body.appendChild(t); setTimeout(function () { location.reload(); }, 1800);
        }, 700);
    });
})();
</script>
@endsection
