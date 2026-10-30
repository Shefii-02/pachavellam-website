{{-- "Upload marks" drawer for the OMR exam cards: the mark form (kpsc/model-exam-mark-form) loads into it --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="markDrawer" aria-labelledby="markTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="markTitle">Upload your marks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="markBody"></div>
</div>
<script>
window.addEventListener('load', function () {
    var el = document.getElementById('markDrawer'); if (!el || !window.bootstrap) return;
    var oc = new bootstrap.Offcanvas(el), body = document.getElementById('markBody');
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-mark]'); if (!b) return;
        document.getElementById('markTitle').textContent = b.dataset.title ? 'Upload marks: ' + b.dataset.title : 'Upload your marks';
        body.innerHTML = '<div class="text-muted small">Loading…</div>'; oc.show();
        fetch(@json(url('kpsc/model-exam-mark-form')) + '?id=' + encodeURIComponent(b.dataset.mark), { credentials: 'same-origin' })
            .then(function (r) { return r.text(); }).then(function (h) { body.innerHTML = h; })
            .catch(function () { body.innerHTML = '<div class="text-danger small">Could not load the form.</div>'; });
    });
});
</script>
{{-- leaderboard drawer + "My attempts" loader for the OMR exam cards (series-card partial) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="lbDrawer" aria-labelledby="lbTitle">
    <div class="lb-head">
        <div class="d-flex justify-content-between align-items-start"><div><h5 id="lbTitle">Leaderboard</h5><small id="lbSub"></small></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
        <div class="lb-podium" id="lbPodium"></div>
    </div>
    <div id="lbList"></div>
    <div id="lbMe"><div class="tag">Your rank</div><div class="lb-row" id="lbMeRow"></div><div id="lbCut" class="small px-3 pb-2"></div></div>
</div>
<script>
window.addEventListener('load', function () {
    var el = document.getElementById('lbDrawer'); if (!el || !window.bootstrap) return;
    var oc = new bootstrap.Offcanvas(el), list = document.getElementById('lbList'), pod = document.getElementById('lbPodium');
    var cur = null, curBase = null, page = 0, more = false, busy = false, sentinel = null, seen = {};
    var urlBase = @json(url('kpsc/model-exam-leaderboard-data'));
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var ini = function (n) { return esc((n || '?').trim().charAt(0).toUpperCase()); };
    function row(a) {
        return '<div class="lb-row' + (a.me ? ' me' : '') + '"><span class="rk">' + a.rank + '</span><span class="nm">' + esc(a.name) + (a.me ? ' (You)' : '') + '<small>' + a.right + ' right · ' + a.wrong + ' wrong</small></span><span class="mk">' + a.mark + '</span></div>';
    }
    function podium(rows) {
        var o =  [[0, 'p1'], [1, 'p2'], [2, 'p3']].filter(function (x) { return rows[x[0]]; });
        pod.innerHTML = o.map(function (x) {
            var a = rows[x[0]];
            return '<div class="lb-pd ' + x[1] + (a.me ? ' me' : '') + '"><div class="av">' + (x[0] === 0 ? '<i class="bi bi-trophy-fill crown"></i>' : '') + ini(a.name) + '</div><div class="nm">' + esc(a.name) + '</div><div class="mk">' + a.mark + ' marks</div><div class="bar">' + a.rank + '</div></div>';
        }).join('');
    }
    function load() {
        if (busy || !cur) return; busy = true;
        fetch((curBase || urlBase) + '/' + cur + '?page=' + (page + 1), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
            busy = false; page++;
            if (!d.ended) { list.innerHTML = '<div class="lb-msg"><i class="bi bi-hourglass-split d-block fs-3"></i>The leaderboard opens when the exam ends.</div>'; return; }
            if (sentinel) sentinel.remove();
            if (page === 1) {
                document.getElementById('lbSub').textContent = d.total + ' student' + (d.total == 1 ? '' : 's') + (d.cutoff != null ? ' · Cutoff ' + d.cutoff : '');
                podium(d.rows.slice(0, 3)); list.innerHTML = '';
                if (!d.rows.length) list.innerHTML = '<div class="lb-msg">No marks uploaded yet.</div>';
                if (d.me) {
                    document.getElementById('lbMeRow').innerHTML = '<span class="rk">#' + d.me.rank + '</span><span class="nm">' + esc(d.me.name) + '<small>' + d.me.right + ' right · ' + d.me.wrong + ' wrong</small></span><span class="mk">' + d.me.mark + '</span>';
                    var cut = document.getElementById('lbCut');
                    cut.innerHTML = d.cutoff == null ? '' : (d.me.below
                        ? '<b style="color:#a4242a"><i class="bi bi-exclamation-triangle-fill"></i> Below cutoff.</b> Your mark ' + d.me.mark + ' is ' + (Math.round((d.cutoff - d.me.mark) * 100) / 100) + ' short of the cutoff ' + d.cutoff + '. Keep practising!'
                        : '<b style="color:#0a6b46"><i class="bi bi-check-circle-fill"></i> Above cutoff</b> (' + d.cutoff + '). Well done!');
                    document.getElementById('lbMe').style.display = 'block';
                }
            }
            list.insertAdjacentHTML('beforeend', d.rows.filter(function (a) { return a.rank > 3; }).map(row).join(''));
            more = d.more;
            if (more) {
                sentinel = document.createElement('div'); sentinel.className = 'lb-msg'; sentinel.textContent = 'Loading more…'; list.appendChild(sentinel); io.observe(sentinel);
            }
        }).catch(function () { busy = false; if (page === 0) list.innerHTML = '<div class="lb-msg">Could not load the leaderboard.</div>'; });
    }
    var io = new IntersectionObserver(function (es) { if (es[0].isIntersecting && more) { io.disconnect(); load(); } }, { root: list, rootMargin: '150px' });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-lb]'); if (!b) return;
        cur = b.dataset.lb; curBase = b.dataset.lbBase || null; page = 0; more = false; busy = false; sentinel = null;
        document.getElementById('lbTitle').textContent = b.dataset.title || 'Leaderboard'; document.getElementById('lbSub').textContent = '';
        pod.innerHTML = ''; list.innerHTML = '<div class="lb-msg">Loading…</div>'; document.getElementById('lbMe').style.display = 'none';
        oc.show(); load();
    });
});
</script>
<script>
(function () {
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var urlBase = @json(url('kpsc/model-exam-summary'));
    function fill(card, d) {
        var last = d.mine[0], el = card.querySelector('.omr-last');
        if (el) el.textContent = last ? 'Last: ' + last.mark + ' marks · ' + last.at : '';
        var mine = card.querySelector('[data-w=mine] .omr-body');
        mine.innerHTML = !d.loggedIn ? 'Sign in to see your attempts.' : (d.mine.length ? d.mine.map(function (a, i) {
            return '<div class="ol"><span>#' + (d.mine.length - i) + ' · ' + esc(a.at) + '</span><b>' + a.mark + '</b><span>' + a.right + '✓ ' + a.wrong + '✗</span></div>';
        }).join('') + (d.cutoff != null ? '<div class="mt-1" style="color:' + (d.mine[0].below ? '#a4242a' : '#0a6b46') + '">' + (d.mine[0].below
            ? '<b>Below cutoff</b> (' + d.cutoff + '): your latest mark ' + d.mine[0].mark + ' is short by ' + (Math.round((d.cutoff - d.mine[0].mark) * 100) / 100) + '.'
            : '<b>Above cutoff</b> (' + d.cutoff + ')') + '</div>' : '') : 'No attempt yet.');
    }
    var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
        es.forEach(function (e) {
            if (!e.isIntersecting) return;
            io.unobserve(e.target);
            var c = e.target;
            fetch((c.dataset.sum || urlBase) + '/' + c.dataset.omr, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); }).then(function (d) { fill(c, d); })
                .catch(function () { c.querySelectorAll('.omr-body').forEach(function (b) { b.textContent = 'Could not load.'; }); });
        });
    }, { rootMargin: '200px' }) : null;
    function scan(root) { if (io) [].forEach.call(root.querySelectorAll('[data-omr]:not([data-seen])'), function (c) { c.dataset.seen = 1; io.observe(c); }); }
    scan(document);
    var list = document.getElementById('srOthers');
    if (list && window.MutationObserver) new MutationObserver(function () { scan(list); }).observe(list, { childList: true });
})();
</script>
