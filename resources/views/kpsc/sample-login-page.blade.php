@php
    $title = 'Sign in';
    // opened through a friend's refer & earn link? (cookie set by /r/CODE)
    $refInviter = null;
    $refCoins = 0;
    [$refCode] = array_pad(explode('|', (string) request()->cookie(\App\Services\ReferralService::COOKIE), 2), 2, null);
    if ($refCode && \Illuminate\Support\Facades\Schema::hasColumn('users', 'referral_code')) {
        $refService = app(\App\Services\ReferralService::class);
        if ($refService->settings()['enabled'] && ($owner = \App\Models\User::where('referral_code', strtoupper($refCode))->first())) {
            $refInviter = trim(explode(' ', trim((string) $owner->name))[0] ?? '') ?: 'a friend';
            $refCoins = $refService->settings()['referred_coins'];
        }
    }
@endphp
@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('styles')
    @include('kpsc.hub.styles')
    <style>
        .si-wrap { max-width: 460px; margin: 0 auto; padding: 18px 0 30px; }
        .si-hero { text-align:center; margin-bottom:16px; }
        .si-logo { width:68px; height:68px; border-radius:20px; background:var(--pv-brand-soft); color:var(--pv-brand); display:inline-flex; align-items:center; justify-content:center; font-size:2rem; margin-bottom:10px; }
        .si-hero h1 { font-size:1.5rem; font-weight:800; margin:0; color:var(--pv-text); } .si-hero p { margin:4px 0 0; color:var(--pv-muted); font-size:.92rem; }
        .si-perks { display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin:12px 0 0; }
        .si-perks span { background:var(--pv-chip); border:1px solid var(--pv-border); border-radius:20px; padding:4px 12px; font-size:.78rem; font-weight:600; color:var(--pv-text); }
        .si-perks i { color:var(--pv-brand); margin-right:4px; }
        .si-card { background:var(--pv-surface); border:1px solid var(--pv-border); border-radius:20px; padding:20px 18px; }
        .si-card h2 { font-size:1.05rem; font-weight:800; margin:0 0 12px; color:var(--pv-text); }
        .si-card label { font-size:.82rem; font-weight:700; color:var(--pv-muted); margin-bottom:4px; display:block; }
        .si-row { display:flex; gap:8px; }
        .si-card select, .si-card input { border:1px solid var(--pv-border); background:var(--pv-surface); color:var(--pv-text); border-radius:12px; padding:11px 12px; font-size:1rem; width:100%; min-width:0; }
        .si-card select { width:auto; max-width:124px; flex:none; }
        .si-card input:focus, .si-card select:focus { outline:2px solid var(--pv-brand); outline-offset:0; }
        .si-otp { letter-spacing:.55em; text-align:center; font-size:1.3rem !important; font-weight:700; }
        .si-btn { width:100%; margin-top:12px; padding:12px; font-size:.95rem; }
        .si-msg { border-radius:12px; padding:9px 12px; font-size:.85rem; margin-bottom:12px; display:none; }
        .si-msg.err { background:color-mix(in srgb, #e5484d 14%, var(--pv-surface)); color:#b4232a; } .si-msg.ok { background:color-mix(in srgb, #0a8f5b 14%, var(--pv-surface)); color:var(--pv-text); }
        .si-or { display:flex; align-items:center; gap:10px; color:var(--pv-muted); font-size:.8rem; margin:16px 0 4px; } .si-or::before, .si-or::after { content:""; flex:1; border-top:1px solid var(--pv-border); }
        .si-google { display:flex; align-items:center; justify-content:center; gap:10px; width:100%; margin-top:10px; padding:11px; border:1px solid var(--pv-border); border-radius:12px; background:var(--pv-surface); color:var(--pv-text) !important; font-weight:700; text-decoration:none !important; }
        .si-google img { width:20px; height:20px; }
        .si-foot { text-align:center; margin-top:14px; font-size:.78rem; color:var(--pv-muted); } .si-foot a { color:var(--pv-brand); font-weight:700; }
        .si-ref { display:flex; align-items:center; gap:10px; background:#fff7e0; color:#7c4f06; border-radius:14px; padding:10px 14px; font-size:.88rem; font-weight:600; margin-bottom:12px; }
        .si-ref i { font-size:1.4rem; color:#d97706; }
        [data-theme="dark"] .si-ref, .dark .si-ref { background:#3a2c0a; color:#fcd34d; }
    </style>
@endsection
@section('content')
<div class="page-content-wrapper"><div class="container"><div class="si-wrap">
    <div class="si-hero">
        <div class="si-logo"><i class="bi bi-mortarboard-fill"></i></div>
        <h1>Welcome to Pachavellam</h1>
        <p>Sign in to join classes, take tests and track your rank.</p>
        <div class="si-perks"><span><i class="bi bi-play-circle"></i>Free classes</span><span><i class="bi bi-calendar-check"></i>Daily exams</span><span><i class="bi bi-trophy"></i>Rank &amp; coins</span></div>
    </div>

    @if ($refInviter)
        <div class="si-ref"><i class="bi bi-gift-fill"></i><span>{{ ucfirst($refInviter) }} invited you.@if ($refCoins) Join now and get <b>{{ $refCoins }} coins</b>.@endif</span></div>
    @endif

    <div class="si-card">
        <div class="si-msg err" id="otpError"></div>
        <div class="si-msg ok" id="otpInfo"></div>

        {{-- Step 1: mobile number --}}
        <form id="numberStep" autocomplete="off">
            <h2>Continue with your mobile number</h2>
            <label for="otpMobile">Mobile number</label>
            <div class="si-row">
                <select id="otpCountry" aria-label="Country code">
                    @foreach (['91' => 'IN +91', '1' => 'US +1', '44' => 'UK +44', '971' => 'AE +971', '966' => 'SA +966', '974' => 'QA +974', '965' => 'KW +965', '968' => 'OM +968', '973' => 'BH +973', '65' => 'SG +65', '60' => 'MY +60', '61' => 'AU +61', '49' => 'DE +49', '33' => 'FR +33', '94' => 'LK +94', '977' => 'NP +977', '880' => 'BD +880'] as $code => $label)
                        <option value="{{ $code }}" @selected($code === '91')>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="tel" inputmode="numeric" id="otpMobile" maxlength="12" placeholder="Mobile number" required autocomplete="tel-national">
            </div>
            <button type="submit" class="hb-btn si-btn" id="sendOtpBtn">Send OTP</button>
        </form>

        {{-- Step 2: OTP --}}
        <form id="otpStep" autocomplete="off" style="display:none">
            <h2>Enter the OTP</h2>
            <p class="small" style="color:var(--pv-muted);margin-bottom:10px">Sent to <strong id="otpSentTo" style="color:var(--pv-text)"></strong> <a href="#" id="changeNumber" style="color:var(--pv-brand);font-weight:700;margin-left:4px">Change</a></p>
            <input type="text" inputmode="numeric" id="otpCode" class="si-otp" maxlength="6" placeholder="------" required autocomplete="one-time-code">
            <button type="submit" class="hb-btn si-btn" id="verifyOtpBtn">Verify &amp; continue</button>
            <div class="text-center mt-3">
                <button type="button" class="hb-btn ghost sm" id="resendBtn">Resend OTP</button>
                <div class="small mt-1" id="resendInfo" style="color:var(--pv-muted)"></div>
            </div>
        </form>

        <div class="si-or">or</div>
        <a href="{{ route('auth.google') }}" class="si-google"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="">Continue with Google</a>
    </div>

    <div class="si-foot">By continuing you agree to our <a href="{{ url('info/terms-and-conditions') }}">Terms &amp; Privacy</a>.<br><a href="{{ url('/') }}">Skip for now &mdash; browse the site</a></div>
</div></div></div>
@endsection
@section('scripts')
<script>
(function () {
    var csrf = '{{ csrf_token() }}';
    var $ = function (id) { return document.getElementById(id); };
    var resendsLeft = 2, lockTimer = null;

    function show(el, msg) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
    function notify(kind, msg) { show($('otpError'), kind === 'error' ? msg : ''); show($('otpInfo'), kind === 'info' ? msg : ''); }

    async function post(url, payload) {
        var res = await fetch(url, { method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify(payload), credentials: 'same-origin' });
        var data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok && !data.message) { data.message = res.status === 422 ? 'Please check the details and try again.' : 'Something went wrong. Please try again.'; }
        if (data.errors) { data.message = Object.values(data.errors)[0][0]; }
        return {ok: res.ok, data: data};
    }
    function payload() { return {country_code: $('otpCountry').value, mobile: $('otpMobile').value}; }
    function validNumber() { var n = $('otpMobile').value; if ($('otpCountry').value === '91') return /^[6-9]\d{9}$/.test(n); return n.length >= 6 && n.length <= 12; }

    function updateResendUi(retryAfter) {
        var btn = $('resendBtn'), info = $('resendInfo');
        clearInterval(lockTimer);
        if (resendsLeft > 0) { btn.disabled = false; btn.style.display = ''; info.textContent = resendsLeft + (resendsLeft === 1 ? ' resend' : ' resends') + ' left'; return; }
        btn.disabled = true; btn.style.display = 'none';
        var until = Date.now() + (retryAfter || 900) * 1000;
        function tick() {
            var left = Math.max(0, Math.ceil((until - Date.now()) / 1000));
            if (left === 0) { clearInterval(lockTimer); info.textContent = ''; btn.style.display = ''; btn.disabled = false; resendsLeft = 2; return; }
            info.textContent = 'Resend limit reached. Try again after ' + Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0') + ' minutes.';
        }
        tick(); lockTimer = setInterval(tick, 1000);
    }

    async function sendOtp(isResend) {
        notify('error', '');
        if (!validNumber()) { notify('error', $('otpCountry').value === '91' ? 'Enter a valid 10-digit Indian mobile number.' : 'Enter a valid mobile number.'); return; }
        var btn = isResend ? $('resendBtn') : $('sendOtpBtn');
        btn.disabled = true;
        var r = await post('{{ url("send-otp") }}', payload());
        btn.disabled = false;
        if (!r.ok) {
            notify('error', r.data.message);
            if (r.data.retry_after) { resendsLeft = 0; $('numberStep').style.display = 'none'; $('otpStep').style.display = ''; $('otpSentTo').textContent = '+' + $('otpCountry').value + ' ' + $('otpMobile').value; updateResendUi(r.data.retry_after); }
            return;
        }
        resendsLeft = r.data.resends_left;
        $('numberStep').style.display = 'none'; $('otpStep').style.display = '';
        $('otpSentTo').textContent = '+' + $('otpCountry').value + ' ' + $('otpMobile').value;
        $('otpCode').value = ''; $('otpCode').focus();
        notify('info', r.data.message);
        updateResendUi(r.data.retry_after);
    }

    $('otpMobile').addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 12); });
    $('otpCode').addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 6); });
    $('numberStep').addEventListener('submit', function (e) { e.preventDefault(); sendOtp(false); });
    $('resendBtn').addEventListener('click', function () { sendOtp(true); });
    $('changeNumber').addEventListener('click', function (e) { e.preventDefault(); notify('error', ''); $('otpStep').style.display = 'none'; $('numberStep').style.display = ''; });

    $('otpStep').addEventListener('submit', async function (e) {
        e.preventDefault();
        notify('error', '');
        if ($('otpCode').value.length !== 6) { notify('error', 'Enter the 6-digit OTP.'); return; }
        var btn = $('verifyOtpBtn'); btn.disabled = true;
        var p = payload(); p.otp = $('otpCode').value;
        var r = await post('{{ url("verify-otp") }}', p);
        btn.disabled = false;
        if (!r.ok) { notify('error', r.data.message); return; }
        window.location.href = r.data.redirect === '/' ? '{{ url("/") }}' : '{{ url("account-settings") }}';
    });
})();
</script>
@endsection
