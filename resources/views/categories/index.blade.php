@extends('theme.'.theme().'.layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Categories') }}
  </h2>
@endsection

@section('main')
<div class="content">
  <div class="py-6">
    <div class="container-lg">

      {{-- flash --}}
      @includeWhen(session('success'), 'partials.alert-success', ['msg'=>session('success')])

      {{-- TOP BAR ------------------------------------------------------------ --}}
      <div class="flex flex-col md:flex-row justify-between md:items-center mb-4">
        {{-- search --}}
        <form action="{{ route('admin.categories.index') }}" method="GET" class="w-full w-md-auto mb-3 mb-0 md:mb-0">
          <div class="flex w-full items-stretch">
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Search categories..."
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                   autocomplete="off">
            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" type="submit">Search</button>
            @if(request()->filled('q'))
              <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50">&times;</a>
            @endif
          </div>
        </form>

        {{-- new category --}}
        <div class="flex gap-2 ms-md-3 items-center">
          <button type="button" id="bulkUpdateBtn" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50" disabled data-ui-toggle="modal" data-ui-target="#bulkUpdateModal">
            Bulk Update
          </button>
          <div class="vr hidden md:block"></div>
          <button type="button" id="bulkMoveBtn" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" disabled data-ui-toggle="modal" data-ui-target="#bulkMoveModal">
            Move To Parent
          </button>
          <div class="vr hidden md:block"></div>
          <button type="button" id="collapseAll" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Collapse All</button>
          <button type="button" id="expandAll"   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">Expand All</button>
          <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
            + New Category
          </a>
        </div>
      </div>
      {{-- /TOP BAR ----------------------------------------------------------- --}}

      @if($parents->isEmpty())
        <p class="text-slate-500">No categories found.</p>
      @else
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm">
            <div class="p-4 sm:p-5 p-0">
              <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-sm mb-0 align-middle category-table">
              <thead class="bg-slate-50">
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
                  <th class="text-right" style="width:220px">Actions</th>
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
          <div class="modal" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form class="rounded-2xl border border-slate-200 bg-white shadow-xl" action="{{ route('admin.categories.bulk-update') }}" method="POST" id="bulkUpdateForm">
                @csrf
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                  <h5 class="text-base font-semibold text-slate-900">Bulk Update Categories</h5>
                  <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal"></button>
                </div>
                <div class="px-4 py-4">
                  <p class="text-slate-500 mb-3">
                    Applying changes to <span id="selectedCount">0</span> selected categor<span id="selectedCountPlural">ies</span>.
                  </p>

                  <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Listing Fee</label>
                    <input type="number" name="listing_fee" step="0.01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Leave blank to keep">
                  </div>

                  <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Listing Type</label>
                    <select name="listing_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                      <option value="">- Leave unchanged -</option>
                      <option value="products">Products</option>
                      <option value="services">Services</option>
                      <option value="digital">Digital Downloads</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Listing Frequency</label>
                    <select name="listing_frequency" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                      <option value="">- Leave unchanged -</option>
                      <option value="1">1 month</option>
                      <option value="4">4 months</option>
                    </select>
                  </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                  <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Cancel</button>
                  <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Update Selected</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Bulk Move Modal -->
          <div class="modal" id="bulkMoveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form class="rounded-2xl border border-slate-200 bg-white shadow-xl" action="{{ route('admin.categories.bulk-move') }}" method="POST" id="bulkMoveForm">
                @csrf
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                  <h5 class="text-base font-semibold text-slate-900">Move Categories</h5>
                  <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal"></button>
                </div>
                <div class="px-4 py-4">
                  <p class="text-slate-500">Select a new parent for the selected categories. Choose "None" to move them to top level.</p>

                  <label class="mb-1 block text-sm font-medium text-slate-700">New Parent</label>
                  <select name="parent_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">- None (Top level) -</option>
                    @php
                      $renderOptions = function($nodes, $depth = 0) use (&$renderOptions) {
                        foreach ($nodes as $node) {
                          echo '<option value="'.$node->id.'">'.str_repeat('- ', $depth).e($node->name).'</option>';
                          if ($node->relationLoaded('children') && $node->children->isNotEmpty()) {
                              $renderOptions($node->children, $depth+1);
                          }
                        }
                      };
                    @endphp
                    @php $renderOptions($parents); @endphp
                  </select>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                  <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Cancel</button>
                  <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">Move Selected</button>
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
    const bulkMoveModalEl = document.getElementById('bulkMoveModal');
    const bulkMoveBtn = document.getElementById('bulkMoveBtn');
    const collapseAllBtn = document.getElementById('collapseAll');
    const expandAllBtn   = document.getElementById('expandAll');

    function updateState(){
      const count = checkboxes().filter(cb => cb.checked).length;
      bulkBtn.disabled = count === 0;
      if (bulkMoveBtn) bulkMoveBtn.disabled = count === 0;
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

    if (bulkMoveModalEl) {
      bulkMoveModalEl.addEventListener('show.bs.modal', function (e) {
        const selected = checkboxes().filter(cb => cb.checked);
        if (selected.length === 0) {
          e.preventDefault();
          e.stopImmediatePropagation();
          return false;
        }
        const form = document.getElementById('bulkMoveForm');
        if (!form) return;
        Array.from(form.querySelectorAll('input[type="hidden"][name="ids[]"]')).forEach(el => el.remove());
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
