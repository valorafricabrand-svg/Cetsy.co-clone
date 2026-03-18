@extends('theme.'.theme().'.layouts.app')

@section('main')
@php
    $currentUser = Auth::user();
    $accountSwitchErrors = $errors->getBag('accountSwitch');
@endphp
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 space-y-4 lg:col-span-9">
                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <h3 class="text-2xl font-semibold text-slate-900">Account Settings</h3>
                    <p class="mt-1 text-slate-500">Update your profile information and manage the accounts saved for quick switching.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold text-slate-900">Profile Details</h4>
                        <p class="mt-1 text-sm text-slate-500">Keep your name and email address up to date.</p>
                    </div>

                    <form action="{{ route('account.updateDetails') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                            <input type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" id="name" name="name" value="{{ $currentUser?->name }}" placeholder="Enter your name">
                        </div>
                        <div>
                            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
                            <input type="email" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" id="email" name="email" value="{{ $currentUser?->email }}" placeholder="Enter your email">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Update Details</button>
                        </div>
                    </form>
                </div>

                <div id="account-switching" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h4 class="text-lg font-semibold text-slate-900">Switch Accounts</h4>
                        <p class="mt-1 text-sm text-slate-500">Stay signed in and move between your saved buyer or seller accounts from one place.</p>
                    </div>

                    <div class="grid gap-0 lg:grid-cols-[minmax(0,1.3fr)_minmax(20rem,0.9fr)]">
                        <div class="divide-y divide-slate-200">
                            @foreach ($switchAccounts as $switchAccount)
                                @php
                                    $switchShop = $switchAccount->shop;
                                    $switchName = trim((string) ($switchShop?->name ?: $switchAccount->name ?: $switchAccount->email));
                                    $switchMeta = trim((string) ($switchAccount->email ?: ucfirst((string) $switchAccount->user_type)));
                                    $switchAvatar = $switchShop?->logo_url
                                        ?: (!empty($switchShop?->logo)
                                            ? asset('storage/' . ltrim((string) $switchShop->logo, '/'))
                                            : (!empty($switchAccount->photo)
                                                ? asset('storage/' . ltrim((string) $switchAccount->photo, '/'))
                                                : null));
                                    $switchInitial = \Illuminate\Support\Str::upper(
                                        \Illuminate\Support\Str::substr($switchName !== '' ? $switchName : 'A', 0, 1)
                                    );
                                    $isCurrentSwitchAccount = (int) $switchAccount->id === (int) ($currentUser?->id ?? 0);
                                @endphp

                                @if ($isCurrentSwitchAccount)
                                    <div class="flex items-center gap-3 px-5 py-4">
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

                                        <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Current
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50">
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
                            @endforeach

                            @if ($switchAccounts->count() <= 1)
                                <div class="px-5 py-4 text-sm text-slate-500">
                                    No other accounts are saved yet. Add another account on the right and it will appear here for one-tap switching near logout.
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-slate-200 bg-slate-50/80 px-5 py-5 lg:border-l lg:border-t-0">
                            <div class="mb-4">
                                <h5 class="text-base font-semibold text-slate-900">Add Another Account</h5>
                                <p class="mt-1 text-sm text-slate-500">Enter the other account's login details once to save it for quick switching on this device for longer.</p>
                            </div>

                            <form method="POST" action="{{ route('account.switch.authenticate') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="switch_email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                                    <input id="switch_email" name="switch_email" type="email" value="{{ old('switch_email') }}" autocomplete="username" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none">
                                    @if ($accountSwitchErrors->has('switch_email'))
                                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $accountSwitchErrors->first('switch_email') }}</p>
                                    @endif
                                </div>

                                <div>
                                    <label for="switch_password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                                    <input id="switch_password" name="switch_password" type="password" autocomplete="current-password" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none">
                                    @if ($accountSwitchErrors->has('switch_password'))
                                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $accountSwitchErrors->first('switch_password') }}</p>
                                    @endif
                                </div>

                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                                    Add and switch
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
