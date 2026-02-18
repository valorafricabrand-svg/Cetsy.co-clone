@extends('theme.'.theme().'.layouts.app')

@section('title', 'Seller Billing')

@section('main')
@php
    $shop = auth()->user()->shop;
    $brandColor = optional($shop)->primary_color;
    if (!is_string($brandColor) || !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $brandColor)) {
        $brandColor = '#0f766e';
    }

    $currency = get_currency();
    $walletBalance = wallet();

    $cards = [
        [
            'title' => 'Wallet',
            'text' => 'Current balance: '.$currency.' '.number_format((float) $walletBalance, 2),
            'icon' => 'fas fa-wallet',
            'actions' => [
                ['label' => 'View Wallet', 'href' => route('wallet.index'), 'tone' => 'border'],
                \Illuminate\Support\Facades\Route::has('wallet.deposit.form')
                    ? ['label' => 'Deposit Funds', 'href' => route('wallet.deposit.form'), 'tone' => 'solid']
                    : null,
            ],
        ],
        [
            'title' => 'Subscription',
            'text' => 'Manage your seller plan and renewals.',
            'icon' => 'fas fa-file-invoice-dollar',
            'actions' => [
                ['label' => 'Manage Subscription', 'href' => route('seller.subscription'), 'tone' => 'border'],
            ],
        ],
        [
            'title' => 'Payouts',
            'text' => 'Request payouts and track status.',
            'icon' => 'fas fa-money-bill-transfer',
            'actions' => [
                ['label' => 'Payout Requests', 'href' => route('seller.payouts.index'), 'tone' => 'border'],
            ],
        ],
        [
            'title' => 'Order Payments',
            'text' => 'Review payments received for orders.',
            'icon' => 'fas fa-receipt',
            'actions' => [
                [
                    'label' => 'View Payments',
                    'href' => \Illuminate\Support\Facades\Route::has('seller.orders.payments')
                        ? route('seller.orders.payments')
                        : url('/seller/order/payments'),
                    'tone' => 'border',
                ],
            ],
        ],
    ];
@endphp

<section class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            @include('seller.partials.sidebar')

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Billing & Payments</h1>
                    <p class="mt-1 text-sm text-slate-500">Quick access to wallet, deposits, subscriptions, and payouts.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($cards as $card)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background-color: {{ $brandColor }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold text-slate-900">{{ $card['title'] }}</h2>
                                    <p class="mt-1 text-sm text-slate-600">{{ $card['text'] }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach(array_filter($card['actions']) as $action)
                                    <a href="{{ $action['href'] }}"
                                       class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold {{ $action['tone'] === 'solid' ? 'text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-100' }}"
                                       @if($action['tone'] === 'solid') style="background-color: {{ $brandColor }}" @endif>
                                        {{ $action['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
