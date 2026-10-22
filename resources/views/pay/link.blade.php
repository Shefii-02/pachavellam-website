<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Complete your admission</title>
    <style>
        :root { --bg:#f4f6fb; --card:#fff; --text:#1d2433; --muted:#6b7280; --brand:#0d6efd; --ok:#198754; --bad:#dc3545; --line:#e5e7eb; }
        @media (prefers-color-scheme: dark) { :root { --bg:#0f1420; --card:#171d2c; --text:#e8ebf3; --muted:#9aa3b5; --line:#2a3246; } }
        * { box-sizing: border-box; } body { margin:0; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background:var(--bg); color:var(--text); }
        .wrap { max-width:460px; margin:0 auto; padding:20px 16px 40px; }
        .card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .cover { width:100%; height:150px; object-fit:cover; display:block; }
        .body { padding:18px; } h1 { font-size:1.2rem; margin:0 0 4px; } .muted { color:var(--muted); font-size:.9rem; }
        .row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--line); } .row:last-of-type { border-bottom:0; }
        .total { font-weight:700; font-size:1.15rem; } .save { color:var(--ok); }
        button { width:100%; margin-top:16px; padding:14px; border:0; border-radius:10px; background:var(--brand); color:#fff; font-size:1rem; font-weight:600; cursor:pointer; }
        button[disabled] { opacity:.6; cursor:default; } .msg { margin-top:12px; padding:10px 12px; border-radius:8px; font-size:.92rem; }
        .msg.err { background:rgba(220,53,69,.12); color:var(--bad); } .msg.ok { background:rgba(25,135,84,.12); color:var(--ok); }
        .big { text-align:center; padding:28px 18px; } .big .icon { font-size:2.6rem; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @if ($link->course->cover_image)<img class="cover" src="{{ asset('storage/' . $link->course->cover_image) }}" alt="">@endif
        @if ($state === 'active')
            <div class="body" id="payBox">
                <h1>{{ $link->course->title }}</h1>
                <div class="muted">For {{ $maskedName }} · {{ $link->course->validityLabel() }}</div>
                <div style="margin-top:14px">
                    <div class="row"><span>Course fee</span><span>₹{{ number_format($quote['original'], 2) }}</span></div>
                    @if ($quote['discount'] > 0)<div class="row save"><span>Coupon {{ $link->coupon_code }}</span><span>− ₹{{ number_format($quote['discount'], 2) }}</span></div>@endif
                    <div class="row total"><span>To pay</span><span>₹{{ number_format($quote['final'], 2) }}</span></div>
                </div>
                <button id="payBtn" type="button">Pay ₹{{ number_format($quote['final'], 2) }}</button>
                <div id="msg"></div>
                <p class="muted" style="margin-top:14px;font-size:.8rem">Secure payment through Razorpay / PhonePe. This link works for one student and expires {{ $link->expires_at?->format('d M Y, h:i A') ?? 'never' }}.</p>
            </div>
        @elseif ($state === 'paid')
            <div class="big"><div class="icon">✅</div><h1>Admission complete</h1><p class="muted">This link has been paid. {{ $link->course->title }} is now in the student's account — open the app to start learning.</p></div>
        @elseif ($state === 'expired')
            <div class="big"><div class="icon">⌛</div><h1>This link has expired</h1><p class="muted">Please ask for a new payment link.</p></div>
        @else
            <div class="big"><div class="icon">⚠️</div><h1>This link is not valid</h1><p class="muted">Please ask for a new payment link.</p></div>
        @endif
    </div>
</div>

@if ($state === 'active')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var token = @json($link->token), btn = document.getElementById('payBtn'), msg = document.getElementById('msg');
    var csrf = document.querySelector('meta[name=csrf-token]').content;
    function show(text, cls) { msg.innerHTML = '<div class="msg ' + cls + '"></div>'; msg.firstChild.textContent = text; }
    function post(path, body) {
        return fetch(@json(url('pay')) + '/' + token + '/' + path, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body || {}) })
            .then(function (r) { return r.json().then(function (j) { j._ok = r.ok; return j; }); });
    }
    function done() { document.getElementById('payBox').innerHTML = '<div class="big"><div class="icon">✅</div><h1>Payment received</h1><p class="muted">Your admission is complete. Open the app to start learning.</p></div>'; }
    function poll(n) {
        fetch(@json(url('pay')) + '/' + token + '/status', { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }).then(function (j) {
            if (j.paid) { done(); } else if (n > 0) { setTimeout(function () { poll(n - 1); }, 3000); } else { show('We have not received the payment yet. If money was deducted it will be added shortly; you can reopen this link to check.', 'err'); btn.disabled = false; }
        });
    }

    btn.addEventListener('click', function () {
        btn.disabled = true; msg.innerHTML = '';
        post('start').then(function (j) {
            if (!j._ok) { show(j.message || 'Could not start the payment.', 'err'); btn.disabled = false; return; }
            if (j.free) { done(); return; }
            if (j.gateway === 'phonepe') { window.location = j.checkout.redirect_url; return; }
            var c = j.checkout;
            var rz = new Razorpay({
                key: c.key_id, order_id: c.order_id, amount: c.amount, currency: c.currency, name: c.name, description: c.description, prefill: c.prefill,
                handler: function (resp) {
                    show('Confirming your payment…', 'ok');
                    post('verify', { merchant_ref: j.merchant_ref, razorpay_order_id: resp.razorpay_order_id, razorpay_payment_id: resp.razorpay_payment_id, razorpay_signature: resp.razorpay_signature })
                        .then(function (v) { if (v.paid) { done(); } else { poll(5); } });
                },
                modal: { ondismiss: function () { btn.disabled = false; } }
            });
            rz.on('payment.failed', function (e) { show((e.error && e.error.description) || 'The payment failed. You can try again.', 'err'); btn.disabled = false; });
            rz.open();
        }).catch(function () { show('Network problem. Please try again.', 'err'); btn.disabled = false; });
    });
})();
</script>
@endif
</body>
</html>
