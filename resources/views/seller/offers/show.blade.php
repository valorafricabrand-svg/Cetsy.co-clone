@extends('layouts.app')
@section('title', 'Offer Details')

@section('content')
<div class="content py-4">
    <div class="container-xxl">
        <h2 class="mb-4">Offer Details</h2>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Offer ID</dt>
                    <dd class="col-sm-9">{{ $offer->id }}</dd>

                    <dt class="col-sm-3">Product</dt>
                    <dd class="col-sm-9">
                        {{ $offer->product->name ?? '-' }}<br>
                        <span class="text-muted small">#{{ $offer->product_id }}</span>
                    </dd>

                    <dt class="col-sm-3">Buyer</dt>
                    <dd class="col-sm-9">
                        {{ $offer->user->name ?? '-' }}<br>
                        <span class="text-muted small">{{ $offer->user->email ?? '' }}</span>
                    </dd>

                    <dt class="col-sm-3">Offer Price</dt>
                    <dd class="col-sm-9">{{ get_currency() }} {{ number_format($offer->offer_price, 2) }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9 text-capitalize">{{ $offer->status ?? '-' }}</dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ $offer->created_at ? $offer->created_at->format('d M Y, H:i') : '-' }}</dd>

                    <dt class="col-sm-3">Notes</dt>
                    <dd class="col-sm-9">{{ $offer->notes ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        <a href="{{ route('seller.offers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Offers
        </a>
        <!-- <a href="{{ route('seller.offers.edit', $offer->id) }}" class="btn btn-outline-primary ms-2">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <form action="{{ route('seller.offers.destroy', $offer->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Delete this offer?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger">
                <i class="fas fa-trash-alt me-1"></i> Delete
            </button>
        </form> -->
    </div>
</div>
@endsection 