@props(['product'])

@php
    // Eager-load everything needed for types, options and variants
    $product->load('variations.options.variationType', 'variationTypes.options');
    $variationTypes = $product->variationTypes;
@endphp

{{-- FLASH + VALIDATION --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>There were some problems with your input.</strong>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- HEADER --}}
<div class="mb-4 d-flex justify-content-between align-items-center">
  <h3 class="mb-0">Variations</h3>
  <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manageVariationsModal">
    Manage variation types
  </button>
</div>

{{-- QUICK OVERVIEW OF TYPES --}}
<div class="mb-4">
  @forelse($variationTypes as $type)
    <div class="card mb-2 shadow-sm">
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
          {{-- Open the per-type modal for this type --}}
          <button
            class="btn btn-sm btn-outline-secondary"
            data-bs-toggle="modal"
            data-bs-target="#typeOptionsModal{{ $type->id }}">
            Manage
          </button>

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
    <div class="alert alert-info mb-0">
      No variation types defined yet. Use <strong>Manage variation types</strong> to add some.
    </div>
  @endforelse
</div>

{{-- MANAGE VARIATION TYPES MODAL (list + add) --}}
<div class="modal fade" id="manageVariationsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
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
              <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Delete</button>
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

{{-- PER-TYPE OPTIONS + VARIANTS MODALS (one per type, WITHOUT SKU/STOCK fields) --}}
@foreach($variationTypes as $type)
  @php
      $variantsForType = $product->variations->filter(
          fn($v) => $v->options->pluck('variation_type_id')->contains($type->id)
      );
      // Other types (besides the current type) for building combinations in the add-variant form
      $otherTypes = $variationTypes->where('id', '!=', $type->id);
  @endphp

  <div class="modal fade" id="typeOptionsModal{{ $type->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Manage: {{ $type->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            {{-- LEFT: Options --}}
            <div class="col-lg-5">
              <h6 class="mb-3">Options</h6>

              <div class="list-group mb-3">
                @foreach($type->options as $opt)
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <form class="d-flex align-items-center" action="{{ route('variationOptions.update', $opt) }}" method="POST">
                      @csrf
                      @method('PATCH')
                      <input type="text" class="form-control form-control-sm me-2" name="value" value="{{ $opt->value }}">
                      <button class="btn btn-sm btn-outline-primary">Save</button>
                    </form>
                    <form action="{{ route('variationOptions.destroy', $opt) }}" method="POST"
                          onsubmit="return confirm('Delete this option?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                @endforeach
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

            {{-- RIGHT: Add Variant (only PRICE) + list existing (inline PRICE only) --}}
            <div class="col-lg-7">
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

              <h6 class="mb-3">Variants with “{{ $type->name }}”</h6>
              @php
                  $variantsForType = $product->variations->filter(
                      fn($v) => $v->options->pluck('variation_type_id')->contains($type->id)
                  );
              @endphp

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
                              {{ $v->options->map(fn($o)=>$o->variationType->name.': '.$o->value)->join(' • ') }}
                            </small>
                          </td>
                          <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                   name="price" value="{{ $v->price }}" form="{{ $formId }}" required>
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

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
