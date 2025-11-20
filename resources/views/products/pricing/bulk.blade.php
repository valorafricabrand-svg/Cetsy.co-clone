{{-- resources/views/products/pricing/bulk.blade.php --}}
@extends('layouts.app')

@section('title', 'Bulk Price Editor')

@section('content')
<div class="content" x-data="bulkPricer({{ (int)($shopId ?? 0) }})">

    <h2 class="mb-4">
        <i class="bi bi-cash-coin me-1"></i> Bulk Price Editor
    </h2>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter/search (optional) --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search product name">
        </div>
        {{-- Example: category filter --}}
        {{-- <div class="col-sm-3">
            <select name="category_id" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div> --}}
        <div class="col-auto">
            <button class="btn btn-outline-secondary">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>
    </form>

    <form action="{{ route('seller.products.pricing.bulk.store') }}" method="POST" @submit="return confirmSubmit()">
        @csrf
        <input type="hidden" name="apply_to_all" :value="applyAll ? 1 : 0">

        {{-- Settings card --}}
        <div class="card mb-3">
            <div class="card-header bg-light fw-semibold">Update Settings</div>
            <div class="card-body row g-3 align-items-end">

                <div class="col-sm-2">
                    <label class="form-label mb-0 small text-muted">Direction</label>
                    <select name="direction" class="form-select form-select-sm">
                        <option value="up">Increase</option>
                        <option value="down">Decrease</option>
                    </select>
                </div>

                <div class="col-sm-2">
                    <label class="form-label mb-0 small text-muted">Percent (%)</label>
                    <input type="number" step="0.01" min="0" name="percent" class="form-control form-control-sm" required>
                </div>

                <div class="col-sm-2">
                    <label class="form-label mb-0 small text-muted">Column</label>
                    <select name="column" class="form-select form-select-sm">
                        <option value="price">price</option>
                        <option value="sale_price">sale_price</option>
                    </select>
                </div>

                <div class="col-sm-2">
                    <label class="form-label mb-0 small text-muted">Round to</label>
                    <select name="round_to" class="form-select form-select-sm">
                        @foreach([0,1,2,3,4] as $r)
                            <option value="{{ $r }}">{{ $r }} decimals</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="apply_all"
                               @change="setAll(true)" :checked="applyAll">
                        <label class="form-check-label" for="apply_all">Apply to ALL filtered products</label>
                    </div>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="apply_selected"
                               @change="setAll(false)" :checked="!applyAll">
                        <label class="form-check-label" for="apply_selected">Apply only to selected rows</label>
                    </div>
                    <div class="form-text">When "Apply to ALL" is on, row checkboxes are disabled.</div>
                </div>
            </div>
        </div>

        {{-- Products list --}}
        <div class="card">
            <div class="card-header bg-light fw-semibold d-flex justify-content-between">
                <span>Products ({{ $products->count() }})</span>
                <span class="small text-muted">Showing 1–{{ $products->count() }}</span>
            </div>
            <div class="table-responsive" style="max-height:60vh;overflow:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:32px;">
                                <input type="checkbox" @change="togglePage($event)" x-ref="page_select" :disabled="applyAll">
                            </th>
                            <th>Item</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Sale Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                            @php
                                $thumb = null; $mediaType = 'image';
                                if (!empty($p->featured_image)) {
                                    $thumb = str_starts_with($p->featured_image, 'http')
                                        ? $p->featured_image
                                        : asset('storage/' . ltrim($p->featured_image, '/'));
                                } else {
                                    $firstMedia = $p->media->first();
                                    if ($firstMedia) {
                                        $thumb = asset('storage/' . ltrim($firstMedia->url ?? '', '/'));
                                        $mediaType = $firstMedia->type ?? 'image';
                                    } else {
                                        $shopLogo = ($p->shop && $p->shop->logo)
                                                    ? asset('storage/' . ltrim($p->shop->logo, '/'))
                                                    : (setting('favicon_url') ?: asset('storage/placeholder.jpg'));
                                        $thumb = $shopLogo;
                                        $mediaType = 'image';
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="product_ids[]"
                                           value="{{ $p->id }}"
                                           :disabled="applyAll"
                                           x-model="selectedIds">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($thumb)
                                            @if($mediaType === 'video')
                                                <span class="bg-light rounded" style="width:44px;height:44px;display:inline-block;"></span>
                                            @else
                                                <img src="{{ $thumb ?? setting('favicon_url') }}" alt="{{ $p->name }}"
                                                     style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;">
                                            @endif
                                        @endif
                                        <div class="fw-medium text-truncate" style="max-width:420px;" title="{{ $p->name }}">{{ $p->name }}</div>
                                    </div>
                                </td>
                                <td class="text-end">{{ number_format($p->price,2) }}</td>
                                <td class="text-end">{{ number_format($p->sale_price ?? $p->discount_price ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- No pagination footer: all filtered products are listed above --}}
        </div>

        <div class="mt-3 text-end">
            <button class="btn btn-primary">
                <i class="bi bi-check2-circle me-1"></i> Apply Update
            </button>
            <div id="selected-hidden-container"></div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function bulkPricer(shopId = 0){
    return {
        storageKey: `bulkPricer:${shopId}`,
        applyAll: false,
        selectedIds: [],

        init(){
            // Load persisted state
            const saved = this.readStorage();
            if(saved){
                this.applyAll = !!saved.applyAll;
                this.selectedIds = Array.isArray(saved.selected) ? saved.selected : [];
            }

            // Reflect selection on this page's checkboxes
            this.$nextTick(() => {
                this.syncPageCheckboxes();
                this.updateHeaderCheckbox();
            });

            // Watchers to persist state
            this.$watch('applyAll', () => {
                if(this.applyAll){
                    // Clear page toggle and disable checkboxes
                    if(this.$refs.page_select){ this.$refs.page_select.checked = false; }
                }
                this.persist();
            });
            this.$watch('selectedIds', () => {
                this.persist();
                this.updateHeaderCheckbox();
            });
        },

        setAll(isAll){
            this.applyAll = isAll;
            if(isAll){
                // keep selections but disable inputs; allow switching back without losing
                if(this.$refs.page_select){ this.$refs.page_select.checked = false; }
            }
        },

        togglePage(event){
            const checked = event.target.checked;
            const boxes = document.querySelectorAll('input[name="product_ids[]"]:not(:disabled)');
            if(checked){
                boxes.forEach(b => {
                    if(!this.selectedIds.includes(b.value)){
                        this.selectedIds.push(b.value);
                    }
                });
            }else{
                const idsOnPage = [...boxes].map(b => b.value);
                this.selectedIds = this.selectedIds.filter(id => !idsOnPage.includes(id));
            }
        },

        syncPageCheckboxes(){
            const boxes = document.querySelectorAll('input[name="product_ids[]"]');
            boxes.forEach(b => { b.checked = this.selectedIds.includes(b.value); });
        },

        updateHeaderCheckbox(){
            const boxes = [...document.querySelectorAll('input[name="product_ids[]"]:not(:disabled)')];
            if(!this.$refs.page_select || boxes.length === 0){ return; }
            const allChecked = boxes.every(b => this.selectedIds.includes(b.value));
            const anyChecked = boxes.some(b => this.selectedIds.includes(b.value));
            this.$refs.page_select.indeterminate = !allChecked && anyChecked;
            this.$refs.page_select.checked = allChecked;
        },

        persist(){
            const data = { applyAll: this.applyAll, selected: this.selectedIds };
            try { localStorage.setItem(this.storageKey, JSON.stringify(data)); } catch(e) {}
        },
        readStorage(){
            try { return JSON.parse(localStorage.getItem(this.storageKey)); } catch(e) { return null; }
        },
        mergeFromStorage(){
            const saved = this.readStorage();
            if(saved && Array.isArray(saved.selected)){
                const set = new Set([ ...this.selectedIds, ...saved.selected ]);
                this.selectedIds = Array.from(set);
            }
        },

        confirmSubmit(){
            // Ensure we have merged any persisted selection
            this.mergeFromStorage();

            if(!this.applyAll && this.selectedIds.length === 0){
                alert('Select at least one product or choose "Apply to ALL".');
                return false;
            }

            // Inject hidden inputs for all selected ids so they submit from any page
            const container = document.getElementById('selected-hidden-container');
            if(container){ container.innerHTML = ''; }
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_ids[]';
                input.value = id;
                container.appendChild(input);
            });

            return confirm('Are you sure you want to update prices? This cannot be undone.');
        }
    }
}
</script>
@endpush
