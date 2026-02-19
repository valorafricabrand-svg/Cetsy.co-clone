{{-- resources/views/products/success_deposit_fee.blade.php --}}
@extends('theme.'.theme().'.layouts.app')
@section('title','Payment Successful')

@section('main')
<div class="content flex justify-center">
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="max-width:480px;">
    <div class="border-b border-slate-200 px-4 py-3 bg-success text-white">
      <h4 class="mb-0 text-white">Payment Received!</h4>
    </div>
    <div class="p-4 sm:p-5">
      <p>Your <strong>{{ $planLabel ?? ucfirst($plan) }}</strong> plan payment of 
         <strong>{{ $product->currency }}{{ number_format($amount,2) }}</strong> 
         has been successfully processed.
      </p>
      @if($product->is_active)
        <p>Your listing "<strong>{{ $product->name }}</strong>" is now active until 
           <strong>{{ \Carbon\Carbon::parse($nextDue)->toFormattedDateString() }}</strong>.
        </p>
        <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 w-full">
          View My Listing
        </a>
      @else
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
          Listing not published yet. Please add a featured image, then publish your listing.
        </div>
        <p>Your payment is recorded and your next due date is
           <strong>{{ \Carbon\Carbon::parse($nextDue)->toFormattedDateString() }}</strong>.
        </p>
        <a href="{{ route('products.media', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 w-full mb-2">
          Add Featured Image
        </a>
        <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 w-full">
          Manage Listing
        </a>
      @endif
    </div>
  </div>
</div>
@endsection


