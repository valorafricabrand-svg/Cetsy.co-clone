@extends('theme.'.theme().'.layouts.app')
@section('title', $product->name . ' | Edit Price & Inventory')

@push('styles')
<style>
  .page-header-sticky{position:sticky;top:0;z-index:1020;background:#fff;border-bottom:1px solid rgba(0,0,0,.06)}
  .tab-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
  .tab-scroll .nav-link{border-radius:999px}
  .rounded-4,.rounded-top-4{border-radius:1rem!important}
</style>
@endpush

@section('main')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<div class="content">
  {{-- Header tabs --}}
  <div class="page-header-sticky">
    <div class="mx-auto w-full px-4 sm:px-6 px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class=""><a class="nav-link {{ $current==='products.show' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question mr-1"></i> About</a></li>
          <li class=""><a class="nav-link {{ $current==='products.pricing' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags mr-1"></i> Price & Inventory</a></li>
          <li class=""><a class="nav-link {{ $current==='products.variations' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group mr-1"></i> Variations</a></li>
          <li class=""><a class="nav-link {{ $current==='products.details' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list mr-1"></i> Details</a></li>
          <li class=""><a class="nav-link {{ $current==='products.shipping' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck mr-1"></i> Shipping</a></li>
          <li class=""><a class="nav-link {{ $current==='products.media' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.media', $product) }}"><i class="fa-regular fa-images mr-1"></i> Media</a></li>
          <li class=""><a class="nav-link {{ $current==='products.settings' ? 'active' : 'btn-outline-secondary' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear mr-1"></i> Settings</a></li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800 mt-3">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 mb-0 pl-3">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
  @endif

  {{-- Heading --}}
  <div class="flex justify-between items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} â€” Edit Price & Inventory</h2>
    <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-900 text-slate-900 hover:bg-slate-100 px-3 py-1.5 text-xs"><i class="fas fa-arrow-left mr-1"></i>Back</a>
  </div>

  {{-- Form --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-0 rounded-4">
    <div class="p-4 sm:p-5">
      <form action="{{ route('products.pricing.update', $product) }}" method="POST" novalidate x-data="pricingForm()">
        @csrf @method('PATCH')

        <div class="grid grid-cols-12 gap-4 gap-3">
          <div class="md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700"
                   x-data="{ t: '{{ $product->type }}' }"
                   x-text="t==='service' ? 'Priced From ({{ get_currency() }})' : 'Price ({{ get_currency() }})'">
              Price ({{ get_currency() }})
            </label>
            <input type="number" step="0.01" min="0" name="price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   x-model.number="price" value="{{ old('price', $product->price) }}" required>
            @error('price') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">% Discount</label>
            <input type="number" step="1" min="0" max="100" name="discount_percent"
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('discount_percent') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   x-model.number="discount" value="{{ old('discount_percent', $product->discount_percent) }}">
            @error('discount_percent') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            <div class="mt-1 text-xs text-slate-500">Final: <strong x-text="formattedFinal()"></strong></div>
          </div>

          <div class="md:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">Stock</label>
            <input type="number" step="1" min="0" name="stock" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('stock') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   value="{{ old('stock', $product->stock) }}">
            @error('stock') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="md:col-span-6">
            <label class="mb-1 block text-sm font-medium text-slate-700">SKU (optional)</label>
            <input type="text" name="sku" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('sku') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                   value="{{ old('sku', $product->sku) }}">
            @error('sku') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500"><i class="fas fa-save mr-1"></i> Save</button>
          <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function pricingForm(){
  return {
    price: Number('{{ old('price', $product->price ?? 0) }}') || 0,
    discount: Number('{{ old('discount_percent', $product->discount_percent ?? 0) }}') || 0,
    finalAmount(){ const p=Number(this.price)||0, d=Math.min(100,Math.max(0,Number(this.discount)||0)); return p*(1-d/100); },
    formattedFinal(){ return '{{ get_currency() }} ' + (this.finalAmount().toFixed(2)); }
  }
}
</script>
@endpush


