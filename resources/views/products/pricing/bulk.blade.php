{{-- resources/views/products/pricing/bulk.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('title', 'Bulk Price Editor')

@section('main')
<div class="content" x-data="bulkPricer({{ (int)($shopId ?? 0) }})">

    <h2 class="mb-4">
        <i class="fa-solid fa-coins mr-1"></i> Bulk Price Editor
    </h2>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
            <ul class="mb-0 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter/search (optional) --}}
    <form method="GET" class="grid grid-cols-12 gap-4 gap-2 mb-3">
        <div class="sm:col-span-4">
            <input type="text" name="q" value="{{ request('q') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Search product name">
        </div>
        {{-- Example: category filter --}}
        {{-- <div class="sm:col-span-3">
            <select name="category_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div> --}}
        <div class="col-auto">
            <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </div>
    </form>

    <form action="{{ route('seller.products.pricing.bulk.store') }}" method="POST" @submit="return confirmSubmit()">
        @csrf
        <input type="hidden" name="apply_to_all" :value="applyAll ? 1 : 0">

        {{-- Settings card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-3">
            <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 font-semibold">Update Settings</div>
            <div class="p-4 sm:p-5 grid grid-cols-12 gap-4 gap-3 items-end">

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700 mb-0 text-xs text-slate-500">Direction</label>
                    <select name="direction" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs">
                        <option value="up">Increase</option>
                        <option value="down">Decrease</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700 mb-0 text-xs text-slate-500">Percent (%)</label>
                    <input type="number" step="0.01" min="0" name="percent" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs" required>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700 mb-0 text-xs text-slate-500">Column</label>
                    <select name="column" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs">
                        <option value="price">price</option>
                        <option value="sale_price">sale_price</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700 mb-0 text-xs text-slate-500">Round to</label>
                    <select name="round_to" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs">
                        @foreach([0,1,2,3,4] as $r)
                            <option value="{{ $r }}">{{ $r }} decimals</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
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
                    <div class="mt-1 text-xs text-slate-500">When "Apply to ALL" is on, row checkboxes are disabled.</div>
                </div>
            </div>
        </div>

        {{-- Products list --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 font-semibold flex justify-between">
                <span>Active Products ({{ $products->count() }})</span>
                <span class="text-xs text-slate-500">Showing 1–{{ $products->count() }} (only active listings are included)</span>
            </div>
            <div class="overflow-x-auto" style="max-height:60vh;overflow:auto;">
                <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
                    <thead class="bg-slate-50">
                        <tr>
                            <th style="width:32px;">
                                <input type="checkbox" @change="togglePage($event)" x-ref="page_select" :disabled="applyAll">
                            </th>
                            <th>Item</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Sale Price</th>
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
                                    <div class="flex items-center gap-2">
                                        @if($thumb)
                                            @if($mediaType === 'video')
                                                <span class="bg-slate-100 rounded" style="width:44px;height:44px;display:inline-block;"></span>
                                            @else
                                                <img src="{{ $thumb ?? setting('favicon_url') }}" alt="{{ $p->name }}"
                                                     style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;">
                                            @endif
                                        @endif
                                        <div class="fw-medium text-truncate" style="max-width:420px;" title="{{ $p->name }}">{{ $p->name }}</div>
                                    </div>
                                </td>
                                <td class="text-right">{{ number_format($p->price,2) }}</td>
                                <td class="text-right">{{ number_format($p->sale_price ?? $p->discount_price ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- No pagination footer: all filtered products are listed above --}}
        </div>

        <div class="mt-3 flex flex-col lg:flex-row justify-between items-start gap-3">
            @isset($history)
                <div class="flex-grow-1">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-sm border-0">
                        <div class="border-b border-slate-200 px-4 py-3 bg-slate-100 font-semibold">Recent Bulk Updates</div>
                        <div class="p-4 sm:p-5 p-0">
                            @if($history->isEmpty())
                                <p class="text-xs text-slate-500 m-3">No bulk edits recorded yet.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm table-sm mb-0 align-middle">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th>When</th>
                                                <th>Direction</th>
                                                <th>Percent</th>
                                                <th>Column</th>
                                                <th>Scope</th>
                                                <th class="text-right">Products</th>
                                                <th class="text-right">Variants</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($history as $log)
                                                <tr>
                                                    <td class="text-xs text-slate-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                                                    <td>{{ ucfirst($log->direction) }}</td>
                                                    <td>{{ rtrim(rtrim(number_format($log->percent,2), '0'), '.') }}%</td>
                                                    <td>{{ $log->column }}</td>
                                                    <td class="text-xs">
                                                        {{ $log->apply_all ? 'All filtered' : ($log->selection_count . ' selected') }}
                                                    </td>
                                                    <td class="text-right text-xs">{{ $log->affected_products }}</td>
                                                    <td class="text-right text-xs">{{ $log->affected_variants }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endisset

            <div class="text-right">
                <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                    <i class="fa-solid fa-circle-check mr-1"></i> Apply Update
                </button>
                <div id="selected-hidden-container"></div>
            </div>
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


