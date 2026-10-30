{{-- "Report an error with this quiz": a floating button + popup. Needs $examId. --}}
<style>
    .xr-fab { position:fixed; right:14px; bottom:84px; z-index:1040; border:0; border-radius:24px; padding:9px 16px; font-size:.8rem; font-weight:800; background:#e53935; color:#fff; box-shadow:0 4px 14px rgba(0,0,0,.25); }
    .xr-ov { display:none; position:fixed; inset:0; z-index:1060; background:rgba(0,0,0,.5); align-items:center; justify-content:center; padding:16px; } .xr-ov.on { display:flex; }
    .xr-box { background:#fff; color:#111; border-radius:16px; width:100%; max-width:440px; padding:18px; max-height:92vh; overflow:auto; } .xr-box h5 { font-weight:800; margin-bottom:10px; }
    .xr-box label.o { display:flex; gap:8px; align-items:center; margin-bottom:6px; font-size:.9rem; } .xr-box textarea, .xr-box input[type=text] { width:100%; border:1px solid #ccd; border-radius:10px; padding:8px 10px; font-size:.9rem; }
    .xr-msg { font-size:.78rem; color:#a4242a; min-height:1.1em; } .xr-row { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }
    .xr-row button { border:0; border-radius:20px; padding:8px 22px; font-weight:800; } .xr-c { background:#eef0f3; } .xr-s { background:#0a8f5b; color:#fff; }
</style>
@auth
<button type="button" class="xr-fab" id="xrOpen"><i class="bi bi-flag-fill"></i> Report an error</button>
@else
<a class="xr-fab" href="{{ url('sign-in') }}" style="text-decoration:none"><i class="bi bi-flag-fill"></i> Report an error</a>
@endauth
<div class="xr-ov" id="xrOv"><div class="xr-box">
    <h5>Report an Error with this Quiz</h5>
    @foreach (\App\Models\ExamReport::TYPES as $i => $t)
        <label class="o"><input type="radio" name="xrType" value="{{ $t }}" @checked($i === 0)> {{ $t }}</label>
    @endforeach
    <input type="text" id="xrQ" class="mt-2" maxlength="20" placeholder="Question number (optional)">
    <textarea id="xrMsg" rows="4" class="mt-2" placeholder="Enter your doubt here" maxlength="2000"></textarea>
    <div class="xr-msg" id="xrErr">Min 20 characters</div>
    <div class="xr-row"><button type="button" class="xr-c" id="xrCancel">Cancel</button><button type="button" class="xr-s" id="xrSend">Submit</button></div>
</div></div>
<script>
(function () {
    var ov = document.getElementById('xrOv'), msg = document.getElementById('xrMsg'), err = document.getElementById('xrErr');
    var close = function () { ov.classList.remove('on'); };
    var xo = document.getElementById('xrOpen'); if (!xo) return;
    xo.onclick = function () { ov.classList.add('on'); };
    document.getElementById('xrCancel').onclick = close;
    ov.addEventListener('click', function (e) { if (e.target === ov) close(); });
    document.getElementById('xrSend').onclick = function () {
        if (msg.value.trim().length < 20) { err.textContent = 'Please write at least 20 characters.'; return; }
        var fd = new FormData();
        fd.append('exam_id', @json($examId)); fd.append('type', document.querySelector('input[name=xrType]:checked').value);
        fd.append('message', msg.value.trim()); fd.append('question_no', document.getElementById('xrQ').value);
        fetch(@json(url('kpsc/exam-report')), { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw 0; return r.json(); })
            .then(function () { msg.value = ''; document.getElementById('xrQ').value = ''; err.style.color = '#0a6b46'; err.textContent = 'Thanks, we will check it.'; setTimeout(function () { close(); err.style.color = ''; err.textContent = 'Min 20 characters'; }, 1200); })
            .catch(function () { err.textContent = 'Could not send. Try again.'; });
    };
})();
</script>
