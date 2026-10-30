{{-- The instructions card shown before an exam: needs $exam, $instruction (ExamInstruction|null), $meta (label => value); optional $startUrl (a page link) -- without it the Start button fires the "exam-start" event on the document --}}
<style>
    .xi-card { background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:16px; padding:18px; color:var(--pv-text,#111); }
    .xi-card h3 { font-size:1.15rem; font-weight:800; margin:0 0 8px; }
    .xi-meta { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; } .xi-meta span { background:var(--pv-chip,#eef0f3); border-radius:20px; padding:4px 14px; font-size:.8rem; font-weight:700; }
    .xi-body { border-top:1px solid var(--pv-border,#e5e7eb); padding-top:12px; font-size:.92rem; line-height:1.55; } .xi-body li { margin-bottom:6px; }
    .xi-agree { display:flex; gap:10px; align-items:flex-start; margin:14px 0; font-size:.85rem; }
    .xi-go { display:inline-block; border:0; border-radius:24px; padding:10px 30px; font-weight:800; background:#0a8f5b; color:#fff !important; text-decoration:none !important; }
    .xi-cd { border-radius:12px; padding:10px 14px; margin-bottom:12px; background:#fff1d6; color:#8a5500; font-size:.9rem; } .xi-cd .xi-left { font-variant-numeric:tabular-nums; }
    .xi-go[disabled], .xi-go.off { opacity:.45; pointer-events:none; }
</style>
<div class="xi-card" data-exam="{{ $exam->id }}">
    <h3>{{ $exam->examtitle ?: ($exam->exam_title ?: 'Exam') }}</h3>
    <div class="xi-meta">@foreach ($meta as $label => $value)<span>{{ $label }}: {{ $value }}</span>@endforeach</div>
    @if (!empty($startsAt))
        <div class="xi-cd" data-start="{{ $startsAt }}"><i class="bi bi-hourglass-split"></i> This exam opens on <b>{{ \Illuminate\Support\Carbon::parse($startsAt)->format('d M Y, h:i a') }}</b>@if ($exam->ended_at) and closes on <b>{{ \Illuminate\Support\Carbon::parse($exam->ended_at)->format('d M Y, h:i a') }}</b>@endif.<br>Starts in <b class="xi-left">--</b>. The question paper, answer key and marks open when it starts.</div>
    @endif
    <div class="xi-body">{!! $instruction?->body ?: '<p>Read the questions carefully. Good luck!</p>' !!}</div>
    <label class="xi-agree"><input type="checkbox" id="xiAgree"> <span>I have read and understood the instructions.</span></label>
    @if (!empty($startUrl))
        <a class="xi-go off" id="xiStart" href="{{ $startUrl }}">Start exam</a>
    @else
        <button type="button" class="xi-go" id="xiStart" disabled>Start exam</button>
    @endif
</div>
<script>
(function () {
    var cb = document.getElementById('xiAgree'), go = document.getElementById('xiStart'), cd = document.querySelector('.xi-cd');
    if (cd) {   // not started: the countdown, and the page reloads itself when the time comes
        var at = new Date(cd.dataset.start).getTime(), left = cd.querySelector('.xi-left');
        var p = function (n) { return (n < 10 ? '0' : '') + n; };
        (function tick() { var ms = at - Date.now(); if (ms <= 0) { location.reload(); return; } var s = Math.floor(ms / 1000), d = Math.floor(s / 86400);
            left.textContent = (d ? d + 'd ' : '') + p(Math.floor(s / 3600) % 24) + 'h ' + p(Math.floor(s / 60) % 60) + 'm ' + p(s % 60) + 's'; setTimeout(tick, 1000); })();
        cb.disabled = true; cb.closest('label').style.display = 'none'; go.style.display = 'none'; return;
    }
    cb.addEventListener('change', function () { if (go.tagName === 'A') go.classList.toggle('off', !cb.checked); else go.disabled = !cb.checked; });
    if (go.tagName !== 'A') go.addEventListener('click', function () { document.dispatchEvent(new CustomEvent('exam-start')); });
})();
</script>
