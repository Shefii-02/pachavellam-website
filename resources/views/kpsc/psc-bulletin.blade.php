@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Bulletin-ALL")
@section('styles')
<style>
    .bl-page { background: #f4f7f1 url(https://www.pachavellam.com/assets/images/bg-pacha-area.jpg) center / cover fixed no-repeat; min-height: 100vh; padding: 18px 0 40px; }
    .bl-hero { background: linear-gradient(135deg, #3f7d1d, #77ab09); color: #fff; border-radius: 16px; padding: 22px 20px; box-shadow: 0 6px 18px rgba(63, 125, 29, .25); }
    .bl-hero h1 { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
    .bl-hero p { margin: 0; font-size: 14px; line-height: 1.8; opacity: .95; }
    .bl-share { display: flex; align-items: center; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
    .bl-share span { font-size: 13px; opacity: .9; }
    .bl-share a { width: 36px; height: 36px; border-radius: 50%; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; text-decoration: none; }
    .bl-grid { display: flex; flex-wrap: wrap; margin: 18px -8px 0; }
    .bl-col { width: 100%; padding: 8px; }
    @media (min-width: 576px) { .bl-col { width: 50%; } }
    @media (min-width: 992px) { .bl-col { width: 33.3333%; } }
    .bl-card { position: relative; display: flex; align-items: center; gap: 14px; height: 100%; background: #fff; border-radius: 14px; padding: 14px 52px 14px 14px; color: #212529; text-decoration: none; box-shadow: 0 2px 10px rgba(0, 0, 0, .06); transition: transform .15s, box-shadow .15s; }
    .bl-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, .12); color: #212529; text-decoration: none; }
    .bl-date { flex: 0 0 62px; height: 68px; border-radius: 12px; background: #eef6df; color: #3f7d1d; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1; }
    .bl-month { font-size: 18px; font-weight: 700; text-transform: uppercase; }
    .bl-year { font-size: 12px; font-weight: 600; opacity: .8; }
    .bl-body { min-width: 0; flex: 1; }
    .bl-title { font-size: 15px; font-weight: 700; margin: 0 0 2px; }
    .bl-file { font-size: 12px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bl-meta { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
    .bl-chip { background: #e8f1ff; color: #1d5fc4; font-size: 11px; font-weight: 600; border-radius: 20px; padding: 2px 10px; }
    .bl-count { font-size: 12px; color: #6c757d; }
    .bl-dl { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border-radius: 50%; background: #77ab09; color: #fff; display: flex; align-items: center; justify-content: center; }
    .bl-dl .fa { margin: 0; }
    .bl-more { text-align: center; margin-top: 18px; }
    .bl-more .btn { border-radius: 24px; padding: 8px 28px; }
    .bl-state { text-align: center; color: #6c757d; font-size: 14px; padding: 18px 0; }
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
        <div class="bl-state ajax-loading"><span class="bl-spin"></span></div>
        <div class="bl-more"><a class="btn btn-primary load_more" href="#" style="display:none">Load more</a></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var SITEURL = "{{ url('/') }}";
    var page = 1;
    var loading = false;
    load_more(page);

    $('body').on('click', '.load_more', function (e) {
        e.preventDefault();
        if (!loading) { load_more(++page); }
    });

    $('body').on('click', '.dwn_c', function () {
        $.ajax({ url: "{{ url('kpsc/psc-bulletin-downloading') }}", data: { dwn_id: $(this).data('id') }, cache: false });
    });

    function load_more(page) {
        loading = true;
        $('.ajax-loading').html('<span class="bl-spin"></span>').show();
        $('.load_more').hide();
        $.ajax({ url: SITEURL + "/kpsc/psc-bulletin?page=" + page, type: "get", dataType: "html" })
            .done(function (data) {
                var $d = $('<div>').html(data);
                var last = $d.find('.bl-end').remove().length > 0;   // the partial marks the last page
                var empty = $.trim($d.html()).length === 0;
                $('#results').append($d.children());
                if (empty && page === 1) {
                    $('.ajax-loading').text('No bulletins yet.');
                } else if (last || empty) {
                    $('.ajax-loading').hide();
                } else {
                    $('.ajax-loading').hide();
                    $('.load_more').show();
                }
            })
            .fail(function () { $('.ajax-loading').text('Could not load bulletins. Please try again.'); $('.load_more').show(); })
            .always(function () { loading = false; });
    }
</script>
@endsection
