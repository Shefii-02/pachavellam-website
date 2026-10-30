{{-- The instructions card shown before an exam: needs $exam, $instruction (ExamInstruction|null), $meta (label => value); optional $startUrl (a page link) -- without it the Start button fires the "exam-start" event on the document --}}
<style>
    .xi-card { background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:16px; padding:18px; color:var(--pv-text,#111); }
    .xi-card h3 { font-size:1.15rem; font-weight:800; margin:0 0 8px; }
    .xi-meta { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; } .xi-meta span { background:var(--pv-chip,#eef0f3); border-radius:20px; padding:4px 14px; font-size:.8rem; font-weight:700; }
    .xi-body { border-top:1px solid var(--pv-border,#e5e7eb); padding-top:12px; font-size:.92rem; line-height:1.55; } .xi-body li { margin-bottom:6px; }
    .xi-agree { display:flex; gap:10px; align-items:flex-start; margin:14px 0; font-size:.85rem; }
    .xi-go { display:inline-block; border:0; border-radius:24px; padding:10px 30px; font-weight:800; background:#0a8f5b; color:#fff !important; text-decoration:none !important; }
    .xi-go[disabled], .xi-go.off { opacity:.45; pointer-events:none; }
</style>
<div class="xi-card" data-exam="{{ $exam->id }}">
    <h3>{{ $exam->examtitle ?: ($exam->exam_title ?: 'Exam') }}</h3>
    <div class="xi-meta">@foreach ($meta as $label => $value)<span>{{ $label }}: {{ $value }}</span>@endforeach</div>
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
    var cb = document.getElementById('xiAgree'), go = document.getElementById('xiStart');
    cb.addEventListener('change', function () { if (go.tagName === 'A') go.classList.toggle('off', !cb.checked); else go.disabled = !cb.checked; });
    if (go.tagName !== 'A') go.addEventListener('click', function () { document.dispatchEvent(new CustomEvent('exam-start')); });
})();
</script>
