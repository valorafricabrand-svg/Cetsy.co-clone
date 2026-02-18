@extends('theme.'.theme().'.layouts.app')

@section('title', $product->name . ' | Variations')

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

      <div class="space-y-6">
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

  {{-- HEADER --}}
  <div class="flex justify-between items-center mt-3 mb-3">
    <h2 class="mb-0">{{ $product->name }} - Variations</h2>
    <div class="flex gap-2">
      <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-900 text-slate-900 hover:bg-slate-100 px-3 py-1.5 text-xs">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
      <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs" data-bs-toggle="modal" data-bs-target="#manageVariationsModal">
        <i class="fas fa-sliders-h mr-1"></i> Manage variation types
      </button>
    </div>
  </div>

  {{-- QUICK OVERVIEW OF TYPES --}}
  <div class="mb-4">
    @forelse($variationTypes as $type)
      <div class="variation-card mb-2 rounded-2xl border border-slate-200 bg-white shadow-sm" data-type-id="{{ $type->id }}">
        <div class="p-4 sm:p-5 flex justify-between items-center">
          <div class="mr-3">
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

          <div class="text-right">
            <a
              href="{{ route('products.variations.manage', ['product' => $product, 'type' => $type]) }}"
              class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50">
              Manage
            </a>
            <form
              action="{{ route('variationTypes.destroy', $type) }}"
              method="POST"
              class="ml-2 inline-block variation-delete-form"
              data-type-id="{{ $type->id }}"
              onsubmit="return false;">
              @csrf
              @method('DELETE')
              <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50">Delete</button>
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
  <div class="modal" id="manageVariationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900">Manage Variation Types</h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-bs-dismiss="modal"></button>
        </div>

        <div class="px-4 py-4">
          @forelse($variationTypes as $type)
            <div class="mb-4 p-3 border rounded flex justify-between items-start variation-card" data-type-id="{{ $type->id }}">
              <div class="mr-3">
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
                <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </form>
            </div>
          @empty
            <p class="text-slate-500 mb-0">No variation types found.</p>
          @endforelse

          <hr class="my-4">

          {{-- Add new type --}}
          <form class="border p-3 rounded" method="POST" action="{{ route('variationTypes.store', $product) }}">
            @csrf
            <h6 class="mb-3">Add Custom Variation Type</h6>
            <div class="grid grid-cols-12 gap-4 gap-3">
              <div class="md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                <input name="name" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Length" required>
              </div>
              <div class="md:col-span-8">
                <label class="mb-1 block text-sm font-medium text-slate-700">Options</label>
                <input name="options" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Red,Blue,Green" required>
                <small class="mt-1 text-xs text-slate-500 text-slate-500">Separate options with commas.</small>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Add Type</button>
            </div>
          </form>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-bs-dismiss="modal">Close</button>
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

