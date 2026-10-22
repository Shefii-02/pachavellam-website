<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $inviter }} invited you to Pachavellam</title>
<meta property="og:title" content="{{ $inviter }} invited you to Pachavellam">
<meta property="og:description" content="Free classes, daily exams and mock tests for your exam preparation.@if ($coins) Join with this link and get {{ $coins }} coins.@endif">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px;background:linear-gradient(160deg,#0a8f5b,#064e3b);font:15px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1b2a41}
    .c{background:#fff;border-radius:22px;max-width:420px;width:100%;padding:26px 22px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.25)}
    .c .logo{width:64px;height:64px;border-radius:16px;background:#e7f6ef;color:#0a8f5b;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:10px}
    h1{font-size:1.3rem;margin:0 0 6px}.c p{margin:0 0 14px;color:#4b5b52}
    .gift{display:inline-flex;align-items:center;gap:8px;background:#fff7e0;color:#92600a;border-radius:99px;padding:6px 16px;font-weight:700;margin-bottom:16px}
    .b{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;border-radius:14px;font-weight:700;text-decoration:none;margin-top:10px;border:0;font-size:1rem}
    .b.p{background:#0a8f5b;color:#fff}.b.o{background:#fff;color:#0a8f5b;border:2px solid #0a8f5b}.b.k{background:#111;color:#fff}
    .code{margin-top:16px;font-size:.85rem;color:#6b7a72}.code b{letter-spacing:2px;font-size:1rem;color:#1b2a41}
</style>
</head>
<body>
<div class="c">
    <div class="logo"><i class="bi bi-mortarboard-fill"></i></div>
    <h1>{{ $inviter }} invited you to Pachavellam</h1>
    <p>Free classes, daily exams and mock tests for your exam preparation.</p>
    @if ($coins)<div class="gift"><i class="bi bi-coin"></i> {{ $coins }} coins for you when you join</div>@endif

    @if ($platform === 'android' && $androidLink)
        <a class="b p" id="open" href="{{ $androidLink }}"><i class="bi bi-google-play"></i> Get the Android app</a>
    @elseif ($platform === 'ios' && $ios)
        <a class="b k" id="open" href="{{ $ios }}"><i class="bi bi-apple"></i> Get the iPhone app</a>
    @endif
    <a class="b {{ in_array($platform, ['android', 'ios']) ? 'o' : 'p' }}" href="{{ $signUp }}"><i class="bi bi-globe2"></i> {{ in_array($platform, ['android', 'ios']) ? 'Continue on the website' : 'Join on the website' }}</a>
    @if ($platform === 'web')
        <div style="display:flex;gap:8px">
            @if ($androidLink)<a class="b o" style="font-size:.85rem" href="{{ $androidLink }}"><i class="bi bi-google-play"></i> Android</a>@endif
            @if ($ios)<a class="b o" style="font-size:.85rem" href="{{ $ios }}"><i class="bi bi-apple"></i> iPhone</a>@endif
        </div>
    @endif
    <div class="code">Friend's code <b id="cd">{{ $code }}</b></div>
</div>
<script>
    // iPhone has no install referrer: keep the code on the clipboard so the app can ask for it on first start, and the student can paste it under My account
    try { if (navigator.clipboard) document.getElementById('open') && document.getElementById('open').addEventListener('click', function () { navigator.clipboard.writeText('pachavellam-ref:{{ $code }}'); }); } catch (e) {}
    // app installed? open it straight away
    @if (in_array($platform, ['android', 'ios']))
    setTimeout(function () { location.href = 'pachavellam://refer?code={{ $code }}'; }, 400);
    @endif
</script>
</body>
</html>
