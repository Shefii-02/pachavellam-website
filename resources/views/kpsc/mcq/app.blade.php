@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', ($exam->examtitle ?: 'Exam') . ($mode === 'result' ? ' - Result' : ''))
@if ($mode === 'play')@section('no_ads', '1')@endif   {{-- the exam screen has no publisher content: no ads --}}
@section('styles')
<style>
    .mq { --fs:16px; --bg:var(--pv-bg,#f4f6fa); --card:var(--pv-surface,#fff); --bd:var(--pv-border,#e3e7ee); --tx:var(--pv-text,#16202e); --mu:var(--pv-muted,#6b7585); --pri:#1a63e0; --ok:#0a8f5b; --bad:#e0314b; --warn:#d98a00; --mark:#7c4dff;
        max-width:1180px; margin:0 auto; padding:10px 0 90px; color:var(--tx); }
    .mq * { box-sizing:border-box; }
    .mq-top { position:sticky; top:0; z-index:20; display:flex; align-items:center; gap:10px; background:var(--card); border:1px solid var(--bd); border-radius:14px; padding:8px 12px; margin-bottom:10px; }
    .mq-top h1 { font-size:1rem; font-weight:800; margin:0; flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .mq-chip { border-radius:20px; padding:4px 12px; font-weight:800; font-size:.85rem; background:var(--bg); border:1px solid var(--bd); white-space:nowrap; font-variant-numeric:tabular-nums; }
    .mq-chip.warn { background:#fff3d6; color:#8a5500; border-color:transparent; } .mq-chip.crit { background:#fde8e8; color:#a4242a; border-color:transparent; animation:mqp 1s infinite; } @keyframes mqp { 50% { opacity:.55; } }
    .mq-btn { border:1px solid var(--bd); background:var(--card); color:var(--tx); border-radius:10px; padding:8px 14px; font-weight:700; font-size:.88rem; cursor:pointer; }
    .mq-btn.pri { background:var(--pri); border-color:var(--pri); color:#fff; } .mq-btn.ok { background:var(--ok); border-color:var(--ok); color:#fff; } .mq-btn.bad { background:var(--bad); border-color:var(--bad); color:#fff; } .mq-btn.mark { border-color:var(--mark); color:var(--mark); } .mq-btn[disabled] { opacity:.45; }
    .mq-grid { display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:12px; align-items:start; }
    .mq-card { background:var(--card); border:1px solid var(--bd); border-radius:16px; padding:16px; }
    .mq-qh { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px; } .mq-qh b { font-size:1rem; } .mq-qh .sp { flex:1; }
    .mq-q { font-size:calc(var(--fs) * 1.08); line-height:1.6; margin-bottom:14px; overflow-wrap:anywhere; } .mq-q img, .mq-opt img, .mq-sol img { max-width:100%; height:auto; }
    .mq-opts { display:grid; gap:8px; }
    .mq-opt { display:flex; gap:12px; align-items:flex-start; width:100%; text-align:left; background:var(--card); color:var(--tx); border:2px solid var(--bd); border-radius:12px; padding:12px 14px; font-size:var(--fs); cursor:pointer; line-height:1.45; }
    .mq-opt .l { flex:none; width:28px; height:28px; border-radius:50%; border:2px solid var(--bd); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.82rem; }
    .mq-opt:hover { border-color:#9db9ee; } .mq-opt.on { border-color:var(--pri); background:rgba(26,99,224,.08); } .mq-opt.on .l { background:var(--pri); border-color:var(--pri); color:#fff; }
    .mq-opt.right { border-color:var(--ok); background:rgba(10,143,91,.09); } .mq-opt.right .l { background:var(--ok); border-color:var(--ok); color:#fff; } .mq-opt.wrong { border-color:var(--bad); background:rgba(224,49,75,.08); } .mq-opt.wrong .l { background:var(--bad); border-color:var(--bad); color:#fff; }
    .mq-opt .tag { margin-left:auto; flex:none; font-size:.7rem; font-weight:800; border-radius:10px; padding:2px 9px; background:var(--bg); }
    .mq-bar { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; padding-top:12px; border-top:1px solid var(--bd); position:sticky; bottom:0; background:var(--card); } .mq-bar .sp { flex:1; }
    .mq-pal h6 { font-size:.82rem; font-weight:800; margin:0 0 8px; } .mq-nums { display:grid; grid-template-columns:repeat(5,1fr); gap:7px; max-height:48vh; overflow:auto; padding:2px; }
    .mq-n { position:relative; height:38px; border-radius:9px; border:2px solid transparent; font-weight:800; font-size:.85rem; cursor:pointer; background:#e9edf3; color:#52607a; }
    .mq-n.na { background:#fde8e8; color:#a4242a; } .mq-n.an { background:var(--ok); color:#fff; } .mq-n.mk { background:var(--mark); color:#fff; } .mq-n.am { background:var(--mark); color:#fff; } .mq-n.am::after { content:''; position:absolute; right:3px; bottom:3px; width:9px; height:9px; border-radius:50%; background:#3ddc97; border:1.5px solid #fff; }
    .mq-n.cur { border-color:var(--pri); box-shadow:0 0 0 2px rgba(26,99,224,.25); } .mq-n.dim { opacity:.25; }
    .mq-leg { display:grid; grid-template-columns:1fr 1fr; gap:6px 10px; font-size:.74rem; color:var(--mu); margin-top:12px; } .mq-leg i { display:inline-block; width:14px; height:14px; border-radius:4px; vertical-align:-2px; margin-right:6px; }
    .mq-fil { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px; } .mq-fil button, .mq-tabs button { border:1px solid var(--bd); background:var(--card); color:var(--tx); border-radius:16px; padding:4px 12px; font-size:.76rem; font-weight:700; cursor:pointer; } .mq-fil button.on, .mq-tabs button.on { background:var(--pri); border-color:var(--pri); color:#fff; }
    .mq-tabs { display:flex; gap:8px; margin:12px 0; overflow-x:auto; } .mq-tabs button { padding:8px 20px; font-size:.9rem; }
    .mq-ov { position:fixed; inset:0; z-index:1070; background:rgba(0,0,0,.55); display:none; align-items:center; justify-content:center; padding:16px; } .mq-ov.on { display:flex; }
    .mq-modal { background:var(--card); color:var(--tx); border-radius:16px; width:100%; max-width:460px; padding:20px; } .mq-modal h5 { font-weight:800; margin-bottom:10px; }
    .mq-tbl { width:100%; border-collapse:collapse; margin:10px 0; font-size:.9rem; } .mq-tbl td { border:1px solid var(--bd); padding:7px 10px; } .mq-tbl td:last-child { text-align:center; font-weight:800; }
    .mq-pbtn { display:none; }
    @media (max-width:900px) {
        .mq-grid { grid-template-columns:1fr; } .mq-pbtn { display:inline-block; }
        .mq-pal { position:fixed; left:0; right:0; bottom:0; z-index:1050; border-radius:18px 18px 0 0 !important; transform:translateY(105%); transition:transform .22s; box-shadow:0 -8px 30px rgba(0,0,0,.25); } .mq-pal.open { transform:none; }
        .mq-nums { max-height:38vh; } .mq-bar .mq-btn { flex:1 1 auto; padding:10px 8px; }
    }
    /* result */
    .mq-hero { display:flex; gap:18px; align-items:center; flex-wrap:wrap; } .mq-ring { --p:0; width:120px; height:120px; border-radius:50%; background:conic-gradient(var(--ok) calc(var(--p) * 1%), #e3e7ee 0); display:flex; align-items:center; justify-content:center; flex:none; }
    .mq-ring div { width:92px; height:92px; border-radius:50%; background:var(--card); display:flex; flex-direction:column; align-items:center; justify-content:center; } .mq-ring b { font-size:1.35rem; line-height:1; } .mq-ring small { color:var(--mu); font-size:.72rem; }
    .mq-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; flex:1; min-width:280px; } .mq-stat { border-radius:14px; padding:12px; background:var(--bg); border:1px solid var(--bd); } .mq-stat b { font-size:1.15rem; display:block; } .mq-stat small { color:var(--mu); font-size:.72rem; font-weight:700; }
    .mq-dist { display:flex; height:14px; border-radius:8px; overflow:hidden; background:#e3e7ee; margin:10px 0 8px; } .mq-dist i { display:block; height:100%; }
    .mq-lg { display:flex; gap:16px; flex-wrap:wrap; font-size:.85rem; } .mq-lg span::before { content:''; display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; background:var(--c); }
    .mq-cmp { display:grid; gap:10px; } .mq-cmp .r { display:grid; grid-template-columns:70px 1fr 56px; gap:10px; align-items:center; font-size:.85rem; } .mq-cmp .t { height:14px; border-radius:8px; background:#e3e7ee; overflow:hidden; } .mq-cmp .t i { display:block; height:100%; border-radius:8px; }
    .mq-note { border-radius:12px; padding:10px 14px; font-size:.9rem; margin-bottom:10px; } .mq-note.bad { background:#fde8e8; color:#a4242a; } .mq-note.ok { background:#e7f6ef; color:#0a6b46; } .mq-note.info { background:#e8f0fe; color:#1a4fb0; }
    .mq-sc { margin-bottom:12px; } .mq-sc .hd { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px; font-size:.82rem; color:var(--mu); } .mq-sc .hd b { color:var(--tx); font-size:.95rem; }
    .mq-st { font-size:.68rem; font-weight:800; border-radius:10px; padding:2px 10px; } .mq-st.correct { background:#e7f6ef; color:#0a6b46; } .mq-st.wrong { background:#fde8e8; color:#a4242a; } .mq-st.skipped { background:#eef0f3; color:#4b5563; } .mq-st.rev { background:#ece6ff; color:#5b34d6; }
    .mq-sol { margin-top:12px; border-radius:12px; background:#eaf3ff; color:#12315c; padding:12px 14px; font-size:calc(var(--fs) * .95); line-height:1.6; } .mq-sol h6 { font-weight:800; font-size:.85rem; margin-bottom:6px; }
    .mq-row { display:flex; align-items:center; gap:12px; padding:9px 12px; border-radius:10px; border:1px solid var(--bd); margin-bottom:6px; } .mq-row.me { background:#e8f0fe; border-color:#9db9ee; } .mq-row .rk { width:28px; text-align:center; font-weight:800; color:var(--mu); } .mq-row .nm { flex:1; min-width:0; font-weight:700; } .mq-row .mk { font-weight:800; }
    @media (max-width:640px) { .mq-stats { grid-template-columns:1fr 1fr; } .mq-top h1 { font-size:.9rem; } }
    @media (prefers-color-scheme:dark) { .mq { --bg:#0f141c; --card:#171e29; --bd:#2a3342; --tx:#e8edf5; --mu:#93a0b4; } .mq-n { background:#222c3b; color:#a8b4c8; } .mq-n.na { background:#3a1f26; color:#ff9aa9; } .mq-sol { background:#16273f; color:#cfe0ff; } .mq-note.info { background:#14284a; color:#a8c4ff; } .mq-chip.warn { background:#3a2c0a; color:#ffd27a; } }
</style>
@endsection
@php
    $mcqData = $mode === 'play'
        ? ['mode' => 'play', 'title' => $exam->examtitle ?: 'Exam', 'urls' => $urls, 'back' => $back, 'total' => $total]
        : ['mode' => 'result', 'result' => $result, 'attempts' => $attempts, 'retake' => $retake, 'back' => $back, 'examId' => $exam->id];
@endphp
@section('content')
<div class="page-content-wrapper"><div class="container"><div class="mq" id="mq"></div></div></div>
<div class="mq-ov" id="mqOv"><div class="mq-modal" id="mqModal"></div></div>
@if ($mode === 'result')
    @unless (View::hasSection('no_ads')){!! single_ads_show() !!}@endunless
@endif
@include('kpsc.partials.exam-report', ['examId' => $exam->id])
@endsection
@section('scripts')
<script>
window.MCQ = @json($mcqData);
window.MCQ.csrf = @json(csrf_token());
</script>
<script>
(function () {
    var C = window.MCQ, root = document.getElementById('mq');
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var clock = function (s) { s = Math.max(0, Math.round(s)); return (s >= 3600 ? pad(Math.floor(s / 3600)) + ':' : '') + pad(Math.floor(s / 60) % 60) + ':' + pad(s % 60); };
    var L = ['', 'A', 'B', 'C', 'D'];
    var fs = 16; try { fs = parseInt(localStorage.getItem('mq_fs')) || 16; } catch (e) {}
    function setFs(d) { fs = Math.min(24, Math.max(13, fs + d)); root.style.setProperty('--fs', fs + 'px'); try { localStorage.setItem('mq_fs', fs); } catch (e) {} }
    root.style.setProperty('--fs', fs + 'px');
    var modal = function (html) { $('mqModal').innerHTML = html; $('mqOv').classList.add('on'); };
    var closeModal = function () { $('mqOv').classList.remove('on'); };
    var post = function (url, body, keep) { return fetch(url, { method: 'POST', credentials: 'same-origin', keepalive: !!keep, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': C.csrf }, body: JSON.stringify(body) }); };

    /* ===================== the exam ===================== */
    function play() {
        var S = { qs: [], ans: {}, marked: {}, vis: {}, sec: {}, cur: 0, endAt: null, t0: Date.now(), e0: 0, qs0: Date.now(), filter: 'all', busy: false }, saveT = null, tickT = null;
        root.innerHTML = '<div class="mq-card">Loading the exam…</div>';
        fetch(C.urls.questions, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { if (!r.ok) throw r.status; return r.json(); }).then(function (d) {
            S.qs = d.questions; if (!S.qs.length) { root.innerHTML = '<div class="mq-card">This exam has no questions yet.</div>'; return; }
            var sv = d.saved || {};
            Object.keys(sv.answers || {}).forEach(function (k) { S.ans[k] = sv.answers[k]; });
            (sv.marked || []).forEach(function (k) { S.marked[k] = 1; }); (sv.visited || []).forEach(function (k) { S.vis[k] = 1; });
            Object.keys(sv.seconds || {}).forEach(function (k) { S.sec[k] = sv.seconds[k]; });
            S.cur = Math.min(S.qs.length - 1, sv.cur || 0); S.vis[S.qs[S.cur].id] = 1;
            S.e0 = d.elapsed || 0; S.t0 = Date.now(); S.qs0 = Date.now();
            if (d.remaining != null) S.endAt = Date.now() + d.remaining * 1000;
            shell(); draw(); tickT = setInterval(tick, 1000); tick();
            document.addEventListener('visibilitychange', function () { if (document.hidden) save(true); });
        }).catch(function (c) { root.innerHTML = '<div class="mq-card">' + (c === 404 ? 'No exam is in progress. <a href="' + esc(C.back) + '">Go back</a> and start it again.' : 'Could not load the exam. Refresh the page.') + '</div>'; });

        function shell() {
            root.innerHTML = '<div class="mq-top"><h1>' + esc(C.title) + '</h1><span class="mq-chip" id="mqT">--:--</span>'
                + '<button class="mq-btn" id="fsm" title="Smaller text">A−</button><button class="mq-btn" id="fsp" title="Larger text">A+</button>'
                + '<button class="mq-btn mq-pbtn" id="palBtn">☰ <span id="palN"></span></button><button class="mq-btn bad" id="subBtn">Submit</button></div>'
                + '<div class="mq-grid"><div class="mq-card" id="mqQ"></div><div class="mq-card mq-pal" id="mqPal"></div></div>';
            $('fsm').onclick = function () { setFs(-1); }; $('fsp').onclick = function () { setFs(1); };
            $('palBtn').onclick = function () { $('mqPal').classList.toggle('open'); }; $('subBtn').onclick = confirmSubmit;
        }
        function st(i) {
            var id = S.qs[i].id, a = S.ans[id] != null, m = S.marked[id];
            return a && m ? 'am' : m ? 'mk' : a ? 'an' : S.vis[id] ? 'na' : 'nv';
        }
        function counts() {
            var c = { an: 0, na: 0, mk: 0, nv: 0 };
            S.qs.forEach(function (q, i) { var s = st(i); if (s === 'an' || s === 'am') c.an++; if (s === 'na') c.na++; if (s === 'mk' || s === 'am') c.mk++; if (s === 'nv') c.nv++; });
            return c;
        }
        function draw() { drawQ(); drawPal(); }
        function drawQ() {
            var q = S.qs[S.cur], sel = S.ans[q.id], last = S.cur === S.qs.length - 1;
            var opts = Object.keys(q.o).map(function (k) { return '<button type="button" class="mq-opt' + (sel == k ? ' on' : '') + '" data-o="' + k + '"><span class="l">' + L[k] + '</span><span>' + q.o[k] + '</span></button>'; }).join('');
            $('mqQ').innerHTML = '<div class="mq-qh"><b>Question ' + (S.cur + 1) + ' <span style="color:var(--mu);font-weight:600">of ' + S.qs.length + '</span></b><span class="sp"></span>'
                + (S.marked[q.id] ? '<span class="mq-st rev">Marked for review</span>' : '') + '</div><div class="mq-q">' + q.q + '</div><div class="mq-opts">' + opts + '</div>'
                + '<div class="mq-bar"><button class="mq-btn" id="bPrev"' + (S.cur === 0 ? ' disabled' : '') + '>‹ Previous</button><button class="mq-btn" id="bClr"' + (sel == null ? ' disabled' : '') + '>Clear</button><span class="sp"></span>'
                + '<button class="mq-btn mark" id="bMark">' + (S.marked[q.id] ? 'Unmark review' : 'Mark for review') + (last ? '' : ' & Next') + '</button>'
                + '<button class="mq-btn pri" id="bNext">' + (last ? 'Save' : 'Save & Next ›') + '</button></div>';
            $('mqQ').querySelectorAll('.mq-opt').forEach(function (b) { b.onclick = function () { pick(b.dataset.o); }; });
            $('bPrev').onclick = function () { go(S.cur - 1); }; $('bClr').onclick = clear; $('bMark').onclick = mark; $('bNext').onclick = next;
        }
        function drawPal() {
            var c = counts();
            var fil = [['all', 'All'], ['an', 'Answered'], ['na', 'Not answered'], ['mk', 'Marked'], ['nv', 'Not visited']].map(function (f) { return '<button data-f="' + f[0] + '" class="' + (S.filter === f[0] ? 'on' : '') + '">' + f[1] + '</button>'; }).join('');
            var nums = S.qs.map(function (q, i) {
                var s = st(i), dim = S.filter !== 'all' && !(S.filter === 'an' ? (s === 'an' || s === 'am') : S.filter === 'mk' ? (s === 'mk' || s === 'am') : s === S.filter);
                return '<button class="mq-n ' + s + (i === S.cur ? ' cur' : '') + (dim ? ' dim' : '') + '" data-i="' + i + '">' + (i + 1) + '</button>';
            }).join('');
            $('mqPal').innerHTML = '<h6>Question palette <span style="float:right;color:var(--mu)">' + c.an + '/' + S.qs.length + ' answered</span></h6><div class="mq-fil">' + fil + '</div><div class="mq-nums">' + nums + '</div>'
                + '<div class="mq-leg"><span><i style="background:var(--ok)"></i>Answered ' + c.an + '</span><span><i style="background:#fde8e8"></i>Not answered ' + c.na + '</span><span><i style="background:var(--mark)"></i>Marked ' + c.mk + '</span><span><i style="background:#e9edf3"></i>Not visited ' + c.nv + '</span></div>'
                + '<p style="font-size:.72rem;color:var(--mu);margin:10px 0 0">Keys: A–D choose · ← → move · M mark · C clear</p>';
            $('palN').textContent = c.an + '/' + S.qs.length;
            $('mqPal').querySelectorAll('[data-f]').forEach(function (b) { b.onclick = function () { S.filter = b.dataset.f; drawPal(); }; });
            $('mqPal').querySelectorAll('[data-i]').forEach(function (b) { b.onclick = function () { go(+b.dataset.i); $('mqPal').classList.remove('open'); }; });
        }
        function flush() { var id = S.qs[S.cur].id, now = Date.now(); S.sec[id] = (S.sec[id] || 0) + Math.round((now - S.qs0) / 1000); S.qs0 = now; }
        function go(i) { if (i < 0 || i >= S.qs.length) return; flush(); S.cur = i; S.vis[S.qs[i].id] = 1; draw(); queue(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
        function pick(k) { var id = S.qs[S.cur].id; if (S.ans[id] == k) delete S.ans[id]; else S.ans[id] = +k; draw(); queue(); }
        function clear() { delete S.ans[S.qs[S.cur].id]; draw(); queue(); }
        function next() { if (S.cur < S.qs.length - 1) go(S.cur + 1); else { draw(); queue(); } }
        function mark() { var id = S.qs[S.cur].id; if (S.marked[id]) { delete S.marked[id]; draw(); queue(); } else { S.marked[id] = 1; next(); if (S.cur === S.qs.length - 1) { draw(); } } }
        function payload() { flush(); return { answers: S.ans, marked: Object.keys(S.marked).map(Number), seconds: S.sec, visited: Object.keys(S.vis).map(Number), cur: S.cur }; }
        function queue() { clearTimeout(saveT); saveT = setTimeout(function () { save(false); }, 900); }
        function save(keep) { if (S.busy) return; post(C.urls.progress, payload(), keep).catch(function () {}); }
        setInterval(function () { if (!S.busy && S.qs.length) save(false); }, 25000);
        function tick() {
            var t = $('mqT'); if (!t) return;
            if (S.endAt) {
                var r = Math.max(0, Math.round((S.endAt - Date.now()) / 1000));
                t.textContent = '⏱ ' + clock(r); t.className = 'mq-chip' + (r <= 60 ? ' crit' : r <= 300 ? ' warn' : '');
                if (r === 0 && !S.busy) submit(true);
            } else { t.textContent = '⏱ ' + clock(S.e0 + (Date.now() - S.t0) / 1000); }
        }
        function confirmSubmit() {
            var c = counts();
            modal('<h5>Submit the test?</h5><table class="mq-tbl"><tr><td>Answered</td><td>' + c.an + '</td></tr><tr><td>Not answered</td><td>' + (S.qs.length - c.an) + '</td></tr><tr><td>Marked for review</td><td>' + c.mk + '</td></tr><tr><td>Total questions</td><td>' + S.qs.length + '</td></tr></table>'
                + (c.an < S.qs.length ? '<div class="mq-note info">' + (S.qs.length - c.an) + ' question(s) are still unanswered.</div>' : '')
                + '<div style="display:flex;gap:10px;justify-content:flex-end"><button class="mq-btn" id="mCancel">Keep going</button><button class="mq-btn bad" id="mGo">Submit test</button></div>');
            $('mCancel').onclick = closeModal; $('mGo').onclick = function () { submit(false); };
        }
        function submit(auto) {
            if (S.busy) return; S.busy = true; clearInterval(tickT);
            modal('<h5>' + (auto ? 'Time is up!' : 'Submitting…') + '</h5><p>Saving your answers and preparing your result…</p>');
            var p = payload();
            post(C.urls.submit, { answers: p.answers, marked: p.marked, seconds: p.seconds }).then(function (r) { if (!r.ok) throw 0; return r.json(); })
                .then(function (d) { location.href = d.redirect; })
                .catch(function () { S.busy = false; modal('<h5>Could not submit</h5><p>Check your connection and try again. Your answers are saved.</p><div style="text-align:right"><button class="mq-btn pri" id="mRe">Try again</button></div>'); $('mRe').onclick = function () { submit(false); }; });
        }
        document.addEventListener('keydown', function (e) {
            if (S.busy || $('mqOv').classList.contains('on') || /INPUT|TEXTAREA/.test(e.target.tagName) || e.ctrlKey || e.metaKey) return;
            var k = e.key.toLowerCase(), q = S.qs[S.cur]; if (!q) return;
            if ('abcd'.indexOf(k) > -1 && k.length === 1 && q.o['abcd'.indexOf(k) + 1]) pick('abcd'.indexOf(k) + 1);
            else if (k === 'arrowright' || k === 'n') next(); else if (k === 'arrowleft' || k === 'p') go(S.cur - 1); else if (k === 'm') mark(); else if (k === 'c') clear();
        });
        window.addEventListener('beforeunload', function () { if (!S.busy && S.qs.length) save(true); });
    }

    /* ===================== the result ===================== */
    function result() {
        var R = C.result, tab = 'ov', filter = 'all';
        var tm = function (s) { return clock(s); };
        var open = function (n) { var b = $('xrOpen'); if (b) { $('xrQ').value = n; b.click(); } };
        function head() {
            var att = C.attempts.length > 1 ? '<select id="attSel" class="mq-btn">' + C.attempts.map(function (a) { return '<option value="' + a.no + '"' + (a.no === R.attemptNo ? ' selected' : '') + '>Attempt ' + a.no + ' (' + a.mark + ')' + (a.no === 1 ? ' · leaderboard' : '') + '</option>'; }).join('') + '</select>' : '';
            return '<div class="mq-card" style="margin-bottom:10px"><div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap"><div style="flex:1;min-width:220px"><h1 style="font-size:1.15rem;font-weight:800;margin:0">' + esc(R.title) + '</h1><small style="color:var(--mu)">Attempt ' + R.attemptNo + ' · ' + esc(R.at) + ' · ' + R.total + ' questions</small></div>'
                + att + '<a class="mq-btn" href="' + esc(C.back) + '">All exams</a><a class="mq-btn bad" href="' + esc(C.retake) + '">Re-attempt</a></div></div>'
                + (!R.isFirst ? '<div class="mq-note info">This is attempt ' + R.attemptNo + '. Rank and leaderboard use your <b>first attempt</b>.</div>' : '')
                + (R.cutoff != null ? (R.belowCutoff ? '<div class="mq-note bad"><b>Below cutoff.</b> Your score ' + R.score + ' is ' + (Math.round((R.cutoff - R.score) * 100) / 100) + ' short of the cutoff ' + R.cutoff + '. Keep practising!</div>' : '<div class="mq-note ok"><b>Above cutoff</b> (' + R.cutoff + '). Well done!</div>') : '')
                + '<div class="mq-tabs"><button data-t="ov" class="' + (tab === 'ov' ? 'on' : '') + '">Performance</button><button data-t="sol" class="' + (tab === 'sol' ? 'on' : '') + '">Solutions</button><button data-t="lb" class="' + (tab === 'lb' ? 'on' : '') + '">Leaderboard</button></div>';
        }
        function bar(label, v, color) { var w = R.max ? Math.max(0, Math.min(100, v * 100 / R.max)) : 0; return '<div class="r"><span>' + label + '</span><div class="t"><i style="width:' + w + '%;background:' + color + '"></i></div><b>' + v + '</b></div>'; }
        function overview() {
            var pctS = R.max ? Math.max(0, R.score * 100 / R.max) : 0, tot = R.total || 1;
            var slow = R.review.filter(function (q) { return q.sec > 0; }).sort(function (a, b) { return b.sec - a.sec; }).slice(0, 3);
            var avgQ = (R.right + R.wrong) ? Math.round(R.sec / (R.right + R.wrong)) : 0;
            return '<div class="mq-card" style="margin-bottom:10px"><div class="mq-hero"><div class="mq-ring" style="--p:' + pctS + '"><div><b>' + R.score + '</b><small>of ' + R.max + '</small></div></div>'
                + '<div class="mq-stats"><div class="mq-stat"><b>' + (R.rank ? '#' + R.rank : '–') + '</b><small>Rank' + (R.participants ? ' of ' + R.participants : '') + '</small></div>'
                + '<div class="mq-stat"><b>' + (R.percentile != null ? R.percentile : '–') + '</b><small>Percentile</small></div>'
                + '<div class="mq-stat"><b>' + R.accuracy + '%</b><small>Accuracy</small></div>'
                + '<div class="mq-stat"><b>' + tm(R.sec) + '</b><small>Time' + (R.duration ? ' of ' + tm(R.duration) : '') + '</small></div></div></div>'
                + (!R.ended ? '<p style="font-size:.78rem;color:var(--mu);margin:10px 0 0">Rank is provisional until the exam ends.</p>' : '') + '</div>'
                + '<div class="mq-card" style="margin-bottom:10px"><h6 style="font-weight:800">Question distribution</h6><div class="mq-dist"><i style="width:' + R.right * 100 / tot + '%;background:var(--ok)"></i><i style="width:' + R.wrong * 100 / tot + '%;background:var(--bad)"></i></div>'
                + '<div class="mq-lg"><span style="--c:var(--ok)">Correct ' + R.right + '</span><span style="--c:var(--bad)">Wrong ' + R.wrong + ' (−' + R.negative + ' each)</span><span style="--c:#c3cad6">Unattempted ' + R.skipped + '</span></div></div>'
                + '<div class="mq-card" style="margin-bottom:10px"><h6 style="font-weight:800">How you compare</h6><div class="mq-cmp">' + bar('You', R.score, 'var(--pri)') + bar('Average', R.average, '#f5a524') + bar('Topper', R.topper, 'var(--ok)') + '</div></div>'
                + '<div class="mq-card"><h6 style="font-weight:800">Speed</h6><p style="margin:0 0 8px;font-size:.9rem">You spent about <b>' + tm(avgQ) + '</b> on each question you answered.</p>'
                + (slow.length ? '<div style="font-size:.85rem;color:var(--mu)">Most time spent on: ' + slow.map(function (q) { return '<a href="#" data-q="' + q.n + '">Q' + q.n + ' (' + tm(q.sec) + ')</a>'; }).join(', ') + '</div>' : '') + '</div>';
        }
        function solutions() {
            var f = { all: R.review.length, correct: 0, wrong: 0, skipped: 0, marked: 0 }; R.review.forEach(function (q) { f[q.status]++; if (q.marked) f.marked++; });
            var chips = [['all', 'All'], ['correct', 'Correct'], ['wrong', 'Wrong'], ['skipped', 'Unattempted'], ['marked', 'Marked']].map(function (c) { return '<button data-f="' + c[0] + '" class="' + (filter === c[0] ? 'on' : '') + '">' + c[1] + ' ' + f[c[0]] + '</button>'; }).join('');
            var list = R.review.filter(function (q) { return filter === 'all' || (filter === 'marked' ? q.marked : q.status === filter); }).map(function (q) {
                var opts = Object.keys(q.o).map(function (k) { k = +k; var cls = k === q.correct ? ' right' : (k === q.chosen ? ' wrong' : ''), tag = k === q.correct ? '<span class="tag" style="color:var(--ok)">Correct answer</span>' : (k === q.chosen ? '<span class="tag" style="color:var(--bad)">Your answer</span>' : '');
                    return '<div class="mq-opt' + cls + '" style="cursor:default"><span class="l">' + L[k] + '</span><span>' + q.o[k] + '</span>' + (k === q.correct && k === q.chosen ? '<span class="tag" style="color:var(--ok)">Your answer ✓</span>' : tag) + '</div>'; }).join('');
                return '<div class="mq-card mq-sc" id="sq' + q.n + '"><div class="hd"><b>Q' + q.n + '</b><span class="mq-st ' + q.status + '">' + { correct: 'Correct', wrong: 'Wrong', skipped: 'Unattempted' }[q.status] + '</span>' + (q.marked ? '<span class="mq-st rev">Marked</span>' : '')
                    + '<span style="flex:1"></span><span>You: ' + tm(q.sec) + '</span>' + (q.avgSec != null ? '<span>Avg: ' + tm(q.avgSec) + '</span>' : '') + (q.pct != null ? '<span>' + q.pct + '% answered correctly</span>' : '') + '</div>'
                    + '<div class="mq-q">' + q.q + '</div><div class="mq-opts">' + opts + '</div>'
                    + (q.notes ? '<div class="mq-sol"><h6>Solution / Notes</h6>' + q.notes + '</div>' : '')
                    + '<div style="margin-top:10px"><button class="mq-btn" data-rep="' + q.n + '">⚑ Report</button></div></div>';
            }).join('');
            return '<div class="mq-fil">' + chips + '</div>' + (list || '<div class="mq-card">Nothing in this filter.</div>');
        }
        function board() {
            var me = R.board.some(function (b) { return b.me; });
            return '<div class="mq-card"><h6 style="font-weight:800;margin-bottom:10px">Leaderboard · first attempts</h6>' + (R.board.length ? R.board.map(function (b) { return '<div class="mq-row' + (b.me ? ' me' : '') + '"><span class="rk">' + b.rank + '</span><span class="nm">' + esc(b.name) + (b.me ? ' (You)' : '') + '</span><span style="color:var(--mu);font-size:.8rem">' + tm(b.sec) + '</span><span class="mk">' + b.mark + '</span></div>'; }).join('') : '<p>No results yet.</p>')
                + (!me && R.rank ? '<div class="mq-row me"><span class="rk">#' + R.rank + '</span><span class="nm">You</span><span class="mk">' + R.score + '</span></div>' : '') + '</div>';
        }
        function draw() {
            root.innerHTML = head() + (tab === 'ov' ? overview() : tab === 'sol' ? solutions() : board());
            root.querySelectorAll('[data-t]').forEach(function (b) { b.onclick = function () { tab = b.dataset.t; draw(); }; });
            root.querySelectorAll('[data-f]').forEach(function (b) { b.onclick = function () { filter = b.dataset.f; draw(); }; });
            root.querySelectorAll('[data-rep]').forEach(function (b) { b.onclick = function () { open(b.dataset.rep); }; });
            root.querySelectorAll('[data-q]').forEach(function (a) { a.onclick = function (e) { e.preventDefault(); tab = 'sol'; filter = 'all'; draw(); var t = $('sq' + a.dataset.q); if (t) t.scrollIntoView({ behavior: 'smooth' }); }; });
            var sel = $('attSel'); if (sel) sel.onchange = function () { location.href = location.pathname + '?attempt=' + sel.value; };
        }
        draw();
    }
    if (C.mode === 'play') play(); else result();
})();
</script>
@endsection
