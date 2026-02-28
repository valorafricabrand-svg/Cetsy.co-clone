{{-- resources/views/products/variations.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Manage Variations - ' . ($type->name ?? 'Variation Type'))

@push('styles')
<style>
  .card-rounded { border-radius: 1rem; }
  .table td, .table th { vertical-align: middle; }
  .content .page-header { border-bottom: 1px solid rgba(0,0,0,.08); }
  .sticky-actions { position: sticky; bottom: 0; background: #fff; padding: .75rem 0; border-top: 1px solid rgba(0,0,0,.08); z-index: 5; }
</style>
@endpush

@section('main')
<div class="content">
  <div class="mx-auto w-full px-4 sm:px-6">

    {{-- Page header / breadcrumbs --}}
    <div class="flex items-center justify-between page-header py-2 mb-3">
      <div>
        <nav class="text-xs text-slate-500" aria-label="Breadcrumb">
          <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ url('/products') }}" class="hover:text-slate-700">Products</a></li>
            @isset($product)
              <li>/</li>
              <li><a href="{{ route('products.show', $product) }}" class="hover:text-slate-700">{{ $product->name ?? ('#'.$product->id) }}</a></li>
            @endisset
            <li>/</li>
            <li class="font-semibold text-slate-700" aria-current="page">Manage: {{ $type->name ?? 'Variation Type' }}</li>
          </ol>
        </nav>
        <h1 class="text-lg font-semibold mt-2 mb-0">Manage: <span class="text-emerald-600">{{ $type->name ?? 'Variation Type' }}</span></h1>
      </div>
      <div class="flex gap-2 items-center">
        <form method="POST" action="{{ route('variationTypes.affects_price', $type) }}" class="flex items-center gap-2">
          @csrf
          @method('PATCH')
          <div class="flex items-center gap-2">
            <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" id="ap_header" name="affects_price" value="1" {{ $type->affects_price ? 'checked' : '' }}>
            <label class="text-sm text-slate-700" for="ap_header">Affects price</label>
          </div>
          <button class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">Save</button>
        </form>
        <a href="{{ route('products.variations', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Back</a>
      </div>
    </div>

    {{-- Alerts --}}
    @if (session('status'))
      <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800" role="alert">
        {{ session('status') }}
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
      </div>
    @endif
    @if (session('success'))
      <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800" role="alert">
        {{ session('success') }}
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
      </div>
    @endif
    @if ($errors->any())
      <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
        <div class="font-semibold mb-1">Please fix the following:</div>
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="grid grid-cols-12 gap-4">
      {{-- LEFT: Options --}}
      <div class="lg:col-span-5">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm card-rounded">
          <div class="p-4 sm:p-5">
            <h5 class="text-lg font-semibold text-slate-900 mb-3">Options <small class="text-slate-500">for "{{ $type->name }}"</small></h5>

            <div class="divide-y divide-slate-200 rounded-xl border border-slate-200 mb-3">
              @forelse($type->options as $opt)
                <div class="px-4 py-3 flex justify-between items-center">
                  <form class="flex flex-1 items-center"
                        action="{{ route('variationOptions.update', $opt) }}"
                        method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="text"
                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs mr-2"
                           name="value"
                           value="{{ $opt->value }}"
                           placeholder="Option value">
                    <button class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">Save</button>
                  </form>
                  <form action="{{ route('variationOptions.destroy', $opt) }}"
                        method="POST"
                        onsubmit="return confirm('Delete this option?')">
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex items-center justify-center rounded-xl border border-rose-600 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              @empty
                <div class="px-4 py-3 text-slate-500">No options yet for this type.</div>
              @endforelse
            </div>

            {{-- Add option --}}
            <form class="rounded-xl border border-slate-200 p-3" action="{{ route('variationOptions.store', $type) }}" method="POST">
              @csrf
              <h6 class="mb-2">Add Option</h6>
              <div class="flex w-full items-stretch">
                <input type="text" name="value" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Red / 28 inches" required>
                <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Add</button>
              </div>
              <small class="text-slate-500">Adds a single option to this type.</small>
            </form>
          </div>
        </div>
      </div>

      {{-- RIGHT: Add Variant + existing variants --}}
      <div class="lg:col-span-7">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm card-rounded mb-4">
          <div class="p-4 sm:p-5">
            <h5 class="text-lg font-semibold text-slate-900 mb-3">Add Variant</h5>

            <form
              class="js-add-variant-form"
              method="POST"
              action="{{ route('variations.store', $product) }}"
              data-form-scope="type-{{ $type->id }}"
            >
              @csrf

              {{-- Required: pick one option from the current type --}}
              <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Option for "{{ $type->name }}" <span class="text-rose-600">*</span></label>
                <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" name="base_value" required>
                  <option value="" disabled selected>- Select {{ $type->name }} -</option>
                  @foreach($type->options as $opt)
                    <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Optional: pick options from other types to form a combo --}}
              @foreach($otherTypes as $ot)
                <div class="mb-3">
                  <label class="mb-1 block text-sm font-medium text-slate-700">Option for "{{ $ot->name }}" <span class="text-slate-500">(optional)</span></label>
                  <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" name="extra_values[]">
                    <option value="">- None -</option>
                    @foreach($ot->options as $opt)
                      <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                    @endforeach
                  </select>
                </div>
              @endforeach

              <div class="grid grid-cols-12 gap-3">
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

              <div class="mt-3 flex justify-end">
                <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Create Variant</button>
              </div>
            </form>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm card-rounded">
          <div class="p-4 sm:p-5">
            <h5 class="text-lg font-semibold text-slate-900 mb-3">Variants with "{{ $type->name }}"</h5>

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
                            {{ $v->options->map(fn($o) => $o->variationType->name . ': ' . $o->value)->join(' • ') }}
                          </small>
                        </td>
                        <td>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs"
                            name="price"
                            value="{{ $v->price }}"
                            form="{{ $formId }}"
                            required>
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
                          <button class="mr-1 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500" form="{{ $formId }}">Save</button>
                          <button class="inline-flex items-center justify-center rounded-xl border border-rose-600 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
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
    </div>

    {{-- Bottom actions --}}
    <div class="sticky-actions mt-4">
      <div class="mx-auto w-full px-4 sm:px-6 flex justify-end">
        <a href="{{ route('products.variations', $product) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500">Close</a>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  /**
   * Convert the selected base_value and extra_values[] into values[] inputs
   * before submit. Mirrors the original modal behavior.
   */
  document.querySelectorAll('.js-add-variant-form').forEach(function(form) {
    form.addEventListener('submit', function () {
      const base = form.querySelector('select[name="base_value"]');
      if (!base || !base.value) return; // HTML5 'required' handles empty

      const container = form.querySelector('[data-values-container]');
      if (!container) return;
      container.innerHTML = '';

      // Add base value
      container.appendChild(makeHidden('values[]', base.value));

      // Add extras if chosen
      form.querySelectorAll('select[name="extra_values[]"]').forEach(function(sel) {
        if (sel.value) container.appendChild(makeHidden('values[]', sel.value));
      });
    });
  });

  function makeHidden(name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
  }
</script>
@endpush



