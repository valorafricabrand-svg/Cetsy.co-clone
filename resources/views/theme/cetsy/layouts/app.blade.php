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
        $legacyBootstrapCompat = (bool) config('theme.legacy_bootstrap_compat', true);

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
    <div class="flex min-h-screen flex-col">
        <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center gap-3 px-4 py-3 sm:px-6">
                <button @click="open = !open" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" type="button" aria-label="Toggle menu">
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
                    <a href="{{ route('contact') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900">Support</a>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    @auth
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
                @if ($topNavCategories->isNotEmpty())
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

            <div x-show="open" x-cloak x-transition class="border-t border-slate-200 bg-white lg:hidden">
                <div class="space-y-3 px-4 py-4 sm:px-6">
                    <form method="GET" action="{{ route('search') }}">
                        <label for="mobileSearch" class="sr-only">Search products</label>
                        <div class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2">
                            <i class="fas fa-search text-slate-400"></i>
                            <input id="mobileSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Search products" class="w-full border-0 bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none">
                            <button type="submit" class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white">Go</button>
                        </div>
                    </form>

                    <nav class="grid grid-cols-2 gap-2">
                        <a href="{{ route('listings') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Listings</a>
                        <a href="{{ route('shops.index') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Shops</a>
                        <a href="{{ route('become-seller') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Sell</a>
                        <a href="{{ route('contact') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Support</a>
                    </nav>

                    @if ($topNavCategories->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($topNavCategories as $cat)
                                @php $children = collect($cat->children ?? []); @endphp
                                @if ($children->isNotEmpty())
                                    <details class="rounded-xl border border-slate-200 bg-slate-50">
                                        <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2 text-sm font-semibold text-slate-700">
                                            <span>{{ $cat->name }}</span>
                                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                                        </summary>

                                        <div class="space-y-1 border-t border-slate-200 px-2 py-2">
                                            <a href="{{ route('category.show', $cat->slug) }}" class="block rounded-lg px-2 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
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
                                                            <a href="{{ route('category.show', $child->slug) }}" class="block rounded-md px-2 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                                                All {{ $child->name }}
                                                            </a>
                                                            @foreach ($grandChildren as $grand)
                                                                <a href="{{ route('category.show', $grand->slug) }}" class="block rounded-md px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                                                                    {{ $grand->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                @else
                                                    <a href="{{ route('category.show', $child->slug) }}" class="block rounded-lg px-2 py-2 text-xs text-slate-700 hover:bg-slate-100">
                                                        {{ $child->name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </details>
                                @else
                                    <a href="{{ route('category.show', $cat->slug) }}" class="block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
                                        {{ $cat->name }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-1">
            @yield('main')
        </main>

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

    @yield('scripts')
    @stack('scripts')
</body>
</html>
