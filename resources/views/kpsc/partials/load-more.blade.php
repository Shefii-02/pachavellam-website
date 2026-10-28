{{-- "Load more" under a list: needs $paginator (the page being shown) and $target (css selector of the grid the next page's items are added to).
     The next page loads by itself when the button comes into view (scroll down), or on tap. Floating buttons jump to the bottom / top. --}}
@if ($paginator->hasMorePages())
    <div class="text-center my-4"><button type="button" class="btn btn-outline-dark rounded-pill px-4" id="lmBtn" data-next="{{ $paginator->nextPageUrl() }}" data-target="{{ $target }}">Load more <i class="bi bi-chevron-down ms-1"></i></button></div>
@endif
<div id="scrollFab" style="position:fixed;right:14px;bottom:84px;z-index:50;display:flex;flex-direction:column;gap:8px">
    <button type="button" class="btn btn-dark rounded-circle shadow" style="width:42px;height:42px;padding:0" id="fabTop" aria-label="Scroll to top"><i class="bi bi-arrow-up"></i></button>
    <button type="button" class="btn btn-dark rounded-circle shadow" style="width:42px;height:42px;padding:0" id="fabDown" aria-label="Scroll to bottom"><i class="bi bi-arrow-down"></i></button>
</div>
<script>
    (function () {
        var btn = document.getElementById('lmBtn'), busy = false;
        document.getElementById('fabTop').onclick = function () { window.scrollTo({ top: 0, behavior: 'smooth' }); };
        document.getElementById('fabDown').onclick = function () { window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' }); };
        if (!btn) return;
        function more() {
            if (busy || !btn.dataset.next) return;
            busy = true; btn.disabled = true; btn.firstChild.textContent = 'Loading… ';
            fetch(btn.dataset.next, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.text(); }).then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var from = doc.querySelector(btn.dataset.target), to = document.querySelector(btn.dataset.target);
                if (from && to) { [].forEach.call(from.children, function (c) { to.appendChild(document.importNode(c, true)); }); if (window.pvAds) window.pvAds(); }
                var nb = doc.getElementById('lmBtn');
                if (nb && nb.dataset.next) { btn.dataset.next = nb.dataset.next; btn.disabled = false; btn.firstChild.textContent = 'Load more '; busy = false; watch(); }
                else { btn.parentNode.remove(); }
            }).catch(function () { btn.disabled = false; btn.firstChild.textContent = 'Try again '; busy = false; });
        }
        btn.addEventListener('click', more);
        var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (e) { if (e[0].isIntersecting) more(); }, { rootMargin: '200px' }) : null;
        function watch() { if (io) { io.disconnect(); io.observe(btn); } }
        watch();
    })();
</script>
