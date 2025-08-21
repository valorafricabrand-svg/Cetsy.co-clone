{{-- resources/views/products/variations.blade.php --}}
@extends('layouts.app')

@section('title', $product->name . ' | Variations')

@push('styles')
<style>
  /* Sticky tab header */
  .page-header-sticky {
    position: sticky;
    top: 0; /* adjust if your main navbar is fixed */
    z-index: 1020;
    background: #fff;
    border-bottom: 1px solid rgba(0,0,0,.06);
  }
  /* Horizontal scroll for tabs on small screens */
  .tab-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    white-space: nowrap;
  }
  .tab-scroll .nav-link { border-radius: 999px; }
  .rounded-4 { border-radius: 1rem !important; }

  .table td, .table th { vertical-align: middle; }
  .list-group-item input.form-control-sm { min-width: 140px; }
</style>
@endpush

@section('content')
@php
  // Eager-load everything needed for types, options and variants
  $product->loadMissing('variations.options.variationType', 'variationTypes.options');
  $variationTypes = $product->variationTypes;
  $current = \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<div class="content">

  {{-- ───────── Clickable Tabs Header (navigate to pages) ───────── --}}
  <div class="page-header-sticky">
    <div class="container-fluid px-0">
      <div class="tab-scroll px-2 py-2">
        <ul class="nav nav-pills gap-2 flex-nowrap">
          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.show' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.show', $product) }}">
              <i class="fa-regular fa-circle-question me-1"></i> About
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.pricing' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.pricing', $product) }}">
              <i class="fa-solid fa-tags me-1"></i> Price & Inventory
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.variations' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.variations', $product) }}">
              <i class="fa-solid fa-layer-group me-1"></i> Variations
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.details' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.details', $product) }}">
              <i class="fa-regular fa-rectangle-list me-1"></i> Details
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.shipping' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.shipping', $product) }}">
              <i class="fa-solid fa-truck me-1"></i> Shipping
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.media' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.media', $product) }}">
              <i class="fa-regular fa-images me-1"></i> Media
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link {{ $current === 'products.settings' ? 'active' : 'btn-outline-secondary' }}"
               href="{{ route('products.settings', $product) }}">
              <i class="fa-solid fa-gear me-1"></i> Settings
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- FLASH + VALIDATION --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mt-3" role="alert">
      <strong>There were some problems with your input.</strong>
      <ul class="mb-0 mt-2 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- HEADER --}}
  <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} — Variations</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('products.show', $product) }}" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
      </a>
      <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#manageVariationsModal">
        <i class="fas fa-sliders-h me-1"></i> Manage variation types
      </button>
    </div>
  </div>

  {{-- QUICK OVERVIEW OF TYPES --}}
  <div class="mb-4">
    @forelse($variationTypes as $type)
      <div class="card mb-2 shadow-sm border-0 rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div class="me-3">
            <h6 class="mb-1">{{ $type->name }}</h6>
            <div class="small text-muted">
              {{ $type->options->count() }} {{ \Illuminate\Support\Str::plural('option', $type->options->count()) }}
            </div>
            <div class="mt-2">
              @foreach($type->options->take(6) as $opt)
                <span class="badge bg-light text-dark me-1 mb-1">{{ $opt->value }}</span>
              @endforeach
              @if($type->options->count() > 6)
                <span class="badge bg-secondary">+{{ $type->options->count() - 6 }} more</span>
              @endif
            </div>
          </div>

          <div class="text-end">
      
<a
  href="{{ route('products.variations.manage', ['product' => $product, 'type' => $type]) }}"
  class="btn btn-sm btn-outline-secondary">
  Manage
</a>


            <form
              action="{{ route('variationTypes.destroy', $type) }}"
              method="POST"
              class="d-inline ms-2"
              onsubmit="return confirm('Delete variation type “{{ $type->name }}”? This will also remove its options.')">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="alert alert-info mb-0 rounded-4">
        No variation types defined yet. Use <strong>Manage variation types</strong> to add some.
      </div>
    @endforelse
  </div>

  {{-- MANAGE VARIATION TYPES MODAL (list + add) --}}
  <div class="modal fade" id="manageVariationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title">Manage Variation Types</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          @forelse($variationTypes as $type)
            <div class="mb-4 p-3 border rounded d-flex justify-content-between align-items-start">
              <div class="me-3">
                <strong>{{ $type->name }}</strong>
                <div class="mt-2">
                  @foreach($type->options as $opt)
                    <span class="badge bg-light text-dark me-1 mb-1">{{ $opt->value }}</span>
                  @endforeach
                </div>
              </div>
              <form action="{{ route('variationTypes.destroy', $type) }}" method="POST"
                    onsubmit="return confirm('Delete this variation type and its options?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </form>
            </div>
          @empty
            <p class="text-muted mb-0">No variation types found.</p>
          @endforelse

          <hr class="my-4">

          {{-- Add new type --}}
          <form class="border p-3 rounded" method="POST" action="{{ route('variationTypes.store', $product) }}">
            @csrf
            <h6 class="mb-3">Add Custom Variation Type</h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Name</label>
                <input name="name" type="text" class="form-control" placeholder="e.g. Length" required>
              </div>
              <div class="col-md-8">
                <label class="form-label">Options</label>
                <input name="options" type="text" class="form-control" placeholder="Red,Blue,Green" required>
                <small class="form-text text-muted">Separate options with commas.</small>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-success">Add Type</button>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- PER-TYPE OPTIONS + VARIANTS MODALS (one per type, price-only editing) --}}
  @foreach($variationTypes as $type)
    @php
      $variantsForType = $product->variations->filter(
        fn($v) => $v->options->pluck('variation_type_id')->contains($type->id)
      );
      // Other types (besides the current type) for building combinations in the add-variant form
      $otherTypes = $variationTypes->where('id', '!=', $type->id);
    @endphp



   


  @endforeach
</div>
@endsection

@push('scripts')
<script>
  // Build values[] from base + optional extras for each "Add Variant" form
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.js-add-variant-form').forEach(function(form){
      form.addEventListener('submit', function(e){
        const container = form.querySelector('[data-values-container]');
        if (!container) return;
        container.innerHTML = '';

        const base = form.querySelector('select[name="base_value"]');
        if (!base || !base.value) {
          e.preventDefault();
          alert('Please select an option for the current variation type.');
          return;
        }
        container.appendChild(hidden('values[]', base.value));

        form.querySelectorAll('select[name="extra_values[]"]').forEach(function(sel){
          if (sel.value) container.appendChild(hidden('values[]', sel.value));
        });

        function hidden(name, value){
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          input.value = value;
          return input;
        }
      });
    });
  });
</script>
@endpush
