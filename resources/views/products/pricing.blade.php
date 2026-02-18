@extends('theme.'.theme().'.layouts.app')
@section('title', $product->name . ' | Edit Price & Inventory')

@section('main')
@php $current = \Illuminate\Support\Facades\Route::currentRouteName(); @endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6" x-data="pricingForm()">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Price & Inventory</h1>
              <p class="mt-1 text-sm text-slate-500">Update listing price, discount, stock, and SKU.</p>
            </div>
            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
              <i class="fas fa-arrow-left mr-2"></i> Back to Listing
            </a>
          </div>
        </div>

        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

        @if ($errors->any())
          <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong>Please fix the following errors:</strong>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
          </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
          <form action="{{ route('products.pricing.update', $product) }}" method="POST" novalidate>
            @csrf @method('PATCH')

            <div class="grid grid-cols-12 gap-3">
              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700"
                       x-data="{ t: '{{ $product->type }}' }"
                       x-text="t==='service' ? 'Priced From ({{ get_currency() }})' : 'Price ({{ get_currency() }})'">
                  Price ({{ get_currency() }})
                </label>
                <input type="number" step="0.01" min="0" name="price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('price') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       x-model.number="price" value="{{ old('price', $product->price) }}" required>
                @error('price') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>

              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">% Discount</label>
                <input type="number" step="1" min="0" max="100" name="discount_percent"
                       class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('discount_percent') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       x-model.number="discount" value="{{ old('discount_percent', $product->discount_percent) }}">
                @error('discount_percent') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                <div class="mt-1 text-xs text-slate-500">Final: <strong x-text="formattedFinal()"></strong></div>
              </div>

              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Stock</label>
                <input type="number" step="1" min="0" name="stock" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('stock') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       value="{{ old('stock', $product->stock) }}">
                @error('stock') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>

              <div class="col-span-12 md:col-span-6">
                <label class="mb-1 block text-sm font-medium text-slate-700">SKU (optional)</label>
                <input type="text" name="sku" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('sku') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror"
                       value="{{ old('sku', $product->sku) }}">
                @error('sku') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500"><i class="fas fa-save mr-1"></i> Save</button>
              <a href="{{ route('products.show', $product) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
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
