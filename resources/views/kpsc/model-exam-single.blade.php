@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@php
    date_default_timezone_set('Asia/Kolkata');
    $title = 'KPSC-Model Exam';
    $ended = $examdetails->ended_at && strtotime($examdetails->ended_at) <= time();
    $qpUrl = $examdetails->qp_file ? url(Storage::url('model-exam/' . $examdetails->qp_file)) : null;
    $akUrl = $examdetails->answer_file ? url(Storage::url('model-exam/' . $examdetails->answer_file)) : null;
@endphp
@section('title', $title)
@if (!$qpUrl && !$instruction)@section('no_ads', '1')@endif   {{-- nothing to read on the page: no ads --}}
@section('styles')
    <style>
        .xt-wrap { max-width: 980px; margin: 0 auto; padding: 14px 0 24px; }
        .xt-tabs { display:flex; gap:6px; border-bottom:2px solid var(--pv-border,#e5e7eb); margin:10px 0 12px; overflow-x:auto; }
        .xt-tab { border:0; background:transparent; padding:9px 18px; font-weight:800; font-size:.9rem; color:var(--pv-muted,#667); border-bottom:3px solid transparent; margin-bottom:-2px; white-space:nowrap; }
        .xt-tab.on { color:#0a8f5b; border-color:#0a8f5b; } .xt-tab[disabled] { opacity:.45; }
        .xt-pane { display:none; } .xt-pane.on { display:block; }
        .xt-bar { display:flex; gap:8px; justify-content:flex-end; margin-bottom:8px; }
        .xt-frame { width:100%; height:78vh; border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; background:#d1d1d1; }
        .xt-timer { position:sticky; top:0; z-index:5; background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; padding:8px 12px; margin-bottom:8px; }
        .xt-timer .row1 { display:flex; align-items:center; gap:8px; flex-wrap:wrap; } .xt-clock { font-variant-numeric:tabular-nums; font-size:1.25rem; font-weight:800; min-width:92px; }
        .xt-timer button { border:0; border-radius:18px; padding:5px 14px; font-size:.8rem; font-weight:800; background:#eef0f3; } .xt-timer button.pri { background:#0a8f5b; color:#fff; } .xt-timer button.stop { background:#e53935; color:#fff; }
        .xt-info { font-size:.78rem; color:var(--pv-muted,#667); } .xt-about { margin-top:22px; line-height:1.6; font-size:.92rem; } .xt-about h5 { font-weight:800; margin:14px 0 6px; }
        .xt-note { font-size:.8rem; color:var(--pv-muted,#667); margin:6px 0 0; }
    </style>
@endsection
@section('content')
    <div class="page-content-wrapper"><div class="container"><div class="xt-wrap">
        <h4>Exam : <span class="text-info">{{ $examdetails->examtitle }}</span></h4>

        <div class="xt-tabs" role="tablist">
            <button class="xt-tab" data-pane="ins">Instructions</button>
            <button class="xt-tab" data-pane="qp">Question paper</button>
            <button class="xt-tab" data-pane="ak" id="akTab">Answer key</button>
        </div>

        <div class="xt-pane" id="pane-ins">
            @include('kpsc.partials.exam-instructions', ['exam' => $examdetails, 'instruction' => $instruction, 'meta' => array_filter(['Ends' => $examdetails->ended_at ? date('d M Y, h:i a', strtotime($examdetails->ended_at)) : null])])
        </div>

        <div class="xt-pane" id="pane-qp">
            <div class="xt-bar">@if ($qpUrl)<a target="_blank" class="btn btn-sm btn-dark" href="{{ $qpUrl }}">Download</a>@endif</div>
            <div class="xt-timer" id="tm">
                <div class="row1">
                    <button type="button" id="tmOn">⏱ Timer (optional)</button>
                    <span id="tmBody" class="row1" style="display:none">
                        <span class="xt-clock" id="tmClock">00:00:00</span>
                        <button type="button" class="pri" id="tmStart">Start</button><button type="button" id="tmPause" style="display:none">Pause</button><button type="button" class="pri" id="tmResume" style="display:none">Resume</button><button type="button" class="stop" id="tmStop" style="display:none">Stop</button><button type="button" id="tmReset" style="display:none">Reset</button>
                    </span>
                </div>
                <div class="xt-info" id="tmInfo">@if ($timing['avg']) Average time taken by {{ $timing['count'] }} students: <b>{{ floor($timing['avg'] / 60) }} min {{ $timing['avg'] % 60 }} s</b> @else Time yourself: start, pause or stop the timer while you write the paper. @endif</div>
            </div>
            @if ($qpUrl)<iframe class="xt-frame" data-src="https://docs.google.com/gview?url={{ $qpUrl }}&embedded=true" frameborder="0"></iframe>@else<p class="text-muted">The question paper is not uploaded yet.</p>@endif
            <p class="xt-note">Write your answers on the OMR sheet. The answer key opens once the paper has been started.</p>
        </div>

        <div class="xt-pane" id="pane-ak">
            <div class="xt-bar">@if ($akUrl)<a target="_blank" class="btn btn-sm btn-dark" href="{{ $akUrl }}">Download</a>@endif</div>
            @if ($akUrl)<iframe class="xt-frame" data-src="https://docs.google.com/gview?url={{ $akUrl }}&embedded=true" frameborder="0"></iframe>@else<p class="text-muted">The answer key is not uploaded yet.</p>@endif
            <p class="xt-note">Check your OMR sheet, then use <b>Upload your marks</b> on the exam list to save your result.</p>
        </div>

        <div class="xt-about">
            <h5>About this OMR exam</h5>
            <p>{{ $examdetails->examtitle }} is a model OMR exam for Kerala PSC preparation. Download the question paper, write your answers on a printed OMR sheet in exam conditions, then check them with the answer key and upload your marks to see where you stand on the leaderboard.</p>
            <h5>How to attempt</h5>
            <ol>
                <li>Read the instructions and tick that you understood them, then press <b>Start exam</b>.</li>
                <li>Open the <b>Question paper</b> tab and mark your answers on the OMR sheet. Use the optional timer to see how long you take.</li>
                <li>Open the <b>Answer key</b> tab, count your correct and wrong answers and calculate your mark (1 mark for a correct answer, 1/3 mark deducted for a wrong one).</li>
                <li>Upload your marks from the exam list. The leaderboard opens when the exam ends.</li>
            </ol>
            <h5>Why time yourself?</h5>
            <p>The Kerala PSC written test is a race against the clock. Recording the time you take on each model exam shows whether you are getting faster, and comparing it with the average time of other students tells you where you need more speed practice.</p>
            @if ($timing['avg'])<p class="mb-0">Average time taken so far: <b>{{ floor($timing['avg'] / 60) }} min {{ $timing['avg'] % 60 }} s</b> ({{ $timing['count'] }} students).</p>@endif
        </div>
        @unless (View::hasSection('no_ads')){!! single_ads_show() !!}@endunless
    </div></div></div>
    @include('kpsc.partials.exam-report', ['examId' => $examdetails->id])
@endsection
@section('scripts')
    <script>
    (function () {
        var id = @json($examdetails->id), key = 'omr_started_' + id;
        var seen = false; try { seen = localStorage.getItem(key) === '1'; } catch (e) {}
        // the key opens when the paper was started, marks were uploaded, or the exam is over
        var keyOpen = @json($ended || $uploaded) || seen;
        var tabs = document.querySelectorAll('.xt-tab'), akTab = document.getElementById('akTab');
        function show(name) {
            if (name === 'ak' && !keyOpen) return;
            tabs.forEach(function (t) { t.classList.toggle('on', t.dataset.pane === name); });
            document.querySelectorAll('.xt-pane').forEach(function (p) { p.classList.toggle('on', p.id === 'pane-' + name); });
            var f = document.querySelector('#pane-' + name + ' iframe[data-src]');
            if (f) { f.src = f.dataset.src; f.removeAttribute('data-src'); }
        }
        function unlock() { keyOpen = true; akTab.disabled = false; akTab.title = ''; }
        if (!keyOpen) { akTab.disabled = true; akTab.title = 'Opens after you start the paper'; }
        tabs.forEach(function (t) { t.addEventListener('click', function () { if (t.dataset.pane === 'qp') { try { localStorage.setItem(key, '1'); } catch (e) {} unlock(); } show(t.dataset.pane); }); });
        // the optional timer: start / pause / resume / stop; kept in the browser so a refresh does not lose it, saved on stop
        var tk = 'omr_timer_' + id, T = { e: 0, s: null, done: false, on: false };
        try { T = Object.assign(T, JSON.parse(localStorage.getItem(tk) || '{}')); } catch (e) {}
        var $ = function (x) { return document.getElementById(x); }, tick = null;
        var save = function () { try { localStorage.setItem(tk, JSON.stringify(T)); } catch (e) {} };
        var el = function () { return T.e + (T.s ? Date.now() - T.s : 0); };
        var fmt = function (ms) { var s = Math.floor(ms / 1000), p = function (n) { return (n < 10 ? '0' : '') + n; }; return p(Math.floor(s / 3600)) + ':' + p(Math.floor(s / 60) % 60) + ':' + p(s % 60); };
        var mins = function (s) { return Math.floor(s / 60) + ' min ' + (s % 60) + ' s'; };
        function draw() {
            $('tmBody').style.display = T.on ? '' : 'none'; $('tmOn').style.display = T.on ? 'none' : '';
            var run = !!T.s, idle = !run && !T.e && !T.done, paused = !run && T.e && !T.done;
            $('tmStart').style.display = idle ? '' : 'none'; $('tmPause').style.display = run ? '' : 'none'; $('tmResume').style.display = paused ? '' : 'none';
            $('tmStop').style.display = (run || paused) ? '' : 'none'; $('tmReset').style.display = T.done ? '' : 'none';
            $('tmClock').textContent = fmt(el());
            if (run && !tick) tick = setInterval(function () { $('tmClock').textContent = fmt(el()); }, 500);
            if (!run && tick) { clearInterval(tick); tick = null; }
        }
        $('tmOn').onclick = function () { T.on = true; save(); draw(); };
        $('tmStart').onclick = function () { T.e = 0; T.s = Date.now(); T.done = false; save(); try { localStorage.setItem(key, '1'); } catch (e) {} unlock(); draw(); };
        $('tmPause').onclick = function () { T.e = el(); T.s = null; save(); draw(); };
        $('tmResume').onclick = function () { T.s = Date.now(); save(); draw(); };
        $('tmReset').onclick = function () { T = { e: 0, s: null, done: false, on: true }; save(); draw(); };
        $('tmStop').onclick = function () {
            T.e = el(); T.s = null; T.done = true; save(); draw();
            var secs = Math.round(T.e / 1000);
            if (secs < 10) { $('tmInfo').textContent = 'Too short to record.'; return; }
            var fd = new FormData(); fd.append('seconds', secs);
            fetch(@json(url('kpsc/model-exam/' . $examdetails->id . '/time')), { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw 0; return r.json(); })
                .then(function (d) { $('tmInfo').innerHTML = 'You took <b>' + mins(secs) + '</b>.' + (d.avg ? ' Average of ' + d.count + ' students: <b>' + mins(d.avg) + '</b>.' : ''); })
                .catch(function () { $('tmInfo').innerHTML = 'You took <b>' + mins(secs) + '</b> (could not save).'; });
        };
        draw();

        document.addEventListener('exam-start', function () { try { localStorage.setItem(key, '1'); } catch (e) {} unlock(); show('qp'); });
        show(@json(($uploaded || $ended) ? 'qp' : 'ins'));
    })();
    </script>
@endsection
