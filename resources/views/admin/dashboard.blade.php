@extends('layouts.app')

@section('header')
    <h2 class="h4 text-secondary mb-0">
        {{ __('Admin Dashboard') }}
    </h2>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="content">
        <div class="row row-cols-1 row-cols-md-3 g-4">

             <div class="col">
    <div class="card h-100 shadow-sm position-relative">
        <div class="card-body text-center">
            <h5 class="card-title mb-2">Amount in Wallets</h5>
            <p class="display-6 mb-0">
                {{ get_currency() }} {{ admin_wallet() }}
            </p>

            {{-- Invisible link that makes the whole card clickable --}}
            <a href="{{ route('admin.wallets.index') }}" class="stretched-link"></a>
        </div>
    </div>
</div>


            
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Sellers</h5>
                        <p class="display-6 mb-0">{{ \App\Models\User::where('user_type', 'seller')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Active Listing</h5>
                        <p class="display-6 mb-0">{{ \App\Models\Product::where('is_active', '1')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Revenue (KES)</h5>
                        <p class="display-6 mb-0">
                            {{ number_format(\App\Models\Order::sum('total_amount'), 2) }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
