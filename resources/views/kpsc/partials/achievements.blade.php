{{-- Stories that load as the visitor scrolls (normal stories + stories synced from Instagram), shown as cards.
     Needs $target (element id of the strip / grid). $mode = 'strip' (home, horizontal) or 'grid' (/stories). Optional $per, $exam (id), $col (grid column class). --}}
@php $mode = $mode ?? 'strip'; $per = $per ?? ($mode === 'grid' ? 18 : 10); @endphp
@once
<style>
    .st-card { flex:none; width:132px; height:210px; border-radius:16px; background:#10243e center/cover; position:relative; cursor:pointer; border:3px solid #fff; outline:2px solid #ff5a5f; box-shadow:0 3px 10px rgba(0,0,0,.2); overflow:hidden; display:block; }
    .st-card.ach { outline-color:#f59e0b; }
    @media (min-width:768px) { .st-card { width:170px; height:270px; } }
    .row .st-card { width:100%; height:auto; aspect-ratio:9/14; }
    .st-card.ach::after { content:""; position:absolute; inset:0; background:linear-gradient(transparent 55%, rgba(0,0,0,.55)); }
    .st-card .pl { position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); color:#fff; font-size:2.2rem; z-index:2; text-shadow:0 2px 8px rgba(0,0,0,.6); }
    .st-card .ti { position:absolute; left:8px; right:8px; bottom:8px; color:#fff; font-size:.74rem; font-weight:700; z-index:2; line-height:1.2; }
    .st-skel { flex:none; width:132px; height:210px; border-radius:16px; border:3px solid transparent; background:linear-gradient(100deg,#e6ebf2 30%,#f5f8fc 50%,#e6ebf2 70%); background-size:300% 100%; animation:stShim 1.2s linear infinite; }
    @media (min-width:768px) { .st-skel { width:170px; height:270px; } } .row .st-skel { width:100%; height:auto; aspect-ratio:9/14; }
    @keyframes stShim { from { background-position:100% 0; } to { background-position:0 0; } }
    #achViewer { position:fixed; inset:0; background:rgba(0,0,0,.94); z-index:3000; display:none; align-items:center; justify-content:center; }
    #achViewer.on { display:flex; }
    #achViewer .box { position:relative; height:min(92vh, 780px); aspect-ratio:9/16; max-width:96vw; background:#000; border-radius:14px; overflow:hidden; }
    #achViewer video, #achViewer img { width:100%; height:100%; object-fit:contain; background:#000; }
    #achViewer .bars { position:absolute; left:8px; right:8px; top:8px; display:flex; gap:3px; z-index:3; } #achViewer .bars i { flex:1; height:3px; background:rgba(255,255,255,.35); border-radius:2px; overflow:hidden; } #achViewer .bars i b { display:block; height:100%; width:0; background:#fff; }
    #achViewer .x, #achViewer .nv { position:absolute; z-index:4; background:rgba(255,255,255,.18); border:0; color:#fff; width:38px; height:38px; border-radius:50%; font-size:1.2rem; }
    #achViewer .x { right:8px; top:18px; } #achViewer .nv.l { left:-52px; top:50%; } #achViewer .nv.r { right:-52px; top:50%; }
    @media (max-width:560px) { #achViewer .nv.l { left:6px; } #achViewer .nv.r { right:6px; } }
</style>
<div id="achViewer" role="dialog" aria-label="Stories">
    <button class="nv l" type="button" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
    <div class="box"><div class="bars"></div><button class="x" type="button" aria-label="Close"><i class="bi bi-x-lg"></i></button><div class="media" style="height:100%"></div></div>
    <button class="nv r" type="button" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
</div>
<script>
    window.StoryViewer = (function () {
        var v = document.getElementById('achViewer'), media = v.querySelector('.media'), bars = v.querySelector('.bars'), cards = [], cur = 0, timer;
        function show(i) {
            cur = (i + cards.length) % cards.length; clearTimeout(timer); var c = cards[cur];
            bars.innerHTML = cards.map(function (x, k) { return '<i><b style="width:' + (k < cur ? '100%' : '0') + '"></b></i>'; }).join('');
            var fill = bars.children[cur].firstChild;
            if (c.dataset.type === 'video') {
                media.innerHTML = '<video playsinline autoplay controls controlsList="nodownload" poster="' + c.dataset.poster + '" src="' + c.dataset.src + '"></video>';
                var vid = media.firstChild; vid.addEventListener('timeupdate', function () { if (vid.duration) fill.style.width = (vid.currentTime / vid.duration * 100) + '%'; }); vid.addEventListener('ended', function () { show(cur + 1); });
            } else {
                media.innerHTML = '<img src="' + c.dataset.src + '" alt="">'; fill.style.transition = 'width 5s linear'; setTimeout(function () { fill.style.width = '100%'; }, 30); timer = setTimeout(function () { show(cur + 1); }, 5000);
            }
            v.classList.add('on');
        }
        function close() { v.classList.remove('on'); media.innerHTML = ''; clearTimeout(timer); }
        v.querySelector('.x').onclick = close; v.querySelector('.nv.l').onclick = function () { show(cur - 1); }; v.querySelector('.nv.r').onclick = function () { show(cur + 1); };
        v.addEventListener('click', function (e) { if (e.target === v) close(); });
        document.addEventListener('keydown', function (e) { if (!v.classList.contains('on')) return; if (e.key === 'Escape') close(); if (e.key === 'ArrowRight') show(cur + 1); if (e.key === 'ArrowLeft') show(cur - 1); });
        return { open: function (list, i) { cards = list; show(i); } };
    })();
</script>
@endonce
<script>
    (function () {
        var box = document.getElementById(@json($target)), grid = @json($mode === 'grid'), col = @json($col ?? 'col-6 col-md-3 col-lg-2'), per = @json($per), page = 0, loading = false, more = true, exam = @json($exam ?? 0);
        function skel(n) { var h = ''; for (var i = 0; i < n; i++) h += (grid ? '<div class="' + col + ' sk"><div class="st-skel"></div></div>' : '<div class="st-skel sk"></div>'); box.insertAdjacentHTML('beforeend', h); }
        function unskel() { [].forEach.call(box.querySelectorAll('.sk'), function (x) { x.remove(); }); }
        function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
        function card(it) {
            var h;
            if (it.kind === 'story') h = '<a class="st-card" href="' + esc(it.href) + '" style="background-image:url(\'' + esc(it.img) + '\')"></a>';
            else h = '<div class="st-card ach" data-type="' + it.type + '" data-src="' + esc(it.src) + '" data-poster="' + esc(it.poster) + '" style="background-image:url(\'' + esc(it.poster) + '\')">' + (it.type === 'video' ? '<i class="bi bi-play-circle-fill pl"></i>' : '') + (it.title ? '<div class="ti">' + esc(it.title) + '</div>' : '') + '</div>';
            return grid ? '<div class="' + col + '">' + h + '</div>' : h;
        }
        function load() {
            if (loading || !more) return; loading = true; page++; skel(grid ? 6 : 4);
            fetch(@json(url('stories-feed')) + '?page=' + page + '&per=' + per + (exam ? '&exam=' + exam : ''), { headers: { Accept: 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (j) { unskel(); box.insertAdjacentHTML('beforeend', j.items.map(card).join('')); more = j.next; if (!j.total && page === 1) box.innerHTML = '<div class="text-muted small">No stories right now.</div>'; loading = false; check(); })
                .catch(function () { unskel(); loading = false; more = false; });
        }
        function check() { if (!more || loading) return; if (grid) { if (box.getBoundingClientRect().bottom - innerHeight < 400) load(); } else if (box.scrollWidth - box.scrollLeft - box.clientWidth < 300) load(); }
        box.addEventListener('click', function (e) { var c = e.target.closest('.ach'); if (!c) return; var list = [].slice.call(box.querySelectorAll('.ach')); window.StoryViewer.open(list, list.indexOf(c)); });
        (grid ? window : box).addEventListener('scroll', check, { passive: true });
        window.addEventListener('resize', check);
        box.reload = function (newExam) { exam = newExam || 0; page = 0; more = true; loading = false; box.innerHTML = ''; load(); };
        load();
    })();
</script>
