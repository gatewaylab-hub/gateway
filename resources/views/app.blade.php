@php
    $path = request()->path();
    $isMemberArea = str_starts_with($path, 'm/') || request()->attributes->get('member_area_slug');
    $isCheckout = str_starts_with($path, 'c/') || str_starts_with($path, 'checkout') || str_starts_with($path, 'api-checkout');
    $skipPanelPwa = $isMemberArea || $isCheckout;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.panel-theme-init')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.marketplace-seo')
    @unless($skipPanelPwa)
    @php
        $wlFavicon = config('getfy.favicon_url');
        $wlFavicon = ($wlFavicon !== null && $wlFavicon !== '') ? $wlFavicon : '/images/favicon.png';
        $wlThemeColor = config('getfy.pwa_theme_color');
        $wlThemeColor = ($wlThemeColor !== null && $wlThemeColor !== '') ? $wlThemeColor : config('getfy.theme_primary', '#0ea5e9');
        $wlAppleIcon = config('getfy.pwa_icon_192');
        $wlVersion = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('branding_settings')) {
                $tenantId = auth()->check() ? auth()->user()?->tenant_id : null;
                $ts = null;
                if ($tenantId !== null) {
                    $ts = \App\Models\BrandingSetting::query()->where('tenant_id', $tenantId)->value('updated_at');
                }
                $ts = $ts ?: \App\Models\BrandingSetting::query()->whereNull('tenant_id')->value('updated_at');
                if ($ts) {
                    $wlVersion = (string) \Illuminate\Support\Carbon::parse($ts)->getTimestamp();
                }
            }
        } catch (\Throwable) {
            $wlVersion = null;
        }
        $wlWithVersion = function (string $url) use ($wlVersion): string {
            if (! $wlVersion) return $url;
            if (str_contains($url, 'v=')) return $url;
            return str_contains($url, '?') ? ($url.'&v='.$wlVersion) : ($url.'?v='.$wlVersion);
        };
    @endphp
    <link rel="icon" href="{{ $wlWithVersion($wlFavicon) }}" type="image/png">
    <link rel="manifest" href="{{ $wlWithVersion(url('/manifest.json')) }}">
    <meta name="theme-color" content="{{ $wlThemeColor }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @if($wlAppleIcon !== null && $wlAppleIcon !== '')
    <link rel="apple-touch-icon" href="{{ $wlWithVersion($wlAppleIcon) }}">
    @elseif(is_file(public_path('icons/icon-192x192.png')))
    <link rel="apple-touch-icon" href="{{ $wlWithVersion(url('/icons/icon-192x192.png')) }}">
    @elseif(is_file(public_path('icons/icon-512x512.png')))
    <link rel="apple-touch-icon" href="{{ $wlWithVersion(url('/icons/icon-512x512.png')) }}">
    @endif
    <script>
        (function(){var e=null;window.addEventListener('beforeinstallprompt',function(t){t.preventDefault();e=t;window.__pwaInstallPrompt=e;},{capture:true});Object.defineProperty(window,'__pwaInstallPrompt',{get:function(){return e;},set:function(t){e=t;}});})();
    </script>
    @endunless
    @inertiaHead
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $gaId = '';
        $gtmId = '';
        try {
            $gaId = \App\Support\MarketplaceSeoSettings::googleAnalyticsId();
            $gtmId = \App\Support\MarketplaceSeoSettings::googleTagManagerId();
        } catch (\Throwable) {
            $gaId = '';
            $gtmId = '';
        }
        $gaId = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $gaId) ?? '';
        $gtmId = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $gtmId) ?? '';
    @endphp
    @if($gtmId !== '')
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @endif
    @if($gaId !== '')
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif
</head>
<body class="antialiased">
    @if($gtmId !== '')
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    @php
        $page = $page ?? [];
        $page['props'] = array_merge(
            [
                'auth' => ['user' => null],
                'flash' => ['success' => null, 'error' => null],
                'platform' => null,
            ],
            $page['props'] ?? []
        );
    @endphp
    <div id="app" data-page="{{ json_encode($page) }}"></div>
</body>
</html>
