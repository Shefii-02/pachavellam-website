@php 
    $title = "Login/Signup";
@endphp

@extends('web.layout')
@section('title', $title ?? "KPSC")

@section('styles')

<style>

.frame {
    min-height: 200px;
    /*width: 420px;*/
       background-color:#e9ecefa8;
    margin-left: auto;
    margin-right: auto;
    border-top: solid 1px rgba(255, 255, 255, .5);
    border-radius: 15px;
    box-shadow: 0px 2px 7px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transition: all .5s ease
      text-shadow: 0 0 3px #FF0000;
}
</style>
	<link rel="stylesheet" type="text/css" 
	href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('content')
  <div class="preloader" id="preloader" style="display:none">
      <div class="spinner-grow text-secondary" role="status">
        <div class="sr-only">Loading...</div>
      </div>
    </div>
<div class="page-content-wrapper">
    <div class="element-wrapper">
        <div class="container">
            <div class="d-flex justify-content-center mt-6">
                <div class="col-lg-4 mb-5">
                    <div class="frame p-4">
                        <div class="text-center">
                            <h2 class="mt-1">Welcome Back</h2>
                            <p class="text-dark font-weight-bold mt-2">Login with your mobile number</p>
                            <hr>
                        </div>

                        <div class="alert alert-danger py-2" id="otpError" style="display:none"></div>
                        <div class="alert alert-success py-2" id="otpInfo" style="display:none"></div>

                        {{-- Step 1: mobile number --}}
                        <form id="numberStep" autocomplete="off">
                            <label class="form-label" for="otpMobile">Mobile number</label>
                            <div class="input-group mb-3">
                                <select id="otpCountry" class="form-select" style="max-width:130px" aria-label="Country code">
                                    @foreach ([
                                        '91' => 'IN +91', '1' => 'US +1', '44' => 'UK +44', '971' => 'AE +971',
                                        '966' => 'SA +966', '974' => 'QA +974', '965' => 'KW +965', '968' => 'OM +968',
                                        '973' => 'BH +973', '65' => 'SG +65', '60' => 'MY +60', '61' => 'AU +61',
                                        '49' => 'DE +49', '33' => 'FR +33', '94' => 'LK +94', '977' => 'NP +977',
                                        '880' => 'BD +880',
                                    ] as $code => $label)
                                        <option value="{{ $code }}" @selected($code === '91')>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="tel" inputmode="numeric" id="otpMobile" class="form-control" maxlength="12"
                                       placeholder="Mobile number" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100" id="sendOtpBtn">Send OTP</button>
                        </form>

                        {{-- Step 2: OTP --}}
                        <form id="otpStep" autocomplete="off" style="display:none">
                            <p class="mb-2">Enter the 6-digit OTP sent to <strong id="otpSentTo"></strong>
                                <a href="#" id="changeNumber" class="ms-1">Change</a></p>
                            <input type="text" inputmode="numeric" id="otpCode" class="form-control mb-3 text-center"
                                   maxlength="6" placeholder="------" style="letter-spacing:.5em" required>
                            <button type="submit" class="btn btn-success w-100" id="verifyOtpBtn">Verify OTP</button>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-link p-0" id="resendBtn">Resend OTP</button>
                                <div class="small text-muted mt-1" id="resendInfo"></div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <a style="border:1px solid #e6e6e6;" href="{{ route('auth.google') }}" class="btn btn-light">
                                <img src="https://colorlib.com/etc/lf/Login_v11/images/icons/icon-google.png" alt="">
                                &nbsp; Continue with your Email Id
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var $ = function (id) { return document.getElementById(id); };
    var resendsLeft = 2, lockTimer = null;

    function show(el, msg) { el.textContent = msg; el.style.display = msg ? '' : 'none'; }
    function notify(kind, msg) {
        show($('otpError'), kind === 'error' ? msg : '');
        show($('otpInfo'), kind === 'info' ? msg : '');
    }

    async function post(url, payload) {
        var res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        });
        var data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok && !data.message) {
            data.message = res.status === 422 ? 'Please check the details and try again.' : 'Something went wrong. Please try again.';
        }
        if (data.errors) { data.message = Object.values(data.errors)[0][0]; }
        return {ok: res.ok, data: data};
    }

    function payload() {
        return {country_code: $('otpCountry').value, mobile: $('otpMobile').value};
    }

    function validNumber() {
        var n = $('otpMobile').value;
        if ($('otpCountry').value === '91') return /^[6-9]\d{9}$/.test(n);
        return n.length >= 6 && n.length <= 12;
    }

    function updateResendUi(retryAfter) {
        var btn = $('resendBtn'), info = $('resendInfo');
        clearInterval(lockTimer);
        if (resendsLeft > 0) {
            btn.disabled = false; btn.style.display = '';
            info.textContent = resendsLeft + (resendsLeft === 1 ? ' resend' : ' resends') + ' left';
            return;
        }
        btn.disabled = true; btn.style.display = 'none';
        var until = Date.now() + (retryAfter || 900) * 1000;
        function tick() {
            var left = Math.max(0, Math.ceil((until - Date.now()) / 1000));
            if (left === 0) {
                clearInterval(lockTimer);
                info.textContent = '';
                btn.style.display = ''; btn.disabled = false;
                resendsLeft = 2;
                return;
            }
            info.textContent = 'Resend limit reached. Try again after ' + Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0') + ' minutes.';
        }
        tick();
        lockTimer = setInterval(tick, 1000);
    }

    async function sendOtp(isResend) {
        notify('error', '');
        if (!validNumber()) {
            notify('error', $('otpCountry').value === '91' ? 'Enter a valid 10-digit Indian mobile number.' : 'Enter a valid mobile number.');
            return;
        }
        var btn = isResend ? $('resendBtn') : $('sendOtpBtn');
        btn.disabled = true;
        var r = await post('{{ url("send-otp") }}', payload());
        btn.disabled = false;

        if (!r.ok) {
            notify('error', r.data.message);
            if (r.data.retry_after) {   // locked
                resendsLeft = 0;
                $('numberStep').style.display = 'none';
                $('otpStep').style.display = '';
                $('otpSentTo').textContent = '+' + $('otpCountry').value + ' ' + $('otpMobile').value;
                updateResendUi(r.data.retry_after);
            }
            return;
        }
        resendsLeft = r.data.resends_left;
        $('numberStep').style.display = 'none';
        $('otpStep').style.display = '';
        $('otpSentTo').textContent = '+' + $('otpCountry').value + ' ' + $('otpMobile').value;
        $('otpCode').value = ''; $('otpCode').focus();
        notify('info', r.data.message);
        updateResendUi(r.data.retry_after);
    }

    $('otpMobile').addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 12); });
    $('otpCode').addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 6); });

    $('numberStep').addEventListener('submit', function (e) { e.preventDefault(); sendOtp(false); });
    $('resendBtn').addEventListener('click', function () { sendOtp(true); });
    $('changeNumber').addEventListener('click', function (e) {
        e.preventDefault();
        notify('error', '');
        $('otpStep').style.display = 'none';
        $('numberStep').style.display = '';
    });

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
