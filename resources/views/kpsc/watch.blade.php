@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-3" style="max-width:960px">
    <a href="{{ url('/') }}" class="small"><i class="bi bi-arrow-left"></i> Home</a>
    <style>
        .pf-player{width:100%;border:0;border-radius:16px;background:#000;margin:12px 0;display:block}
        .pf-player.nochat{aspect-ratio:16/9;height:auto}
        .pf-player.chat{height:min(80vh,520px)}
        @media(max-width:820px){.pf-player{border-radius:12px;margin:8px 0}.pf-player.chat{height:min(calc(56.25vw + 46px + 430px), calc(100vh - 90px));height:min(calc(56.25vw + 46px + 430px), calc(100dvh - 90px))}}
        @media(max-height:520px) and (orientation:landscape){.pf-player.chat{height:calc(100vh - 70px);height:calc(100dvh - 70px)}}
    .fullscreen-action-menu{display:none!important}
    </style>
    <iframe class="pf-player {{ $v->chat_mode === 'always' ? 'chat' : 'nochat' }}" src="{{ url('player/' . $v->uid) }}" allowfullscreen allow="autoplay; fullscreen; picture-in-picture" title="{{ $v->title }}"></iframe>
    <h4 class="fw-bold">{{ $v->title }}</h4>
    @if ($more->count())
        <h6 class="mt-4">More YouTube classes</h6>
        <div class="row g-3">@foreach ($more as $m)<div class="col-6 col-md-3"><a href="{{ url('watch/' . $m->uid) }}" class="d-block text-decoration-none text-dark"><img src="https://img.youtube.com/vi/{{ $m->redirection }}/hqdefault.jpg" class="w-100 rounded-3" alt="" loading="lazy"><div class="small fw-semibold mt-1">{{ \Illuminate\Support\Str::limit($m->title, 50) }}</div></a></div>@endforeach</div>
    @endif
</div></div>
<script>
(function () {
    // hide .fullscreen-action-menu in every iframe once it has loaded (only possible for frames of our own site; the browser blocks it for YouTube's frame)
    function hide(f) { try { var d = f.contentDocument; if (d && !d.getElementById('fam-hide')) { var s = d.createElement('style'); s.id = 'fam-hide'; s.textContent = '.fullscreen-action-menu{display:none!important}'; d.head.appendChild(s); } } catch (e) {} }
    function wire(f) { f.addEventListener('load', function () { hide(f); }); hide(f); }
    [].forEach.call(document.querySelectorAll('iframe'), wire);
    new MutationObserver(function (ms) { ms.forEach(function (m) { [].forEach.call(m.addedNodes, function (n) { if (n.tagName === 'IFRAME') wire(n); else if (n.querySelectorAll) [].forEach.call(n.querySelectorAll('iframe'), wire); }); }); }).observe(document.documentElement, { childList: true, subtree: true });
})();
</script>
@endsection
