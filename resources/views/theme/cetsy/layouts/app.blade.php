<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $siteName = config('app.name', 'Cetsy');
        $siteUrl = config('app.url', url('/'));
        $defaultTitle = $siteName . ' | All-in-one Platform to Showcase Your Handmade Products Globally';
        $metaTitle = trim($__env->yieldContent('title', $defaultTitle));
        $metaDescription = trim($__env->yieldContent('meta_description', 'Cetsy is the all-in-one platform to showcase, sell, and promote your handmade products to a global audience.'));
        $canonicalUrl = trim($__env->yieldContent('canonical_url', url()->current()));
        $metaImage = trim($__env->yieldContent('meta_image', asset('assets/images/cetsylogmain.png')));
        $metaRobots = trim($__env->yieldContent('meta_robots', 'index, follow'));
        $favicon = favicon_url();
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

        $topNavCategories = collect();
        try {
            $topNavCategories = \App\Models\Category::whereNull('parent_id')
                ->with([
                    'children' => function ($query) {
                        $query->orderBy('name')
                            ->with([
                                'children' => function ($childQuery) {
                                    $childQuery->orderBy('name');
                                }
                            ]);
                    }
                ])
                ->orderBy('name')
                ->take(10)
                ->get(['id', 'name', 'slug']);
        } catch (\Throwable $e) {
            $topNavCategories = collect();
        }

        $settings = \App\Models\Setting::first();
        $hideMarketplaceCategories = auth()->check() && $isSellerArea;

        $headerUnreadNotifications = 0;
        $headerRecentNotifications = collect();
        if (auth()->check() && \Illuminate\Support\Facades\Route::has('notifications.index')) {
            try {
                $headerUser = auth()->user();
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

    <title>{{ $metaTitle }}</title>
    <link rel="canonical" href="{{ $canonicalUrl }}">

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
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
    </style>

    @yield('styles')
    @stack('styles')

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $metaImage ?: $favicon,
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    @stack('structured-data')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div x-data="{ mobileDrawerOpen: false }" x-init="$watch('mobileDrawerOpen', value => document.body.classList.toggle('overflow-hidden', value))" class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center gap-3 px-4 py-3 sm:px-6">
                <button @click="mobileDrawerOpen = true" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" type="button" aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <img src="{{ logo_url() }}" alt="{{ $siteName }}" class="h-10 w-auto"
                         onerror="this.onerror=null;this.src=@json(asset('assets/images/cetsylogmain.png'));">
                </a>

                <form method="GET" action="{{ route('search') }}" class="hidden flex-1 lg:block">
                    <label for="globalSearch" class="sr-only">Search products</label>
                    <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
                        <i class="fas fa-search text-slate-400"></i>
                        <input id="globalSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Search products, services, shops" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                        <button type="submit" class="rounded-full bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Search</button>
                    </div>
                </form>

                <nav class="ml-2 hidden items-center gap-2 lg:flex">
                    <a href="{{ route('listings') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">Listings</a>
                    <a href="{{ route('shops.index') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">Shops</a>
                    <a href="{{ route('become-seller') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">Sell</a>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    @auth
                        @if (\Illuminate\Support\Facades\Route::has('notifications.index'))
                            <div class="relative">
                                <button type="button" data-ui-toggle="dropdown" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50" aria-label="Notifications" aria-expanded="false">
                                    <i class="fas fa-bell text-sm"></i>
                                    @if ($headerUnreadNotifications > 0)
                                        <span class="absolute -right-1 -top-1 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[10px] font-semibold leading-none text-white">
                                            {{ $headerUnreadNotifications > 99 ? '99+' : $headerUnreadNotifications }}
                                        </span>
                                    @endif
                                </button>

                                <div class="tw-dropdown-menu right-0 w-[22rem] max-w-[90vw] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
                                            <p class="text-xs text-slate-500">Latest updates from your account</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                            {{ $headerUnreadNotifications }} unread
                                        </span>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto">
                                        @forelse ($headerRecentNotifications as $notification)
                                            @php
                                                $notificationHref = \Illuminate\Support\Facades\Route::has('notifications.open')
                                                    ? route('notifications.open', $notification->id)
                                                    : route('notifications.index');
                                                $notificationTitle = trim((string) ($notification->title ?: $notification->description ?: $notification->message ?: 'Notification'));
                                                $notificationAge = optional($notification->created_at)->diffForHumans();
                                                $notificationAction = 'Open';
                                                try {
                                                    $notificationAction = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user()) ?: 'Open';
                                                } catch (\Throwable $e) {
                                                    $notificationAction = 'Open';
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
                                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">New</span>
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
                                                No notifications yet.
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="border-t border-slate-200 p-3">
                                        <a href="{{ route('notifications.index') }}" class="inline-flex w-full items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                            View all notifications
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <a href="{{ url('/dashboard') }}" class="hidden rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300 sm:inline-flex">Dashboard</a>
                        @if (Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">Logout</button>
                            </form>
                        @endif
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">Login</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Create account</a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="mx-auto hidden w-full max-w-7xl px-4 pb-3 lg:block sm:px-6">
                @if (!$hideMarketplaceCategories && $topNavCategories->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 pb-1">
                        @foreach ($topNavCategories as $cat)
                            @php $children = collect($cat->children ?? []); @endphp
                            <div class="relative" x-data="{ open: false, pinned: false }" @mouseenter="open = true" @mouseleave="if (!pinned) open = false" @focusin="open = true" @focusout="if (!pinned) open = false" @click.outside="open = false; pinned = false">
                                <a href="{{ route('category.show', $cat->slug) }}" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" @if ($children->isNotEmpty()) @click.prevent="pinned = !pinned; open = pinned" @endif>
                                    <span>{{ $cat->name }}</span>
                                    @if ($children->isNotEmpty())
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                                    @endif
                                </a>

                                @if ($children->isNotEmpty())
                                    <div x-show="open" x-cloak x-transition class="absolute left-0 top-full z-50 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                        <a href="{{ route('category.show', $cat->slug) }}" class="mb-1 block rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                            All {{ $cat->name }}
                                        </a>

                                        <ul class="space-y-1">
                                            @foreach ($children as $child)
                                                @php $grandChildren = collect($child->children ?? []); @endphp
                                                <li class="relative" x-data="{ openChild: false, pinnedChild: false }" @mouseenter="openChild = true" @mouseleave="if (!pinnedChild) openChild = false" @focusin="openChild = true" @focusout="if (!pinnedChild) openChild = false" @click.outside="openChild = false; pinnedChild = false">
                                                    <a href="{{ route('category.show', $child->slug) }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900" @if ($grandChildren->isNotEmpty()) @click.prevent="pinnedChild = !pinnedChild; openChild = pinnedChild" @endif>
                                                        <span class="truncate">{{ $child->name }}</span>
                                                        @if ($grandChildren->isNotEmpty())
                                                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                                                        @endif
                                                    </a>

                                                    @if ($grandChildren->isNotEmpty())
                                                        <div x-show="openChild" x-cloak x-transition class="absolute left-full top-0 ml-2 w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                                            <a href="{{ route('category.show', $child->slug) }}" class="mb-1 block rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                                                All {{ $child->name }}
                                                            </a>
                                                            <ul class="space-y-1">
                                                                @foreach ($grandChildren as $grand)
                                                                    <li>
                                                                        <a href="{{ route('category.show', $grand->slug) }}" class="block rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900">
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
                @endif
            </div>

        </header>

        <div x-show="mobileDrawerOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 lg:hidden" @click="mobileDrawerOpen = false"></div>

        <aside x-show="mobileDrawerOpen" x-cloak x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-50 h-full w-[88%] max-w-sm overflow-y-auto border-l border-slate-200 bg-white shadow-2xl lg:hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Menu</h3>
                <button @click="mobileDrawerOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-100" type="button" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4 px-4 py-4">
                <form method="GET" action="{{ route('search') }}">
                    <label for="mobileDrawerSearch" class="sr-only">Search products</label>
                    <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
                        <i class="fas fa-search text-slate-400"></i>
                        <input id="mobileDrawerSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Search products" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                        <button type="submit" class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Go</button>
                    </div>
                </form>

                <nav class="grid grid-cols-2 gap-2">
                    <a href="{{ route('listings') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Listings</a>
                    <a href="{{ route('shops.index') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Shops</a>
                    <a href="{{ route('become-seller') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Sell</a>
                    <a href="{{ route('contact') }}" @click="mobileDrawerOpen = false" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Support</a>
                </nav>

                @if(auth()->check() && auth()->user()->isBuyer())
                    @php
                        $buyerUnreadMessages = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                        $buyerPendingOffers = \App\Models\Offer::where('buyer_id', auth()->id())->where('status', 'pending')->count();
                    @endphp
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Buyer Menu</div>
                        <div class="space-y-1.5">
                            <a href="{{ route('buyer.dashboard') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-gauge mr-2"></i>Dashboard</span>
                            </a>
                            <a href="{{ route('account.orders') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-receipt mr-2"></i>Orders</span>
                            </a>
                            @if(\Illuminate\Support\Facades\Route::has('buyer.offers'))
                                <a href="{{ route('buyer.offers') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-hand-holding-dollar mr-2"></i>Offers</span>
                                    @if($buyerPendingOffers > 0)
                                        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $buyerPendingOffers }}</span>
                                    @endif
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('buyer.messages.index'))
                                <a href="{{ route('buyer.messages.index') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-comments mr-2"></i>Messages</span>
                                    @if($buyerUnreadMessages > 0)
                                        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $buyerUnreadMessages }}</span>
                                    @endif
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('buyer.favorites'))
                                <a href="{{ route('buyer.favorites') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-heart mr-2"></i>Favorites</span>
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('wishlist'))
                                <a href="{{ route('wishlist') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-bookmark mr-2"></i>Wishlist</span>
                                </a>
                            @endif
                            <a href="{{ route('wallet.index') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-wallet mr-2"></i>Wallet</span>
                            </a>
                            @if(\Illuminate\Support\Facades\Route::has('notifications.index'))
                                <a href="{{ route('notifications.index') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <span><i class="fas fa-bell mr-2"></i>Notifications</span>
                                </a>
                            @endif
                            <a href="{{ route('account.payments') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-credit-card mr-2"></i>Payments</span>
                            </a>
                            <a href="{{ route('account.details') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-user mr-2"></i>Account</span>
                            </a>
                            <a href="{{ route('account.addresses') }}" @click="mobileDrawerOpen = false" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span><i class="fas fa-location-dot mr-2"></i>Addresses</span>
                            </a>
                        </div>
                    </div>
                @endif

                @auth
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Account</div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ url('/dashboard') }}" @click="mobileDrawerOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Dashboard</a>
                            <a href="{{ url('/cart') }}" @click="mobileDrawerOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Cart</a>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Account</div>
                        <div class="grid grid-cols-2 gap-2">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" @click="mobileDrawerOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Login</a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" @click="mobileDrawerOpen = false" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Register</a>
                            @endif
                        </div>
                    </div>
                @endauth

                @if (!$hideMarketplaceCategories && $topNavCategories->isNotEmpty())
                    <div class="space-y-2">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Categories</div>
                        @foreach ($topNavCategories as $cat)
                            @php $children = collect($cat->children ?? []); @endphp
                            @if ($children->isNotEmpty())
                                <details class="rounded-xl border border-slate-200 bg-slate-50">
                                    <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2 text-sm font-semibold text-slate-700">
                                        <span>{{ $cat->name }}</span>
                                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                                    </summary>
                                    <div class="space-y-1 border-t border-slate-200 px-2 py-2">
                                        <a href="{{ route('category.show', $cat->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-lg px-2 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
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
                                                        <a href="{{ route('category.show', $child->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-md px-2 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                                            All {{ $child->name }}
                                                        </a>
                                                        @foreach ($grandChildren as $grand)
                                                            <a href="{{ route('category.show', $grand->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-md px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                                                                {{ $grand->name }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @else
                                                <a href="{{ route('category.show', $child->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-lg px-2 py-2 text-xs text-slate-700 hover:bg-slate-100">
                                                    {{ $child->name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                <a href="{{ route('category.show', $cat->slug) }}" @click="mobileDrawerOpen = false" class="block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
                                    {{ $cat->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>

        <main class="flex-1 pb-20 lg:pb-0">
            @yield('main')
        </main>

        @if (!$isSellerArea)
            <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-3 py-2 backdrop-blur lg:hidden" aria-label="Mobile Bottom Navigation">
                <div class="mx-auto grid w-full max-w-7xl grid-cols-4 gap-2">
                    <a href="{{ route('home') }}" class="inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold {{ request()->routeIs('home') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fas fa-house mb-1 text-sm"></i>
                        Home
                    </a>
                    <a href="{{ route('listings') }}" class="inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold {{ request()->routeIs('listings') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fas fa-list-ul mb-1 text-sm"></i>
                        Listings
                    </a>
                    <a href="{{ url('/cart') }}" class="inline-flex flex-col items-center justify-center rounded-xl px-2 py-1 text-[11px] font-semibold {{ request()->is('cart*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fas fa-shopping-cart mb-1 text-sm"></i>
                        Cart
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
                        <li><a class="hover:text-white" href="{{ route('contact') }}">Contact Us</a></li>
                        <li><a class="hover:text-white" href="{{ url('/refunds-returns') }}">Refund & Returns</a></li>
                        <li><a class="hover:text-white" href="{{ url('/shipping-delivery') }}">Shipping & Delivery</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-white">Marketplace</h4>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="hover:text-white" href="{{ route('listings') }}">Browse Listings</a></li>
                        <li><a class="hover:text-white" href="{{ route('shops.index') }}">Find a Shop</a></li>
                        <li><a class="hover:text-white" href="{{ route('become-seller') }}">Sell on {{ $siteName }}</a></li>
                        <li><a class="hover:text-white" href="{{ route('blog.index') }}">Blog</a></li>
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
                    @if ($settings)
                        <div class="mt-4 flex items-center gap-3 text-lg">
                            @foreach([
                                'facebook_url'  => 'fab fa-facebook-f',
                                'instagram_url' => 'fab fa-instagram',
                                'x_url'         => 'fab fa-x-twitter',
                                'linkedin_url'  => 'fab fa-linkedin-in',
                                'tiktok_url'    => 'fab fa-tiktok',
                            ] as $key => $icon)
                                @if(!empty($settings->{$key}))
                                    <a href="{{ $settings->{$key} }}" target="_blank" rel="noopener" class="text-slate-300 hover:text-white" aria-label="{{ ucfirst(str_replace('_url','',$key)) }}">
                                        <i class="{{ $icon }}"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
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

    @yield('scripts')
    @stack('scripts')
</body>
</html>

