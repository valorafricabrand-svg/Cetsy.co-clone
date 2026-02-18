{{-- resources/views/shops/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('My Shops') }}
    </h2>
@endsection

@section('main')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- â€œNew Shopâ€ Button --}}
        <div class="flex justify-end mb-4">
            <a href="{{ route('shops.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
               + New Shop
            </a>
        </div>

        {{-- Shops List --}}
        @if($shops->isEmpty())
            <p class="text-gray-500">You have no shops yet.</p>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <ul class="divide-y divide-gray-200">
                    @foreach($shops as $shop)
                        <li>
                            <a href="{{ route('shops.show', $shop) }}"
                               class="block hover:bg-gray-50 p-4 flex items-center">
                                @if($shop->logo)
                                    <img src="{{ asset('storage/' . $shop->logo) }}"
                                         alt="{{ $shop->name }} logo"
                                         class="w-12 h-12 object-cover rounded mr-4">
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $shop->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $shop->slug }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection

