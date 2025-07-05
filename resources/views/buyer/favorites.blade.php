@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Favorites') }}
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">
        <div class="mb-4">
            <h3 class="text-dark mb-1">Favorites</h3>
            <p class="text-muted">
                Here are all the products you've added to your favorites.
            </p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if($favorites->isEmpty())
                    <div class="alert alert-info mb-0">You have no favorite products yet.</div>
                @else
                    <div class="row g-3">
                        @foreach($favorites as $product)
                            <div class="col-md-3">
                                <div class="card shadow-sm border-0 h-100">
                                    @if($product->media->first())
                                        <img src="{{ asset('storage/' . $product->media->first()->url) }}" class="card-img-top" alt="{{ $product->name }}">
                                    @endif
                                    <div class="card-body text-center">
                                        <h6 class="card-title">{{ $product->name }}</h6>
                                        <p class="text-muted mb-1">{{ get_currency() }} {{ number_format($product->price, 2) }}</p>
                                        <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn btn-sm btn-outline-success">View</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 