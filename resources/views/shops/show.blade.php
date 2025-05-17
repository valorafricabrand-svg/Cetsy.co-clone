@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl py-8">
    <div class="flex items-center mb-6">
        @if($shop->logo)
            <img 
                src="{{ asset('storage/' . $shop->logo) }}" 
                alt="{{ $shop->name }} logo"
                class="w-16 h-16 object-cover rounded-full mr-4"
            >
        @endif
        <div>
            <h1 class="text-3xl font-bold">{{ $shop->name }}</h1>
            <p class="text-gray-600">Owned by {{ $shop->user->name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($shop->bio)
        <div class="mb-6">
            <p>{{ $shop->bio }}</p>
        </div>
    @endif

    <hr class="my-6">

    <p class="text-gray-500">
        (Welcome to your public shop page—product listings coming soon!)
    </p>
</div>
@endsection
