@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .bk-wrap { max-width:760px; margin:0 auto; padding:14px 0 24px; }
        .bk-wrap h1 { font-size:1.3rem; font-weight:800; color:var(--pv-text); margin:0 0 4px; }
        .bk-ex { margin:20px 0 8px; font-weight:800; color:var(--pv-text); font-size:1rem; }
        .bk-card { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:16px; padding:14px; margin-bottom:10px; color:var(--pv-text); }
        .bk-q { line-height:1.6; margin-bottom:10px; overflow-wrap:anywhere; background:#eceef2; border-radius:12px; padding:10px 12px; } .bk-q img, .bk-o img { max-width:100%; height:auto; }
        .bk-o { display:flex; gap:10px; border:2px solid var(--pv-border); border-radius:12px; padding:9px 12px; margin-bottom:6px; background:#eceef2; }
        .bk-o.right { border-color:#0a8f5b; background:rgba(10,143,91,.1); } .bk-o .l { font-weight:800; }
        .bk-note { border-radius:12px; background:#eaf3ff; color:#12315c; padding:10px 12px; margin-top:8px; font-size:.9rem; }
        .bk-rm { border:1px solid var(--pv-border); background:var(--pv-surface); color:#e5484d; border-radius:10px; padding:6px 12px; font-weight:700; font-size:.82rem; margin-top:8px; cursor:pointer; }
        [data-theme="dark"] .bk-q, [data-theme="dark"] .bk-o { background:var(--pv-surface-2); } [data-theme="dark"] .bk-o.right { background:rgba(10,143,91,.18); } [data-theme="dark"] .bk-note { background:#16273f; color:#cfe0ff; }
    </style>
@endsection
@section('content')
<div class="page-content-wrapper"><div class="container"><div class="bk-wrap">
    <a href="{{ url('my-account') }}" class="small"><i class="bi bi-arrow-left"></i> My profile</a>
    <h1><i class="bi bi-bookmark-star-fill" style="color:var(--pv-brand)"></i> Saved questions <span class="small text-muted" id="bkCount">{{ $count }}</span></h1>
    <p class="small text-muted">Questions you saved while taking an exam or reading its solutions.</p>
    @forelse ($groups as $g)
        <div class="bk-ex">{{ $g['exam']->examtitle ?: ($g['exam']->exam_title ?: 'Exam') }}</div>
        @foreach ($g['items'] as $it)
            <div class="bk-card" id="bk{{ $it['id'] }}">
                <div class="bk-q">{!! $it['q'] !!}</div>
                @foreach ($it['o'] as $k => $o)
                    <div class="bk-o {{ $k === $it['correct'] ? 'right' : '' }}"><span class="l">{{ ['', 'A', 'B', 'C', 'D'][$k] }}</span><span>{!! $o !!}</span>@if ($k === $it['correct'])<span class="ms-auto small" style="color:#0a8f5b;font-weight:800">Correct answer</span>@endif</div>
                @endforeach
                @if (trim(strip_tags($it['notes'])) !== '')<div class="bk-note"><b>Solution / Notes</b><br>{!! $it['notes'] !!}</div>@endif
                <button type="button" class="bk-rm" data-q="{{ $it['id'] }}" data-exam="{{ $g['exam']->id }}"><i class="bi bi-bookmark-x"></i> Remove</button>
            </div>
        @endforeach
    @empty
        <div class="hb-note"><i class="bi bi-bookmark"></i><span class="sp">Nothing saved yet. Tap <b>☆ Save</b> on a question during an exam, or on the Solutions tab of a result.</span></div>
    @endforelse
</div></div></div>
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('.bk-rm'); if (!b) return;
    fetch(@json(url('kpsc/mcq')) + '/' + b.dataset.exam + '/bookmark', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) }, body: JSON.stringify({ question_id: +b.dataset.q }) })
        .then(function (r) { return r.json(); }).then(function (d) {
            if (d.saved) return;   // it was removed elsewhere and this toggled it back on: leave the card
            document.getElementById('bk' + b.dataset.q).remove(); document.getElementById('bkCount').textContent = d.count;
        });
});
</script>
@endsection
