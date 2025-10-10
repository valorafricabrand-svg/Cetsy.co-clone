{{-- resources/views/products/variations.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Variations — ' . ($type->name ?? 'Variation Type'))

@push('styles')
<style>
  .card-rounded { border-radius: 1rem; }
  .list-group-item form + form { margin-left: .5rem; }
  .table td, .table th { vertical-align: middle; }
  .content .page-header { border-bottom: 1px solid rgba(0,0,0,.08); }
  .sticky-actions { position: sticky; bottom: 0; background: #fff; padding: .75rem 0; border-top: 1px solid rgba(0,0,0,.08); z-index: 5; }
</style>
@endpush

@section('content')
<div class="content">
  <div class="container-fluid">

    {{-- Page header / breadcrumbs --}}
    <div class="d-flex align-items-center justify-content-between page-header py-2 mb-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/products') }}">Products</a></li>
            @isset($product)
              <li class="breadcrumb-item">
                <a href="{{ route('products.show', $product) }}">{{ $product->name ?? ('#'.$product->id) }}</a>
              </li>
            @endisset
            <li class="breadcrumb-item active" aria-current="page">Manage: {{ $type->name ?? 'Variation Type' }}</li>
          </ol>
        </nav>
        <h1 class="h4 mt-2 mb-0">Manage: <span class="text-primary">{{ $type->name ?? 'Variation Type' }}</span></h1>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <form method="POST" action="{{ route('variationTypes.affects_price', $type) }}" class="d-flex align-items-center gap-2">
          @csrf
          @method('PATCH')
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ap_header" name="affects_price" value="1" {{ $type->affects_price ? 'checked' : '' }}>
            <label class="form-check-label" for="ap_header">Affects price</label>
          </div>
          <button class="btn btn-sm btn-primary">Save</button>
        </form>
        <a href="{{ route('products.variations', $product) }}" class="btn btn-outline-secondary">Back</a>
      </div>
    </div>

    {{-- Alerts --}}
    @if (session('status'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Please fix the following:</div>
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row g-4">
      {{-- LEFT: Options --}}
      <div class="col-lg-5">
        <div class="card card-rounded shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Options <small class="text-muted">for “{{ $type->name }}”</small></h5>

            <div class="list-group mb-3">
              @forelse($type->options as $opt)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <form class="d-flex align-items-center flex-grow-1"
                        action="{{ route('variationOptions.update', $opt) }}"
                        method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="text"
                           class="form-control form-control-sm me-2"
                           name="value"
                           value="{{ $opt->value }}"
                           placeholder="Option value">
                    <button class="btn btn-sm btn-outline-primary">Save</button>
                  </form>
                  <form action="{{ route('variationOptions.destroy', $opt) }}"
                        method="POST"
                        onsubmit="return confirm('Delete this option?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              @empty
                <div class="list-group-item text-muted">No options yet for this type.</div>
              @endforelse
            </div>

            {{-- Add option --}}
            <form class="border p-3 rounded" action="{{ route('variationOptions.store', $type) }}" method="POST">
              @csrf
              <h6 class="mb-2">Add Option</h6>
              <div class="input-group">
                <input type="text" name="value" class="form-control" placeholder="e.g. Red / 28 inches" required>
                <button class="btn btn-success">Add</button>
              </div>
              <small class="text-muted">Adds a single option to this type.</small>
            </form>
          </div>
        </div>
      </div>

      {{-- RIGHT: Add Variant + existing variants --}}
      <div class="col-lg-7">
        <div class="card card-rounded shadow-sm mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">Add Variant</h5>

            <form
              class="js-add-variant-form"
              method="POST"
              action="{{ route('variations.store', $product) }}"
              data-form-scope="type-{{ $type->id }}"
            >
              @csrf

              {{-- Required: pick one option from the current type --}}
              <div class="mb-3">
                <label class="form-label">Option for “{{ $type->name }}” <span class="text-danger">*</span></label>
                <select class="form-select" name="base_value" required>
                  <option value="" disabled selected>— Select {{ $type->name }} —</option>
                  @foreach($type->options as $opt)
                    <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Optional: pick options from other types to form a combo --}}
              @foreach($otherTypes as $ot)
                <div class="mb-3">
                  <label class="form-label">Option for “{{ $ot->name }}” <span class="text-muted">(optional)</span></label>
                  <select class="form-select" name="extra_values[]">
                    <option value="">— None —</option>
                    @foreach($ot->options as $opt)
                      <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                    @endforeach
                  </select>
                </div>
              @endforeach

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Price</label>
                  <input type="number" step="0.01" min="0" name="price" class="form-control" required>
                </div>
              </div>

              {{-- This container will be filled with values[] by JS on submit --}}
              <div class="d-none" data-values-container></div>

              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-primary">Create Variant</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card card-rounded shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Variants with “{{ $type->name }}”</h5>

            @if($variantsForType->count())
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Combination</th>
                      <th style="width:160px;">Price</th>
                      <th class="text-end" style="width:160px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($variantsForType as $v)
                      @php $formId = 'variant-form-'.$type->id.'-'.$v->id; @endphp
                      <form id="{{ $formId }}" action="{{ route('variations.update', $v) }}" method="POST" class="d-none">
                        @csrf
                        @method('PATCH')
                      </form>
                      <form id="delete-variant-{{ $type->id }}-{{ $v->id }}" action="{{ route('variations.destroy', $v) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                      </form>
                      <tr>
                        <td>
                          <small class="text-muted">
                            {{ $v->options->map(fn($o) => $o->variationType->name . ': ' . $o->value)->join(' • ') }}
                          </small>
                        </td>
                        <td>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control form-control-sm"
                            name="price"
                            value="{{ $v->price }}"
                            form="{{ $formId }}"
                            required>
                        </td>
                        <td class="text-end">
                          <button class="btn btn-sm btn-primary me-1" form="{{ $formId }}">Save</button>
                          <button class="btn btn-sm btn-outline-danger"
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
              <p class="text-muted mb-0">No variants currently use this type.</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Bottom actions --}}
    <div class="sticky-actions mt-4">
      <div class="container-fluid d-flex justify-content-end">
        <a href="{{ route('products.variations', $product) }}" class="btn btn-secondary">Close</a>
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
