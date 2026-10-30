{{-- Start / end of an exam card and, for an upcoming exam, a live countdown. Needs $r; rows without times show nothing. --}}
@once
<style>
    .sr-dt { display:flex; flex-wrap:wrap; gap:4px 14px; margin-top:6px; font-size:.74rem; color:var(--pv-muted,#667); } .sr-dt i { margin-right:3px; }
    .sr-cd { margin-top:8px; border-radius:10px; padding:6px 10px; font-size:.8rem; background:#fff1d6; color:#8a5500; } .sr-cd b { font-variant-numeric:tabular-nums; }
</style>
<script>
(function () {
    function fmt(ms) { var s = Math.floor(ms / 1000), d = Math.floor(s / 86400), h = Math.floor(s / 3600) % 24, m = Math.floor(s / 60) % 60, p = function (n) { return (n < 10 ? '0' : '') + n; };
        return (d ? d + 'd ' : '') + p(h) + 'h ' + p(m) + 'm ' + p(s % 60) + 's'; }
    function tick() {
        var now = Date.now(), live = false;
        document.querySelectorAll('.sr-cd[data-start]').forEach(function (el) {
            var left = new Date(el.dataset.start).getTime() - now;
            if (isNaN(left)) return;
            if (left > 0) { el.querySelector('b').textContent = fmt(left); live = true; return; }
            el.innerHTML = '<i class="bi bi-play-circle-fill"></i> <b>Started!</b> Refreshing…'; el.removeAttribute('data-start');
            try { if (sessionStorage.getItem('sr_reload') !== el.dataset.id) { sessionStorage.setItem('sr_reload', el.dataset.id || '1'); setTimeout(function () { location.reload(); }, 1200); } } catch (e) {}
        });
    }
    document.addEventListener('DOMContentLoaded', function () { tick(); setInterval(tick, 1000); });
})();
</script>
@endonce
@if (!empty($r->startAt) || !empty($r->endAt))
    <div class="sr-dt">
        @if (!empty($r->startAt))<span><i class="bi bi-play-circle"></i>Start: {{ $r->startLbl }}</span>@endif
        @if (!empty($r->endAt))<span><i class="bi bi-stop-circle"></i>End: {{ $r->endLbl }}</span>@endif
    </div>
    @if (($r->tagc ?? '') === 'up' && !empty($r->startAt))
        <div class="sr-cd" data-start="{{ $r->startAt }}" data-id="{{ md5($r->startAt . ($r->title ?? '')) }}"><i class="bi bi-hourglass-split"></i> Starts in <b>--</b></div>
    @endif
@endif
