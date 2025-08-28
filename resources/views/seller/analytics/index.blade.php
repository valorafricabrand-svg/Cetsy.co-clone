@extends('layouts.app')
@section('title','Analytics Dashboard')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* glassy cards */
        .glass {background:rgba(255,255,255,.8);backdrop-filter:blur(6px)}
        .analytics-icon {width:56px;height:56px;background:rgba(0,0,0,.05);}
        @media (prefers-color-scheme:dark){
            .glass {background:rgba(35,35,35,.55);}
            .analytics-icon {background:rgba(255,255,255,.05);}
        }
    </style>
@endpush

@section('content')
<div class="content">
    <div class="container-xxl">

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 fw-semibold mb-1">Analytics Dashboard</h1>
                <p class="text-muted small mb-0">Monitor your shop performance at a glance.</p>
            </div>
            <form method="GET" class="d-flex align-items-center gap-2" id="rangeForm">
                <select name="range" class="form-select form-select-sm" id="rangeSelect">
                    <option value="today"    {{ $range=='today'    ? 'selected' : '' }}>Today</option>
                    <option value="yesterday"{{ $range=='yesterday'? 'selected' : '' }}>Yesterday</option>
                    <option value="week"     {{ $range=='week'     ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="2weeks"   {{ $range=='2weeks'   ? 'selected' : '' }}>Last 14 Days</option>
                    <option value="1month"   {{ $range=='1month'   ? 'selected' : '' }}>Last 1 Month</option>
                    <option value="2months"  {{ $range=='2months'  ? 'selected' : '' }}>Last 2 Months</option>
                    <option value="3months"  {{ $range=='3months'  ? 'selected' : '' }}>Last 3 Months</option>
                    <option value="6months"  {{ $range=='6months'  ? 'selected' : '' }}>Last 6 Months</option>
                    <option value="custom"   {{ $range=='custom'   ? 'selected' : '' }}>Custom</option>
                </select>
                <div id="customRange" class="d-flex align-items-center gap-2 {{ $range!='custom' ? 'd-none' : '' }}">
                    <input type="date" name="start" value="{{ $startDate }}" class="form-control form-control-sm">
                    <span class="text-muted">to</span>
                    <input type="date" name="end" value="{{ $endDate }}" class="form-control form-control-sm">
                    <button class="btn btn-primary btn-sm" type="submit">Apply</button>
                </div>
            </form>
        </div>

        {{-- ======================= KPIs ======================= --}}
        <div class="row g-4 mb-4">
            <x-analytics.card title="Total Sales"
                              :value="get_currency().' '.number_format($kpi->total_sales,2)"
                              icon="bi bi-currency-dollar text-primary"/>
            <x-analytics.card title="Total Orders"
                              :value="$kpi->total_orders"
                              icon="bi bi-bag-check-fill text-success"/>
            <x-analytics.card title="Avg Order Value"
                              :value="get_currency().' '.number_format($kpi->avg_order_value,2)"
                              icon="bi bi-graph-up-arrow text-warning"/>
        </div>

        {{-- ======================= Revenue & Orders chart ======================= --}}
        <div class="card shadow-sm border-0 rounded-3 mb-5 glass">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">Revenue & Orders <small class="text-muted">({{ $rangeLabel }})</small></h6>
                {{-- toggle --}}
                <div class="btn-group btn-group-sm" role="group" id="chartToggle">
                    <button class="btn btn-outline-secondary active" data-target="revenue">Revenue</button>
                    <button class="btn btn-outline-secondary"           data-target="orders" >Orders</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
                <canvas id="ordersChart"  height="100" class="d-none"></canvas>
            </div>
        </div>

        {{-- ======================= Top products ======================= --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3 glass h-100">
                    <div class="card-header bg-transparent border-0 fw-semibold">Top Products</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProducts as $p)
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            <img src="{{ $p->thumbnail_url ?? 'https://placehold.co/40x40' }}"
                                                 class="rounded" width="40" height="40" alt="">
                                            <span>{{ Str::limit($p->name, 30) }}</span>
                                        </td>
                                        <td class="text-end">{{ $p->qty_sold }}</td>
                                        <td class="text-end">{{ get_currency() }} {{ number_format($p->revenue,2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listing performance --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3 glass h-100">
                    <div class="card-header bg-transparent border-0 fw-semibold">Listing Performance</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Views</th>
                                        <th class="text-end">Sales</th>
                                        <th style="width:130px">Conversion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($performance as $p)
                                    @php
                                        $conv = (float) $p->conversion;
                                        $bar  = $conv >= 5 ? 'bg-success'
                                              : ($conv >= 2 ? 'bg-warning' : 'bg-danger');
                                    @endphp
                                    <tr>
                                        <td>{{ Str::limit($p->name, 25) }}</td>
                                        <td class="text-end">{{ $p->views ?? 0 }}</td>
                                        <td class="text-end">{{ $p->sales ?? 0 }}</td>
                                        <td>
                                            <div class="progress rounded-pill" style="height:6px">
                                                <div class="progress-bar {{ $bar }} rounded-pill"
                                                     role="progressbar"
                                                     style="width: {{ $conv }}%"
                                                     data-bs-toggle="tooltip"
                                                     data-bs-title="{{ $conv }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $conv }}%</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> {{-- row g-4 --}}

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script>
(() => {
    /** ------- range selector ------- **/
    const rangeSelect = document.getElementById('rangeSelect');
    const customRange = document.getElementById('customRange');
    rangeSelect?.addEventListener('change', () => {
        const isCustom = rangeSelect.value === 'custom';
        customRange.classList.toggle('d-none', !isCustom);
        if (!isCustom) rangeSelect.form.submit();
    });

    /** ------- charts data from PHP ------- **/
    const labels  = @json($chart['labels']);
    const revenue = @json($chart['revenue']);
    const orders  = @json($chart['orders']);

    /** ------- chart helpers ------- **/
    const gradient = (ctx, color) => {
        const g = ctx.createLinearGradient(0,0,0,200);
        g.addColorStop(0, `${color}CC`);
        g.addColorStop(1, `${color}00`);
        return g;
    };

    /** ------- revenue -------- **/
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revCtx, {
        type:'bar',
        data:{
            labels: labels,
            datasets:[{
                label:'Revenue',
                data: revenue,
                backgroundColor: gradient(revCtx,'#0d6efd'),
                borderColor:   '#0d6efd',
                borderWidth:1
            }]
        },
        options:{
            maintainAspectRatio:false,
            scales:{y:{beginAtZero:true,ticks:{callback:v=>v.toLocaleString()}}},
            plugins:{legend:{display:false},tooltip:{callbacks:{label:c=> `${c.raw.toLocaleString()}`}}}
        }
    });

    /** ------- orders -------- **/
    const ordCtx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ordCtx, {
        type:'line',
        data:{
            labels: labels,
            datasets:[{
                label:'Orders',
                data: orders,
                borderColor:'#198754',
                backgroundColor:'transparent',
                borderWidth:2,
                tension:.3,
                pointRadius:3
            }]
        },
        options:{
            maintainAspectRatio:false,
            scales:{y:{beginAtZero:true}},
            plugins:{legend:{display:false},tooltip:{callbacks:{label:c=> c.raw}}}
        }
    });

    /** ------- toggle logic ------- **/
    document.querySelectorAll('#chartToggle button').forEach(btn=>{
        btn.addEventListener('click', e=>{
            document.querySelectorAll('#chartToggle button').forEach(b=>b.classList.remove('active'));
            e.target.classList.add('active');

            const target = e.target.dataset.target;
            document.getElementById('revenueChart').parentElement.classList.toggle('d-none', target!=='revenue');
            document.getElementById('ordersChart').parentElement.classList.toggle('d-none', target!=='orders');
        });
    });

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
})();
</script>
@endpush
