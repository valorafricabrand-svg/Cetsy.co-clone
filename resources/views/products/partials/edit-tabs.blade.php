@php
  $current = $current ?? \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<div class="sticky top-16 z-20 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
  <div class="overflow-x-auto">
    <div class="flex min-w-max gap-2">
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.show' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.show', $product) }}"><i class="fa-regular fa-circle-question mr-1"></i> About</a>
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.pricing' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.pricing', $product) }}"><i class="fa-solid fa-tags mr-1"></i> Price & Inventory</a>
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.variations' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.variations', $product) }}"><i class="fa-solid fa-layer-group mr-1"></i> Variations</a>
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.details' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.details', $product) }}"><i class="fa-regular fa-rectangle-list mr-1"></i> Details</a>
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.shipping' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.shipping', $product) }}"><i class="fa-solid fa-truck mr-1"></i> Shipping</a>
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.media' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.media', $product) }}"><i class="fa-regular fa-images mr-1"></i> Media</a>
      <a class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === 'products.settings' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}" href="{{ route('products.settings', $product) }}"><i class="fa-solid fa-gear mr-1"></i> Settings</a>
    </div>
  </div>
</div>
