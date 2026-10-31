{{-- Loads Google AdSense once, when the website ads are on and a publisher id is set. Slots are filled as they appear (also the ones added by "Load more"). --}}
@php $adClient = \App\Models\SiteSetting::get('adsense_client'); @endphp
@if ($adClient && \App\Support\Ads::platformOn('web') && !\App\Support\Ads::suppressed())   {{-- no script = no auto ads either --}}
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adClient }}" crossorigin="anonymous"></script>
    <script>
        window.pvAds = function () { [].forEach.call(document.querySelectorAll('ins.adsbygoogle:not([data-pv-done])'), function (el) { el.setAttribute('data-pv-done', '1'); try { (window.adsbygoogle = window.adsbygoogle || []).push({}); } catch (e) {} }); };
        document.addEventListener('DOMContentLoaded', window.pvAds);
    </script>
@endif
