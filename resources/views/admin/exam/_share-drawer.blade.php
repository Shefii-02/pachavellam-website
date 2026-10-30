{{-- Share a link with a message: any button with class js-share and data-url / data-title / data-text opens this drawer (right side). --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="shareDrawer" aria-labelledby="shareTitle" style="width:min(440px,100vw)">
    <div class="offcanvas-header border-bottom"><h5 class="offcanvas-title" id="shareTitle"><i class="bi bi-share me-1"></i>Share</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body">
        <div class="fw-bold mb-2" id="shareName"></div>
        <label class="form-label small mb-0">Message</label>
        <textarea class="form-control mb-2" id="shareText" rows="4" maxlength="500"></textarea>
        <label class="form-label small mb-0">Link</label>
        <div class="input-group mb-3"><input class="form-control" id="shareUrl" readonly><button class="btn btn-outline-secondary mb-0" type="button" id="shareCopyUrl"><i class="bi bi-clipboard"></i></button></div>
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-success mb-0 d-none" id="shareNative"><i class="bi bi-share-fill me-1"></i>Share from this device…</button>
            <a class="btn mb-0 text-white" style="background:#25d366" id="shareWa" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
            <a class="btn mb-0 text-white" style="background:#229ed9" id="shareTg" target="_blank" rel="noopener"><i class="bi bi-telegram me-1"></i>Telegram</a>
            <a class="btn mb-0 text-white" style="background:#1877f2" id="shareFb" target="_blank" rel="noopener"><i class="bi bi-facebook me-1"></i>Facebook</a>
            <a class="btn mb-0 text-white" style="background:#111" id="shareX" target="_blank" rel="noopener"><i class="bi bi-twitter-x me-1"></i>X (Twitter)</a>
            <a class="btn btn-outline-secondary mb-0" id="shareMail"><i class="bi bi-envelope me-1"></i>Email</a>
            <button type="button" class="btn btn-outline-secondary mb-0" id="shareCopyAll"><i class="bi bi-clipboard-check me-1"></i>Copy message and link</button>
        </div>
        <div class="small text-success mt-2" id="shareOk" style="min-height:1.2em"></div>
    </div>
</div>
<script>
(function () {
    var el = document.getElementById('shareDrawer'), $ = function (i) { return document.getElementById(i); }, title = '', url = '';
    var msg = function () { return $('shareText').value.trim(); }, full = function () { return msg() + '\n' + url; };
    function links() {
        var t = encodeURIComponent(msg()), u = encodeURIComponent(url), f = encodeURIComponent(full());
        $('shareWa').href = 'https://wa.me/?text=' + f; $('shareTg').href = 'https://t.me/share/url?url=' + u + '&text=' + t;
        $('shareFb').href = 'https://www.facebook.com/sharer/sharer.php?u=' + u + '&quote=' + t; $('shareX').href = 'https://twitter.com/intent/tweet?text=' + t + '&url=' + u;
        $('shareMail').href = 'mailto:?subject=' + encodeURIComponent(title) + '&body=' + f;
    }
    function copy(text, ok) { var done = function () { $('shareOk').textContent = ok; setTimeout(function () { $('shareOk').textContent = ''; }, 2000); };
        if (navigator.clipboard) navigator.clipboard.writeText(text).then(done); else { var a = document.createElement('textarea'); a.value = text; document.body.appendChild(a); a.select(); document.execCommand('copy'); a.remove(); done(); } }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.js-share'); if (!b || b.disabled) return;
        title = b.dataset.title || ''; url = b.dataset.url || '';
        $('shareName').textContent = title; $('shareText').value = b.dataset.text || title; $('shareUrl').value = url; links();
        $('shareNative').classList.toggle('d-none', !navigator.share); new bootstrap.Offcanvas(el).show();
    });
    $('shareText').addEventListener('input', links);
    $('shareCopyUrl').onclick = function () { copy(url, 'Link copied'); }; $('shareCopyAll').onclick = function () { copy(full(), 'Message and link copied'); };
    $('shareNative').onclick = function () { navigator.share({ title: title, text: msg(), url: url }).catch(function () {}); };
})();
</script>
