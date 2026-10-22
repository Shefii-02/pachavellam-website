@extends('kpsc.tests.layout')
@section('title', $title)
@section('styles')
<style>
    body { background: var(--pv-surface); display: flex; flex-direction: column; min-height: 100vh; }
    .s-hd { flex: 1; min-width: 0; } .s-hd b { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--pv-text); }
    .s-sec { border: 0; background: none; color: var(--pv-text); font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; gap: 6px; padding: 0; } .s-sec i { color: #2f80ed; }
    .s-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 16px; border-bottom: 1px solid var(--pv-border); max-width: 900px; margin: 0 auto; width: 100%; }
    .s-speed { font-size: 1.3rem; }
    .s-main { flex: 1; }
    .s-sol { margin-top: 18px; border-top: 1px solid var(--pv-border); padding-top: 12px; }
    .s-sol > button { border: 0; background: none; color: #2f80ed; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 6px 0; border-bottom: 3px solid #2f80ed; }
    .s-sol .body { margin-top: 12px; line-height: 1.6; color: var(--pv-text); }
    .s-help { margin-top: 18px; color: var(--pv-text); } .s-help .row { display: flex; gap: 10px; margin-top: 8px; } .s-help .t-btn { flex: 1; max-width: 160px; }
    .s-help .t-btn[aria-pressed="true"] { border-color: var(--pv-brand); background: var(--pv-brand-soft); }
    .s-foot { position: sticky; bottom: 0; background: var(--pv-surface); border-top: 1px solid var(--pv-border); padding: 12px 14px calc(12px + env(safe-area-inset-bottom)); display: grid; grid-template-columns: auto auto 1fr auto; gap: 8px; }
    .s-foot .t-btn { white-space: nowrap; padding: 11px 12px; }
    .s-mark { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: .7rem; font-weight: 700; text-align: center; }
    .t-opt { position: relative; padding-right: 80px; }
</style>
@endsection
@section('content')
<div class="t-top">
    <a class="t-ibtn" href="{{ url('tests/attempt/' . $a->id . '/result') }}" aria-label="Back to result"><i class="bi bi-arrow-left"></i></a>
    <div class="s-hd"><b>{{ $exam->title }}</b></div>
    <button class="t-ibtn" id="langBtn" aria-label="Change language" hidden><i class="bi bi-translate"></i></button>
    <button class="t-ibtn" id="palBtn" aria-label="All questions"><i class="bi bi-grid-3x3-gap"></i></button>
</div>
<div class="s-bar">
    <button class="s-sec" id="secBtn"><span id="secName"></span> <i class="bi bi-chevron-down"></i></button>
    <span style="flex:1"></span>
    <span class="s-speed" id="speed" aria-hidden="true"></span>
    <span class="t-pill" id="you">You : 00:00</span><span class="t-pill" id="avg">Avg : 00:00</span>
    <span class="t-pill g" id="pct"><i class="bi bi-people"></i> 0% answered correctly</span>
</div>
<main class="s-main"><div class="t-q">
    <div class="p-qhd" style="display:flex;align-items:center;gap:10px;margin-bottom:10px"><span class="t-pill" id="qNo">1</span><b style="flex:1;color:var(--pv-text)">Question</b><span class="t-pill g" id="qMark">+1</span><span class="t-pill" id="qRes"></span></div>
    <div class="t-qtext" id="qText"></div>
    <img id="qImg" alt="" hidden style="max-width:100%;margin-top:10px;border-radius:8px">
    <div id="qOpts"></div>
    <div class="s-sol"><button id="solBtn" aria-expanded="true" aria-controls="solBody"><i class="bi bi-eye"></i> View Solution <i class="bi bi-chevron-down"></i></button><div class="body" id="solBody"></div></div>
    <div class="s-help"><div>Help us improve! Was this solution helpful?</div><div class="row"><button class="t-btn" data-help="helpful" aria-pressed="false"><i class="bi bi-hand-thumbs-up-fill" style="color:#1aa260"></i> Yes</button><button class="t-btn" data-help="unhelpful" aria-pressed="false"><i class="bi bi-hand-thumbs-down-fill" style="color:#e5484d"></i> No</button></div></div>
    <div style="height:24px"></div>
</div></main>
<div class="s-foot">
    <button class="t-btn" id="prevBtn" aria-label="Previous question"><i class="bi bi-chevron-left"></i></button>
    <button class="t-btn" id="reportBtn"><i class="bi bi-flag" style="color:#e5484d"></i> Report</button>
    <a class="t-btn" href="{{ url('tests/attempt/' . $a->id . '/result') }}">Test Summary</a>
    <button class="t-btn pri" id="nextBtn" aria-label="Next question"><i class="bi bi-chevron-right"></i></button>
</div>

<div class="t-sheet-bg" id="palSheet" hidden><div class="t-sheet" role="dialog" aria-modal="true" aria-label="All questions">
    <div class="t-sheet-hd" style="padding:6px 10px 6px 0;border:0"><span></span><button class="t-ibtn" data-close aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    <div id="palBody" style="display:flex;flex-direction:column;min-height:0;flex:1"></div>
</div></div>
<div class="t-sheet-bg" id="dlg" hidden style="align-items:center"><div class="t-sheet center" role="dialog" aria-modal="true" aria-labelledby="dlgT" style="max-width:520px">
    <div class="t-sheet-bd"><div style="font-weight:800;font-size:1.1rem" id="dlgT"></div><div id="dlgB" style="margin-top:6px"></div></div>
    <div class="t-sheet-ft" style="border:0" id="dlgF"></div>
</div></div>
@php $boot = ['paper' => $paper, 'answers' => (object) ($a->answers ?: []), 'stats' => (object) $stats, 'lang' => $a->lang, 'avgSec' => $avgSec]; @endphp
<script type="application/json" id="solData">@json($boot)</script>
@endsection
@section('scripts')
<script>
(function () {
    var D = JSON.parse(document.getElementById('solData').textContent), P = D.paper, A = D.answers || {}, ST = D.stats || {};
    var CSRF = document.querySelector('meta[name="csrf-token"]').content, REPORT = @json(url('tests/' . $exam->id . '/report'));
    var LN = { en: 'English', ml: 'മലയാളം', hi: 'हिन्दी', ta: 'தமிழ்', kn: 'ಕನ್ನಡ' };
    var lang = D.lang || P.langs[0], qs = [], secOf = {}, cur = 0, filter = 'all', palSec = null;
    P.sections.forEach(function (s) { s.questions.forEach(function (q) { qs.push(q); secOf[q.id] = s; }); });
    var $ = function (id) { return document.getElementById(id); };
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function mmss(s) { s = Math.round(s || 0); return (s < 600 ? '0' : '') + Math.floor(s / 60) + ':' + (s % 60 < 10 ? '0' : '') + (s % 60); }
    function chosen(q) { return ((A[q.id] || {}).o || []).map(Number); }
    function res(q) { var c = chosen(q).slice().sort(), k = q.key.map(Number).slice().sort(); return !c.length ? 'skip' : (c.join(',') === k.join(',') ? 'ok' : 'bad'); }
    function toast(t) { var d = document.createElement('div'); d.className = 't-toast'; d.setAttribute('role', 'status'); d.textContent = t; document.body.appendChild(d); setTimeout(function () { d.remove(); }, 2500); }
    function post(body) { return fetch(REPORT, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) }).then(function (r) { if (!r.ok) throw 0; return r.json(); }); }

    function show(i) {
        if (i < 0 || i >= qs.length) return;
        cur = i;
        var q = qs[i], t = q.langs[lang] || q.langs[Object.keys(q.langs)[0]], c = chosen(q), r = res(q), st = ST[q.id] || {}, you = (A[q.id] || {}).t || 0, avg = st.avg || D.avgSec || 0;
        $('secName').textContent = secOf[q.id].title; $('qNo').textContent = q.n;
        $('qMark').textContent = r === 'bad' ? '-' + q.neg : (r === 'ok' ? '+' + q.right : '0'); $('qMark').className = 't-pill ' + (r === 'ok' ? 'g' : (r === 'bad' ? 'r' : ''));
        $('qRes').textContent = r === 'ok' ? 'Correct' : (r === 'bad' ? 'Incorrect' : 'Unattempted'); $('qRes').className = 't-pill ' + (r === 'ok' ? 'g' : (r === 'bad' ? 'r' : ''));
        $('you').textContent = 'You : ' + mmss(you); $('avg').textContent = 'Avg : ' + mmss(avg);
        $('pct').innerHTML = '<i class="bi bi-people"></i> ' + (st.pct || 0) + '% answered correctly';
        var sp = !you || !avg ? '' : (you < avg * 0.75 ? ['bi-lightning-charge-fill', '#1aa260', 'Fast'] : (you > avg * 1.25 ? ['bi-hourglass-bottom', '#e5484d', 'Slow'] : ['bi-stopwatch', '#f5b301', 'On time']));
        $('speed').innerHTML = sp ? '<i class="bi ' + sp[0] + '" style="color:' + sp[1] + '" title="' + sp[2] + '"></i><span class="sr-only">' + sp[2] + '</span>' : '';
        $('qText').innerHTML = t.q || '';
        $('qImg').hidden = !q.img; if (q.img) $('qImg').src = q.img;
        $('qOpts').innerHTML = q.oimg.map(function (img, k) {
            var n = k + 1, right = q.key.indexOf(n) >= 0, mine = c.indexOf(n) >= 0, cls = right ? ' ok' : (mine ? ' bad' : '');
            var tag = right ? '<span class="s-mark" style="color:#1aa260"><i class="bi bi-check-square-fill" style="font-size:1.3rem"></i><br>' + (mine ? 'Your answer' : 'Correct') + '</span>' : (mine ? '<span class="s-mark" style="color:#e5484d"><i class="bi bi-x-square-fill" style="font-size:1.3rem"></i><br>Your answer</span>' : '');
            return '<div class="t-opt' + cls + '"><span class="l">' + String.fromCharCode(65 + k) + '</span><span class="x">' + (t.o[k] || '') + (img ? '<img src="' + esc(img) + '" alt="">' : '') + '</span>' + tag + '</div>';
        }).join('');
        $('solBody').innerHTML = (t.e || '<span style="color:var(--pv-muted)">No written solution for this question. The right answer is ' + q.key.map(function (n) { return String.fromCharCode(64 + n); }).join(', ') + '.</span>') + (q.eimg ? '<img src="' + esc(q.eimg) + '" alt="" style="max-width:100%;margin-top:10px">' : '');
        [].forEach.call(document.querySelectorAll('[data-help]'), function (b) { b.setAttribute('aria-pressed', 'false'); });
        $('prevBtn').disabled = i === 0; $('nextBtn').disabled = i === qs.length - 1;
        window.scrollTo(0, 0);
    }
    $('solBtn').addEventListener('click', function () { var b = $('solBody'), open = b.hidden; b.hidden = !open; this.setAttribute('aria-expanded', open); });
    $('prevBtn').addEventListener('click', function () { show(cur - 1); }); $('nextBtn').addEventListener('click', function () { show(cur + 1); });
    document.addEventListener('keydown', function (e) { if (!$('palSheet').hidden || !$('dlg').hidden) { if (e.key === 'Escape') { $('palSheet').hidden = true; $('dlg').hidden = true; } return; } if (e.key === 'ArrowRight') show(cur + 1); if (e.key === 'ArrowLeft') show(cur - 1); });
    [].forEach.call(document.querySelectorAll('[data-help]'), function (b) {
        b.addEventListener('click', function () {
            [].forEach.call(document.querySelectorAll('[data-help]'), function (x) { x.setAttribute('aria-pressed', x === b); });
            post({ question_id: qs[cur].id, kind: b.dataset.help }).then(function (j) { toast(j.message); }).catch(function () { toast('Could not send.'); });
        });
    });
    if (P.langs.length > 1) { $('langBtn').hidden = false; $('langBtn').addEventListener('click', function () { lang = P.langs[(P.langs.indexOf(lang) + 1) % P.langs.length]; toast(LN[lang] || lang); show(cur); }); }

    // question list with filter
    function palette() {
        var sid = palSec || secOf[qs[cur].id].id, sec = P.sections.filter(function (s) { return s.id === sid; })[0];
        var att = sec.questions.filter(function (q) { return res(q) !== 'skip'; }).length;
        var h = '<div class="t-tabs" role="tablist">' + P.sections.map(function (s) { return '<button class="t-tab" role="tab" data-sec="' + s.id + '" aria-selected="' + (s.id === sid) + '">' + esc(s.title) + '</button>'; }).join('') + '</div>';
        h += '<div class="t-sheet-bd" style="flex:1"><div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px"><span>Questions: ' + sec.questions.length + ' | Attempted: ' + att + '</span>' +
            '<select data-filter aria-label="Filter" style="border:1px solid var(--pv-border);background:var(--pv-surface);color:var(--pv-text);border-radius:8px;padding:5px 8px">' +
            [['all', 'All'], ['ok', 'Correct'], ['bad', 'Incorrect'], ['skip', 'Unattempted']].map(function (o) { return '<option value="' + o[0] + '"' + (filter === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select></div><div class="t-grid">';
        sec.questions.forEach(function (q) {
            var r = res(q); if (filter !== 'all' && r !== filter) return;
            var i = qs.indexOf(q);
            h += '<button class="t-num ' + r + (i === cur ? ' cur' : '') + '" data-go="' + i + '" aria-label="Question ' + q.n + ', ' + ({ ok: 'correct', bad: 'incorrect', skip: 'unattempted' })[r] + '">' + q.n + '</button>';
        });
        h += '</div></div><div class="t-sheet-bd" style="border-top:1px solid var(--pv-border);flex:none"><div style="font-weight:600;margin-bottom:8px">Speed Indicator</div><div class="t-legend" style="margin-bottom:12px"><span><i class="bi bi-lightning-charge-fill" style="border:0;color:#1aa260"></i>Fast</span><span><i class="bi bi-stopwatch" style="border:0;color:#f5b301"></i>On Time</span><span><i class="bi bi-hourglass-bottom" style="border:0;color:#e5484d"></i>Slow</span></div>' +
            '<div class="t-legend"><span><i style="background:#1aa260;border-color:#1aa260"></i>Correct</span><span><i style="background:#e5484d;border-color:#e5484d"></i>Incorrect</span><span><i style="background:#c9cfd6;border-color:#c9cfd6"></i>Unattempted</span></div></div>' +
            '<div class="t-sheet-ft"><button class="t-btn" data-step="-1">Previous</button><button class="t-btn pri" data-step="1">Next</button></div>';
        $('palBody').innerHTML = h;
    }
    $('palBtn').addEventListener('click', function () { palSec = null; palette(); $('palSheet').hidden = false; });
    $('secBtn').addEventListener('click', function () { palSec = null; palette(); $('palSheet').hidden = false; });
    $('palBody').addEventListener('click', function (e) {
        var t = e.target.closest('[data-sec],[data-go],[data-step]'); if (!t) return;
        if (t.dataset.sec) { palSec = +t.dataset.sec; palette(); }
        if (t.dataset.go) { $('palSheet').hidden = true; show(+t.dataset.go); }
        if (t.dataset.step) { $('palSheet').hidden = true; show(cur + +t.dataset.step); }
    });
    $('palBody').addEventListener('change', function (e) { if (e.target.matches('[data-filter]')) { filter = e.target.value; palette(); } });
    [].forEach.call(document.querySelectorAll('.t-sheet-bg'), function (bg) { bg.addEventListener('click', function (e) { if (e.target === bg || e.target.closest('[data-close]')) bg.hidden = true; }); });

    $('reportBtn').addEventListener('click', function () {
        var reasons = { wrong_answer: 'Wrong answer', wrong_question: 'Wrong question', typo: 'Spelling / typing mistake', image: 'Picture missing or wrong', translation: 'Translation problem', other: 'Other' };
        $('dlgT').textContent = 'Report question ' + qs[cur].n;
        $('dlgB').innerHTML = Object.keys(reasons).map(function (k, i) { return '<label style="display:flex;gap:10px;padding:6px 0"><input type="radio" name="rr" value="' + k + '"' + (i ? '' : ' checked') + '> ' + reasons[k] + '</label>'; }).join('') +
            '<textarea id="rrNote" maxlength="500" rows="2" placeholder="Tell us more (optional)" style="width:100%;margin-top:8px;border:1px solid var(--pv-border);border-radius:10px;padding:8px;background:var(--pv-surface-2);color:var(--pv-text)"></textarea>';
        $('dlgF').innerHTML = '<button class="t-btn" data-x>Cancel</button><button class="t-btn pri" data-send>Send report</button>';
        $('dlg').hidden = false;
        $('dlgF').onclick = function (e) {
            if (e.target.closest('[data-x]')) $('dlg').hidden = true;
            if (e.target.closest('[data-send]')) {
                var r = document.querySelector('input[name="rr"]:checked');
                post({ question_id: qs[cur].id, kind: 'report', reason: r ? r.value : 'other', note: $('rrNote').value }).then(function (j) { toast(j.message); }).catch(function () { toast('Could not send.'); });
                $('dlg').hidden = true;
            }
        };
    });
    show(0);
})();
</script>
@endsection
