@php
    $pageData = is_array($page ?? null) ? $page : [];
    $seo = is_array($pageData['props']['seo'] ?? null) ? $pageData['props']['seo'] : null;
@endphp
@if(is_array($seo) && ! empty($seo['title']))
    <title>{{ $seo['title'] }}</title>
    @if(! empty($seo['description']))
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    @if(! empty($seo['robots']))
        <meta name="robots" content="{{ $seo['robots'] }}">
    @endif
    @if(! empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif

    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:site_name" content="{{ $seo['og_site_name'] ?? config('getfy.app_name', 'Gamkon') }}">
    <meta property="og:locale" content="{{ $seo['og_locale'] ?? 'pt_BR' }}">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] }}">
    @if(! empty($seo['og_description'] ?? $seo['description'] ?? null))
        <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] }}">
    @endif
    @if(! empty($seo['og_url'] ?? $seo['canonical'] ?? null))
        <meta property="og:url" content="{{ $seo['og_url'] ?? $seo['canonical'] }}">
    @endif
    @if(! empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif

    <meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seo['twitter_title'] ?? ($seo['og_title'] ?? $seo['title']) }}">
    @if(! empty($seo['twitter_description'] ?? $seo['description'] ?? null))
        <meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['description'] }}">
    @endif
    @if(! empty($seo['twitter_image'] ?? $seo['og_image'] ?? null))
        <meta name="twitter:image" content="{{ $seo['twitter_image'] ?? $seo['og_image'] }}">
    @endif

    @foreach(($seo['json_ld'] ?? []) as $block)
        @if(is_array($block))
            <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        @endif
    @endforeach
@else
    <title>{{ config('getfy.app_name', config('app.name', 'Stacker')) }}</title>
@endif
