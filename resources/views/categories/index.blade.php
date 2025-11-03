@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Categories') }}
  </h2>
@endsection

@section('content')
<div class="content">
  <div class="py-6">
    <div class="container-lg">

      {{-- flash --}}
      @includeWhen(session('success'), 'partials.alert-success', ['msg'=>session('success')])

      {{-- TOP BAR ------------------------------------------------------------ --}}
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        {{-- search --}}
        <form action="{{ route('admin.categories.index') }}" method="GET" class="w-100 w-md-auto mb-3 mb-md-0">
          <div class="input-group">
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Search categories…"
                   class="form-control"
                   autocomplete="off">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
            @if(request()->filled('q'))
              <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-danger">×</a>
            @endif
          </div>
        </form>

        {{-- new category --}}
        <div class="d-flex gap-2 ms-md-3 align-items-center">
          <button type="button" id="bulkUpdateBtn" class="btn btn-outline-primary" disabled data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
            Bulk Update
          </button>
          <div class="vr d-none d-md-block"></div>
          <button type="button" id="collapseAll" class="btn btn-outline-secondary">Collapse All</button>
          <button type="button" id="expandAll"   class="btn btn-outline-secondary">Expand All</button>
          <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            + New Category
          </a>
        </div>
      </div>
      {{-- /TOP BAR ----------------------------------------------------------- --}}

      @if($parents->isEmpty())
        <p class="text-muted">No categories found.</p>
      @else
          <div class="card shadow-sm">
            <div class="card-body p-0">
              <div class="table-responsive">
              <table class="table table-striped mb-0 align-middle table-hover category-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width:40px">
                    <input type="checkbox" id="select_all">
                  </th>
                  <th style="width:70px">Image</th>
                  <th style="min-width:280px">Name</th>
                  <th>Type</th>
                  <th>Parent</th>
                  <th>Fee</th>
                  <th>Cycle</th>
                  <th class="text-end" style="width:220px">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($parents as $parent)
                  @include('categories._row', ['cat' => $parent, 'depth' => 0])
                @endforeach
              </tbody>
              </table>
              </div>
            </div>
          </div>

          <!-- Bulk Update Modal -->
          <div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form class="modal-content" action="{{ route('admin.categories.bulk-update') }}" method="POST" id="bulkUpdateForm">
                @csrf
                <div class="modal-header">
                  <h5 class="modal-title">Bulk Update Categories</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p class="text-muted mb-3">
                    Applying changes to <span id="selectedCount">0</span> selected categor<span id="selectedCountPlural">ies</span>.
                  </p>

                  <div class="mb-3">
                    <label class="form-label">Listing Fee</label>
                    <input type="number" name="listing_fee" step="0.01" class="form-control" placeholder="Leave blank to keep">
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Listing Type</label>
                    <select name="listing_type" class="form-select">
                      <option value="">— Leave unchanged —</option>
                      <option value="products">Products</option>
                      <option value="services">Services</option>
                      <option value="digital">Digital Downloads</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Listing Frequency</label>
                    <select name="listing_frequency" class="form-select">
                      <option value="">— Leave unchanged —</option>
                      <option value="1">1 month</option>
                      <option value="4">4 months</option>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Selected</button>
                </div>
              </form>
            </div>
          </div>
      @endif

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  function ready(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  ready(function(){
    const selectAll = document.getElementById('select_all');
    const checkboxes = () => Array.from(document.querySelectorAll('.category-checkbox'));
    const bulkBtn = document.getElementById('bulkUpdateBtn');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectedCountPlural = document.getElementById('selectedCountPlural');
    const bulkModalEl = document.getElementById('bulkUpdateModal');
    const collapseAllBtn = document.getElementById('collapseAll');
    const expandAllBtn   = document.getElementById('expandAll');

    function updateState(){
      const count = checkboxes().filter(cb => cb.checked).length;
      bulkBtn.disabled = count === 0;
      if (selectedCountEl) selectedCountEl.textContent = count;
      if (selectedCountPlural) selectedCountPlural.textContent = count === 1 ? 'y' : 'ies';
      if (selectAll) selectAll.checked = count > 0 && count === checkboxes().length;
      if (selectAll) selectAll.indeterminate = count > 0 && count < checkboxes().length;
    }

    if (selectAll) {
      selectAll.addEventListener('change', function(){
        checkboxes().forEach(cb => cb.checked = selectAll.checked);
        updateState();
      });
    }

    checkboxes().forEach(cb => cb.addEventListener('change', updateState));
    updateState();

    // Build parent->children adjacency map from DOM
    const childMap = (function(){
      const map = {};
      document.querySelectorAll('tr[data-id]')
        .forEach(row => {
          const id = row.getAttribute('data-id');
          const pid = row.getAttribute('data-parent-id');
          if (pid) {
            if (!map[pid]) map[pid] = [];
            map[pid].push(id);
          }
        });
      return map;
    })();

    function descendantsOf(id){
      const out = [];
      const stack = (childMap[id] ? [...childMap[id]] : []);
      while (stack.length){
        const cid = stack.pop();
        out.push(cid);
        if (childMap[cid]) stack.push(...childMap[cid]);
      }
      return out;
    }

    function checkboxFor(id){
      return document.querySelector('.category-checkbox[value="'+id+'"]');
    }

    // Delegate click: select all descendants for a row
    document.addEventListener('click', function(e){
      const btn = e.target.closest('.select-subtree');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const ids = descendantsOf(id);
      if (ids.length === 0) return; // nothing to do
      const allSelected = ids.every(cid => {
        const cb = checkboxFor(cid);
        return cb && cb.checked;
      });
      ids.forEach(cid => {
        const cb = checkboxFor(cid);
        if (cb) cb.checked = !allSelected; // toggle: select if not all selected, else deselect all
      });
      updateState();
    }, false);

    // Expand/Collapse helpers
    function setCollapsed(collapsed){
      document.querySelectorAll('tr[data-parent-id]')
        .forEach(row => row.classList.toggle('d-none', collapsed));
    }
    if (collapseAllBtn) collapseAllBtn.addEventListener('click', () => setCollapsed(true));
    if (expandAllBtn)   expandAllBtn.addEventListener('click',  () => setCollapsed(false));

    // Populate hidden inputs on modal show
    if (bulkModalEl) {
      bulkModalEl.addEventListener('show.bs.modal', function (e) {
        const selected = checkboxes().filter(cb => cb.checked);
        if (selected.length === 0) {
          e.preventDefault();
          e.stopImmediatePropagation();
          return false;
        }
        const form = document.getElementById('bulkUpdateForm');
        if (!form) return;
        // Remove previous hidden ids
        Array.from(form.querySelectorAll('input[type="hidden"][name="ids[]"]')).forEach(el => el.remove());
        // Add selected ids
        selected.forEach(cb => {
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'ids[]';
          hidden.value = cb.value;
          form.appendChild(hidden);
        });
      });
    }
  });
})();
</script>
@endpush

@push('styles')
<style>
  .category-table thead th { position: sticky; top: 0; z-index: 1; }
  .badge-soft { background: rgba(99,102,241,.12); color:#4f46e5; border:1px solid rgba(79,70,229,.25); padding:.35rem .5rem; border-radius:.5rem; font-weight:600; }
  .badge-soft.green { background: rgba(16,185,129,.12); color:#065f46; border-color: rgba(16,185,129,.25); }
  .slug { font-size: 12px; color:#6b7280; }
</style>
@endpush

