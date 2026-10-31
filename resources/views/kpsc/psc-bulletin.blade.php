@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Bulletin-ALL")
@section('styles')
<style>
    .bl-page { background: #f4f7f1 url(https://www.pachavellam.com/assets/images/bg-pacha-area.jpg) center / cover fixed no-repeat; min-height: 100vh; padding: 18px 0 40px; }
    .bl-hero { background: linear-gradient(135deg, #3f7d1d, #77ab09); color: #fff; border-radius: 16px; padding: 22px 20px; box-shadow: 0 6px 18px rgba(63, 125, 29, .25); }
    .bl-hero h1 { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
    .bl-hero p { margin: 0; font-size: 14px; line-height: 1.8; opacity: .95; color: #fff; }
    .bl-share { display: flex; align-items: center; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
    .bl-share span { font-size: 13px; opacity: .9; }
    .bl-share a { width: 36px; height: 36px; border-radius: 50%; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; text-decoration: none; }
    .bl-grid { display: flex; flex-wrap: wrap; margin: 18px -8px 0; }
    .bl-col { width: 100%; padding: 8px; }
    @media (min-width: 768px) { .bl-col { width: 50%; } }   /* two cards a row, like the OMR exam list */
    .sr-card { display: block; height: 100%; background: var(--pv-surface, #fff); border: 1px solid var(--pv-border, #e5e7eb); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, .06); color: var(--pv-text, #16202e); }
    .sr-card .bd { padding: 12px; }
    .sr-chip { font-size: .66rem; font-weight: 800; border-radius: 10px; padding: 2px 9px; background: #eef0f3; color: #4b5563; } .sr-chip.fr { background: #e7f6ef; color: #0a6b46; }
    .sr-go { display: inline-block; border-radius: 20px; padding: 5px 14px; font-size: .78rem; font-weight: 800; background: var(--pv-green, #0a8f5b); color: var(--pv-on-brand, #fff) !important; text-decoration: none !important; }
    .sr-go.off { background: var(--pv-surface, #fff); border: 1px solid var(--pv-border, #e5e7eb); color: var(--pv-muted, #667) !important; }
    .bl-ttl { font-weight: 700; line-height: 1.3; min-height: 2.6em; }
    .bl-file { font-size: .76rem; color: var(--pv-muted, #667); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .bl-more { text-align: center; margin-top: 18px; }
    .bl-more .btn { border-radius: 24px; padding: 8px 28px; }
    .bl-state { text-align: center; color: #6c757d; font-size: 14px; padding: 18px 0; }
    .bl-skel .sr-card { pointer-events: none; }
    .bl-skel .bl-line { height: 12px; border-radius: 6px; margin-bottom: 9px; background: linear-gradient(90deg, #eceff1 25%, #f7f9fa 50%, #eceff1 75%); background-size: 200% 100%; animation: bl-shimmer 1.2s linear infinite; }
    @keyframes bl-shimmer { to { background-position: -200% 0; } }
    .bl-spin { width: 28px; height: 28px; border: 3px solid #cfe3a8; border-top-color: #77ab09; border-radius: 50%; display: inline-block; animation: bl-rot .8s linear infinite; }
    @keyframes bl-rot { to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')
@php $msg = 'മത്സര പരീക്ഷകളിലെ സുപ്രധാന ഭാഗമാണ് പിഎസ്സി ബുള്ളറ്റിൻ.. ഓരോ മാസത്തേയും ബുള്ളറ്റിൻ ഫ്രീ ആയി ഇവിടെ ലഭിക്കും.'; @endphp
<div class="page-content-wrapper bl-page">
    <div class="container">
        <div class="bl-hero">
            <h1>PSC Bulletin</h1>
            <p>{{ $msg }}<br>നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക</p>
            <div class="bl-share">
                <span>Share this</span>
                <a href="https://wa.me/?text={{ urlencode($msg . ' ' . url()->current()) }}" target="_blank" rel="noopener" class="text-success" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i></a>
                <a href="https://www.facebook.com/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="text-primary" aria-label="Facebook"><i class="fa fa-facebook-square"></i></a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($msg) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="text-info" aria-label="Twitter"><i class="fa fa-twitter-square"></i></a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($msg) }}&source={{ urlencode(url('/')) }}" target="_blank" rel="noopener" class="text-primary" aria-label="LinkedIn"><i class="fa fa-linkedin-square"></i></a>
            </div>
        </div>

        <div id="results" class="bl-grid"></div>
        <div class="bl-state ajax-loading" style="display:none"></div>
        <div id="bl-sentinel" style="height:1px"></div>
        <div class="bl-more"><a class="btn btn-primary load_more" href="#" style="display:none">Load more</a></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var SITEURL = "{{ url('/') }}";
    var page = 1;
    var loading = false;
    var done = false;
    var skeleton = '<div class="bl-col bl-skel"><div class="sr-card"><div class="bd"><div class="bl-line" style="width:35%"></div><div class="bl-line" style="width:75%"></div><div class="bl-line" style="width:50%"></div><div class="bl-line" style="width:40%;height:26px;border-radius:20px;margin:0"></div></div></div></div>';
    load_more(page);

    $('body').on('click', '.load_more', function (e) {
        e.preventDefault();
        next();
    });

    function next() { if (!loading && !done) { load_more(++page); } }

    // load the next page when the end of the list scrolls into view (the button is the fallback)
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) { if (en[0].isIntersecting && page > 0 && $('#results').children().length) { next(); } }, { rootMargin: '400px' })
            .observe(document.getElementById('bl-sentinel'));
    }

    $('body').on('click', '.dwn_c', function () {
        $.ajax({ url: "{{ url('kpsc/psc-bulletin-downloading') }}", data: { dwn_id: $(this).data('id') }, cache: false });
    });

    function load_more(page) {
        loading = true;
        $('.ajax-loading').hide();
        $('.load_more').hide();
        $('#results').append($(skeleton + skeleton + skeleton).addClass('bl-ph'));   // shimmer cards while the page loads
        $.ajax({ url: SITEURL + "/kpsc/psc-bulletin?page=" + page, type: "get", dataType: "html" })
            .done(function (data) {
                var $d = $('<div>').html(data);
                $d.find('script').remove();   // ad scripts are started below, once the ads are in the page
                var last = $d.find('.bl-end').remove().length > 0;   // the partial marks the last page
                var empty = $.trim($d.html()).length === 0;
                $('#results .bl-ph').remove();
                $('#results').append($d.children());
                $('#results ins.adsbygoogle:not([data-adsbygoogle-status])').each(function () {
                    try { (window.adsbygoogle = window.adsbygoogle || []).push({}); } catch (e) {}
                });
                if (last || empty) {
                    done = true;
                    $('.ajax-loading').text(empty && page === 1 ? 'No bulletins yet.' : "You've reached the end.").show();
                } else {
                    $('.load_more').show();
                }
            })
            .fail(function () {
                $('#results .bl-ph').remove();
                $('.ajax-loading').text('Could not load bulletins. Tap Load more to try again.').show();
                $('.load_more').show();
                page--;
            })
            .always(function () { loading = false; });
    }
</script>
@endsection
