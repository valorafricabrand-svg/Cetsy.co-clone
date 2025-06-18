@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('My Wishlist') }}
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">

        <div class="mb-4">
            <h4 class="text-dark">Wishlist</h4>
            <p class="text-muted">Here are the items you’ve added to your wishlist.</p>
        </div>

        @if($wishlistItems->isEmpty())
            <div class="alert alert-warning">
                Your wishlist is empty.
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach($wishlistItems as $item)
                    @php
                        $product = $item->product;
                    @endphp
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="text-decoration-none">
                                @if($product->media->first())
                                    <img src="{{ asset('storage/' . $product->media->first()->file_path) }}"
                                         class="card-img-top"
                                         alt="{{ $product->name }}">
                                @else
                                    <img src="{{ asset('images/no-image.png') }}" class="card-img-top" alt="No Image">
                                @endif
                            </a>
                            <div class="card-body">
                                <h5 class="card-title text-dark">{{ $product->name }}</h5>
                                <p class="card-text text-muted mb-1">
                                    {{ get_currency() }} {{ number_format($product->price, 2) }}
                                </p>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center bg-white border-0">
                                <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                                <form method="POST" action="{{ route('wishlist.remove', $item->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
