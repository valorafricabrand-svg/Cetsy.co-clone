@extends('theme.'.theme().'.layouts.app')
@section('title','Inventory Report')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
  <div class="flex justify-between items-center mb-3">
    <h2 class="mb-0">Inventory Report</h2>
    <a href="{{ route('seller.analytics.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg"><i class="fas fa-chart-line mr-1"></i> Analytics</a>
  </div>

  @if(!$shopId)
    <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">You don’t have a shop set up yet. Create your shop to see inventory insights.</div>
  @else

  <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">This report shows stock issues for your listings only.</div>

  {{-- Negative product stock --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="border-b border-slate-200 px-4 py-3 bg-rose-100 text-rose-800 border-rose-200 text-white">Negative product stock (critical)</div>
    <div class="p-4 p-0">
      @if($productsNegative->isEmpty())
        <div class="p-3 text-slate-500">No issues found.</div>
      @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm table-sm mb-0 align-middle">
          <thead class="bg-slate-50 text-slate-600"><tr><th>ID</th><th>Name</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($productsNegative as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td class="text-rose-600 font-semibold">{{ $p->stock }}</td>
              <td><a href="{{ route('products.show', $p) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Edit</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Negative variant stock --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="border-b border-slate-200 px-4 py-3 bg-rose-100 text-rose-800 border-rose-200 text-white">Negative variant stock (critical)</div>
    <div class="p-4 p-0">
      @if($variantsNegative->isEmpty())
        <div class="p-3 text-slate-500">No issues found.</div>
      @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm table-sm mb-0 align-middle">
          <thead class="bg-slate-50 text-slate-600"><tr><th>Variant ID</th><th>Product</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($variantsNegative as $v)
            <tr>
              <td>#{{ $v->id }}</td>
              <td>{{ $v->product->name ?? '—' }}</td>
              <td class="text-rose-600 font-semibold">{{ $v->stock }}</td>
              <td>
                @if($v->product)
                  <a href="{{ route('products.variations', $v->product) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Manage Variations</a>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Non-physical with stock --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="border-b border-slate-200 px-4 py-3 bg-amber-100 text-amber-800 border-amber-200">Non-physical items with stock set (warning)</div>
    <div class="p-4 p-0">
      @if($nonPhysicalWithStock->isEmpty())
        <div class="p-3 text-slate-500">No issues found.</div>
      @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm table-sm mb-0 align-middle">
          <thead class="bg-slate-50 text-slate-600"><tr><th>ID</th><th>Name</th><th>Type</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($nonPhysicalWithStock as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td>{{ ucfirst($p->type ?? '—') }}</td>
              <td>{{ $p->stock }}</td>
              <td><a href="{{ route('products.show', $p) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Open</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Physical without tracked stock --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="border-b border-slate-200 px-4 py-3 bg-amber-100 text-amber-800 border-amber-200">Physical listings without tracked stock (warning)</div>
    <div class="p-4 p-0">
      @if($physicalUntracked->isEmpty())
        <div class="p-3 text-slate-500">No issues found.</div>
      @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm table-sm mb-0 align-middle">
          <thead class="bg-slate-50 text-slate-600"><tr><th>ID</th><th>Name</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($physicalUntracked as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td>—</td>
              <td><a href="{{ route('products.settings', $p) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Settings</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- Active but out of stock --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 text-slate-700 text-white">Active listings but out of stock (attention)</div>
    <div class="p-4 p-0">
      @if($activeButOut->isEmpty())
        <div class="p-3 text-slate-500">No issues found.</div>
      @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm table-sm mb-0 align-middle">
          <thead class="bg-slate-50 text-slate-600"><tr><th>ID</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          @foreach($activeButOut as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td><span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">Active</span></td>
              <td>
                <a href="{{ route('products.show', $p) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">Open</a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  @endif
</div>
      </div>
    </div>
  </div>
</section>
@endsection





