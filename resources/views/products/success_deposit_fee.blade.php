{{-- resources/views/products/success_deposit_fee.blade.php --}}
@extends('layouts.app')
@section('title','Payment Successful')

@section('content')
<div class="content d-flex justify-content-center">
  <div class="card shadow-sm" style="max-width:480px;">
    <div class="card-header bg-success text-white">
      <h4 class="mb-0 text-white">Payment Received!</h4>
    </div>
    <div class="card-body">
      <p>Your <strong>{{ ucfirst($plan) }}</strong> plan payment of 
         <strong>{{ $product->currency }}{{ number_format($amount,2) }}</strong> 
         has been successfully processed.
      </p>
      @if($product->is_active)
        <p>Your listing "<strong>{{ $product->name }}</strong>" is now active until 
           <strong>{{ \Carbon\Carbon::parse($nextDue)->toFormattedDateString() }}</strong>.
        </p>
        <a href="{{ route('products.show', $product) }}" class="btn btn-primary w-100">
          View My Listing
        </a>
      @else
        <div class="alert alert-warning">
          Listing not published yet. Please add a featured image, then publish your listing.
        </div>
        <p>Your payment is recorded and your next due date is
           <strong>{{ \Carbon\Carbon::parse($nextDue)->toFormattedDateString() }}</strong>.
        </p>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary w-100 mb-2">
          Add Featured Image
        </a>
        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary w-100">
          Manage Listing
        </a>
      @endif
    </div>
  </div>
</div>
@endsection
