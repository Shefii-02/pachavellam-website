@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-3" style="max-width:820px">
    <a href="{{ url('articles') }}" class="small"><i class="bi bi-arrow-left"></i> Free articles</a>
    @if ($a->image)<img src="{{ Storage::url('files/' . $a->image) }}" class="w-100 rounded-4 my-3" alt="">@endif
    <div class="mb-1">@foreach ($a->exams as $e)<span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span> @endforeach</div>
    <h2 class="fw-bold">{{ $a->title }}</h2>
    @if ($a->published_at)<div class="text-muted small mb-3">{{ $a->published_at->format('d M Y') }}</div>@endif
    {{-- learn by listening: the recorded voice when there is one, else the browser reads the article aloud --}}
    @if ($a->audioSrc())
        <div class="my-3 p-2 rounded-4 bg-white shadow-sm"><div class="small fw-bold mb-1"><i class="bi bi-headphones me-1"></i>Listen to this article</div><audio controls preload="none" style="width:100%" src="{{ $a->audioSrc() }}"></audio></div>
    @else
        <div class="my-3 d-flex gap-2 align-items-center" id="tts" hidden>
            <button type="button" class="btn btn-outline-dark rounded-pill btn-sm" id="ttsPlay"><i class="bi bi-headphones me-1"></i><span>Listen to this article</span></button>
            <button type="button" class="btn btn-light rounded-pill btn-sm" id="ttsStop" hidden><i class="bi bi-stop-fill"></i> Stop</button>
        </div>
        <script>
            (function () {
                if (!('speechSynthesis' in window)) return;
                var box = document.getElementById('tts'), play = document.getElementById('ttsPlay'), stop = document.getElementById('ttsStop'), label = play.querySelector('span');
                var text = @json($a->speakText()), parts = text.match(/[^.!?।]+[.!?।]?/g) || [text], i = 0, on = false;
                box.hidden = false;
                function say() {
                    if (!on || i >= parts.length) { reset(); return; }
                    var u = new SpeechSynthesisUtterance(parts[i++]); u.lang = /[\u0D00-\u0D7F]/.test(text) ? 'ml-IN' : 'en-IN'; u.onend = say; speechSynthesis.speak(u);
                }
                function reset() { on = false; i = 0; speechSynthesis.cancel(); label.textContent = 'Listen to this article'; stop.hidden = true; }
                play.onclick = function () {
                    if (!on) { on = true; label.textContent = 'Pause'; stop.hidden = false; if (speechSynthesis.paused) speechSynthesis.resume(); else say(); }
                    else if (speechSynthesis.paused) { speechSynthesis.resume(); label.textContent = 'Pause'; }
                    else { speechSynthesis.pause(); label.textContent = 'Resume'; }
                };
                stop.onclick = reset; window.addEventListener('pagehide', reset);
            })();
        </script>
    @endif
    @include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'article_detail'])
    <div class="art-body" style="line-height:1.7;overflow-wrap:anywhere">{!! $a->descriptionHtml() !!}</div>
    @include('partials.ad-slot', ['type' => 'web_article', 'page' => 'article_detail'])
    <style>.art-body img{max-width:100%;height:auto;border-radius:10px}.art-body table{max-width:100%;display:block;overflow-x:auto}</style>
    @if ($a->link_url)<a href="{{ $a->link_url }}" class="btn btn-outline-dark rounded-pill mt-3" target="_blank" rel="noopener">Read more</a>@endif
</div></div>
@endsection
