@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' | Variations')

@push('styles')
<style>
  .variation-overview-card__actions{
    display:flex;
    flex-direction:column;
    gap:.5rem;
    width:100%;
  }
  .variation-modal-panel{
    width:100%;
    max-height:92vh;
    overflow:hidden;
  }
  .variation-modal-body{
    max-height:calc(92vh - 7.5rem);
    overflow-y:auto;
  }
  @media (min-width: 640px){
    .variation-overview-card__actions{
      width:auto;
      flex-direction:row;
      align-items:center;
      justify-content:flex-end;
    }
  }
</style>
@endpush

@section('main')
@php
  // Eager-load everything needed for types, options and variants
  $product->loadMissing('variations.options.variationType', 'variationTypes.options');
  $variationTypes = $product->variationTypes;
  $current = \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6" x-data="{ manageVariationsModal: false }" @keydown.escape.window="manageVariationsModal = false">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Variations</h1>
              <p class="mt-1 text-sm text-slate-500">Organize variation types, option values, and shopper-facing combinations for this listing.</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
              <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" @click="manageVariationsModal = true">
                <i class="fas fa-sliders-h mr-2"></i> Manage variation types
              </button>
              <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                <i class="fas fa-arrow-left mr-2"></i> Back to Listing
              </a>
            </div>
          </div>
        </div>

        @include('products.partials.edit-tabs', ['product' => $product, 'current' => $current])

  {{-- FLASH + VALIDATION --}}
  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
      <strong>There were some problems with your input.</strong>
      <ul class="mb-0 mt-2 pl-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- QUICK OVERVIEW OF TYPES --}}
  <div class="mb-4">
    @forelse($variationTypes as $type)
      <div class="variation-card mb-2 rounded-2xl border border-slate-200 bg-white shadow-sm" data-type-id="{{ $type->id }}">
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
          <div class="min-w-0 sm:mr-3">
            <h6 class="mb-1">{{ $type->name }}</h6>
            <div class="text-xs text-slate-500">
              {{ $type->options->count() }} {{ \Illuminate\Support\Str::plural('option', $type->options->count()) }}
            </div>
            <div class="mt-2">
              @foreach($type->options->take(6) as $opt)
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 mr-1 mb-1">{{ $opt->value }}</span>
              @endforeach
              @if($type->options->count() > 6)
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200">+{{ $type->options->count() - 6 }} more</span>
              @endif
            </div>
          </div>

          <div class="variation-overview-card__actions">
            <a
              href="{{ route('products.variations.manage', ['product' => $product, 'type' => $type]) }}"
              class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
              Manage
            </a>
            <form
              action="{{ route('variationTypes.destroy', $type) }}"
              method="POST"
              class="variation-delete-form"
              data-type-id="{{ $type->id }}"
              onsubmit="return false;">
              @csrf
              @method('DELETE')
              <button class="inline-flex w-full items-center justify-center rounded-xl border border-rose-600 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 sm:w-auto">Delete</button>
            </form>
          </div>
        </div>
      </div>
    @empty
    <div class="mb-0 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
        No variation types defined yet. Use <strong>Manage variation types</strong> to add some.
      </div>
    @endforelse
  </div>

  {{-- MANAGE VARIATION TYPES MODAL (list + add) --}}
  <div x-cloak x-show="manageVariationsModal" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
    <div class="absolute inset-0 bg-slate-900/50" @click="manageVariationsModal = false"></div>
    <div class="variation-modal-panel relative max-w-5xl rounded-t-3xl border border-slate-200 bg-white shadow-xl sm:rounded-2xl">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900">Manage Variation Types</h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="manageVariationsModal = false">&times;</button>
        </div>

        <div class="variation-modal-body px-4 py-4">
          @forelse($variationTypes as $type)
            <div class="variation-card mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 p-3 sm:flex-row sm:items-start sm:justify-between" data-type-id="{{ $type->id }}">
              <div class="min-w-0 sm:mr-3">
                <strong>{{ $type->name }}</strong>
                <div class="mt-2">
                  @foreach($type->options as $opt)
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 mr-1 mb-1">{{ $opt->value }}</span>
                  @endforeach
                </div>
              </div>
              <form action="{{ route('variationTypes.destroy', $type) }}" method="POST"
                    class="variation-delete-form"
                    data-type-id="{{ $type->id }}"
                    onsubmit="return false;">
                @csrf
                @method('DELETE')
                <button class="inline-flex w-full items-center justify-center rounded-xl border border-rose-600 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 sm:w-auto">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </form>
            </div>
          @empty
            <p class="text-slate-500 mb-0">No variation types found.</p>
          @endforelse

          <hr class="my-4">

          {{-- Add new type --}}
          <form class="rounded-2xl border border-slate-200 p-3 sm:p-4" method="POST" action="{{ route('variationTypes.store', $product) }}">
            @csrf
            <h6 class="mb-3">Add Custom Variation Type</h6>
            <div class="grid grid-cols-12 gap-3">
              <div class="col-span-12 md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                <input name="name" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Length" required>
              </div>
              <div class="col-span-12 md:col-span-8">
                <label class="mb-1 block text-sm font-medium text-slate-700">Options</label>
                <input name="options" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Red,Blue,Green" required>
                <small class="mt-1 text-xs text-slate-500">Separate options with commas.</small>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Add Type</button>
            </div>
          </form>
        </div>

        <div class="flex flex-col-reverse items-stretch gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:justify-end">
          <button type="button" class="inline-flex items-center justify-center rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-500 sm:w-auto" @click="manageVariationsModal = false">Close</button>
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
    </div>
  </div>
</section>
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

    // FIX: Make variation types disappear instantly after delete
    document.querySelectorAll('.variation-delete-form').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!confirm('Delete this variation type?')) return;
        fetch(form.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new URLSearchParams(new FormData(form))
        })
        .then(res => {
          if (res.ok) {
            // Remove all cards with this variation type id
            document.querySelectorAll('.variation-card[data-type-id="' + form.getAttribute('data-type-id') + '"]').forEach(card => card.remove());
          } else {
            alert('Failed to delete. Please refresh and try again.');
          }
        });
      });
    });
  });
</script>
@endpush
