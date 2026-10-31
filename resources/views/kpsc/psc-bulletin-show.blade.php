@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title)
@section('styles')
    <style>
        .xt-wrap { max-width: 980px; margin: 0 auto; padding: 14px 0 24px; }
        .xt-tabs { display:flex; gap:6px; border-bottom:2px solid var(--pv-border,#e5e7eb); margin:10px 0 12px; overflow-x:auto; }
        .xt-tab { border:0; background:transparent; padding:9px 18px; font-weight:800; font-size:.9rem; color:#0a8f5b; border-bottom:3px solid #0a8f5b; margin-bottom:-2px; white-space:nowrap; }
        .xt-note { font-size:.8rem; color:var(--pv-muted,#667); margin:6px 0 0; }
        .xt-info { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-bottom:8px; }
        .xt-chip { background:var(--pv-chip,#eef0f3); border-radius:18px; padding:4px 12px; font-size:.78rem; font-weight:700; }
        .xt-about { margin-top:22px; line-height:1.6; font-size:.92rem; } .xt-about h5 { font-weight:800; margin:14px 0 6px; }
        .xt-more { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:8px; }
        .xt-more a { background:var(--pv-surface,#fff); border:1px solid var(--pv-border,#e5e7eb); border-radius:12px; padding:10px 12px; color:inherit; text-decoration:none; font-weight:700; font-size:.85rem; }
        .xt-more a small { display:block; font-weight:600; color:var(--pv-muted,#667); }
    </style>
@endsection
@section('content')
    <div class="page-content-wrapper"><div class="container"><div class="xt-wrap">
        <h4>Bulletin : <span class="text-info">{{ date('F Y', strtotime($bulletin->month_year)) }}</span></h4>

        <div class="xt-tabs" role="tablist">
            <button class="xt-tab" type="button">PSC Bulletin</button>
        </div>

        <div class="xt-info">
            <span class="xt-chip">Issue {{ $bulletin->issue }}</span>
            <span class="xt-chip"><i class="fa fa-download"></i> {{ number_format((int) $bulletin->download) }}</span>
        </div>

        @include('kpsc.partials.pdf-viewer', ['pdfId' => 'bl', 'pdfUrl' => $fileUrl])
        <p class="xt-note">Pinch or use + / − to zoom. Use <b>Download</b> to keep a copy.</p>

        @if ($others->count())
            <div class="xt-about">
                <h5>Other bulletins</h5>
                <div class="xt-more">
                    @foreach ($others as $o)
                        <a href="{{ url('kpsc/psc-bulletin/' . $o->id) }}">{{ date('F Y', strtotime($o->month_year)) }}<small>Issue {{ $o->issue }}</small></a>
                    @endforeach
                </div>
                <p class="mt-3 mb-0"><a href="{{ url('kpsc/psc-bulletin') }}">All bulletins &rarr;</a></p>
            </div>
        @endif
    </div></div></div>
@endsection
@section('scripts')
    <script>
        if (window.pvStart && window.pvStart.bl) window.pvStart.bl();   // the viewer loads the PDF
        document.addEventListener('click', function (e) {   // count a download from the viewer bar
            var a = e.target.closest('.pv-bar a[download]');
            if (a) { fetch("{{ url('kpsc/psc-bulletin-downloading') }}?dwn_id={{ $bulletin->id }}", { cache: 'no-store' }); }
        });
    </script>
@endsection
