<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    @php
        $siteName = config('app.name', 'Cetsy');
        $siteUrl = config('app.url', url('/'));
        $defaultTitle = $siteName . ' | All-in-one Platform to Showcase Your Handmade Products Globally';
        $metaTitle = trim($__env->yieldContent('title', $defaultTitle));
        $metaDescription = trim($__env->yieldContent('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.'));
        $canonicalUrl = trim($__env->yieldContent('canonical_url', localized_current_url()));
        $metaImage = trim($__env->yieldContent('meta_image', asset('assets/images/cetsylogmain.png')));
        $noindexByDefault = request()->is(
                'admin*',
                'seller*',
                'buyer*',
                'account*',
                'wallet*',
                'orders*',
                'notifications*',
                'profile*',
                'cart*',
                'checkout*',
                'login',
                'register',
                'forgot-password',
                'reset-password*',
                'verify-email*',
                'confirm-password',
                'pay-now*',
                'success-deposit*',
                'disputes*',
                'evidence-requests*'
            )
            || request()->routeIs(
                'admin.*',
                'seller.*',
                'buyer.*',
                'account.*',
                'wallet.*',
                'orders.*',
                'notifications.*',
                'profile.*',
                'cart.*',
                'checkout.*',
                'password.*',
                'verification.*',
                'login',
                'register',
                'products.*',
                'shipping-profiles.*',
                'disputes.*',
                'evidence-requests.*'
            );
        $metaRobots = trim($__env->yieldContent('meta_robots', $noindexByDefault ? 'noindex, nofollow' : 'index, follow'));
        $favicon = favicon_url();
        $organizationLogo = logo_url();
        $isSellerArea = request()->is('seller*') || request()->routeIs('products.*');
        $isTailwindPrimaryArea = request()->is('buyer*')
            || request()->is('account*')
            || request()->is('wallet*')
            || request()->is('orders*')
            || request()->is('notifications*')
            || request()->is('profile*')
            || request()->routeIs('buyer.*')
            || request()->routeIs('account.*')
            || request()->routeIs('orders.*')
            || request()->routeIs('wallet.*')
            || request()->routeIs('notifications.*')
            || request()->routeIs('profile.*');
        $legacyBootstrapCompat = (bool) config('theme.legacy_bootstrap_compat', true)
            && !$isSellerArea
            && !$isTailwindPrimaryArea;
        $headerUser = auth()->user();
        if ($headerUser) {
            try {
                $headerUser->loadMissing('shop');
            } catch (\Throwable $e) {
                // Ignore eager-load errors in the layout.
            }
        }

        $isAdminUser = $headerUser && method_exists($headerUser, 'isAdmin') && $headerUser->isAdmin();
        $isSellerUser = $headerUser && method_exists($headerUser, 'isSeller') && $headerUser->isSeller();
        $headerUserShop = $headerUser?->shop;
        $headerAccountName = trim((string) ($headerUserShop?->localized_name ?: $headerUserShop?->name ?: $headerUser?->name ?: $headerUser?->email ?: 'Account'));
        $headerAccountSubtitle = $headerUser?->email;
        $headerAccountRoleLabel = match ((string) ($headerUser->user_type ?? '')) {
            'admin' => __('Admin'),
            'seller' => __('Seller'),
            'buyer' => __('Buyer'),
            default => __('Account'),
        };
        $headerAccountAvatarUrl = null;
        if (!empty($headerUserShop?->logo_url)) {
            $headerAccountAvatarUrl = $headerUserShop->logo_url;
        } elseif (!empty($headerUserShop?->logo)) {
            $headerAccountAvatarUrl = asset('storage/' . ltrim((string) $headerUserShop->logo, '/'));
        } elseif (!empty($headerUser?->photo)) {
            $headerAccountAvatarUrl = asset('storage/' . ltrim((string) $headerUser->photo, '/'));
        }
        $headerAccountInitial = \Illuminate\Support\Str::upper(
            \Illuminate\Support\Str::substr($headerAccountName !== '' ? $headerAccountName : 'A', 0, 1)
        );
        $headerCurrentLocale = current_locale();
        $headerLocaleOptions = supported_locales();
        $headerLocaleLabel = locale_label($headerCurrentLocale);
        $headerLocaleRedirect = localized_current_url();
        $headerLocaleRedirects = collect(array_keys($headerLocaleOptions))
            ->mapWithKeys(fn (string $localeCode): array => [$localeCode => localized_current_url($localeCode)])
            ->all();
        $alternateLocaleUrls = localized_alternate_urls();
        $headerDashboardRoute = null;
        $headerDashboardLabel = __('Dashboard');
        $headerDashboardDescription = __('Open your dashboard.');
        if ($headerUser) {
            if ($isAdminUser && \Illuminate\Support\Facades\Route::has('admin.dashboard')) {
                $headerDashboardRoute = route('admin.dashboard');
                $headerDashboardLabel = __('Admin Dashboard');
                $headerDashboardDescription = __('Review platform activity, users, and operations.');
            } elseif ($isSellerUser && \Illuminate\Support\Facades\Route::has('seller.dashboard')) {
                $headerDashboardRoute = route('seller.dashboard');
                $headerDashboardLabel = __('Seller Dashboard');
                $headerDashboardDescription = __('Manage listings, orders, and shop activity.');
            } elseif (\Illuminate\Support\Facades\Route::has('buyer.dashboard')) {
                $headerDashboardRoute = route('buyer.dashboard');
                $headerDashboardLabel = __('Buyer Dashboard');
                $headerDashboardDescription = __('Track orders, messages, and account activity.');
            } elseif (\Illuminate\Support\Facades\Route::has('dashboard')) {
                $headerDashboardRoute = route('dashboard');
            }
        }
        $headerSwitchAccounts = collect();

        $topNavCategories = collect();
        try {
            $topNavCategories = \App\Models\Category::whereNull('parent_id')
                ->with([
                    'children' => function ($query) {
                        $query->orderBy('name')
                            ->select(['id', 'parent_id', 'name', 'slug', 'image'])
                            ->with([
                                'children' => function ($childQuery) {
                                    $childQuery->orderBy('name')
                                        ->select(['id', 'parent_id', 'name', 'slug', 'image']);
                                }
                            ]);
                    }
                ])
                ->orderBy('name')
                ->take(10)
                ->get(['id', 'name', 'slug', 'image']);
        } catch (\Throwable $e) {
            $topNavCategories = collect();
        }

        $settings = \App\Models\Setting::first();
        $hideMarketplaceCategories = auth()->check() && $isSellerArea;

        $headerUnreadNotifications = 0;
        $headerRecentNotifications = collect();
        if ($headerUser && \Illuminate\Support\Facades\Route::has('notifications.index')) {
            try {
                $notificationQuery = \App\Models\Activity::query();
                if (method_exists($headerUser, 'isAdmin') && $headerUser->isAdmin()) {
                    $notificationQuery->where(function ($query) use ($headerUser) {
                        $query->where('user_id', $headerUser->id)->orWhereNull('user_id');
                    });
                } else {
                    $notificationQuery->where('user_id', $headerUser->id);
                }

                $headerUnreadNotifications = (clone $notificationQuery)
                    ->where('is_read', false)
                    ->count();
                $headerRecentNotifications = (clone $notificationQuery)
                    ->orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                $headerUnreadNotifications = 0;
                $headerRecentNotifications = collect();
            }
        }

        $accountSwitchSession = request()->hasSession() ? request()->session() : null;

        if ($headerUser && $accountSwitchSession) {
            try {
                $switchIds = collect(\App\Support\RecentAccountSwitcher::idsForRequest(request()));

                if (! $switchIds->contains((int) $headerUser->id)) {
                    $switchIds->prepend((int) $headerUser->id);
                }

                if ($switchIds->isNotEmpty()) {
                    $headerSwitchAccounts = \App\Models\User::query()
                        ->with('shop:id,user_id,name,logo')
                        ->whereIn('id', $switchIds->all())
                        ->get()
                        ->sortBy(fn ($account) => $switchIds->search((int) $account->id))
                        ->values();

                }
            } catch (\Throwable $e) {
                $headerSwitchAccounts = collect();
            }
        }

        if ($headerSwitchAccounts->isEmpty() && isset($switchAccounts) && collect($switchAccounts)->isNotEmpty()) {
            $headerSwitchAccounts = collect($switchAccounts)->values();
        }

        $accountSwitchModalAccounts = collect($switchAccounts ?? $headerSwitchAccounts ?? [])
            ->filter()
            ->values();

        if ($accountSwitchModalAccounts->isEmpty()) {
            $accountSwitchModalAccounts = $headerSwitchAccounts;
        }
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="{{ $metaRobots }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:locale" content="{{ locale_og_code() }}">
    @auth
        @if (\Illuminate\Support\Facades\Route::has('notifications.pulse'))
            <meta name="cetsy-notifications-pulse-url" content="{{ route('notifications.pulse') }}">
        @endif
        @if (\Illuminate\Support\Facades\Route::has('push-subscriptions.store'))
            <meta name="cetsy-push-subscribe-url" content="{{ route('push-subscriptions.store') }}">
        @endif
        @if (\Illuminate\Support\Facades\Route::has('push-subscriptions.destroy'))
            <meta name="cetsy-push-unsubscribe-url" content="{{ route('push-subscriptions.destroy') }}">
        @endif
        <meta name="cetsy-push-public-key" content="{{ (string) config('webpush.vapid.public_key') }}">
        <meta name="cetsy-push-enabled" content="{{ config('webpush.enabled') ? '1' : '0' }}">
    @endauth

    <title>{{ $metaTitle }}</title>
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if (! empty($alternateLocaleUrls))
        @foreach ($alternateLocaleUrls as $alternateLocale => $alternateUrl)
            <link rel="alternate" hreflang="{{ locale_html_code($alternateLocale) }}" href="{{ $alternateUrl }}">
            @if ($alternateLocale !== current_locale())
                <meta property="og:locale:alternate" content="{{ locale_og_code($alternateLocale) }}">
            @endif
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $alternateLocaleUrls[default_locale()] ?? reset($alternateLocaleUrls) }}">
    @endif

    @section('social-meta')
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:site_name" content="{{ $siteName }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
    @show

    <link rel="apple-touch-icon" sizes="180x180" href="{{ $favicon }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $favicon }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $favicon }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="manifest" href="{{ pwa_manifest_url() }}">
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @php
        $viteFallbackCss = null;
        try {
            $isViteHot = app(\Illuminate\Foundation\Vite::class)->isRunningHot();
            $isLocalEnv = app()->environment(['local', 'development']);
            if ($isViteHot && !$isLocalEnv) {
                $manifestPath = public_path('build/manifest.json');
                if (is_file($manifestPath)) {
                    $manifest = json_decode((string) file_get_contents($manifestPath), true) ?: [];
                    $viteFallbackCss = $manifest['resources/css/app.css']['file'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            $viteFallbackCss = null;
        }
    @endphp

    <style id="renderGuardStyle">
        html.css-pending body {
            opacity: 0;
        }
    </style>
    <script>
        (function () {
            var root = document.documentElement;
            root.classList.add('css-pending');

            var released = false;
            var maxWaitMs = 2200;
            var pollEveryMs = 60;

            function releaseRenderGuard() {
                if (released) return;
                released = true;
                root.classList.remove('css-pending');
                var guardStyle = document.getElementById('renderGuardStyle');
                if (guardStyle) guardStyle.remove();
            }

            function isAppCssApplied() {
                if (!document.body) return false;
                var probe = document.createElement('div');
                probe.className = 'hidden';
                probe.style.position = 'absolute';
                probe.style.pointerEvents = 'none';
                document.body.appendChild(probe);
                var applied = window.getComputedStyle(probe).display === 'none';
                probe.remove();
                return applied;
            }

            window.__releaseRenderGuard = releaseRenderGuard;

            document.addEventListener('DOMContentLoaded', function () {
                var startedAt = Date.now();
                var timer = window.setInterval(function () {
                    if (isAppCssApplied() || (Date.now() - startedAt) >= maxWaitMs) {
                        window.clearInterval(timer);
                        releaseRenderGuard();
                    }
                }, pollEveryMs);
            });

            window.addEventListener('load', releaseRenderGuard, { once: true });
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (!empty($viteFallbackCss))
        <link rel="stylesheet" href="{{ asset('build/' . ltrim($viteFallbackCss, '/')) }}">
    @endif

    @if ($legacyBootstrapCompat)
        <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">
    @endif

    @if (file_exists(public_path('vendors/fontawesome/css/all.min.css')))
        <link rel="stylesheet" href="{{ asset('vendors/fontawesome/css/all.min.css') }}">
    @elseif (file_exists(public_path('vendors/fontawesome/all.min.js')))
        <script src="{{ asset('vendors/fontawesome/all.min.js') }}" defer></script>
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        [x-cloak] { display: none !important; }
        .tw-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.55);
            padding: 1rem;
        }
        .tw-modal.is-open { display: flex; }
        .tw-modal-dialog { width: 100%; max-width: 32rem; }
        .tw-modal-dialog.tw-modal-lg { max-width: 56rem; }
        .tw-modal-content {
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.25);
        }
        .tw-modal-header, .tw-modal-footer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
        }
        .tw-modal-header { justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
        .tw-modal-footer { justify-content: flex-end; border-top: 1px solid #e2e8f0; }
        .tw-modal-body { padding: 1rem; }
        .tw-modal-title { margin: 0; font-size: 1rem; font-weight: 600; color: #0f172a; }
        .account-switch-sheet-dialog { max-width: 30rem; }
        .account-switch-sheet-content { overflow: hidden; }
        @media (max-width: 640px) {
            .account-switch-sheet-dialog {
                display: flex;
                min-height: 100%;
                max-width: 100%;
                align-items: flex-end;
            }
            .account-switch-sheet-content {
                width: 100%;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                max-height: min(84vh, 46rem);
            }
        }

        .tw-dropdown-menu {
            position: absolute;
            z-index: 50;
            margin-top: 0.5rem;
            display: none;
            min-width: 15rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.35rem;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.15);
        }
        .tw-dropdown-menu.show { display: block; }
        .tw-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            border-radius: 0.5rem;
            padding: 0.45rem 0.55rem;
            font-size: 0.85rem;
            color: #1e293b;
            text-align: left;
            text-decoration: none;
        }
        .tw-dropdown-item:hover { background: #f1f5f9; }
        .tw-dropdown-divider { margin: 0.35rem 0; border-color: #e2e8f0; }

        .form-label { display: block; margin-bottom: 0.35rem; font-size: 0.875rem; font-weight: 600; color: #334155; }
        .form-text { display: block; margin-top: 0.35rem; font-size: 0.75rem; color: #64748b; }
        .invalid-feedback { display: block; margin-top: 0.35rem; font-size: 0.75rem; color: #b91c1c; }
        .form-check { display: flex; align-items: center; gap: 0.5rem; }
        .form-check-input { width: 1rem; height: 1rem; border: 1px solid #94a3b8; border-radius: 0.25rem; }
        .form-check-label { font-size: 0.875rem; color: #334155; }
        .text-uppercase { text-transform: uppercase; }
        .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .fw-medium { font-weight: 500; }
        .position-relative { position: relative; }
        .position-absolute { position: absolute; }
        .top-0 { top: 0; }
        .bottom-0 { bottom: 0; }
        .start-0 { left: 0; }
        .end-0 { right: 0; }
        .rounded-3 { border-radius: 0.75rem; }
        .bg-dark { background: #0f172a; }
        .border-0 { border-width: 0 !important; }
        .table-hover tbody tr:hover { background: #f8fafc; }
        .table-striped tbody tr:nth-child(odd) { background: #f8fafc; }
        .table-sm > :not(caption) > * > * { padding-top: 0.4rem; padding-bottom: 0.4rem; }

        .top-category-carousel {
            position: relative;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        .top-category-scroll {
            overflow: visible;
            overflow-x: clip;
            overflow-y: visible;
            position: relative;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .top-category-carousel:has(.top-category-item--open) .top-category-scroll {
            overflow: visible;
        }
        .top-category-scroll::-webkit-scrollbar { display: none; }
        .top-category-scroll [data-top-category-track] {
            display: flex;
            min-width: 100%;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.45s ease;
            will-change: transform;
            transform: translate3d(0, 0, 0);
        }
        .top-category-item {
            flex: 0 0 calc((100% - 1.5rem) / 4);
            min-width: 0;
        }
        .top-category-item > a {
            width: 100%;
            max-width: 100%;
        }
        .top-category-item > a > span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .top-category-menu,
        .top-category-submenu {
            width: min(26rem, calc(100vw - 2rem));
            max-height: min(72vh, 36rem);
            overflow-y: auto;
            overflow-x: visible;
            z-index: 80;
        }
        .top-category-menu a,
        .top-category-submenu a {
            white-space: normal;
            overflow-wrap: anywhere;
            line-height: 1.35;
        }
        .top-category-menu-label {
            min-width: 0;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }
        .top-category-submenu {
            left: 100%;
            right: auto;
            margin-left: 0.5rem;
        }
        .top-category-item:nth-child(4n) .top-category-submenu {
            left: auto;
            right: 100%;
            margin-left: 0;
            margin-right: 0.5rem;
        }
        .top-category-nav {
            position: absolute;
            top: 50%;
            z-index: 30;
            display: inline-flex;
            height: 2.15rem;
            width: 2.15rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.14);
            transform: translateY(-50%);
            transition: all 0.2s ease;
        }
        .top-category-nav:hover {
            border-color: #a7f3d0;
            color: #047857;
        }
        .top-category-nav:focus-visible {
            outline: 2px solid #10b981;
            outline-offset: 2px;
        }
        .top-category-nav-prev { left: 0; }
        .top-category-nav-next { right: 0; }
        .top-category-nav.is-disabled {
            opacity: 0;
            pointer-events: none;
        }

        .page-transition-loader {
            position: fixed;
            inset: 0;
            z-index: 120;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.16s ease, visibility 0s linear 0.16s;
        }
        .page-transition-loader.is-active {
            opacity: 1;
            visibility: visible;
            transition-delay: 0s;
        }
        .page-transition-loader__bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            overflow: hidden;
            background: rgba(16, 185, 129, 0.16);
        }
        .page-transition-loader__bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: min(42vw, 14rem);
            background: linear-gradient(90deg, #10b981 0%, #34d399 55%, #6ee7b7 100%);
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.45);
            animation: page-loader-slide 1.05s ease-in-out infinite;
        }
        .page-transition-loader__pulse {
            position: absolute;
            top: 0.7rem;
            right: 1rem;
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 9999px;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.35);
            animation: page-loader-pulse 1.3s ease-out infinite;
        }
        body.is-transitioning, body.is-transitioning a {
            cursor: progress;
        }
        @keyframes page-loader-slide {
            0% { transform: translateX(-120%); }
            55% { transform: translateX(150%); }
            100% { transform: translateX(240%); }
        }
        @keyframes page-loader-pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.35); }
            80% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .page-transition-loader__bar::after,
            .page-transition-loader__pulse {
                animation: none;
            }
            .page-transition-loader__bar::after {
                width: 100%;
                transform: translateX(0);
                opacity: 0.75;
            }
        }
        .cetsy-preview-watermark {
            position: relative;
            isolation: isolate;
            overflow: hidden;
        }
        .cetsy-preview-watermark::before {
            content: "";
            position: absolute;
            inset: 18% -32%;
            z-index: 4;
            border-top: 1px solid rgba(255, 255, 255, 0.22);
            border-bottom: 1px solid rgba(255, 255, 255, 0.22);
            background:
                linear-gradient(
                    90deg,
                    rgba(15, 23, 42, 0) 0%,
                    rgba(15, 23, 42, 0.16) 16%,
                    rgba(15, 23, 42, 0.26) 50%,
                    rgba(15, 23, 42, 0.16) 84%,
                    rgba(15, 23, 42, 0) 100%
                );
            transform: rotate(-14deg);
            transform-origin: center;
            pointer-events: none;
        }
        .cetsy-preview-watermark::after {
            content: attr(data-watermark-label);
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: min(92%, 18rem);
            padding: 0.42rem 0.95rem;
            color: rgba(255, 255, 255, 0.76);
            font-size: clamp(0.62rem, 0.82vw, 0.82rem);
            font-weight: 900;
            letter-spacing: 0.32em;
            line-height: 1;
            text-transform: uppercase;
            white-space: nowrap;
            pointer-events: none;
            text-align: center;
            text-shadow: 0 2px 14px rgba(15, 23, 42, 0.35);
            transform: translate(-50%, -50%) rotate(-14deg);
        }
        .cetsy-preview-watermark--thumb::after {
            width: min(92%, 7rem);
            padding: 0.18rem 0.45rem;
            font-size: 0.44rem;
            letter-spacing: 0.22em;
        }
        .cetsy-preview-watermark--thumb::before {
            inset: 24% -38%;
            border-top-color: rgba(255, 255, 255, 0.18);
            border-bottom-color: rgba(255, 255, 255, 0.18);
        }
        .cetsy-preview-watermark--lightbox::after {
            width: min(86%, 28rem);
            padding: 0.56rem 1.25rem;
            font-size: clamp(0.8rem, 1.1vw, 1rem);
            letter-spacing: 0.38em;
        }
        .cetsy-preview-watermark--lightbox::before {
            inset: 20% -28%;
        }
        .cetsy-preview-watermark img,
        .cetsy-preview-watermark video {
            position: relative;
            z-index: 1;
        }
        .cetsy-preview-watermark img {
            -webkit-user-drag: none;
            -webkit-touch-callout: none;
            pointer-events: none;
            user-select: none;
        }
        .cetsy-preview-watermark {
            -webkit-touch-callout: none;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        .cetsy-preview-watermark video {
            -webkit-user-drag: none;
            -webkit-touch-callout: none;
            pointer-events: none;
            user-select: none;
        }
    </style>

    @yield('styles')
    @stack('styles')

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $organizationLogo ?: $favicon,
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    @stack('structured-data')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div id="pageTransitionLoader" class="page-transition-loader" aria-hidden="true">
        <span class="page-transition-loader__bar"></span>
        <span class="page-transition-loader__pulse"></span>
    </div>

    <div x-data="{ mobileDrawerOpen: false }" x-init="$watch('mobileDrawerOpen', value => document.body.classList.toggle('overflow-hidden', value))" class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center gap-3 px-4 py-3 sm:px-6">
                <button @click="mobileDrawerOpen = true" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" type="button" aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>

                <a href="{{ localized_route('home') }}" class="inline-flex items-center gap-2">
                    <img src="{{ logo_url() }}" alt="{{ $siteName }}" class="h-10 w-auto"
                         onerror='this.onerror=null;this.src=@json(asset("assets/images/cetsylogmain.png"));'>
                </a>

                <form method="GET" action="{{ localized_route('search') }}" class="hidden flex-1 lg:block">
                    <label for="globalSearch" class="sr-only">{{ __('Search products') }}</label>
                    <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
                        <i class="fas fa-search text-slate-400"></i>
                        <input id="globalSearch" name="q" type="search" value="{{ request('q') }}" placeholder="{{ __('Search products, services, shops') }}" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                        <button type="submit" class="rounded-full bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">{{ __('Search') }}</button>
                    </div>
                </form>

                <nav class="ml-2 hidden items-center gap-2 lg:flex">
                    @php
                        $headerCartCount = isset($cartCount)
                            ? (int) $cartCount
                            : (int) collect(session('cart', []))->sum(function ($row) {
                                return is_array($row) ? (int) ($row['quantity'] ?? 0) : 0;
                            });
                    @endphp
                    <a href="{{ localized_route('listings') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">{{ __('Listings') }}</a>
                    <a href="{{ localized_route('shops.index') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">{{ __('Shops') }}</a>
                    <a href="{{ localized_route('become-seller') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">{{ __('Sell') }}</a>
                    <a href="{{ url('/cart') }}" class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-sm font-semibold {{ request()->is('cart*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fas fa-shopping-cart text-sm"></i>
                        <span>{{ __('Cart') }}</span>
                        @if ($headerCartCount > 0)
                            <span class="inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[10px] font-semibold leading-none text-white">
                                {{ $headerCartCount > 99 ? '99+' : $headerCartCount }}
                            </span>
                        @endif
                    </a>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    <div class="relative">
                        <button type="button" data-ui-toggle="dropdown" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-3 text-slate-700 hover:border-slate-300 hover:bg-slate-50" aria-label="{{ __('Change language') }}" aria-expanded="false">
                            <i class="fas fa-globe text-sm"></i>
                            <span class="hidden text-sm font-semibold sm:inline">{{ $headerLocaleLabel }}</span>
                        </button>

                        <div class="tw-dropdown-menu right-0 w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900">{{ __('Language') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Choose your preferred interface language.') }}</p>
                            </div>
                            <div class="p-2">
                                @foreach ($headerLocaleOptions as $localeCode => $localeMeta)
                                    <a href="{{ route('locale.set', ['locale' => $localeCode, 'redirect' => $headerLocaleRedirects[$localeCode] ?? $headerLocaleRedirect]) }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ $headerCurrentLocale === $localeCode ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span>{{ $localeMeta['native'] ?? strtoupper($localeCode) }}</span>
                                        @if ($headerCurrentLocale === $localeCode)
                                            <i class="fas fa-check text-xs"></i>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @auth
                        @if (\Illuminate\Support\Facades\Route::has('notifications.index'))
                            <div class="relative">
                                <button type="button" data-ui-toggle="dropdown" data-live-notification-bell class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50" aria-label="{{ __('Notifications') }}" aria-expanded="false">
                                    <i class="fas fa-bell text-sm"></i>
                                    @if ($headerUnreadNotifications > 0)
                                        <span data-live-notification-count class="absolute -right-1 -top-1 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[10px] font-semibold leading-none text-white">
                                            {{ $headerUnreadNotifications > 99 ? '99+' : $headerUnreadNotifications }}
                                        </span>
                                    @endif
                                </button>

                                <div class="tw-dropdown-menu right-0 w-[22rem] max-w-[90vw] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-slate-900">{{ __('Notifications') }}</h3>
                                            <p class="text-xs text-slate-500">{{ __('Latest updates from your account') }}</p>
                                        </div>
                                        <span data-live-notification-unread-label class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                            {{ __(':count unread', ['count' => $headerUnreadNotifications]) }}
                                        </span>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto">
                                        @forelse ($headerRecentNotifications as $notification)
                                            @php
                                                $notificationHref = \Illuminate\Support\Facades\Route::has('notifications.open')
                                                    ? route('notifications.open', $notification->id)
                                                    : route('notifications.index');
                                                $notificationTitle = trim((string) ($notification->title ?: $notification->description ?: $notification->message ?: __('Notification')));
                                                $notificationAge = optional($notification->created_at)->diffForHumans();
                                                $notificationAction = __('Open');
                                                try {
                                                    $notificationAction = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user()) ?: __('Open');
                                                } catch (\Throwable $e) {
                                                    $notificationAction = __('Open');
                                                }
                                            @endphp
                                            <div class="border-b border-slate-100 px-4 py-3 last:border-b-0">
                                                <div class="flex items-start gap-3">
                                                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $notification->is_read ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700' }}">
                                                        <i class="fas fa-bell text-xs"></i>
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-sm {{ $notification->is_read ? 'text-slate-700' : 'font-semibold text-slate-900' }}">{{ $notificationTitle }}</p>
                                                        <div class="mt-1 flex items-center justify-between gap-2">
                                                            <p class="text-xs text-slate-500">{{ $notificationAge }}</p>
                                                            @if (!$notification->is_read)
                                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">{{ __('New') }}</span>
                                                            @endif
                                                        </div>
                                                        <a href="{{ $notificationHref }}" class="mt-2 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100" @click="open = false">
                                                            {{ $notificationAction }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-4 py-8 text-center text-sm text-slate-500">
                                                <i class="far fa-bell-slash mb-2 block text-2xl text-slate-300"></i>
                                                {{ __('No notifications yet.') }}
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="border-t border-slate-200 p-3">
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <button type="button" data-ui-toggle="modal" data-ui-target="#liveNotificationPrefsModal" data-notify-settings-trigger class="inline-flex w-full items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto">
                                                {{ __('Alert settings') }}
                                            </button>
                                            <a href="{{ route('notifications.index') }}" class="inline-flex w-full items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                                {{ __('View all notifications') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="relative">
                            <button type="button" data-ui-toggle="dropdown" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-2 text-slate-700 hover:border-slate-300 hover:bg-slate-50 sm:px-3" aria-label="{{ __('Account menu') }}" aria-expanded="false">
                                @if ($headerAccountAvatarUrl)
                                    <span class="inline-flex h-7 w-7 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-white">
                                        <img src="{{ $headerAccountAvatarUrl }}" alt="{{ $headerAccountName }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                        <span class="hidden inline-flex h-full w-full items-center justify-center bg-emerald-100 text-xs font-bold text-emerald-700">{{ $headerAccountInitial }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ $headerAccountInitial }}</span>
                                @endif
                                <span class="hidden max-w-[8rem] truncate text-sm font-semibold sm:inline">{{ $headerAccountName }}</span>
                                <i class="fas fa-chevron-down hidden text-[10px] text-slate-400 sm:inline"></i>
                            </button>

                            <div class="tw-dropdown-menu right-0 w-[22rem] max-w-[92vw] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($headerAccountAvatarUrl)
                                            <span class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                                <img src="{{ $headerAccountAvatarUrl }}" alt="{{ $headerAccountName }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                                <span class="hidden inline-flex h-full w-full items-center justify-center bg-emerald-100 text-sm font-bold text-emerald-700">{{ $headerAccountInitial }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-bold text-emerald-700">{{ $headerAccountInitial }}</span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-900">{{ $headerAccountName }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $headerAccountSubtitle }}</p>
                                        </div>
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                            {{ $headerAccountRoleLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-2 p-4">
                                    @if ($headerDashboardRoute)
                                        <a href="{{ $headerDashboardRoute }}" class="flex w-full items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-3 text-left transition hover:bg-emerald-100">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                                <i class="fas fa-gauge-high"></i>
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $headerDashboardLabel }}</span>
                                                <span class="block truncate text-xs text-slate-500">{{ $headerDashboardDescription }}</span>
                                            </span>
                                            <i class="fas fa-chevron-right text-sm text-slate-400"></i>
                                        </a>
                                    @endif
                                    <button type="button" data-ui-toggle="modal" data-ui-target="#accountSwitchModal" class="flex w-full items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-left transition hover:bg-slate-50">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                            <i class="fas fa-repeat"></i>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-slate-900">{{ __('Switch Account') }}</span>
                                            <span class="block truncate text-xs text-slate-500">
                                                {{ $accountSwitchModalAccounts->count() === 1 ? __('Current account only') : __(':count saved on this device', ['count' => $accountSwitchModalAccounts->count()]) }}
                                            </span>
                                        </span>
                                        <i class="fas fa-chevron-right text-sm text-slate-400"></i>
                                    </button>
                                    <a href="{{ route('account.details') }}#account-switching" class="flex w-full items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-left transition hover:bg-slate-50">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                            <i class="fas fa-gear"></i>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-slate-900">{{ __('Account settings') }}</span>
                                            <span class="block truncate text-xs text-slate-500">{{ __('Manage profile details and add another account.') }}</span>
                                        </span>
                                    </a>
                                    @if (Route::has('logout'))
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                                                {{ __('Log Out') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">{{ __('Login') }}</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Create account') }}</a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="mx-auto hidden w-full max-w-7xl px-4 pb-3 lg:block sm:px-6">
                @if (!$hideMarketplaceCategories && $topNavCategories->isNotEmpty())
                    <div class="top-category-carousel pb-1"
                         data-top-category-carousel
                         data-visible-count="4"
                         data-autoplay-ms="5000">
                        <button type="button" class="top-category-nav top-category-nav-prev" data-top-category-prev aria-label="Previous categories">
                            <i class="fa-solid fa-chevron-left text-[11px]"></i>
                        </button>
                        <div class="top-category-scroll">
                        <div class="flex min-w-full flex-nowrap items-center" data-top-category-track>
                        @foreach ($topNavCategories as $cat)
                            @php
                                $children = collect($cat->children ?? []);
                                $catThumb = null;
                                if (!empty($cat->image)) {
                                    $catImagePath = (string) $cat->image;
                                    $catThumb = \Illuminate\Support\Str::startsWith($catImagePath, ['http://', 'https://', '//'])
                                        ? $catImagePath
                                        : media_url($catImagePath);
                                }
                            @endphp
                            <div class="relative top-category-item" data-top-category-item x-data="{ open: false, pinned: false }" :class="{ 'top-category-item--open': open }" @mouseenter="open = true" @mouseleave="if (!pinned) open = false" @focusin="open = true" @focusout="if (!pinned) open = false" @click.outside="open = false; pinned = false">
                                <a href="{{ localized_route('category.show', $cat->slug) }}" class="inline-flex min-w-0 items-center justify-start gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" @if ($children->isNotEmpty()) @click.prevent="pinned = !pinned; open = pinned" @endif>
                                    @if ($catThumb)
                                        <img src="{{ $catThumb }}" alt="{{ $cat->name }}" class="h-8 w-8 rounded object-cover" onerror="this.onerror=null;this.style.display='none';">
                                    @else
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs text-emerald-700">
                                            <i class="fa-solid fa-folder"></i>
                                        </span>
                                    @endif
                                    <span class="truncate">{{ $cat->name }}</span>
                                    @if ($children->isNotEmpty())
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                                    @endif
                                </a>

                                @if ($children->isNotEmpty())
                                    <div x-show="open" x-cloak x-transition class="top-category-menu absolute left-0 top-full mt-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                        <a href="{{ localized_route('category.show', $cat->slug) }}" class="mb-1 block rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                            All {{ $cat->name }}
                                        </a>

                                        <ul class="space-y-1">
                                            @foreach ($children as $child)
                                                @php $grandChildren = collect($child->children ?? []); @endphp
                                                <li class="relative" x-data="{ openChild: false, pinnedChild: false }" @mouseenter="openChild = true" @mouseleave="if (!pinnedChild) openChild = false" @focusin="openChild = true" @focusout="if (!pinnedChild) openChild = false" @click.outside="openChild = false; pinnedChild = false">
                                                    <a href="{{ localized_route('category.show', $child->slug) }}" class="flex items-center justify-between gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900" @if ($grandChildren->isNotEmpty()) @click.prevent="pinnedChild = !pinnedChild; openChild = pinnedChild" @endif>
                                                        <span class="top-category-menu-label">{{ $child->name }}</span>
                                                        @if ($grandChildren->isNotEmpty())
                                                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                                                        @endif
                                                    </a>

                                                    @if ($grandChildren->isNotEmpty())
                                                        <div x-show="openChild" x-cloak x-transition class="top-category-submenu absolute top-0 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                                            <a href="{{ localized_route('category.show', $child->slug) }}" class="mb-1 block rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                                                All {{ $child->name }}
                                                            </a>
                                                            <ul class="space-y-1">
                                                                @foreach ($grandChildren as $grand)
                                                                    <li>
                                                                        <a href="{{ localized_route('category.show', $grand->slug) }}" class="block rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900">
                                                                            {{ $grand->name }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        </div>
                        </div>
                        <button type="button" class="top-category-nav top-category-nav-next" data-top-category-next aria-label="Next categories">
                            <i class="fa-solid fa-chevron-right text-[11px]"></i>
                        </button>
                    </div>
                @endif
            </div>

        </header>

        <div x-show="mobileDrawerOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[68] bg-slate-900/50 lg:hidden" @click="mobileDrawerOpen = false"></div>

        <aside x-show="mobileDrawerOpen" x-cloak x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-[69] h-full w-[88%] max-w-sm overflow-y-auto border-l border-slate-200 bg-white shadow-2xl lg:hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Menu</h3>
                <button @click="mobileDrawerOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100" type="button" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4 px-4 py-4 pb-8">
                <form method="GET" action="{{ localized_route('search') }}">
                    <label for="mobileDrawerSearch" class="sr-only">Search products</label>
                    <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
                        <i class="fas fa-search text-slate-400"></i>
                        <input id="mobileDrawerSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Search products" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                        <button type="submit" class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Go</button>
                    </div>
                </form>

                @auth
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($headerAccountAvatarUrl)
                                    <span class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                        <img src="{{ $headerAccountAvatarUrl }}" alt="{{ $headerAccountName }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                        <span class="hidden inline-flex h-full w-full items-center justify-center bg-emerald-100 text-sm font-bold text-emerald-700">{{ $headerAccountInitial }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-bold text-emerald-700">{{ $headerAccountInitial }}</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $headerAccountName }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $headerAccountSubtitle }}</p>
                                </div>
                            </div>
                            <button type="button" data-ui-toggle="modal" data-ui-target="#accountSwitchModal" @click="mobileDrawerOpen = false" class="inline-flex shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                Switch Account
                            </button>
                        </div>
                    </div>
                @endauth

                <nav class="grid grid-cols-2 gap-2">
                    <a href="{{ localized_route('listings') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Listings') }}</a>
                    <a href="{{ localized_route('shops.index') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Shops') }}</a>
                    <a href="{{ localized_route('become-seller') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Sell') }}</a>
                    <a href="{{ localized_route('contact') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Support') }}</a>
                </nav>

                @if($isAdminUser)
                    @php
                        $adminDrawerLinks = [
                            ['route' => 'admin.dashboard', 'icon' => 'fas fa-gauge-high', 'label' => __('Dashboard')],
                            ['route' => 'admin.notifications.index', 'icon' => 'fas fa-bell', 'label' => __('Notifications')],
                            ['route' => 'admin.users.index', 'icon' => 'fas fa-users', 'label' => __('Manage Users')],
                            ['route' => 'admin.kyc.index', 'icon' => 'fas fa-id-card', 'label' => __('KYC Management')],
                        ];
                    @endphp
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('Admin Menu') }}</div>
                        <div class="space-y-1.5">
                            @foreach($adminDrawerLinks as $adminItem)
                                @php
                                    $adminRouteName = $adminItem['route'] ?? null;
                                    $adminHref = $adminRouteName && \Illuminate\Support\Facades\Route::has($adminRouteName)
                                        ? route($adminRouteName)
                                        : null;
                                @endphp
                                @if($adminHref)
                                    <a href="{{ $adminHref }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                        <span><i class="{{ $adminItem['icon'] }} mr-2"></i>{{ $adminItem['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(auth()->check() && auth()->user()->isBuyer())
                    @php
                        $buyerUnreadMessages = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                        $buyerPendingOffers = \App\Models\Offer::where('buyer_id', auth()->id())->where('status', 'pending')->count();
                    @endphp
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('Buyer Menu') }}</div>
                        <div class="space-y-1.5">
                            <a href="{{ route('buyer.dashboard') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-gauge mr-2"></i>{{ __('Dashboard') }}</span>
                            </a>
                            <a href="{{ route('account.orders') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-receipt mr-2"></i>{{ __('Orders') }}</span>
                            </a>
                            @if(\Illuminate\Support\Facades\Route::has('buyer.offers'))
                                <a href="{{ route('buyer.offers') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-hand-holding-dollar mr-2"></i>{{ __('Offers') }}</span>
                                    @if($buyerPendingOffers > 0)
                                        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $buyerPendingOffers }}</span>
                                    @endif
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('buyer.messages.index'))
                                <a href="{{ route('buyer.messages.index') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-comments mr-2"></i>{{ __('Messages') }}</span>
                                    @if($buyerUnreadMessages > 0)
                                        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $buyerUnreadMessages }}</span>
                                    @endif
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('buyer.favorites'))
                                <a href="{{ route('buyer.favorites') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-heart mr-2"></i>{{ __('Favorites') }}</span>
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('wishlist'))
                                <a href="{{ localized_route('wishlist') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-bookmark mr-2"></i>{{ __('Wishlist') }}</span>
                                </a>
                            @endif
                            <a href="{{ route('wallet.index') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-wallet mr-2"></i>{{ __('Wallet') }}</span>
                            </a>
                            @if(\Illuminate\Support\Facades\Route::has('notifications.index'))
                                <a href="{{ route('notifications.index') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-bell mr-2"></i>{{ __('Notifications') }}</span>
                                </a>
                            @endif
                            <a href="{{ route('account.payments') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-credit-card mr-2"></i>{{ __('Payments') }}</span>
                            </a>
                            <a href="{{ route('account.details') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-user mr-2"></i>{{ __('Account Settings') }}</span>
                            </a>
                            <a href="{{ route('account.addresses') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-location-dot mr-2"></i>{{ __('Addresses') }}</span>
                            </a>
                        </div>
                    </div>
                @endif

                @if($isSellerUser)
                    @php
                        $sellerShop = auth()->user()?->shop;
                        $sellerShopUrl = $sellerShop
                            ? localized_route('shop.show', $sellerShop->slug ?: $sellerShop->getKey())
                            : route('seller.shop.create');
                        $sellerDrawerLinks = [
                            ['route' => 'seller.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => __('Seller Dashboard')],
                            ['route' => 'products.index', 'icon' => 'fas fa-box-open', 'label' => __('My Listings')],
                            ['route' => 'seller.deals.index', 'icon' => 'fas fa-percent', 'label' => __('Deals')],
                            ['route' => 'seller.orders.index', 'icon' => 'fas fa-shopping-cart', 'label' => __('Shop Orders')],
                            ['route' => 'seller.reviews.index', 'icon' => 'fas fa-star', 'label' => __('Reviews')],
                            ['route' => 'account.orders', 'icon' => 'fas fa-bag-shopping', 'label' => __('My Orders')],
                            ['route' => 'seller.messages.index', 'icon' => 'fas fa-comments', 'label' => __('Messages')],
                            ['route' => 'seller.offers.index', 'icon' => 'fas fa-handshake', 'label' => __('Offers')],
                            ['route' => 'buyer.favorites', 'icon' => 'fas fa-heart', 'label' => __('Favorites')],
                            ['route' => 'seller.favorites.index', 'icon' => 'fas fa-store', 'label' => __('Shop Favorites')],
                            ['route' => 'seller.notifications.index', 'icon' => 'fas fa-bell', 'label' => __('Notifications')],
                            ['route' => 'disputes.index', 'icon' => 'fas fa-exclamation-triangle', 'label' => __('Disputes')],
                            ['href' => $sellerShopUrl, 'icon' => 'fas fa-store', 'label' => __('My Shop')],
                            ['route' => 'seller.analytics.index', 'icon' => 'fas fa-chart-line', 'label' => __('Analytics')],
                            ['route' => 'seller.reports.inventory', 'icon' => 'fas fa-boxes-stacked', 'label' => __('Inventory Report')],
                            ['route' => 'seller.payouts.index', 'icon' => 'fas fa-money-bill-wave', 'label' => __('Payouts')],
                            ['route' => 'seller.subscription', 'icon' => 'fas fa-file-invoice', 'label' => __('Subscription')],
                            ['route' => 'seller.kyc', 'icon' => 'fas fa-id-card', 'label' => __('KYC')],
                            ['route' => 'wallet.index', 'icon' => 'fas fa-wallet', 'label' => __('Wallet')],
                        ];
                    @endphp
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('Seller Menu') }}</div>
                        <div class="space-y-1.5">
                            @foreach($sellerDrawerLinks as $sellerItem)
                                @php
                                    $sellerRouteName = $sellerItem['route'] ?? null;
                                    $sellerHref = $sellerItem['href'] ?? ($sellerRouteName && \Illuminate\Support\Facades\Route::has($sellerRouteName) ? route($sellerRouteName) : null);
                                @endphp
                                @continue(!$sellerHref)
                                <a href="{{ $sellerHref }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="{{ $sellerItem['icon'] }} mr-2"></i>{{ $sellerItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @auth
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('Account') }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ $headerDashboardRoute ?: url('/dashboard') }}" @click="mobileDrawerOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ $headerDashboardLabel }}</a>
                            <a href="{{ url('/cart') }}" @click="mobileDrawerOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Cart') }}</a>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('Account') }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" @click="mobileDrawerOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">{{ __('Login') }}</a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" @click="mobileDrawerOpen = false" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">{{ __('Register') }}</a>
                            @endif
                        </div>
                    </div>
                @endauth

                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ __('Language') }}</div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($headerLocaleOptions as $localeCode => $localeMeta)
                            <a href="{{ route('locale.set', ['locale' => $localeCode, 'redirect' => $headerLocaleRedirects[$localeCode] ?? $headerLocaleRedirect]) }}" @click="mobileDrawerOpen = false" class="rounded-lg border px-3 py-2 text-sm font-semibold {{ $headerCurrentLocale === $localeCode ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-700' }}">
                                {{ $localeMeta['native'] ?? strtoupper($localeCode) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if (!$hideMarketplaceCategories && $topNavCategories->isNotEmpty())
                    <div class="space-y-2">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Categories</div>
                        @foreach ($topNavCategories as $cat)
                            @php
                                $children = collect($cat->children ?? []);
                                $catThumb = null;
                                if (!empty($cat->image)) {
                                    $catImagePath = (string) $cat->image;
                                    $catThumb = \Illuminate\Support\Str::startsWith($catImagePath, ['http://', 'https://', '//'])
                                        ? $catImagePath
                                        : media_url($catImagePath);
                                }
                            @endphp
                            @if ($children->isNotEmpty())
                                <details class="rounded-xl border border-slate-200 bg-slate-50">
                                    <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2 text-sm font-semibold text-slate-700">
                                        <span class="flex items-center gap-2">
                                            @if ($catThumb)
                                                <img src="{{ $catThumb }}" alt="{{ $cat->name }}" class="h-8 w-8 rounded object-cover" onerror="this.onerror=null;this.style.display='none';">
                                            @else
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs text-emerald-700">
                                                    <i class="fa-solid fa-folder"></i>
                                                </span>
                                            @endif
                                            <span>{{ $cat->name }}</span>
                                        </span>
                                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                                    </summary>
                                    <div class="space-y-1 border-t border-slate-200 px-2 py-2">
                                        <a href="{{ localized_route('category.show', $cat->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-lg px-2 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                            All {{ $cat->name }}
                                        </a>
                                        @foreach ($children as $child)
                                            @php $grandChildren = collect($child->children ?? []); @endphp
                                            @if ($grandChildren->isNotEmpty())
                                                <details class="rounded-lg border border-slate-200 bg-white">
                                                    <summary class="flex cursor-pointer list-none items-center justify-between px-2 py-2 text-xs font-semibold text-slate-700">
                                                        <span>{{ $child->name }}</span>
                                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                                                    </summary>
                                                    <div class="space-y-1 border-t border-slate-200 px-2 py-2">
                                                        <a href="{{ localized_route('category.show', $child->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-md px-2 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                                            All {{ $child->name }}
                                                        </a>
                                                        @foreach ($grandChildren as $grand)
                                                            <a href="{{ localized_route('category.show', $grand->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-md px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                                                                {{ $grand->name }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @else
                                                <a href="{{ localized_route('category.show', $child->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-lg px-2 py-2 text-xs text-slate-700 hover:bg-slate-100">
                                                    {{ $child->name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                <a href="{{ localized_route('category.show', $cat->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
                                    <span class="flex items-center gap-2">
                                        @if ($catThumb)
                                            <img src="{{ $catThumb }}" alt="{{ $cat->name }}" class="h-8 w-8 rounded object-cover" onerror="this.onerror=null;this.style.display='none';">
                                        @else
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs text-emerald-700">
                                                <i class="fa-solid fa-folder"></i>
                                            </span>
                                        @endif
                                        <span>{{ $cat->name }}</span>
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>

        @auth
            <div id="accountSwitchModal" class="tw-modal hidden" aria-hidden="true">
                <div class="tw-modal-dialog account-switch-sheet-dialog">
                    <div class="tw-modal-content account-switch-sheet-content">
                        <div class="tw-modal-header">
                            <div class="flex items-center gap-3">
                                <button type="button" data-ui-dismiss="modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100" aria-label="Close switch account">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div>
                                    <h3 class="tw-modal-title">Switch Account</h3>
                                    <p class="text-xs text-slate-500">Stay signed in and jump between your saved accounts.</p>
                                </div>
                            </div>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto">
                            <div class="divide-y divide-slate-200 px-4">
                                @forelse ($accountSwitchModalAccounts as $switchAccount)
                                    @php
                                        $switchShop = $switchAccount->shop;
                                        $switchName = trim((string) ($switchShop?->localized_name ?: $switchShop?->name ?: $switchAccount->name ?: $switchAccount->email));
                                        $switchMeta = trim((string) ($switchAccount->email ?: ucfirst((string) $switchAccount->user_type)));
                                        $switchAvatar = $switchShop?->logo_url ?: (!empty($switchShop?->logo) ? asset('storage/' . ltrim((string) $switchShop->logo, '/')) : (!empty($switchAccount->photo) ? asset('storage/' . ltrim((string) $switchAccount->photo, '/')) : null));
                                        $switchInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($switchName !== '' ? $switchName : 'A', 0, 1));
                                        $isCurrentSwitchAccount = (int) $switchAccount->id === (int) $headerUser->id;
                                    @endphp
                                    @if ($isCurrentSwitchAccount)
                                        <div class="flex items-center gap-3 py-4">
                                            @if ($switchAvatar)
                                                <span class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                                    <img src="{{ $switchAvatar }}" alt="{{ $switchName }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                                    <span class="hidden inline-flex h-full w-full items-center justify-center bg-slate-100 text-sm font-bold text-slate-700">{{ $switchInitial }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-sm font-bold text-slate-700">{{ $switchInitial }}</span>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-base font-semibold text-slate-900">{{ $switchName }}</p>
                                                <p class="truncate text-sm text-slate-500">{{ $switchMeta }}</p>
                                            </div>
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3 py-4">
                                            <form method="POST" action="{{ route('account.switch', $switchAccount) }}" class="flex min-w-0 flex-1 items-center gap-3">
                                                @csrf
                                                @if ($switchAvatar)
                                                    <span class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                                        <img src="{{ $switchAvatar }}" alt="{{ $switchName }}" class="h-full w-full object-cover" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                                        <span class="hidden inline-flex h-full w-full items-center justify-center bg-slate-100 text-sm font-bold text-slate-700">{{ $switchInitial }}</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-sm font-bold text-slate-700">{{ $switchInitial }}</span>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-base font-semibold text-slate-900">{{ $switchName }}</p>
                                                    <p class="truncate text-sm text-slate-500">{{ $switchMeta }}</p>
                                                </div>

                                                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    Switch
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('account.switch.forget', $switchAccount) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50" onclick="return confirm('Remove this saved account from quick switching on this device?')">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @empty
                                    <div class="py-4 text-sm text-slate-500">
                                        No saved accounts are available yet. Add another account from Account Settings first.
                                    </div>
                                @endforelse
                            </div>

                            <div class="border-t border-slate-200 px-4 py-3">
                                <a href="{{ route('account.details') }}#account-switching" class="flex items-center gap-3 rounded-2xl px-1 py-2 text-left text-slate-900 hover:bg-slate-50">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                        <i class="fas fa-plus text-lg"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-base font-semibold">Add Account</span>
                                        <span class="block text-sm text-slate-500">Use Account Settings to add another login for quick switching.</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

        <main class="flex-1 pb-20 lg:pb-0">
            @yield('main')
        </main>

        @php
            $mobileNavContext = trim($__env->yieldContent('cetsy_mobile_nav_context', ''));
            $showThemeMobileBottomNav = ! $isAdminUser
                && $mobileNavContext !== 'seller'
                && ! $isSellerArea;
        @endphp

        @if ($showThemeMobileBottomNav)
            @php
                $mobileCartCount = isset($headerCartCount)
                    ? (int) $headerCartCount
                    : (int) collect(session('cart', []))->sum(function ($row) {
                        return is_array($row) ? (int) ($row['quantity'] ?? 0) : 0;
                    });
            @endphp
            <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-3 py-2 backdrop-blur lg:hidden" aria-label="Mobile Bottom Navigation">
                <div class="mx-auto grid w-full max-w-7xl grid-cols-4 gap-2">
                    <a href="{{ localized_route('home') }}" class="inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold {{ localized_route_is('home') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fas fa-house mb-1 text-sm"></i>
                        Home
                    </a>
                    <a href="{{ localized_route('listings') }}" class="inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold {{ localized_route_is('listings') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fas fa-list-ul mb-1 text-sm"></i>
                        Listings
                    </a>
                    <a href="{{ url('/cart') }}" class="relative inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold {{ request()->is('cart*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fas fa-shopping-cart mb-1 text-sm"></i>
                        Cart
                        @if ($mobileCartCount > 0)
                            <span class="absolute right-3 top-0 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[10px] font-semibold leading-none text-white">
                                {{ $mobileCartCount > 99 ? '99+' : $mobileCartCount }}
                            </span>
                        @endif
                    </a>
                    <button type="button" @click="mobileDrawerOpen = true" class="inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-100" aria-label="Open side menu">
                        <i class="fas fa-bars mb-1 text-sm"></i>
                        Menu
                    </button>
                </div>
            </nav>
        @endif

        <footer class="mt-12 bg-slate-950 text-slate-200">
            <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-white">Help & Support</h4>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="hover:text-white" href="{{ url('/about') }}">About Us</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('contact') }}">Contact Us</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('payment_policy') }}">Payments & Payouts</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('user-agreement') }}">User Agreement</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('user-agreement') }}#fees">Fees Schedule</a></li>
                        <li><a class="hover:text-white" href="{{ url('/refunds-returns') }}">Refund & Returns</a></li>
                        <li><a class="hover:text-white" href="{{ url('/shipping-delivery') }}">Shipping & Delivery</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-white">Marketplace</h4>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="hover:text-white" href="{{ localized_route('listings') }}">Browse Listings</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('shops.index') }}">Find a Shop</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('become-seller') }}">Sell on {{ $siteName }}</a></li>
                        <li><a class="hover:text-white" href="{{ localized_route('blog.index') }}">Blog</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-white">Account</h4>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="hover:text-white" href="{{ url('/login') }}">Login</a></li>
                        <li><a class="hover:text-white" href="{{ url('/register') }}">Register</a></li>
                        <li><a class="hover:text-white" href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a class="hover:text-white" href="{{ url('/cart') }}">Cart</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-white">Follow Us</h4>
                    <p class="mt-4 text-sm text-slate-400">Stay connected with {{ $siteName }} updates and creator stories.</p>
                    <div class="mt-4 flex items-center gap-3 text-lg">
                        @foreach([
                            'facebook_url'  => 'fab fa-facebook-f',
                            'instagram_url' => 'fab fa-instagram',
                            'youtube_url'   => 'fab fa-youtube',
                            'x_url'         => 'fab fa-x-twitter',
                            'linkedin_url'  => 'fab fa-linkedin-in',
                            'tiktok_url'    => 'fab fa-tiktok',
                        ] as $key => $icon)
                            @php
                                $socialUrl = trim((string) (data_get($settings, $key) ?? setting($key, '')));
                            @endphp
                            @if($socialUrl !== '')
                                <a href="{{ $socialUrl }}" target="_blank" rel="noopener" class="text-slate-300 hover:text-white" aria-label="{{ ucfirst(str_replace('_url','',$key)) }}">
                                    <i class="{{ $icon }}"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800">
                <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-3 px-4 py-4 text-xs text-slate-400 sm:flex-row sm:px-6">
                    <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                    <div class="flex items-center gap-2 text-base">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-paypal"></i>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @if ($legacyBootstrapCompat)
        <script src="{{ asset('vendors/popper/popper.min.js') }}" defer></script>
        <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}" defer></script>
    @endif

    <script src="{{ asset('assets/js/pwa-install.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const pageLoader = document.getElementById('pageTransitionLoader');
        let pageLoaderIsVisible = false;
        let pageLoaderDelayTimer = null;

        if (document.querySelector('.tw-modal.is-open, .modal.is-open')) {
            body.classList.add('overflow-hidden');
        }

        function showPageLoader() {
            if (!pageLoader || pageLoaderIsVisible) return;
            pageLoaderIsVisible = true;
            pageLoader.classList.add('is-active');
            body.classList.add('is-transitioning');
        }

        function hidePageLoader() {
            if (!pageLoader) return;
            pageLoaderIsVisible = false;
            window.clearTimeout(pageLoaderDelayTimer);
            pageLoader.classList.remove('is-active');
            body.classList.remove('is-transitioning');
        }

        function shouldTrackLinkNavigation(link, event) {
            if (!link || event.defaultPrevented) return false;
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;

            const target = (link.getAttribute('target') || '').trim().toLowerCase();
            if (target && target !== '_self') return false;
            if (link.hasAttribute('download') || link.hasAttribute('data-no-page-loader')) return false;
            if (link.closest('[data-ui-toggle], [data-toggle], [data-ui-dismiss], [data-dismiss], [data-tw-modal-open], [data-tw-modal-close]')) return false;

            const hrefAttr = (link.getAttribute('href') || '').trim();
            if (!hrefAttr || hrefAttr === '#') return false;
            if (/^(javascript|mailto|tel):/i.test(hrefAttr)) return false;

            let destinationUrl;
            try {
                destinationUrl = new URL(link.href, window.location.href);
            } catch (_) {
                return false;
            }

            const isSameDocumentHash =
                destinationUrl.pathname === window.location.pathname &&
                destinationUrl.search === window.location.search &&
                destinationUrl.hash;

            return !isSameDocumentHash;
        }

        window.addEventListener('pageshow', hidePageLoader);
        window.addEventListener('beforeunload', showPageLoader);

        document.addEventListener('click', function (event) {
            const eventTarget = event.target;
            if (!(eventTarget instanceof Element)) return;

            const link = eventTarget.closest('a[href]');
            if (!shouldTrackLinkNavigation(link, event)) return;

            window.clearTimeout(pageLoaderDelayTimer);
            pageLoaderDelayTimer = window.setTimeout(function () {
                if (event.defaultPrevented) return;
                showPageLoader();
            }, 80);
        }, true);

        // Desktop top-category rail as a paged carousel.
        document.querySelectorAll('[data-top-category-carousel]').forEach(function (carousel) {
            const scroller = carousel.querySelector('.top-category-scroll');
            const track = carousel.querySelector('[data-top-category-track]');
            const prevBtn = carousel.querySelector('[data-top-category-prev]');
            const nextBtn = carousel.querySelector('[data-top-category-next]');
            if (!scroller || !track) return;

            const itemSelector = '[data-top-category-item]';
            const visibleCount = Math.max(1, Number(carousel.getAttribute('data-visible-count') || 6));
            const autoplayMs = Math.max(0, Number(carousel.getAttribute('data-autoplay-ms') || 0));
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            let pageIndex = 0;
            let pageOffsets = [0];
            let maxPageIndex = 0;
            let autoTimer = null;
            let resizeTimer = null;

            function getItems() {
                return Array.from(track.querySelectorAll(itemSelector));
            }

            function updateNavState() {
                const isDisabled = maxPageIndex === 0;
                [prevBtn, nextBtn].forEach(function (btn) {
                    if (!btn) return;
                    btn.classList.toggle('is-disabled', isDisabled);
                    btn.setAttribute('aria-hidden', isDisabled ? 'true' : 'false');
                });
            }

            function applyPage(animate) {
                const offset = pageOffsets[pageIndex] || 0;
                track.style.transition = animate ? 'transform 0.45s ease' : 'none';
                track.style.transform = 'translate3d(' + (-offset) + 'px, 0, 0)';
                if (!animate) {
                    window.requestAnimationFrame(function () {
                        track.style.transition = 'transform 0.45s ease';
                    });
                }
            }

            function rebuildPages() {
                const items = getItems();
                pageOffsets = [0];

                if (items.length > visibleCount) {
                    for (let index = visibleCount; index < items.length; index += visibleCount) {
                        pageOffsets.push(items[index].offsetLeft);
                    }
                }

                maxPageIndex = Math.max(0, pageOffsets.length - 1);
                if (pageIndex > maxPageIndex) {
                    pageIndex = maxPageIndex;
                }

                applyPage(false);
                updateNavState();
            }

            function goNext() {
                if (maxPageIndex === 0) return;
                pageIndex = pageIndex >= maxPageIndex ? 0 : pageIndex + 1;
                applyPage(true);
            }

            function goPrev() {
                if (maxPageIndex === 0) return;
                pageIndex = pageIndex <= 0 ? maxPageIndex : pageIndex - 1;
                applyPage(true);
            }

            function stopAuto() {
                if (!autoTimer) return;
                window.clearInterval(autoTimer);
                autoTimer = null;
            }

            function startAuto() {
                stopAuto();
                if (prefersReducedMotion || autoplayMs < 1200 || maxPageIndex === 0) return;
                autoTimer = window.setInterval(goNext, autoplayMs);
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    goPrev();
                    startAuto();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    goNext();
                    startAuto();
                });
            }

            carousel.addEventListener('mouseenter', stopAuto);
            carousel.addEventListener('mouseleave', startAuto);
            carousel.addEventListener('focusin', stopAuto);
            carousel.addEventListener('focusout', function (event) {
                if (!carousel.contains(event.relatedTarget)) {
                    startAuto();
                }
            });
            carousel.addEventListener('touchstart', stopAuto, { passive: true });
            carousel.addEventListener('touchend', startAuto, { passive: true });

            window.addEventListener('resize', function () {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(rebuildPages, 140);
            });

            rebuildPages();
            startAuto();
            window.addEventListener('beforeunload', stopAuto);
        });

        function resolveTarget(trigger) {
            const selector = trigger.getAttribute('data-ui-target') || trigger.getAttribute('data-target');
            if (!selector) return null;
            try { return document.querySelector(selector); } catch (_) { return null; }
        }

        function openModal(modal) {
            if (!modal) return;
            if (modal.classList.contains('tw-modal')) {
                modal.classList.add('is-open');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else if (modal.classList.contains('modal')) {
                modal.classList.add('is-open');
            } else {
                modal.classList.remove('hidden');
            }
            body.classList.add('overflow-hidden');
            modal.dispatchEvent(new Event('shown.bs.modal'));
            modal.dispatchEvent(new CustomEvent('modal:open', { bubbles: true }));
        }

        function closeModal(modal) {
            if (!modal) return;
            if (modal.classList.contains('tw-modal')) {
                modal.classList.remove('is-open');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            } else if (modal.classList.contains('modal')) {
                modal.classList.remove('is-open');
            } else {
                modal.classList.add('hidden');
            }
            if (!document.querySelector('.tw-modal.is-open, .modal.is-open')) {
                body.classList.remove('overflow-hidden');
            }
            modal.dispatchEvent(new Event('hidden.bs.modal'));
            modal.dispatchEvent(new CustomEvent('modal:close', { bubbles: true }));
        }

        document.addEventListener('click', function (event) {
            const twTrigger = event.target.closest('[data-tw-modal-open]');
            if (twTrigger) {
                event.preventDefault();
                const id = twTrigger.getAttribute('data-tw-modal-open');
                const modal = id ? document.getElementById(id) : null;
                openModal(modal);
                return;
            }

            const trigger = event.target.closest('[data-ui-toggle], [data-toggle]');
            if (trigger) {
                const kind = trigger.getAttribute('data-ui-toggle') || trigger.getAttribute('data-toggle');
                if (kind === 'modal') {
                    event.preventDefault();
                    const openMenu = trigger.closest('.tw-dropdown-menu.show, .dropdown-menu.show');
                    if (openMenu) {
                        openMenu.classList.remove('show');
                        const toggle = openMenu.previousElementSibling;
                        if (toggle) toggle.setAttribute('aria-expanded', 'false');
                    }
                    openModal(resolveTarget(trigger));
                    return;
                }
                if (kind === 'collapse') {
                    event.preventDefault();
                    const panel = resolveTarget(trigger);
                    if (!panel) return;
                    const willOpen = panel.classList.contains('hidden') || panel.classList.contains('collapse');
                    panel.classList.toggle('hidden');
                    panel.classList.toggle('collapse');
                    panel.classList.toggle('show');
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    return;
                }
                if (kind === 'dropdown') {
                    event.preventDefault();
                    const menu = trigger.nextElementSibling;
                    if (!menu) return;
                    const isOpen = menu.classList.toggle('show');
                    trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    return;
                }
            }

            const dismiss = event.target.closest('[data-ui-dismiss], [data-dismiss]');
            const twDismiss = event.target.closest('[data-tw-modal-close]');
            if (twDismiss) {
                event.preventDefault();
                closeModal(twDismiss.closest('.tw-modal, .modal'));
                return;
            }
            if (dismiss) {
                const kind = dismiss.getAttribute('data-ui-dismiss') || dismiss.getAttribute('data-dismiss');
                if (kind === 'alert') {
                    event.preventDefault();
                    const alertNode = dismiss.closest('[role="alert"], .alert, .rounded-xl');
                    if (alertNode) alertNode.remove();
                    return;
                }
                if (kind === 'modal') {
                    event.preventDefault();
                    closeModal(dismiss.closest('.tw-modal, .modal'));
                    return;
                }
            }

            // Click-outside for dropdown menus and modal backdrops.
            document.querySelectorAll('.tw-dropdown-menu.show, .dropdown-menu.show').forEach(function (menu) {
                const toggle = menu.previousElementSibling;
                if (menu.contains(event.target) || (toggle && toggle.contains(event.target))) return;
                menu.classList.remove('show');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            });
            const modalBackdrop = event.target.closest('.tw-modal, .modal');
            if (modalBackdrop && event.target === modalBackdrop) {
                closeModal(modalBackdrop);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('.tw-dropdown-menu.show, .dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });
            const modal = document.querySelector('.tw-modal.is-open, .modal.is-open');
            if (modal) closeModal(modal);
        });
    });
    </script>

    @php
        $tawkEmbedUrl = trim((string) (
            setting('tawk_to_embed_url')
            ?: config('services.tawk.embed_url')
            ?: 'https://embed.tawk.to/6760175aaf5bfec1dbdcc04c/1if7lmf47'
        ));
        $tawkChatPath = trim((string) parse_url($tawkEmbedUrl, PHP_URL_PATH), '/');
        $tawkDirectUrl = $tawkChatPath !== '' ? 'https://tawk.to/chat/' . $tawkChatPath : 'https://tawk.to/';
    @endphp
    @if ($tawkEmbedUrl !== '')
        <a
            id="cetsyDesktopChatLauncher"
            href="{{ $tawkDirectUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Open live chat support"
            aria-expanded="false"
            style="display:none;position:fixed;right:24px;bottom:92px;z-index:160;align-items:center;gap:10px;padding:14px 18px;border-radius:999px;background:#0f766e;color:#ffffff;font-size:14px;font-weight:700;line-height:1;text-decoration:none;box-shadow:0 18px 40px rgba(15, 23, 42, 0.22);"
        >
            <i class="fas fa-comments" aria-hidden="true"></i>
            <span>Live Chat</span>
        </a>
        <button
            id="cetsySupportChatBackdrop"
            type="button"
            aria-label="Close live chat"
            style="display:none;position:fixed;inset:0;z-index:160;background:rgba(15,23,42,0.28);border:0;padding:0;"
        ></button>
        <section
            id="cetsyDesktopChatPanel"
            aria-hidden="true"
            style="display:none;position:fixed;right:24px;bottom:156px;z-index:161;width:min(420px, calc(100vw - 32px));height:min(680px, calc(100vh - 120px));max-height:calc(100vh - 120px);overflow:hidden;border:1px solid rgba(148, 163, 184, 0.3);border-radius:24px;background:#ffffff;box-shadow:0 28px 70px rgba(15, 23, 42, 0.24);"
        >
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;background:#0f766e;color:#ffffff;">
                <div>
                    <strong style="display:block;font-size:15px;line-height:1.2;">Live Chat</strong>
                    <span style="display:block;font-size:12px;line-height:1.3;opacity:0.85;">Cetsy customer support</span>
                </div>
                <button
                    id="cetsyDesktopChatPanelClose"
                    type="button"
                    aria-label="Close live chat"
                    style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:0;border-radius:999px;background:rgba(255,255,255,0.16);color:#ffffff;font-size:20px;line-height:1;cursor:pointer;"
                >
                    &times;
                </button>
            </div>
            <iframe
                id="cetsyDesktopChatFrame"
                title="Cetsy live chat support"
                data-src="{{ $tawkDirectUrl }}"
                src="about:blank"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                style="display:block;width:100%;height:calc(100% - 64px);border:0;background:#ffffff;"
            ></iframe>
        </section>
        <!-- Start of Tawk.to Script -->
        <script type="text/javascript">
            (function () {
                var mobileQuery = window.matchMedia('(max-width: 1023.98px)');
                var desktopLauncher = document.getElementById('cetsyDesktopChatLauncher');
                var desktopChatBackdrop = document.getElementById('cetsySupportChatBackdrop');
                var desktopChatPanel = document.getElementById('cetsyDesktopChatPanel');
                var desktopChatFrame = document.getElementById('cetsyDesktopChatFrame');
                var desktopChatClose = document.getElementById('cetsyDesktopChatPanelClose');
                var mobileNavSelectors = [
                    'nav[aria-label="Mobile Bottom Navigation"]',
                    '[data-seller-mobile-nav-root] > nav',
                    '.mobile-bottom-nav'
                ];

                function getVisibleMobileNavHeight() {
                    if (!mobileQuery.matches) return 0;

                    var tallestNav = 0;

                    mobileNavSelectors.forEach(function (selector) {
                        document.querySelectorAll(selector).forEach(function (mobileNav) {
                            var style = window.getComputedStyle(mobileNav);
                            if (style.display === 'none' || style.visibility === 'hidden') return;

                            var navHeight = Math.ceil(mobileNav.getBoundingClientRect().height || mobileNav.offsetHeight || 0);
                            if (navHeight > tallestNav) {
                                tallestNav = navHeight;
                            }
                        });
                    });

                    return tallestNav;
                }

                function getLauncherBottomOffset() {
                    if (!mobileQuery.matches) {
                        return '24px';
                    }

                    return 'calc(' + (getVisibleMobileNavHeight() + 16) + 'px + env(safe-area-inset-bottom, 0px))';
                }

                function getPanelBottomOffset() {
                    if (!mobileQuery.matches) {
                        return '88px';
                    }

                    return 'calc(' + (getVisibleMobileNavHeight() + 24) + 'px + env(safe-area-inset-bottom, 0px))';
                }

                function isChatPanelOpen() {
                    return !!desktopChatPanel && desktopChatPanel.style.display !== 'none';
                }

                function syncSupportChatLayout() {
                    if (!desktopLauncher) return;

                    var isMobile = mobileQuery.matches;

                    desktopLauncher.style.display = isChatPanelOpen() ? 'none' : 'inline-flex';
                    desktopLauncher.style.right = isMobile ? '12px' : '24px';
                    desktopLauncher.style.bottom = getLauncherBottomOffset();
                    desktopLauncher.style.padding = isMobile ? '12px 16px' : '14px 18px';
                    desktopLauncher.style.fontSize = isMobile ? '13px' : '14px';

                    if (desktopChatBackdrop) {
                        desktopChatBackdrop.style.display = isChatPanelOpen() ? 'block' : 'none';
                    }

                    if (!desktopChatPanel) return;

                    if (isMobile) {
                        desktopChatPanel.style.left = '12px';
                        desktopChatPanel.style.right = '12px';
                        desktopChatPanel.style.top = 'calc(env(safe-area-inset-top, 0px) + 12px)';
                        desktopChatPanel.style.bottom = getPanelBottomOffset();
                        desktopChatPanel.style.width = 'auto';
                        desktopChatPanel.style.height = 'auto';
                        desktopChatPanel.style.maxHeight = 'none';
                        desktopChatPanel.style.borderRadius = '20px';
                    } else {
                        desktopChatPanel.style.left = 'auto';
                        desktopChatPanel.style.right = '24px';
                        desktopChatPanel.style.top = 'auto';
                        desktopChatPanel.style.bottom = getPanelBottomOffset();
                        desktopChatPanel.style.width = 'min(420px, calc(100vw - 32px))';
                        desktopChatPanel.style.height = 'min(680px, calc(100vh - 120px))';
                        desktopChatPanel.style.maxHeight = 'calc(100vh - 120px)';
                        desktopChatPanel.style.borderRadius = '24px';
                    }
                }

                function openDesktopChatPanel() {
                    if (!desktopChatPanel) return;

                    if (desktopChatFrame && desktopChatFrame.getAttribute('src') === 'about:blank') {
                        desktopChatFrame.setAttribute('src', desktopChatFrame.getAttribute('data-src') || @json($tawkDirectUrl));
                    }

                    desktopChatPanel.style.display = 'block';
                    desktopChatPanel.setAttribute('aria-hidden', 'false');
                    if (desktopLauncher) {
                        desktopLauncher.setAttribute('aria-expanded', 'true');
                    }
                    document.body.style.overflow = 'hidden';
                    syncSupportChatLayout();
                }

                function closeDesktopChatPanel() {
                    if (!desktopChatPanel) return;
                    desktopChatPanel.style.display = 'none';
                    desktopChatPanel.setAttribute('aria-hidden', 'true');
                    if (desktopLauncher) {
                        desktopLauncher.setAttribute('aria-expanded', 'false');
                    }
                    document.body.style.overflow = '';
                    syncSupportChatLayout();
                }

                document.addEventListener('DOMContentLoaded', syncSupportChatLayout);
                window.addEventListener('resize', syncSupportChatLayout, { passive: true });
                window.addEventListener('orientationchange', syncSupportChatLayout, { passive: true });
                if (mobileQuery.addEventListener) {
                    mobileQuery.addEventListener('change', syncSupportChatLayout);
                } else if (mobileQuery.addListener) {
                    mobileQuery.addListener(syncSupportChatLayout);
                }

                if (desktopLauncher) {
                    desktopLauncher.addEventListener('click', function (event) {
                        event.preventDefault();
                        if (isChatPanelOpen()) {
                            closeDesktopChatPanel();
                            return;
                        }
                        openDesktopChatPanel();
                    });
                }

                if (desktopChatClose) {
                    desktopChatClose.addEventListener('click', function () {
                        closeDesktopChatPanel();
                    });
                }

                if (desktopChatBackdrop) {
                    desktopChatBackdrop.addEventListener('click', function () {
                        closeDesktopChatPanel();
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeDesktopChatPanel();
                    }
                });

                syncSupportChatLayout();
            })();
        </script>
        <!-- End of Tawk.to Script -->
    @endif

    <script>
        (function () {
            function protectedPreview(target) {
                return target && target.closest ? target.closest('.cetsy-preview-watermark') : null;
            }

            document.addEventListener('contextmenu', function (event) {
                if (protectedPreview(event.target)) {
                    event.preventDefault();
                }
            });

            document.addEventListener('dragstart', function (event) {
                if (protectedPreview(event.target)) {
                    event.preventDefault();
                }
            });

            document.addEventListener('selectstart', function (event) {
                if (protectedPreview(event.target)) {
                    event.preventDefault();
                }
            });

            document.addEventListener('copy', function (event) {
                if (protectedPreview(event.target) || protectedPreview(document.activeElement)) {
                    event.preventDefault();
                }
            });
        })();
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
