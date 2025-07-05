@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Offers') }}
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="container-xxl">
        <div class="mb-4">
            <h3 class="text-dark mb-1">Your Offers</h3>
            <p class="text-muted">
                Here are all the offers you've made on products.
            </p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if($offers->isEmpty())
                    <div class="alert alert-info mb-0">You have not made any offers yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Offer Price</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offers as $offer)
                                    <tr>
                                        <td>{{ $offer->id }}</td>
                                        <td>
                                            @if($offer->product)
                                                <a href="{{ route('products.show', $offer->product->slug ?? $offer->product->id) }}">{{ $offer->product->name }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ get_currency() }} {{ number_format($offer->offer_price, 2) }}</td>
                                        <td class="text-capitalize">{{ $offer->status }}</td>
                                        <td>{{ $offer->created_at ? $offer->created_at->format('d M Y, H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 