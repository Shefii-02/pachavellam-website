<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex">
<title>{{ $title }}</title>
<style>
    *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    html,body{margin:0;height:100%;background:#0b0f0d;color:#e8efe9;font:14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;overflow:hidden}
    .pl{display:flex;height:100%;height:100dvh}
    .pl-v{flex:1;min-width:0;display:flex;flex-direction:column;min-height:0}
    .stage{position:relative;flex:1;background:#000;min-height:0}
    #yt{position:absolute;inset:0;width:100%;height:100%;border:0}
    /* On top of the video only once it is playing - the first tap must reach YouTube's own play button (phones refuse a play started from outside the frame). */
    .shield{position:absolute;inset:0;z-index:2;display:none}
    .live{position:absolute;left:10px;top:10px;z-index:3;background:#e5484d;color:#fff;font-weight:700;font-size:11px;padding:2px 8px;border-radius:99px;pointer-events:none}
    /* YouTube draws the video title over the top of its frame and we cannot change that frame, so a black strip covers it while the video is not playing (it fades by itself a few seconds after play starts, and our shield stops it coming back) */
    .cover{position:absolute;left:0;right:0;top:0;height:clamp(44px,14%,68px);z-index:3;background:linear-gradient(#000 78%,rgba(0,0,0,0));pointer-events:none}
    .vt{flex:none;padding:8px 12px;background:#111815;border-top:1px solid #1f2a25;font-weight:600;font-size:14px;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    /* our own loading screen: covers YouTube's frame (its logo / spinner) until the player is loaded, then goes away completely */
    .loader{position:absolute;inset:0;z-index:6;background:#000 center/cover no-repeat;display:flex;align-items:center;justify-content:center;transition:opacity .25s}
    .loader::before{content:"";position:absolute;inset:0;background:rgba(0,0,0,.45)}
    .loader i{position:relative;width:38px;height:38px;border-radius:50%;border:3px solid rgba(255,255,255,.25);border-top-color:#4ade80;animation:spin .8s linear infinite}
    .loader.gone{opacity:0;pointer-events:none}.loader.off{display:none}
    @keyframes spin{to{transform:rotate(360deg)}}
    /* when the video ends: no YouTube suggestions, no next video, no loop - just a replay button */
    .end{position:absolute;inset:0;z-index:5;background:#000 center/cover no-repeat;display:none;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:#fff}
    .end::before{content:"";position:absolute;inset:0;background:rgba(0,0,0,.6)}
    .end *{position:relative}.end button{border:0;background:#0a8f5b;color:#fff;border-radius:99px;padding:10px 22px;font-size:15px;font-weight:600}
    .msg{position:absolute;inset:0;z-index:4;display:none;align-items:center;justify-content:center;text-align:center;padding:20px;background:#000;color:#cbd5d0}
    .ctl{display:flex;align-items:center;gap:6px;padding:6px 8px;background:#111815;flex:none}
    .ctl button{border:0;background:transparent;color:#fff;font-size:18px;min-width:38px;height:38px;border-radius:8px;padding:0 6px}
    .ctl button:active{background:#1f2a25}.ctl .t{font-size:12px;color:#9fb3a8;white-space:nowrap}
    input[type=range]{flex:1;accent-color:#0a8f5b;min-width:60px}
    .spd{font-size:12px!important;font-weight:700}
    .chat{width:340px;flex:none;display:flex;flex-direction:column;border-left:1px solid #1f2a25;background:#111815;min-height:0}
    .chat h6{margin:0;padding:10px 12px;font-size:14px;border-bottom:1px solid #1f2a25;display:flex;gap:6px;align-items:center;flex:none}
    .chat .b{margin-left:auto;font-size:11px;padding:2px 8px;border-radius:99px;background:#0a8f5b}.chat .b.c{background:#4b5563}
    #list{flex:1;min-height:0;overflow-y:auto;padding:10px;display:flex;flex-direction:column;gap:6px;-webkit-overflow-scrolling:touch;overscroll-behavior:contain}
    #list .m{word-break:break-word}#list .m b{color:#4ade80;margin-right:4px}#list .m.me b{color:#60a5fa}#list small{color:#6b7f74;margin-left:4px}
    #list .e{margin:auto;color:#6b7f74;text-align:center}
    form{display:flex;gap:6px;padding:8px;padding-bottom:calc(8px + env(safe-area-inset-bottom));border-top:1px solid #1f2a25;flex:none}form.off{opacity:.5;pointer-events:none}
    form input{flex:1;min-width:0;border:1px solid #2a3832;background:#0b0f0d;color:#fff;border-radius:99px;padding:9px 14px;font-size:16px}   /* 16px: iOS does not zoom in */
    form button,.signin{border:0;background:#0a8f5b;color:#fff;border-radius:99px;padding:0 16px;height:40px;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:14px}
    .signin{flex:1}

    /* tablet / large phone, upright: video on top (fixed 16:9), chat fills the rest of the screen with the message box at the bottom */
    @media(max-width:820px){
        .pl{flex-direction:column}
        .pl-v{flex:none}.stage{flex:none;aspect-ratio:16/9}
        .chat{width:auto;flex:1;border-left:0;border-top:1px solid #1f2a25}
        .ctl .t{display:none}
    }
    /* no chat: the video simply fills the frame */
    .pl.nochat .pl-v{flex:1}.pl.nochat .stage{flex:1;aspect-ratio:auto}
    /* phone turned sideways: video left, chat right (a 16:9 video would leave no room for chat on top) */
    @media(max-height:520px) and (orientation:landscape){
        .pl{flex-direction:row}
        .pl-v{flex:1}.stage{flex:1;aspect-ratio:auto}
        .chat{width:min(40%,320px);flex:none;border-left:1px solid #1f2a25;border-top:0}
        .ctl{padding:2px 6px}.ctl button{height:32px}
        .chat h6{padding:6px 10px}form{padding-top:4px;padding-bottom:calc(4px + env(safe-area-inset-bottom))}
    }
    @media(max-width:380px){.ctl button{min-width:34px;font-size:16px}#back{display:none}}
.fullscreen-action-menu{display:none!important}
</style>
</head>
<body>
<div class="pl {{ $chatUrl ? '' : 'nochat' }}">
    <div class="pl-v">
        <div class="stage" id="stage">
            <iframe id="yt" title="{{ $title }}" src="https://www.youtube.com/embed/{{ $vid }}?{{ http_build_query(['controls' => 0, 'disablekb' => 1, 'modestbranding' => 1, 'rel' => 0, 'iv_load_policy' => 3, 'fs' => 0, 'playsinline' => 1, 'autoplay' => $autoplay ? 1 : 0, 'mute' => $autoplay ? 1 : 0, 'loop' => 0, 'enablejsapi' => 1]) }}" allow="autoplay; encrypted-media; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" frameborder="0"></iframe>
            <div class="shield" id="shield"></div>
            <div class="cover" id="cover"></div>
            <div class="end" id="end" style="background-image:url('https://img.youtube.com/vi/{{ $vid }}/hqdefault.jpg')"><div>Video finished</div><button id="replay">&#8634; Watch again</button></div>
            <div class="loader" id="loader" style="background-image:url('https://img.youtube.com/vi/{{ $vid }}/hqdefault.jpg')"><i></i></div>
            @if ($live)<span class="live">LIVE</span>@endif
            <div class="msg" id="err">This video cannot be played right now.<br>Please try again later.</div>
        </div>
        <div class="vt">{{ $title }}</div>
        <div class="ctl">
            <button id="pp" aria-label="Play / pause">&#9654;</button>
            <button id="back" aria-label="Back 10 seconds">&#8634;10</button>
            <input type="range" id="seek" min="0" max="1000" value="0" aria-label="Seek">
            <span class="t" id="tm">0:00 / 0:00</span>
            <button class="spd" id="spd" aria-label="Speed">1x</button>
            <button id="mute" aria-label="Sound">{!! $autoplay ? '&#128263;' : '&#128266;' !!}</button>
            <button id="fs" aria-label="Full screen">&#x26F6;</button>
        </div>
    </div>
    @if ($chatUrl)
    <aside class="chat">
        <h6>Class chat <span class="b c" id="state">…</span></h6>
        <div id="list"><div class="e">Loading…</div></div>
        <form id="form" class="off" autocomplete="off">
            @if ($loggedIn)<input id="inp" maxlength="400" placeholder="Say something…" enterkeyhint="send"><button>Send</button>
            @else<a class="signin" href="{{ url('sign-in') }}" target="_top">Sign in to chat</a>@endif
        </form>
    </aside>
    @endif
</div>

<script>
(function () {
    var coverTimer, VID = @json($vid), player, ready = false, dragging = false, speeds = [1, 1.25, 1.5, 2, 0.75], si = 0, fell = false, loaded = false;
    var $ = function (id) { return document.getElementById(id); };
    // loading screen: hidden for good as soon as YouTube's frame has loaded and the player answers (or after 9 s at most)
    function hideLoader() { if (loaded) return; loaded = true; var l = $('loader'); if (!l) return; l.classList.add('gone'); setTimeout(function () { l.classList.add('off'); }, 300); }
    $('yt').addEventListener('load', function () { setTimeout(hideLoader, 900); });
    setTimeout(hideLoader, 9000);
    function fmt(s) { s = Math.max(0, Math.floor(s || 0)); var h = Math.floor(s / 3600), m = Math.floor(s % 3600 / 60), x = s % 60; return (h ? h + ':' + (m < 10 ? '0' : '') : '') + m + ':' + (x < 10 ? '0' : '') + x; }

    // plain YouTube player (with its own controls) when the scripted one cannot start - blocked script, old browser...
    function fallback() {
        if (fell || ready) return; fell = true;
        document.querySelector('.ctl').style.display = 'none'; $('shield').style.display = 'none';
        $('stage').innerHTML = '<iframe src="https://www.youtube.com/embed/' + VID + '?rel=0&playsinline=1&modestbranding=1" style="position:absolute;inset:0;width:100%;height:100%;border:0" allow="autoplay; encrypted-media; picture-in-picture"></iframe>';
    }
    var failTimer = setTimeout(fallback, 7000);

    window.onYouTubeIframeAPIReady = function () {
        player = new YT.Player('yt', {   // attaches to the frame above, so it keeps having no fullscreen permission
            events: {
                onReady: function () { ready = true; clearTimeout(failTimer); hideLoader(); setInterval(tick, 500); },
                onStateChange: function (e) {
                    var s = e.data, playing = s === 1;
                    $('pp').innerHTML = playing ? '&#10074;&#10074;' : '&#9654;';
                    // after the first play, our shield takes over so taps pause / play and never reach YouTube's logo or suggestions
                    $('shield').style.display = (s === 1 || s === 2 || s === 3) ? 'block' : 'none';
                    if (s === 0) { player.stopVideo(); $('end').style.display = 'flex'; $('shield').style.display = 'none'; } else if (s === 1) { $('end').style.display = 'none'; }
                    clearTimeout(coverTimer);
                    if (s === 1) { coverTimer = setTimeout(function () { $('cover').style.display = 'none'; }, 3500); } else { $('cover').style.display = 'block'; }
                },
                onError: function () { clearTimeout(failTimer); hideLoader(); $('err').style.display = 'flex'; }
            }
        });
    };
    var s = document.createElement('script'); s.src = 'https://www.youtube.com/iframe_api'; s.onerror = fallback; document.head.appendChild(s);

    function tick() { if (!player || !player.getDuration) return; var d = player.getDuration(), c = player.getCurrentTime(); $('tm').textContent = fmt(c) + ' / ' + fmt(d); if (!dragging && d) $('seek').value = Math.round(c / d * 1000); }
    function toggle() { if (!ready) return; player.getPlayerState() === 1 ? player.pauseVideo() : player.playVideo(); }
    $('pp').onclick = $('shield').onclick = function () { $('end').style.display = 'none'; toggle(); };
    $('replay').onclick = function () { $('end').style.display = 'none'; if (ready) { player.seekTo(0, true); player.playVideo(); } };
    $('back').onclick = function () { ready && player.seekTo(Math.max(0, player.getCurrentTime() - 10), true); };
    $('seek').oninput = function () { dragging = true; }; $('seek').onchange = function () { dragging = false; if (ready) player.seekTo(player.getDuration() * this.value / 1000, true); };
    $('spd').onclick = function () { si = (si + 1) % speeds.length; ready && player.setPlaybackRate(speeds[si]); this.textContent = speeds[si] + 'x'; };
    $('mute').onclick = function () { if (!ready) return; if (player.isMuted()) { player.unMute(); this.innerHTML = '&#128266;'; } else { player.mute(); this.innerHTML = '&#128263;'; } };
    var stage = $('stage'), rfs = stage.requestFullscreen || stage.webkitRequestFullscreen;
    if (!rfs) $('fs').style.display = 'none';   // e.g. iPhone Safari cannot full-screen a page element
    $('fs').onclick = function () { (document.fullscreenElement || document.webkitFullscreenElement) ? (document.exitFullscreen || document.webkitExitFullscreen).call(document) : rfs.call(stage); };

    @if ($chatUrl)
    var base = @json($chatUrl), last = 0, open = null, busy = false, list = $('list'), form = $('form'), inp = $('inp');
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function draw(j) {
        if (!j.enabled) { document.querySelector('.chat').style.display = 'none'; document.querySelector('.pl').classList.add('nochat'); return; }
        if (open !== j.open) { open = j.open; $('state').textContent = j.open ? 'Open' : 'Closed'; $('state').className = 'b' + (j.open ? '' : ' c'); form.classList.toggle('off', !j.open); if (inp) inp.placeholder = j.open ? 'Say something…' : j.closedText; }
        if (!last && !j.messages.length) { list.innerHTML = '<div class="e">' + esc(j.open ? 'No messages yet - say hello!' : j.closedText) + '</div>'; return; }
        if (j.messages.length) {
            if (!last) list.innerHTML = '';
            var stick = !last || list.scrollTop + list.clientHeight >= list.scrollHeight - 40;
            j.messages.forEach(function (m) { last = Math.max(last, m.id); list.insertAdjacentHTML('beforeend', '<div class="m' + (m.mine ? ' me' : '') + '"><b>' + esc(m.mine ? 'You' : m.name) + '</b>' + esc(m.body) + '<small>' + m.t + '</small></div>'); });
            if (stick) list.scrollTop = list.scrollHeight;
        }
    }
    function poll() { if (busy || document.hidden) return; busy = true; fetch(base + '?after=' + last, { headers: { Accept: 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(draw).catch(function () {}).finally(function () { busy = false; }); }
    poll(); setInterval(poll, 4000);
    if (inp) form.addEventListener('submit', function (e) {
        e.preventDefault(); var v = inp.value.trim(); if (!v) return; inp.value = ''; inp.focus();
        fetch(base, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) }, credentials: 'same-origin', body: JSON.stringify({ body: v }) })
            .then(function (r) { return r.json().then(function (j) { if (!r.ok) alert(j.message || 'Could not send'); else poll(); }); });
    });
    @endif
})();
</script>
<script>
(function () {
    // hide .fullscreen-action-menu in every iframe once it has loaded (only possible for frames of our own site; the browser blocks it for YouTube's frame)
    function hide(f) { try { var d = f.contentDocument; if (d && !d.getElementById('fam-hide')) { var s = d.createElement('style'); s.id = 'fam-hide'; s.textContent = '.fullscreen-action-menu{display:none!important}'; d.head.appendChild(s); } } catch (e) {} }
    function wire(f) { f.addEventListener('load', function () { hide(f); }); hide(f); }
    [].forEach.call(document.querySelectorAll('iframe'), wire);
    new MutationObserver(function (ms) { ms.forEach(function (m) { [].forEach.call(m.addedNodes, function (n) { if (n.tagName === 'IFRAME') wire(n); else if (n.querySelectorAll) [].forEach.call(n.querySelectorAll('iframe'), wire); }); }); }).observe(document.documentElement, { childList: true, subtree: true });
})();
</script>
</body>
</html>
