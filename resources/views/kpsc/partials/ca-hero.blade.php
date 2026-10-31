{{-- Header card of the current affairs pages. Needs $heading, $msg (share text); optional $crumbs = [label => url]. --}}
<div class="ca-hero">
    @if (!empty($crumbs))
        <div class="ca-crumbs">@foreach ($crumbs as $label => $href)<a href="{{ $href }}">{{ $label }}</a><i>/</i>@endforeach<span class="text-capitalize">{{ $heading }}</span></div>
    @endif
    <h1>{{ $heading }}</h1>
    <p>{{ $msg }}<br>നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക</p>
    <div class="ca-share">
        <span>Share this</span>
        <a href="https://wa.me/?text={{ urlencode($msg . ' ' . url()->current()) }}" target="_blank" rel="noopener" class="text-success" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i></a>
        <a href="https://www.facebook.com/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="text-primary" aria-label="Facebook"><i class="fa fa-facebook-square"></i></a>
        <a href="https://twitter.com/intent/tweet?text={{ urlencode($msg) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="text-info" aria-label="Twitter"><i class="fa fa-twitter-square"></i></a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($msg) }}&source={{ urlencode(url('/')) }}" target="_blank" rel="noopener" class="text-primary" aria-label="LinkedIn"><i class="fa fa-linkedin-square"></i></a>
    </div>
</div>
