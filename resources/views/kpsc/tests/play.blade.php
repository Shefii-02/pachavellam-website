@extends('kpsc.tests.layout')
@section('title', $title)
@section('styles')
<style>
    body { background: var(--pv-surface); display: flex; flex-direction: column; min-height: 100vh; }
    .p-sec { flex: 1; min-width: 0; border: 0; background: none; text-align: left; color: var(--pv-text); padding: 0; }
    .p-sec b { font-size: 1.02rem; display: inline-flex; align-items: center; gap: 6px; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .p-sec b i { color: #2f80ed; font-size: .9rem; }
    .p-sec small { display: block; color: var(--pv-muted); font-size: .8rem; } .p-sec small span { color: #e5484d; font-weight: 700; font-variant-numeric: tabular-nums; }
    .p-sec small span.low { animation: pBlink 1s infinite; } @keyframes pBlink { 50% { opacity: .35; } }
    .p-pause { background: #e5484d !important; color: #fff !important; border-color: transparent !important; }
    .p-meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .p-rev { border: 0; background: none; color: var(--pv-text); font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 4px; }
    .p-rev.on { background: #2f80ed; color: #fff; border-radius: 8px; padding: 6px 12px; }
    .p-qhd { display: flex; align-items: center; gap: 10px; margin: 16px 0 10px; }
    .p-qhd .n { background: var(--pv-surface-2); border-radius: 8px; padding: 3px 10px; font-weight: 700; }
    .p-qhd .sp { flex: 1; font-weight: 700; color: var(--pv-text); }
    .p-foot { position: sticky; bottom: 0; background: var(--pv-surface); border-top: 1px solid var(--pv-border); padding: 12px 14px calc(12px + env(safe-area-inset-bottom)); display: grid; grid-template-columns: auto 1fr; gap: 10px; }
    .p-foot .wide { grid-column: 1 / -1; display: grid; grid-template-columns: auto 1fr; gap: 10px; }
    .p-main { flex: 1; }
    .p-summary { overflow-x: auto; }
    .p-radio { display: flex; align-items: center; gap: 14px; padding: 12px 4px; width: 100%; border: 0; background: none; color: var(--pv-text); font-size: 1rem; text-align: left; }
    .p-radio i { width: 26px; height: 26px; border-radius: 50%; border: 2px solid var(--pv-border); flex: none; }
    .p-radio[aria-checked="true"] i { border: 7px solid #2f80ed; }
    .p-radio:disabled { color: var(--pv-muted); opacity: .55; }
    @media (min-width: 992px) {
        body.wide { display: grid; grid-template-columns: 1fr 340px; grid-template-rows: auto 1fr auto; }
        body.wide .t-top { grid-column: 1 / -1; }
        body.wide .p-side { display: block !important; grid-row: 2 / 4; grid-column: 2; border-left: 1px solid var(--pv-border); overflow: auto; padding: 14px; }
        body.wide #palBtn { display: none; }
    }
</style>
@endsection
@section('content')
<div class="t-top">
    <button class="t-ibtn p-pause" id="pauseBtn" aria-label="Pause test"><i class="bi bi-pause-fill"></i></button>
    <button class="p-sec" id="secBtn" aria-haspopup="dialog"><b><span id="secName"></span> <i class="bi bi-chevron-down"></i></b><small><span id="clockLbl">Time left</span>: <span id="clock">--:--</span></small></button>
    <button class="t-ibtn" id="langBtn" aria-label="Change language" title="Language" hidden><i class="bi bi-translate"></i></button>
    <button class="t-ibtn" id="palBtn" aria-label="All questions"><i class="bi bi-grid-3x3-gap"></i></button>
</div>

<main class="p-main"><div class="t-q">
    <div class="p-meta">
        <span class="t-pill" id="qType">Question Type : Multiple Choice</span>
        <button class="p-rev" id="revBtn" aria-pressed="false"><span>Review</span> <i class="bi bi-star"></i></button>
    </div>
    <div class="p-qhd"><span class="n" id="qNo">1</span><span class="sp">Question</span><span class="t-pill g" id="qRight">+1</span><span class="t-pill r" id="qNeg">-0</span></div>
    <div class="t-qtext" id="qText" aria-live="polite"></div>
    <img id="qImg" alt="" hidden style="max-width:100%;margin-top:10px;border-radius:8px">
    <div id="qOpts" role="radiogroup" aria-labelledby="qNo"></div>
    <div style="height:30px"></div>
</div></main>

<aside class="p-side" style="display:none" id="side"></aside>

<div class="p-foot">
    <div class="wide">
        <button class="t-btn" id="prevBtn"><i class="bi bi-chevron-left"></i> Prev</button>
        <button class="t-btn" id="clearBtn">Clear Response</button>
    </div>
    <button class="t-btn" id="reportBtn" aria-label="Report this question"><i class="bi bi-flag" style="color:#e5484d"></i></button>
    <button class="t-btn pri" id="nextBtn">Save &amp; Next</button>
</div>

{{-- sheets --}}
<div class="t-sheet-bg" id="secSheet" hidden><div class="t-sheet" role="dialog" aria-modal="true" aria-labelledby="secT">
    <div class="t-sheet-hd"><span id="secT">Select Section</span><button class="t-ibtn" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    <div class="t-sheet-bd" id="secList" role="radiogroup"></div>
</div></div>

<div class="t-sheet-bg" id="palSheet" hidden><div class="t-sheet" role="dialog" aria-modal="true" aria-label="All questions">
    <div class="t-sheet-hd" style="padding:6px 10px 6px 0;border:0"><span></span><button class="t-ibtn" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    <div id="palBody" style="display:flex;flex-direction:column;min-height:0;flex:1"></div>
</div></div>

<div class="t-sheet-bg" id="dlg" hidden style="align-items:center"><div class="t-sheet center" role="alertdialog" aria-modal="true" aria-labelledby="dlgT" style="max-width:560px">
    <div class="t-sheet-bd"><div style="font-weight:800;font-size:1.1rem" id="dlgT"></div><div id="dlgB" style="color:var(--pv-muted);margin-top:4px"></div></div>
    <div class="t-sheet-ft" style="border:0" id="dlgF"></div>
</div></div>

@php $boot = ['paper' => $paper, 'state' => $state, 'answers' => (object) ($a->answers ?: []), 'lang' => $a->lang, 'q' => $a->current_question_id]; @endphp
<script type="application/json" id="paperData">@json($boot)</script>
@endsection
@section('scripts')
<script>
(function () {
    var D = JSON.parse(document.getElementById('paperData').textContent), P = D.paper, S = D.state;
    var SAVE = @json(url('tests/attempt/' . $a->id . '/save')), REPORT = @json(url('tests/' . $exam->id . '/report')), BACK = @json(url('tests/' . $exam->id));
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var LN = { en: 'English', ml: 'മലയാളം', hi: 'हिन्दी', ta: 'தமிழ்', kn: 'ಕನ್ನಡ' };
    var A = D.answers || {}, dirty = {}, lang = D.lang || P.langs[0], langDirty = false;
    var qs = [], secOf = {};
    P.sections.forEach(function (s) { s.questions.forEach(function (q) { qs.push(q); secOf[q.id] = s; }); });
    var cur = 0, left = S.remaining, busy = false, ended = false;
    var $ = function (id) { return document.getElementById(id); };
    if (window.innerWidth >= 992) document.body.classList.add('wide');

    function ans(q) { return A[q.id] || (A[q.id] = { o: [], m: 0, v: 0, t: 0 }); }
    function touch(q) { dirty[q.id] = 1; }
    function sectional() { return S.timing === 'sectional'; }
    function canOpen(sid) { return !sectional() || sid === S.section; }
    function status(q) {
        var a = A[q.id]; if (!a || !a.v) return 'nv';
        var has = a.o && a.o.length;
        return a.m ? (has ? 'markans' : 'mark') : (has ? 'ans' : 'skip');
    }
    function fmt(s) { if (s == null) return 'No limit'; s = Math.max(0, s); var h = Math.floor(s / 3600), m = Math.floor(s % 3600 / 60), x = s % 60; return (h ? h + ':' + (m < 10 ? '0' : '') : '') + m + ':' + (x < 10 ? '0' : '') + x; }
    function toast(t) { var d = document.createElement('div'); d.className = 't-toast'; d.setAttribute('role', 'status'); d.textContent = t; document.body.appendChild(d); setTimeout(function () { d.remove(); }, 2500); }
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    // ---------------------------------------------------------------- draw one question
    function show(i) {
        if (i < 0 || i >= qs.length) return;
        if (!canOpen(secOf[qs[i].id].id)) return;
        cur = i;
        var q = qs[i], a = ans(q), t = q.langs[lang] || q.langs[Object.keys(q.langs)[0]];
        if (!a.v) { a.v = 1; touch(q); }
        $('secName').textContent = secOf[q.id].title;
        $('qNo').textContent = q.n;
        $('qType').textContent = 'Question Type : ' + (q.type === 'multiple' ? 'Multiple Answers' : (q.type === 'truefalse' ? 'True / False' : 'Multiple Choice'));
        $('qRight').textContent = '+' + q.right; $('qNeg').textContent = '-' + q.neg; $('qNeg').hidden = !q.neg;
        $('qText').innerHTML = t.q || '';
        $('qImg').hidden = !q.img; if (q.img) $('qImg').src = q.img;
        var box = $('qOpts'); box.innerHTML = ''; box.setAttribute('role', q.type === 'multiple' ? 'group' : 'radiogroup');
        q.oimg.forEach(function (img, k) {
            var b = document.createElement('button'), on = a.o.indexOf(k + 1) >= 0;
            b.type = 'button'; b.className = 't-opt' + (on ? ' sel' : '');
            b.setAttribute('role', q.type === 'multiple' ? 'checkbox' : 'radio'); b.setAttribute('aria-checked', on ? 'true' : 'false');
            b.innerHTML = '<span class="l">' + String.fromCharCode(65 + k) + '</span><span class="x">' + (t.o[k] || '') + (img ? '<img src="' + esc(img) + '" alt="">' : '') + '</span>';
            b.addEventListener('click', function () { pick(k + 1); });
            box.appendChild(b);
        });
        var rv = $('revBtn'); rv.classList.toggle('on', !!a.m); rv.setAttribute('aria-pressed', a.m ? 'true' : 'false');
        rv.innerHTML = a.m ? '<span>Marked</span> <i class="bi bi-star-fill"></i>' : '<span>Review</span> <i class="bi bi-star"></i>';
        $('prevBtn').disabled = i === 0 || !canOpen(secOf[qs[i - 1].id].id);
        var last = i === qs.length - 1 || !canOpen(secOf[qs[i + 1].id].id);
        $('nextBtn').textContent = last ? (sectional() && P.sections[P.sections.length - 1].id !== S.section ? 'Save & Submit Section' : 'Save & Submit') : 'Save & Next';
        window.scrollTo(0, 0);
        drawSide();
    }
    function pick(n) {
        var q = qs[cur], a = ans(q), at = a.o.indexOf(n);
        if (q.type === 'multiple') { if (at >= 0) a.o.splice(at, 1); else a.o.push(n); }
        else a.o = at >= 0 ? [] : [n];
        touch(q); show(cur);
    }

    // ---------------------------------------------------------------- question list (palette)
    function counts(sid) {
        var c = { ans: 0, skip: 0, mark: 0, markans: 0, nv: 0, total: 0 };
        qs.forEach(function (q) { if (sid && secOf[q.id].id !== sid) return; c[status(q)]++; c.total++; });
        return c;
    }
    var palSec = null;
    function palette(target) {
        var sid = palSec || secOf[qs[cur].id].id, sec = P.sections.filter(function (s) { return s.id === sid; })[0];
        var h = '<div class="t-tabs" role="tablist">' + P.sections.map(function (s) {
            return '<button class="t-tab" role="tab" data-sec="' + s.id + '" aria-selected="' + (s.id === sid) + '"' + (canOpen(s.id) ? '' : ' disabled') + '>' + esc(s.title) + '</button>';
        }).join('') + '</div><div class="t-sheet-bd" style="flex:1">';
        var c = counts(sid);
        h += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;color:var(--pv-text)"><span>Questions: ' + c.total + (sectional() && sid === S.section ? ' | Section time left: <b data-sleft>' + fmt(left) + '</b>' : '') + '</span></div><div class="t-grid">';
        sec.questions.forEach(function (q) {
            var i = qs.indexOf(q), st = status(q);
            h += '<button class="t-num ' + (st === 'nv' ? '' : st) + (i === cur ? ' cur' : '') + '" data-go="' + i + '" aria-label="Question ' + q.n + ', ' + ({ nv: 'not visited', skip: 'not answered', ans: 'answered', mark: 'marked for review', markans: 'marked and answered' })[st] + '"' + (canOpen(sid) ? '' : ' disabled') + '>' + q.n + '</button>';
        });
        h += '</div></div><div class="t-sheet-bd" style="border-top:1px solid var(--pv-border);flex:none"><div class="t-legend">' +
            '<span><i style="background:#2f80ed;border-color:#2f80ed"></i>Answered</span><span><i style="background:#c9cfd6;border-color:#c9cfd6"></i>Not Answered</span><span><i style="background:#f5c451;border-color:#f5c451"></i>Marked for Review</span>' +
            '<span><i></i>Not visited</span><span><i style="background:#f5c451;border-color:#f5c451;box-shadow:inset 0 0 0 4px #f5c451, inset 0 0 0 9px #2f80ed"></i>Marked &amp; Answered</span></div></div>' +
            '<div class="t-sheet-ft">' + (sectional() && S.section !== P.sections[P.sections.length - 1].id ? '<button class="t-btn" data-act="section">Submit Section</button>' : '') + '<button class="t-btn dark" data-act="submit">Submit Test</button></div>';
        target.innerHTML = h;
    }
    function drawSide() { if (document.body.classList.contains('wide')) palette($('side')); }
    function onPalette(e) {
        var t = e.target.closest('[data-sec],[data-go],[data-act]'); if (!t) return;
        if (t.dataset.sec) { palSec = +t.dataset.sec; palette(e.currentTarget.id === 'side' ? $('side') : $('palBody')); return; }
        if (t.dataset.go) { close('palSheet'); palSec = null; show(+t.dataset.go); return; }
        if (t.dataset.act === 'submit') { close('palSheet'); confirmSubmit(); }
        if (t.dataset.act === 'section') { close('palSheet'); confirmSection(); }
    }
    $('palBody').addEventListener('click', onPalette); $('side').addEventListener('click', onPalette);
    $('palBtn').addEventListener('click', function () { palSec = null; palette($('palBody')); open('palSheet'); });

    // ---------------------------------------------------------------- sections menu
    $('secBtn').addEventListener('click', function () {
        var sid = secOf[qs[cur].id].id;
        $('secList').innerHTML = P.sections.map(function (s) {
            return '<button class="p-radio" role="radio" aria-checked="' + (s.id === sid) + '" data-s="' + s.id + '"' + (canOpen(s.id) ? '' : ' disabled') + '><i></i>' + esc(s.title) +
                (s.minutes && sectional() ? '<small style="margin-left:auto;color:var(--pv-muted)">' + (S.done.indexOf(s.id) >= 0 ? 'Submitted' : (s.id === S.section ? 'Open' : s.minutes + ' min')) + '</small>' : '') + '</button>';
        }).join('');
        open('secSheet');
    });
    $('secList').addEventListener('click', function (e) {
        var b = e.target.closest('[data-s]'); if (!b || b.disabled) return;
        close('secSheet');
        for (var i = 0; i < qs.length; i++) if (secOf[qs[i].id].id === +b.dataset.s) { show(i); break; }
    });

    // ---------------------------------------------------------------- dialogs
    function open(id) { $(id).hidden = false; var f = $(id).querySelector('button:not([disabled])'); if (f) f.focus(); }
    function close(id) { $(id).hidden = true; }
    [].forEach.call(document.querySelectorAll('.t-sheet-bg'), function (bg) {
        bg.addEventListener('click', function (e) { if (e.target === bg && bg.id !== 'dlg') close(bg.id); if (e.target.closest('[data-close]')) close(bg.id); });
    });
    document.addEventListener('keydown', function (e) {
        var openSheet = [].filter.call(document.querySelectorAll('.t-sheet-bg'), function (b) { return !b.hidden; })[0];
        if (openSheet) { if (e.key === 'Escape' && openSheet.id !== 'dlg') close(openSheet.id); return; }
        if (/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) return;
        if (e.key >= '1' && e.key <= '6' && +e.key <= qs[cur].oimg.length) pick(+e.key);
        if (e.key === 'ArrowRight') next(); if (e.key === 'ArrowLeft') show(cur - 1);
    });
    function dialog(title, body, buttons) {
        $('dlgT').textContent = title; $('dlgB').innerHTML = body; $('dlgF').innerHTML = '';
        buttons.forEach(function (b) { var x = document.createElement('button'); x.className = 't-btn' + (b.pri ? ' pri' : ''); x.textContent = b.t; x.onclick = function () { if (b.keep !== true) close('dlg'); b.f && b.f(); }; $('dlgF').appendChild(x); });
        open('dlg');
    }
    function summary() {
        var rows = P.sections.map(function (s) { var c = counts(s.id); return [s.title, c.ans + c.markans, c.skip, c.mark + c.markans, c.nv, c.total]; });
        var t = counts(null); rows.push(['Total', t.ans + t.markans, t.skip, t.mark + t.markans, t.nv, t.total]);
        return '<div class="p-summary" style="margin-top:12px"><table class="t-table"><thead><tr><th>Section</th><th>Attempted</th><th>Skipped</th><th>Marked</th><th>Not visited</th><th>Total</th></tr></thead><tbody>' +
            rows.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + esc(String(c)) + '</td>'; }).join('') + '</tr>'; }).join('') + '</tbody></table></div>';
    }
    function confirmSubmit() { dialog('Submit Test', 'Are you sure you want to submit the test? A submitted test cannot be resumed.' + summary(), [{ t: 'No' }, { t: 'Yes, submit', pri: true, f: function () { send('submit'); } }]); }
    function confirmSection() { dialog('Submit Section', 'Submit "' + esc(secOf[qs[cur].id].title) + '"? You cannot come back to this section.' + summary(), [{ t: 'No' }, { t: 'Yes', pri: true, f: function () { send('next_section'); } }]); }
    $('pauseBtn').addEventListener('click', function () { dialog('Pause Test', 'Are you sure you want to pause the test? The timer stops until you resume.' + summary(), [{ t: 'No' }, { t: 'Yes', pri: true, f: function () { send('pause'); } }]); });

    // ---------------------------------------------------------------- buttons
    function next() {
        var i = cur + 1;
        if (i >= qs.length || !canOpen(secOf[qs[i].id].id)) {
            if (sectional() && S.section !== P.sections[P.sections.length - 1].id) confirmSection(); else confirmSubmit();
            return;
        }
        show(i);
    }
    $('nextBtn').addEventListener('click', function () { save('ping'); next(); });
    $('prevBtn').addEventListener('click', function () { show(cur - 1); });
    $('clearBtn').addEventListener('click', function () { var q = qs[cur]; ans(q).o = []; touch(q); show(cur); });
    $('revBtn').addEventListener('click', function () { var q = qs[cur], a = ans(q); a.m = a.m ? 0 : 1; touch(q); show(cur); });
    if (P.langs.length > 1) {
        $('langBtn').hidden = false;
        $('langBtn').addEventListener('click', function () { lang = P.langs[(P.langs.indexOf(lang) + 1) % P.langs.length]; langDirty = true; toast(LN[lang] || lang); show(cur); });
    }
    $('reportBtn').addEventListener('click', function () {
        var reasons = { wrong_answer: 'Wrong answer', wrong_question: 'Wrong question', typo: 'Spelling / typing mistake', image: 'Picture missing or wrong', translation: 'Translation problem', other: 'Other' };
        dialog('Report question ' + qs[cur].n, Object.keys(reasons).map(function (k, i) { return '<label style="display:flex;gap:10px;padding:6px 0;color:var(--pv-text)"><input type="radio" name="rr" value="' + k + '"' + (i ? '' : ' checked') + '> ' + reasons[k] + '</label>'; }).join('') +
            '<textarea id="rrNote" maxlength="500" rows="2" placeholder="Tell us more (optional)" style="width:100%;margin-top:8px;border:1px solid var(--pv-border);border-radius:10px;padding:8px;background:var(--pv-surface-2);color:var(--pv-text)"></textarea>',
            [{ t: 'Cancel' }, { t: 'Send report', pri: true, f: function () {
                var r = document.querySelector('input[name="rr"]:checked');
                post(REPORT, { question_id: qs[cur].id, kind: 'report', reason: r ? r.value : 'other', note: ($('rrNote') || {}).value || '' }).then(function (j) { toast(j.message || 'Sent'); }).catch(function () { toast('Could not send. Try again.'); });
            } }]);
    });

    // ---------------------------------------------------------------- saving and the clock
    function post(url, body, keep) {
        return fetch(url, { method: 'POST', keepalive: !!keep, credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) })
            .then(function (r) { if (r.status === 401 || r.status === 419) { location.reload(); throw new Error('auth'); } if (!r.ok) throw new Error('fail'); return r.json(); });
    }
    function payload(action) {
        var out = {}; Object.keys(dirty).forEach(function (id) { out[id] = A[id]; });
        var body = { answers: out, q: qs[cur] ? qs[cur].id : null, action: action };
        if (langDirty) body.lang = lang;
        return body;
    }
    function save(action, keep) {
        if (busy && action === 'ping') return Promise.resolve();
        var body = payload(action), sent = dirty; dirty = {}; langDirty = false; busy = true;
        return post(SAVE, body, keep).then(apply).catch(function (e) {
            Object.keys(sent).forEach(function (k) { dirty[k] = 1; });   // keep them for the next save
            if (e.message !== 'auth' && action !== 'ping') toast('Could not save - check your connection.');
            throw e;
        }).finally(function () { busy = false; });
    }
    function send(action) {
        save(action).then(function () {
            if (action === 'pause') location.href = BACK;
        }).catch(function () {});
    }
    function apply(st) {
        var prev = S.section; S = st; left = st.remaining;
        if (st.status === 'submitted' && st.result) { ended = true; location.href = st.result; return; }
        if (st.status === 'paused') return;
        if (sectional() && st.section !== prev) {
            for (var i = 0; i < qs.length; i++) if (secOf[qs[i].id].id === st.section) { cur = -1; show(i); break; }
            toast('Now: ' + secOf[qs[cur].id].title);
        }
        drawSide();
    }
    // the clock: counts down here, the server's number wins on every save
    setInterval(function () {
        if (ended || S.status !== 'running') return;
        var q = qs[cur]; if (q) { ans(q).t = (ans(q).t || 0) + 1; }
        if (left == null) { $('clock').textContent = 'No limit'; return; }
        left = Math.max(0, left - 1);
        $('clock').textContent = fmt(left); $('clock').classList.toggle('low', left <= 60);
        var sl = document.querySelector('[data-sleft]'); if (sl) sl.textContent = fmt(left);
        if (left === 0) {
            ended = true;
            if (sectional() && S.section !== P.sections[P.sections.length - 1].id) {
                save('ping').then(function () {
                    ended = false;
                    dialog('Section time ended', 'The time for this section is over. Press NEXT to move to the next section.', [{ t: 'Pause Test', f: function () { send('pause'); } }, { t: 'Next', pri: true }]);
                }).catch(function () { ended = false; });
            } else {
                toast('Time is over - submitting your test');
                save('submit').catch(function () { ended = false; });
            }
        }
    }, 1000);
    $('clockLbl').textContent = sectional() ? 'Section time left' : 'Total time left';
    setInterval(function () { if (!ended && S.status === 'running') save('ping').catch(function () {}); }, 15000);
    document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden' && !ended) save('ping', true).catch(function () {}); });
    window.addEventListener('beforeunload', function () { if (!ended && Object.keys(dirty).length) save('ping', true).catch(function () {}); });

    // ---------------------------------------------------------------- start where the student left off
    var startAt = 0;
    for (var k = 0; k < qs.length; k++) { if (qs[k].id === D.q && canOpen(secOf[qs[k].id].id)) { startAt = k; break; } if (canOpen(secOf[qs[k].id].id) && !startAt && secOf[qs[k].id].id === S.section) startAt = k; }
    if (!canOpen(secOf[qs[startAt].id].id)) for (k = 0; k < qs.length; k++) if (canOpen(secOf[qs[k].id].id)) { startAt = k; break; }
    show(startAt);
    $('clock').textContent = fmt(left);
})();
</script>
@endsection
