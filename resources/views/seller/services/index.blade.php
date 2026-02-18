@extends('theme.'.theme().'.layouts.app')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">

  {{-- Header --}}
  <div class="flex flex-col flex-md-row justify-between items-start align-items-md-center mb-4">
    <h2 class="h3 mb-3 mb-md-0">Your Services</h2>

    <div class="flex flex-col flex-sm-row align-items-sm-center gap-2 w-full w-sm-auto">
      <form action="{{ route('seller.services.index') }}" method="GET" class="flex flex-grow-1 flex-sm-grow-0">
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          placeholder="Search services..."
          class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 rounded-start"
        >
        <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 rounded-end ml-0">Search</button>
      </form>

      <a href="{{ route('seller.services.create') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
        <i class="fas fa-plus mr-1"></i> Add New Service
      </a>
    </div>
  </div>

  {{-- Success Message --}}
  @if(session('success'))
    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 mb-4">
      {{ session('success') }}
    </div>
  @endif

  {{-- Products Grid --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-12 row-cols-1 row-cols-sm-2 row-cols-md-3">
    @forelse($services as $service)
    <div class="col-span-12">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full">
        {{-- Product Image --}}
        <div class="position-relative">
          @if($img = $service->media->first())
            <img
              src="{{ asset('storage/'.$img->url) }}"
              alt="{{ $service->name }}"
              class="h-48 w-full object-cover"
              style="height: 200px; object-fit: cover;"
            >
          @else
            <div class="bg-slate-100 text-slate-700 border-slate-200 bg-opacity-10 flex items-center justify-center" style="height: 200px;">
              <i class="fas fa-image fa-2x text-secondary"></i>
            </div>
          @endif
          {{-- Status Badge --}}
          @php
            $statusClasses = [
              'active'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
              'draft'    => 'bg-slate-200 text-slate-700 border-slate-300',
              'pending'  => 'bg-amber-100 text-amber-800 border-amber-200 text-slate-900',
              'archived' => 'bg-rose-100 text-rose-700 border-rose-200'
            ];
          @endphp
          <span class="position-absolute top-0 right-0 m-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $statusClasses[$service->status] ?? 'bg-slate-200 text-slate-700 border-slate-300' }}">
            {{ ucfirst($service->status) }}
          </span>
        </div>

        {{-- Card Body --}}
        <div class="p-4">
          <h5 class="text-base font-bold text-slate-900 text-truncate" title="{{ $service->name }}">
            {{ $service->name }}
          </h5>
          <p class="text-sm text-slate-600 text-slate-500 text-xs mb-2">
            {{ $service->category->name ?? 'Uncategorized' }}
          </p>
          
        </div>

        {{-- Card Footer --}}
        <div class="border-t border-slate-200 px-4 py-3 bg-transparent border-top-0">
          <div class="flex justify-between items-center">
            <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 p-1">
              <a href="{{ route('seller.services.edit', $service->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                <i class="fas fa-edit"></i>
              </a>
              <a href="{{ route('products.show', $service) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">
                <i class="fas fa-eye"></i>
              </a>
            </div>
            <form
              action="{{ route('seller.services.destroy', $service->id) }}"
              method="POST"
              class="inline"
              onsubmit="return confirm('Delete this service?');"
            >
              @csrf
              @method('DELETE')
              <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-rose-600 text-rose-700 hover:bg-rose-50">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="col-span-12">
      <div class="text-center text-slate-500 py-5">
        <i class="fas fa-box-open fa-3x mb-3"></i>
        <p class="h5">No services found.</p>
        <a href="{{ route('seller.services.create') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 mt-3">
          Add Your First Service
        </a>
      </div>
    </div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $services->withQueryString()->links() }}
  </div>

</div>
      </div>
    </div>
  </div>
</section>
@endsection





