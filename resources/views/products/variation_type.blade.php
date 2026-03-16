{{-- resources/views/products/variations.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Manage Variations - ' . ($type->name ?? 'Variation Type'))

@push('styles')
<style>
  .card-rounded { border-radius: 1rem; }
  .table td, .table th { vertical-align: middle; }
  .content .page-header { border-bottom: 1px solid rgba(0,0,0,.08); }
  .sticky-actions { position: sticky; bottom: 0; background: #fff; padding: .75rem 0 calc(.75rem + env(safe-area-inset-bottom)); border-top: 1px solid rgba(0,0,0,.08); z-index: 5; }
</style>
@endpush

@section('main')
@php $tracksStock = $product->type === 'physical'; @endphp
<div class="content">
  <div class="mx-auto w-full px-4 sm:px-6">

    {{-- Page header / breadcrumbs --}}
    <div class="page-header mb-3 flex flex-col gap-3 py-3 lg:flex-row lg:items-center lg:justify-between">
      <div class="min-w-0">
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
        <h1 class="mb-0 mt-2 text-xl font-semibold sm:text-2xl">Manage: <span class="text-emerald-600">{{ $type->name ?? 'Variation Type' }}</span></h1>
      </div>
      <div class="flex w-full flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end lg:w-auto">
        <form method="POST" action="{{ route('variationTypes.affects_price', $type) }}" class="flex w-full flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:w-auto sm:flex-row sm:items-center sm:gap-2 sm:rounded-none sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none">
          @csrf
          @method('PATCH')
          <div class="flex items-center gap-2">
            <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" type="checkbox" id="ap_header" name="affects_price" value="1" {{ $type->affects_price ? 'checked' : '' }}>
            <label class="text-sm text-slate-700" for="ap_header">Affects price</label>
          </div>
          <button class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto sm:py-1.5 sm:text-xs">Save</button>
        </form>
        <a href="{{ route('products.variations', $product) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">Back</a>
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

    <div class="grid grid-cols-12 gap-4 xl:gap-5">
      {{-- LEFT: Options --}}
      <div class="col-span-12 min-w-0 lg:col-span-5">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm card-rounded">
          <div class="p-4 sm:p-5">
            <h5 class="text-lg font-semibold text-slate-900 mb-3">Options <small class="text-slate-500">for "{{ $type->name }}"</small></h5>

            <div class="divide-y divide-slate-200 rounded-xl border border-slate-200 mb-3">
              @forelse($type->options as $opt)
                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                  <form class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center"
                        action="{{ route('variationOptions.update', $opt) }}"
                        method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="text"
                           class="min-w-0 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 sm:px-2.5 sm:py-1.5 sm:text-xs"
                           name="value"
                           value="{{ $opt->value }}"
                           placeholder="Option value">
                    <button class="inline-flex w-full items-center justify-center rounded-xl border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 sm:w-auto sm:py-1.5 sm:text-xs">Save</button>
                  </form>
                  <form action="{{ route('variationOptions.destroy', $opt) }}"
                        method="POST"
                        class="sm:shrink-0"
                        onsubmit="return confirm('Delete this option?')">
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex w-full items-center justify-center rounded-xl border border-rose-600 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 sm:w-auto sm:py-1.5 sm:text-xs" title="Delete">
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
              <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-stretch">
                <input type="text" name="value" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Red / 28 inches" required>
                <button class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">Add</button>
              </div>
              <small class="text-slate-500">Adds a single option to this type.</small>
            </form>
          </div>
        </div>
      </div>

      {{-- RIGHT: Add Variant + existing variants --}}
      <div class="col-span-12 min-w-0 lg:col-span-7">
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
                <div class="col-span-12 md:col-span-{{ $tracksStock ? '6' : '12' }}">
                  <label class="mb-1 block text-sm font-medium text-slate-700">Price</label>
                  <input type="number" step="0.01" min="0" name="price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
                </div>
                @if($tracksStock)
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
                @endif
              </div>

              {{-- This container will be filled with values[] by JS on submit --}}
              <div class="hidden" data-values-container></div>

              <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <button class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto">Create Variant</button>
              </div>
            </form>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm card-rounded">
          <div class="p-4 sm:p-5">
            <h5 class="text-lg font-semibold text-slate-900 mb-3">Variants with "{{ $type->name }}"</h5>

            @if($variantsForType->count())
              <div class="hidden">
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
                @endforeach
              </div>

              <div class="space-y-3 md:hidden">
                @foreach($variantsForType as $v)
                  @php
                    $formId = 'variant-form-'.$type->id.'-'.$v->id;
                    $combo = $v->options->map(fn($o) => $o->variationType->name . ': ' . $o->value)->join(' | ');
                  @endphp
                  <div class="rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <div class="mb-3">
                      <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Combination</div>
                      <div class="mt-1 break-words text-sm font-medium leading-6 text-slate-900">{{ $combo }}</div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 {{ $tracksStock ? 'sm:grid-cols-2' : '' }}">
                      <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Price</label>
                        <input
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                          name="price"
                          value="{{ $v->price }}"
                          form="{{ $formId }}"
                          required>
                      </div>
                      @if($tracksStock)
                        <div>
                          <label class="mb-1 block text-sm font-medium text-slate-700">Stock</label>
                          <input
                            type="number"
                            step="1"
                            min="0"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                            name="stock"
                            value="{{ $v->stock ?? '' }}"
                            placeholder="Unlimited"
                            form="{{ $formId }}">
                        </div>
                      @endif
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                      <button class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 sm:w-auto" form="{{ $formId }}">Save</button>
                      <button class="inline-flex w-full items-center justify-center rounded-xl border border-rose-600 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 sm:w-auto"
                              form="delete-variant-{{ $type->id }}-{{ $v->id }}"
                              onclick="return confirm('Remove this variation? This action cannot be undone.');">
                        Delete
                      </button>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-slate-200 text-sm align-middle">
                  <thead class="bg-slate-50">
                    <tr>
                      <th>Combination</th>
                      <th style="width:160px;">Price</th>
                      @if($tracksStock)
                        <th style="width:140px;">Stock</th>
                      @endif
                      <th class="text-right" style="width:160px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($variantsForType as $v)
                      @php
                        $formId = 'variant-form-'.$type->id.'-'.$v->id;
                        $combo = $v->options->map(fn($o) => $o->variationType->name . ': ' . $o->value)->join(' | ');
                      @endphp
                      <tr>
                        <td>
                          <small class="break-words text-slate-500">
                            {{ $combo }}
                          </small>
                        </td>
                        <td>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                            name="price"
                            value="{{ $v->price }}"
                            form="{{ $formId }}"
                            required>
                        </td>
                        @if($tracksStock)
                          <td>
                            <input
                              type="number"
                              step="1"
                              min="0"
                              class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                              name="stock"
                              value="{{ $v->stock ?? '' }}"
                              placeholder="Unlimited"
                              form="{{ $formId }}">
                          </td>
                        @endif
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
      <div class="mx-auto flex w-full px-4 sm:px-6 sm:justify-end">
        <a href="{{ route('products.variations', $product) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-500 sm:w-auto">Close</a>
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


