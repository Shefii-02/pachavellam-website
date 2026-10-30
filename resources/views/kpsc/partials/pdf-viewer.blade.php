{{-- Inline PDF viewer (PDF.js) for a PDF on our own server: needs $pdfId (unique) and $pdfUrl. Starts when window.pvStart[$pdfId]() is called (a tab opening), so a hidden tab loads nothing. --}}
@once
<style>
    .pv-box { border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; overflow:hidden; background:#d1d1d1; }
    .pv-bar { display:flex; align-items:center; gap:6px; flex-wrap:wrap; padding:6px 8px; background:var(--pv-surface,#fff); border-bottom:1px solid var(--pv-border,#e5e7eb); font-size:.8rem; }
    .pv-bar button, .pv-bar a { border:1px solid var(--pv-border,#d5d9e0); background:var(--pv-surface,#fff); color:var(--pv-text,#16202e) !important; border-radius:8px; padding:4px 11px; font-weight:700; font-size:.78rem; text-decoration:none !important; cursor:pointer; }
    .pv-bar .sp { flex:1; } .pv-pages { height:78vh; overflow:auto; padding:8px; -webkit-overflow-scrolling:touch; } .pv-pg { margin:0 auto 8px; background:#fff; box-shadow:0 1px 5px rgba(0,0,0,.25); position:relative; }
    .pv-pg canvas { display:block; } .pv-msg { text-align:center; padding:40px 12px; color:#444; font-size:.9rem; } .pv-msg button, .pv-msg a { margin:6px 4px 0; }
</style>
<script>
window.pvStart = window.pvStart || {};
window.pvLib = window.pvLib || function () {
    if (window.__pvLib) return window.__pvLib;
    return window.__pvLib = new Promise(function (ok, no) {
        if (window.pdfjsLib) return ok(window.pdfjsLib);
        var s = document.createElement('script'); s.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
        s.onload = function () { pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'; ok(window.pdfjsLib); };
        s.onerror = function () { window.__pvLib = null; no(new Error('pdf.js')); }; document.head.appendChild(s);
    });
};
window.pvMake = function (id, url) {
    var box = document.getElementById('pv-' + id), pages = box.querySelector('.pv-pages'), pageInfo = box.querySelector('.pv-page'), zoomInfo = box.querySelector('.pv-zoom');
    var pdf = null, scale = 1, ratio = 1.414, io = null, started = false, gen = 0;
    var dpr = Math.min(2, window.devicePixelRatio || 1);
    var fitW = function () { return Math.max(260, pages.clientWidth - 16); };
    var msg = function (h) { pages.innerHTML = '<div class="pv-msg">' + h + '</div>'; };
    function fail() { msg('Could not load the PDF.<br><button type="button" class="pv-retry">Try again</button> <a href="' + url + '" target="_blank" rel="noopener">Open PDF</a>'); pages.querySelector('.pv-retry').onclick = function () { load(0); }; }
    function load(n) {
        msg('Loading PDF…');
        window.pvLib().then(function (lib) { return lib.getDocument({ url: url, withCredentials: false }).promise; })
            .then(function (d) { pdf = d; build(); })
            .catch(function () { if (n < 3) setTimeout(function () { load(n + 1); }, 800 * (n + 1)); else fail(); });   // a flaky first load retries by itself
    }
    function size(el) { var w = fitW() * scale; el.style.width = w + 'px'; el.style.height = (w * ratio) + 'px'; }
    function build() {
        gen++; pages.innerHTML = '';
        pdf.getPage(1).then(function (p) { var v = p.getViewport({ scale: 1 }); ratio = v.height / v.width; }).catch(function () {}).then(function () {
            if (io) io.disconnect();
            io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting && !e.target.dataset.done) draw(e.target); }); }, { root: pages, rootMargin: '600px 0px' });
            for (var i = 1; i <= pdf.numPages; i++) { var d = document.createElement('div'); d.className = 'pv-pg'; d.dataset.n = i; size(d); pages.appendChild(d); io.observe(d); }
            pageInfo.textContent = '1 / ' + pdf.numPages;
        });
    }
    function draw(el) {
        var g = gen; el.dataset.done = '1';
        pdf.getPage(+el.dataset.n).then(function (p) {
            var cssW = fitW() * scale, v0 = p.getViewport({ scale: 1 }), v = p.getViewport({ scale: cssW / v0.width * dpr });
            var c = document.createElement('canvas'); c.width = v.width; c.height = v.height; c.style.width = cssW + 'px'; c.style.height = (v.height / dpr) + 'px';
            return p.render({ canvasContext: c.getContext('2d'), viewport: v }).promise.then(function () { if (g !== gen) return; el.style.height = (v.height / dpr) + 'px'; el.innerHTML = ''; el.appendChild(c); });
        }).catch(function () { el.dataset.done = ''; });
    }
    function relayout() { if (!pdf) return; gen++; pages.querySelectorAll('.pv-pg').forEach(function (d) { d.dataset.done = ''; d.innerHTML = ''; size(d); io.unobserve(d); io.observe(d); }); zoomInfo.textContent = Math.round(scale * 100) + '%'; }
    box.querySelector('.pv-in').onclick = function () { scale = Math.min(3, scale + 0.25); relayout(); };
    box.querySelector('.pv-out').onclick = function () { scale = Math.max(0.5, scale - 0.25); relayout(); };
    var t = null; window.addEventListener('resize', function () { clearTimeout(t); t = setTimeout(relayout, 250); });
    pages.addEventListener('scroll', function () {
        if (!pdf) return; var top = pages.scrollTop + 40, cur = 1;
        for (var d = pages.firstChild; d; d = d.nextSibling) { if (d.offsetTop + d.offsetHeight > top) { cur = +d.dataset.n; break; } }
        pageInfo.textContent = cur + ' / ' + pdf.numPages;
    }, { passive: true });
    return function () { if (started) return; started = true; load(0); };
};
</script>
@endonce
<div class="pv-box" id="pv-{{ $pdfId }}">
    <div class="pv-bar">
        <button type="button" class="pv-out" title="Zoom out">−</button><span class="pv-zoom">100%</span><button type="button" class="pv-in" title="Zoom in">+</button>
        <span class="pv-page" style="margin-left:8px">–</span><span class="sp"></span>
        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener">Open ↗</a><a href="{{ $pdfUrl }}" download>Download</a>
    </div>
    <div class="pv-pages"><div class="pv-msg">Loading PDF…</div></div>
</div>
<script>window.pvStart[@json($pdfId)] = window.pvMake(@json($pdfId), @json($pdfUrl));</script>
