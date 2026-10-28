{{-- A Google AdSense slot. Banner / article ad:  @include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'articles'])
     Inside a list:  @include('partials.ad-slot', ['type' => 'web_infeed', 'page' => 'articles', 'pos' => $loop->iteration, 'pageNo' => $items->currentPage()])
     Nothing is drawn unless admin/ads has it switched on for this page. --}}
@php $adUnit = isset($pos) ? \App\Support\Ads::inList($type, $page, $pos, $pageNo ?? 1) : \App\Support\Ads::web($type, $page); $adClient = \App\Models\SiteSetting::get('adsense_client'); @endphp
@if ($adUnit && $adClient && $adUnit->slot)
    <div class="pv-ad" style="grid-column:1/-1;flex:0 0 100%;width:100%;max-width:100%;margin:12px 0;text-align:center;overflow:hidden;min-height:110px">
        <div style="font-size:.65rem;color:#9aa3b2;text-transform:uppercase;letter-spacing:.06em">Advertisement</div>
        <ins class="adsbygoogle" style="display:block" data-ad-client="{{ $adClient }}" data-ad-slot="{{ $adUnit->slot }}" data-ad-format="auto" data-full-width-responsive="true"></ins>
    </div>
@endif
