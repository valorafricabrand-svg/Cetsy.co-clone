@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Seller Subscription</h2>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if($subscription && $subscription->isActive())
                    <div class="bg-green-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-green-800">Active Subscription</h3>
                        <p class="mt-2 text-green-700">
                            Your subscription is active until {{ $subscription->end_date->format('F j, Y') }}
                        </p>
                    </div>

                    <form action="{{ route('seller.subscription.cancel') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Cancel Subscription
                        </button>
                    </form>
                @else
                    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-yellow-800">Subscription Required</h3>
                        <p class="mt-2 text-yellow-700">
                            To access seller features, you need an active subscription.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-lg border">
                        <h3 class="text-xl font-semibold mb-4">Monthly Subscription</h3>
                        <p class="text-3xl font-bold mb-4">KES {{ number_format(config('subscription.monthly_fee', 1000), 2) }}</p>
                        
                        <ul class="mb-6 space-y-2">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Access to seller dashboard
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                List unlimited products
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Process orders and payments
                            </li>
                        </ul>

                        <form action="{{ route('seller.subscription.subscribe') }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="mpesa">
                            <input type="hidden" name="transaction_id" value="{{ uniqid() }}">
                            
                            <button type="submit" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                                Subscribe Now
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 