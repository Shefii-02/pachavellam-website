@php $seo = \App\Support\Seo::current(trim($__env->yieldContent('title'))); @endphp
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}" />
    <meta name="keywords" content="{{ $seo['keywords'] }}" />
    <meta name="robots" content="{{ $seo['robots'] }}" />
    <link rel="canonical" href="{{ $seo['canonical'] }}" />
    <meta property="og:site_name" content="Pachavellam Education" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $seo['title'] }}" />
    <meta property="og:description" content="{{ $seo['description'] }}" />
    <meta property="og:url" content="{{ $seo['canonical'] }}" />
    <meta property="og:image" content="{{ $seo['image'] }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seo['title'] }}" />
    <meta name="twitter:description" content="{{ $seo['description'] }}" />
    <meta name="twitter:image" content="{{ $seo['image'] }}" />
