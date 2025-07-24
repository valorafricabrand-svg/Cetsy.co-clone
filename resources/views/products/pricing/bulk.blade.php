{{-- resources/views/products/pricing/bulk.blade.php --}}
@extends('layouts.app')

@section('title', 'Bulk Price Editor')

@section('content')
<div class="content" x-data="bulkPricer()">

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
                </div>
            </div>
        </div>

        {{-- Products list --}}
        <div class="card">
            <div class="card-header bg-light fw-semibold d-flex justify-content-between">
                <span>Products ({{ $products->total() }})</span>
                <span class="small text-muted">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
            </div>
            <div class="table-responsive" style="max-height:60vh;overflow:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:32px;">
                                <input type="checkbox" @change="togglePage($event)" x-ref="page_select" :disabled="applyAll">
                            </th>
                            <th>Name</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Sale Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           name="product_ids[]"
                                           value="{{ $p->id }}"
                                           :disabled="applyAll"
                                           x-model="selectedIds">
                                </td>
                                <td>{{ $p->name }}</td>
                                <td class="text-end">{{ number_format($p->price,2) }}</td>
                                <td class="text-end">{{ number_format($p->sale_price,2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        </div>

        <div class="mt-3 text-end">
            <button class="btn btn-primary">
                <i class="bi bi-check2-circle me-1"></i> Apply Update
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function bulkPricer(){
    return {
        applyAll: true,
        selectedIds: [],

        setAll(isAll){
            this.applyAll = isAll;
            if(isAll){
                // clear selections
                this.selectedIds = [];
                if(this.$refs.page_select){
                    this.$refs.page_select.checked = false;
                }
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

        confirmSubmit(){
            if(!this.applyAll && this.selectedIds.length === 0){
                alert('Select at least one product or choose "Apply to ALL".');
                return false;
            }
            return confirm('Are you sure you want to update prices? This cannot be undone.');
        }
    }
}
</script>
@endpush
