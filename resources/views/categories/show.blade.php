{{-- resources/views/admin/categories/show.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Category / {{ $category->name }}
  </h2>
@endsection

@section('content')
<div class="content" x-data="attributeManager()">
  <div class="py-6">
    <div class="container-lg">

      {{-- Flash --}}
      @foreach(['success','info','warning','danger'] as $msg)
        @if(session()->has($msg))
          <div class="alert alert-{{ $msg }} alert-dismissible fade show" role="alert">
            {{ session($msg) }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif
      @endforeach

      {{-- NAV BUTTONS --}}
      <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
          ← Back to list
        </a>

        {{-- NEW: Edit Category button --}}
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
          ✎ Edit Category
        </a>
      </div>

      {{-- Category card --}}
      <div class="card shadow-sm mb-5">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            @if($category->image)
              <img src="{{ asset('storage/'.$category->image) }}"
                   class="rounded-circle me-3" style="width:60px;height:60px;object-fit:cover;">
            @endif
            <h3 class="mb-0">{{ $category->name }}</h3>
          </div>
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
            <tbody>
              <tr><th>Slug</th><td>{{ $category->slug }}</td></tr>
              <tr><th>Parent</th><td>{{ $category->parent?->name ?? '—' }}</td></tr>
              <tr><th>Listing fee</th><td>{{ get_currency() }} {{ number_format($category->listing_fee,2) }}</td></tr>
              <tr><th>Created</th><td>{{ $category->created_at }}</td></tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- OPTIONS / “VARIATIONS” TEMPLATE ------------------------------------------------ --}}
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">Option Template (Variations)</h4>
        <button class="btn btn-sm btn-primary" @click="openAddModal">
          + Add Option
        </button>
      </div>

      @if($category->attributes->isEmpty())
        <p class="text-muted">No options added for this category yet.</p>
      @else
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:35%">Option Name</th>
                <th>Values</th>
                <th style="width:180px" class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($category->attributes as $attr)
                <tr>
                  <td>{{ $attr->name }}</td>
                  <td>
                    @forelse($attr->values as $val)
                      <span class="badge bg-secondary me-1">{{ $val->value }}</span>
                    @empty
                      <span class="text-muted">—</span>
                    @endforelse
                  </td>
                  <td class="text-end">
                    <span class="badge bg-info cursor-pointer me-1"
                          @click="openEditModal({{ $attr->id }}, '{{ $attr->name }}', {{ $attr->values->pluck('value') }})">
                      Edit
                    </span>
                    <form action="{{ route('admin.category-attributes.destroy', $attr) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this option?');">
                      @csrf @method('DELETE')
                      <button class="badge bg-danger border-0">Delete</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

    </div>
  </div>

  {{-- MODAL (Add / Edit) --}}
  <div class="modal fade" id="attributeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form :action="modal.action" method="POST" class="modal-content">
        @csrf <template x-if="modal.method==='PUT'">@method('PUT')</template>

        <div class="modal-header">
          <h5 class="modal-title" x-text="modal.title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          {{-- Option name --}}
          <div class="mb-3">
            <label class="form-label">Option Name</label>
            <input type="text" name="name" class="form-control" x-model="form.name" required>
          </div>

          {{-- Values (comma-separated) --}}
          <div class="mb-3">
            <label class="form-label">
              Values <small class="text-muted">(comma-separated: <em>Red, Blue, Green</em>)</small>
            </label>
            <textarea rows="3" name="values" class="form-control"
                      placeholder="e.g. Small, Medium, Large"
                      x-model="form.values"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-primary" type="submit" x-text="modal.button"></button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div> {{-- Alpine root --}}

@push('scripts')
<script>
function attributeManager() {
  const modal = new bootstrap.Modal(document.getElementById('attributeModal'));
  return {
    modal: {title:'',button:'',action:'#',method:'POST'},
    form: {name:'',values:''},

    openAddModal(){
      this.modal = {
        title: 'Add Option',
        button:'Create',
        action: '{{ route('admin.categories.attributes.store', $category) }}',
        method:'POST'
      };
      this.form = {name:'',values:''};
      modal.show();
    },

    openEditModal(id,name,values){
      this.modal = {
        title: 'Edit Option',
        button:'Update',
        action: '{{ url('/admin/category-attributes') }}/'+id,
        method:'PUT'
      };
      this.form = {name:name,values: values.join(', ')};
      modal.show();
    }
  }
}
</script>
@endpush
@endsection
