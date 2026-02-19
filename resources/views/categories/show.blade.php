{{-- resources/views/admin/categories/show.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Category / {{ $category->name }}
  </h2>
@endsection

@section('main')
<div class="content" x-data="attributeManager()">
  <div class="py-6">
    <div class="container-lg">

      {{-- Flash --}}
      @foreach(['success','info','warning','danger'] as $msg)
        @if(session()->has($msg))
          <div class="mb-3 rounded-xl border px-4 py-3 text-sm {{ $msg === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : ($msg === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-800' : ($msg === 'danger' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-sky-200 bg-sky-50 text-sky-800')) }}" role="alert">
            {{ session($msg) }}
            <button class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert">&times;</button>
          </div>
        @endif
      @endforeach

      {{-- NAV BUTTONS --}}
      <div class="flex justify-between items-center mb-4">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">
          ← Back to list
        </a>

        {{-- NEW: Edit Category button --}}
        <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
          ✎ Edit Category
        </a>
      </div>

      {{-- Category card --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm mb-5">
        <div class="p-4 sm:p-5">
          <div class="flex items-center mb-3">
            @if($category->image)
              <img src="{{ asset('storage/'.$category->image) }}"
                   class="rounded-full mr-3" style="width:60px;height:60px;object-fit:cover;">
            @endif
            <h3 class="mb-0">{{ $category->name }}</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm table-borderless mb-0">
            <tbody>
              <tr><th>Slug</th><td>{{ $category->slug }}</td></tr>
              <tr><th>Parent</th><td>{{ $category->parent?->name ?? '—' }}</td></tr>
              <tr><th>Listing fee</th><td>{{ get_currency() }} {{ number_format($category->listing_fee,2) }}</td></tr>
              <tr><th>Listing frequency</th><td>{{ $category->listing_frequency }} month{{ $category->listing_frequency == 1 ? '' : 's' }}</td></tr>
              <tr><th>Created</th><td>{{ $category->created_at }}</td></tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- OPTIONS / “VARIATIONS” TEMPLATE ------------------------------------------------ --}}
      <div class="flex justify-between items-center mb-2">
        <h4 class="mb-0">Option Template (Variations)</h4>
        <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500" @click="openAddModal">
          + Add Option
        </button>
      </div>

      @if($category->attributes->isEmpty())
        <p class="text-slate-500">No options added for this category yet.</p>
      @else
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm border border-slate-200 align-middle">
            <thead class="bg-slate-50">
              <tr>
                <th style="width:35%">Option Name</th>
                <th>Values</th>
                <th style="width:180px" class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($category->attributes as $attr)
                <tr>
                  <td>{{ $attr->name }}</td>
                  <td>
                    @forelse($attr->values as $val)
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 mr-1">{{ $val->value }}</span>
                    @empty
                      <span class="text-slate-500">—</span>
                    @endforelse
                  </td>
                  <td class="text-right">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sky-100 cursor-pointer mr-1"
                          @click="openEditModal({{ $attr->id }}, '{{ $attr->name }}', {{ $attr->values->pluck('value') }})">
                      Edit
                    </span>
                    <form action="{{ route('admin.category-attributes.destroy', $attr) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this option?');">
                      @csrf @method('DELETE')
                      <button class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-danger border-0">Delete</button>
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
  <div class="modal" id="attributeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form :action="modal.action" method="POST" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
        @csrf <template x-if="modal.method==='PUT'">@method('PUT')</template>

        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900" x-text="modal.title"></h5>
          <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal">&times;</button>
        </div>

        <div class="px-4 py-4">
          {{-- Option name --}}
          <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">Option Name</label>
            <input type="text" name="name" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.name" required>
          </div>

          {{-- Values (comma-separated) --}}
          <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">
              Values <small class="text-slate-500">(comma-separated: <em>Red, Blue, Green</em>)</small>
            </label>
            <textarea rows="3" name="values" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                      placeholder="e.g. Small, Medium, Large"
                      x-model="form.values"></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" type="submit" x-text="modal.button"></button>
          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">
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
  const modalEl = document.getElementById('attributeModal');
  const showModal = () => {
    if (!modalEl) return;
    modalEl.classList.add('is-open');
    document.body.classList.add('overflow-hidden');
    modalEl.dispatchEvent(new Event('shown.bs.modal'));
  };
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
      showModal();
    },

    openEditModal(id,name,values){
      this.modal = {
        title: 'Edit Option',
        button:'Update',
        action: '{{ url('/admin/category-attributes') }}/'+id,
        method:'PUT'
      };
      this.form = {name:name,values: values.join(', ')};
      showModal();
    }
  }
}
</script>
@endpush
@endsection




