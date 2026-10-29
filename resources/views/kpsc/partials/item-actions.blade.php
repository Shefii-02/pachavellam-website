{{-- Small action row on a card: copy the link, open in a new tab. Needs $url; $copy (bool, from the category's page setting) --}}
<span class="pv-act" style="position:absolute;right:8px;top:8px;display:flex;gap:6px;z-index:2">
    @if ($copy ?? true)<button type="button" class="pv-copy btn btn-light btn-sm rounded-circle shadow-sm" style="width:30px;height:30px;padding:0" data-url="{{ $url }}" title="Copy link" aria-label="Copy link"><i class="bi bi-link-45deg"></i></button>@endif
    <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width:30px;height:30px;padding:0;line-height:28px" title="Open in a new tab" aria-label="Open in a new tab"><i class="bi bi-box-arrow-up-right"></i></a>
</span>
@once
<script>
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.pv-copy'); if (!b) return;
        e.preventDefault(); e.stopPropagation();
        var u = b.dataset.url, done = function () { var i = b.firstElementChild; i.className = 'bi bi-check2'; setTimeout(function () { i.className = 'bi bi-link-45deg'; }, 1500); };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(u).then(done); }
        else { var t = document.createElement('textarea'); t.value = u; t.style.position = 'fixed'; t.style.opacity = 0; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (x) {} t.remove(); }
    });
</script>
@endonce
