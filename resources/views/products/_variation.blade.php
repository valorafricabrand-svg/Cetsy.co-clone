@props(['product'])

@php
    // Eager-load everything needed for types, options and variants
    $product->load('variations.options.variationType', 'variationTypes.options');
    $variationTypes = $product->variationTypes;
@endphp

{{-- FLASH + VALIDATION --}}
@if(session('success'))
  <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800" role="alert">
    {{ session('success') }}
    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
  </div>
@endif
@if ($errors->any())
  <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800" role="alert">
    <strong>There were some problems with your input.</strong>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
  </div>
@endif

{{-- HEADER --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <h3 class="mb-0">Variations</h3>
  <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto" data-ui-toggle="modal" data-ui-target="#manageVariationsModal">
    Manage variation types
  </button>
</div>

{{-- QUICK OVERVIEW OF TYPES --}}
<div class="mb-4">
  @forelse($variationTypes as $type)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-2">
      <div class="p-4 sm:p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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

        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
          {{-- Open the per-type modal for this type --}}
          <button
            class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto"
            data-ui-toggle="modal"
            data-ui-target="#typeOptionsModal{{ $type->id }}">
            Manage
          </button>

          <form
            action="{{ route('variationTypes.destroy', $type) }}"
            method="POST"
            class="w-full sm:ml-2 sm:w-auto"
            onsubmit="return confirm('Delete variation type “{{ $type->name }}”? This will also remove its options.')">
            @csrf
            @method('DELETE')
            <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50 sm:w-auto">Delete</button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-0">
      No variation types defined yet. Use <strong>Manage variation types</strong> to add some.
    </div>
  @endforelse
</div>

{{-- MANAGE VARIATION TYPES MODAL (list + add) --}}
<div class="tw-modal" id="manageVariationsModal" tabindex="-1" aria-hidden="true">
  <div class="tw-modal-dialog tw-modal-lg">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
      <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
        <h5 class="text-base font-semibold text-slate-900">Manage Variation Types</h5>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
      </div>

      <div class="px-4 py-4">
        @forelse($variationTypes as $type)
          <div class="mb-4 p-3 border rounded">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div class="min-w-0 sm:mr-3">
                <strong>{{ $type->name }}</strong>
                <div class="text-xs text-slate-500">Affects price: {{ $type->affects_price ? 'Yes' : 'No' }}</div>
                <div class="mt-2">
                  @foreach($type->options as $opt)
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 mr-1 mb-1">{{ $opt->value }}</span>
                  @endforeach
                </div>
              </div>
              <form action="{{ route('variationTypes.destroy', $type) }}" method="POST"
                    class="w-full sm:w-auto"
                    onsubmit="return confirm('Delete this variation type and its options?')">
                @csrf
                @method('DELETE')
                <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50 sm:w-auto"><i class="fas fa-trash"></i> Delete</button>
              </form>
            </div>

            <div class="mt-3">
              <form class="grid grid-cols-12 gap-4 gap-y-2 gap-x-3 items-center" method="POST" action="{{ route('variationTypes.affects_price', $type) }}">
                @csrf
                @method('PATCH')
                <div class="col-auto">
                  <div class="flex items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" value="1" id="ap_{{ $type->id }}" name="affects_price" {{ $type->affects_price ? 'checked' : '' }}>
                    <label class="text-sm text-slate-700" for="ap_{{ $type->id }}">
                      Affects price
                    </label>
                  </div>
                </div>
                <div class="col-auto">
                  <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500">Save</button>
                </div>
              </form>
            </div>
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
          <div class="mt-3 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
              <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" value="1" id="affects_price_new" name="affects_price">
              <label class="text-sm text-slate-700" for="affects_price_new">Affects price</label>
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 sm:w-auto">Add Type</button>
          </div>
        </form>
      </div>

      <div class="flex flex-col-reverse items-stretch gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:justify-end">
        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- PER-TYPE OPTIONS + VARIANTS MODALS (one per type, WITHOUT SKU/STOCK fields) --}}
@foreach($variationTypes as $type)
  @php
      $variantsForType = $product->variations->filter(
          fn($v) => $v->options->pluck('variation_type_id')->contains($type->id)
      );
      // Other types (besides the current type) for building combinations in the add-variant form
      $otherTypes = $variationTypes->where('id', '!=', $type->id);
  @endphp

  <div class="tw-modal" id="typeOptionsModal{{ $type->id }}" tabindex="-1" aria-hidden="true">
    <div class="tw-modal-dialog tw-modal-lg">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900">Manage: {{ $type->name }}</h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
        </div>

        <div class="px-4 py-4">
          <div class="grid grid-cols-12 gap-4">
            {{-- LEFT: Options --}}
            <div class="col-span-12 lg:col-span-5">
              <h6 class="mb-3">Options</h6>

              <div class="divide-y divide-slate-200 rounded-xl border border-slate-200 mb-3">
                @foreach($type->options as $opt)
                  <div class="px-4 py-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <form class="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row sm:items-center" action="{{ route('variationOptions.update', $opt) }}" method="POST">
                      @csrf
                      @method('PATCH')
                      <input type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs sm:mr-2" name="value" value="{{ $opt->value }}">
                      <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50 sm:w-auto">Save</button>
                    </form>
                    <form action="{{ route('variationOptions.destroy', $opt) }}" method="POST"
                          class="w-full sm:w-auto"
                          onsubmit="return confirm('Delete this option?')">
                      @csrf
                      @method('DELETE')
                      <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50 sm:w-auto"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                @endforeach
              </div>

              {{-- Add option --}}
              <form class="border p-3 rounded" action="{{ route('variationOptions.store', $type) }}" method="POST">
                @csrf
                <h6 class="mb-2">Add Option</h6>
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-stretch">
                  <input type="text" name="value" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Red / 28 inches" required>
                  <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 sm:w-auto">Add</button>
                </div>
                <small class="text-slate-500">Adds a single option to this type.</small>
              </form>
            </div>

            {{-- RIGHT: Add Variant (only PRICE) + list existing (inline PRICE only) --}}
            <div class="col-span-12 lg:col-span-7">
              <div class="border rounded p-3 mb-4">
                <h6 class="mb-3">Add Variant</h6>
                <form
                  class="js-add-variant-form"
                  method="POST"
                  action="{{ route('variations.store', $product) }}"
                  data-form-scope="type-{{ $type->id }}"
                >
                  @csrf

                  {{-- Required: pick one option from the current type --}}
                  <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Option for “{{ $type->name }}” <span class="text-rose-600">*</span></label>
                    <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" name="base_value" required>
                      <option value="" disabled selected>— Select {{ $type->name }} —</option>
                      @foreach($type->options as $opt)
                        <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                      @endforeach
                    </select>
                  </div>

                  {{-- Optional: pick options from other types to form a combo --}}
                  @foreach($otherTypes as $ot)
                    <div class="mb-3">
                      <label class="mb-1 block text-sm font-medium text-slate-700">Option for “{{ $ot->name }}” <span class="text-slate-500">(optional)</span></label>
                      <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" name="extra_values[]">
                        <option value="">— None —</option>
                        @foreach($ot->options as $opt)
                          <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                        @endforeach
                      </select>
                    </div>
                  @endforeach

                  <div class="grid grid-cols-12 gap-4 gap-3">
                    <div class="col-span-12 md:col-span-6">
                      <label class="mb-1 block text-sm font-medium text-slate-700">Price</label>
                      <input type="number" step="0.01" min="0" name="price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                      <label class="mb-1 block text-sm font-medium text-slate-700">Stock</label>
                      <input
                        type="number"
                        step="1"
                        min="0"
                        name="stock"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                        value="{{ old('stock', 0) }}"
                        placeholder="Unlimited">
                    </div>
                  </div>

                  {{-- This container will be filled with values[] by JS on submit --}}
                  <div class="hidden" data-values-container></div>

                  <div class="mt-3 flex sm:justify-end">
                    <button class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 sm:w-auto">Create Variant</button>
                  </div>
                </form>
              </div>

              <h6 class="mb-3">Variants with “{{ $type->name }}”</h6>
              @php
                  $variantsForType = $product->variations->filter(
                      fn($v) => $v->options->pluck('variation_type_id')->contains($type->id)
                  );
              @endphp

              @if($variantsForType->count())
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
                    <thead class="bg-slate-50">
                      <tr>
                        <th>Combination</th>
                        <th style="width:160px;">Price</th>
                        <th style="width:140px;">Stock</th>
                        <th class="text-right" style="width:160px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($variantsForType as $v)
                        @php $formId = 'variant-form-'.$type->id.'-'.$v->id; @endphp
                        <form id="{{ $formId }}" action="{{ route('variations.update', $v) }}" method="POST" class="hidden">
                          @csrf
                          @method('PATCH')
                        </form>
                        <form id="delete-variant-{{ $type->id }}-{{ $v->id }}" action="{{ route('variations.destroy', $v) }}" method="POST" class="hidden">
                          @csrf
                          @method('DELETE')
                        </form>
                        <tr>
                          <td>
                            <small class="text-slate-500">
                              {{ $v->options->map(fn($o)=>$o->variationType->name.': '.$o->value)->join(' • ') }}
                            </small>
                          </td>
                          <td>
                            <input type="number" step="0.01" min="0" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs"
                                   name="price" value="{{ $v->price }}" form="{{ $formId }}" required>
                          </td>
                          <td>
                            <input
                              type="number"
                              step="1"
                              min="0"
                              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs"
                              name="stock"
                              value="{{ $v->stock ?? '' }}"
                              placeholder="Unlimited"
                              form="{{ $formId }}">
                          </td>
                          <td class="text-right">
                            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500 mr-1" form="{{ $formId }}">Save</button>
                            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50"
                                    form="delete-variant-{{ $type->id }}-{{ $v->id }}"
                                    onclick="return confirm('Remove this variation? This action cannot be undone.');">
                              Delete
                            </button>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <p class="text-slate-500 mb-0">No variants currently use this type.</p>
              @endif
            </div>
          </div>
        </div>

        <div class="flex flex-col-reverse items-stretch gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:justify-end">
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

{{-- Vanilla JS: build values[] for add-variant forms --}}
<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.js-add-variant-form').forEach(function(form){
      form.addEventListener('submit', function(e){
        // Build values[] from base + optional extras
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
