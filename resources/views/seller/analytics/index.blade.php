@extends('layouts.app')
@section('title','Analytics Dashboard')

@push('styles')
    <style>
        /* glassy cards */
        .glass {
            background: rgba(255,255,255,.8);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(0,0,0,.05);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
        :root { --brand: {{ optional(auth()->user()->shop)->primary_color && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', auth()->user()->shop->primary_color) ? auth()->user()->shop->primary_color : '#27b105' }}; }
        .analytics-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg,#e9ecef,#fff);
            box-shadow: 0 2px 6px rgba(0,0,0,.1);
            border-radius: 50%;
            border: 2px solid var(--brand);
        }
        .analytics-icon i { color: var(--brand) !important; }
        .range-label { font-size:.825rem; color:#6c757d; }
        }
        @media (prefers-color-scheme:dark){
            .glass {
                background: rgba(35,35,35,.55);
                border-color: rgba(255,255,255,.1);
            }
            .analytics-icon {
                background: linear-gradient(135deg,rgba(255,255,255,.1),rgba(255,255,255,.05));
                box-shadow: 0 2px 6px rgba(0,0,0,.4);
            }
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
                <div class="range-label">Range: <strong>{{ $rangeLabel }}</strong></div>
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
                              :value="shop_currency().' '.number_format($kpi->total_sales,2)"
                              :delta="$kpiDelta->sales"
                              icon="fas fa-dollar-sign"
                              sparkId="sparkRevenue"/>
            <x-analytics.card title="Total Orders"
                              :value="$kpi->total_orders"
                              :delta="$kpiDelta->orders"
                              icon="fas fa-shopping-cart"
                              sparkId="sparkOrders"/>
            <x-analytics.card title="Avg Order Value"
                              :value="shop_currency().' '.number_format($kpi->avg_order_value,2)"
                              :delta="$kpiDelta->aov"
                              icon="fas fa-chart-line"
                              sparkId="sparkAov"/>
        </div>

        {{-- quick facts --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <span class="badge bg-light text-dark border">Best Day: <strong class="ms-1">{{ $bestDay ? date('M j, Y', strtotime($bestDay)) : '—' }}</strong> <span class="ms-1">({{ shop_currency() }} {{ number_format($bestRevenue,2) }})</span></span>
            <span class="badge bg-light text-dark border">Avg Daily Revenue: <strong class="ms-1">{{ shop_currency() }} {{ number_format($avgDailyRevenue,2) }}</strong></span>
            <span class="badge bg-light text-dark border">Overall Conversion: <strong class="ms-1">{{ number_format($overallConversion,2) }}%</strong></span>
        </div>

        {{-- ======================= Revenue & Orders chart ======================= --}}
        <div class="card shadow-sm border-0 rounded-3 mb-5 glass">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="fas fa-chart-area me-2" style="color:var(--brand)"></i>Revenue & Orders</h6>
                <div class="d-flex align-items-center gap-2">
                    {{-- toggle --}}
                    <div class="btn-group btn-group-sm" role="group" id="chartToggle">
                        <button class="btn btn-outline-secondary active" data-target="revenue">Revenue</button>
                        <button class="btn btn-outline-secondary"           data-target="orders" >Orders</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
                <canvas id="ordersChart"  height="100" class="d-none"></canvas>
            </div>
        </div>

        {{-- ======================= Sales by Weekday ======================= --}}
        <div class="card shadow-sm border-0 rounded-3 mb-5 glass">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="fas fa-calendar-week me-2" style="color:var(--brand)"></i>Sales by Day of Week</h6>
            </div>
            <div class="card-body">
                <canvas id="dowChart" height="90"></canvas>
            </div>
        </div>

        {{-- ======================= Top products ======================= --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3 glass h-100">
                    <div class="card-header bg-transparent border-0 fw-semibold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-ranking-star me-2" style="color:var(--brand)"></i>Top Products</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportTopCsv"><i class="fas fa-file-csv me-1"></i>Export</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0" id="topProductsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProducts as $p)
                                        <tr>
                                            <td class="d-flex align-items-center gap-2">
                                                <img src="{{ $p->thumbnail_url ?? asset('storage/placeholder.jpg') }}" class="rounded" width="40" height="40" alt="">
                                                <span>{{ Str::limit($p->name, 30) }}</span>
                                            </td>
                                            <td class="text-end">{{ $p->qty_sold }}</td>
                                            <td class="text-end">{{ shop_currency() }} {{ number_format($p->revenue,2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">No products in this range.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listing performance --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3 glass h-100">
                    <div class="card-header bg-transparent border-0 fw-semibold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bolt me-2" style="color:var(--brand)"></i>Listing Performance</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportPerfCsv"><i class="fas fa-file-csv me-1"></i>Export</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0" id="performanceTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Views</th>
                                        <th class="text-end">Sales</th>
                                        <th style="width:130px">Conversion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($performance as $p)
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
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No listing activity in this range.</td></tr>
                                    @endforelse
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
    const BRAND = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#27b105';
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
    const dowLabels = @json($dowLabels ?? []);
    const dowSeries = @json($dowSeries ?? []);

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
                backgroundColor: gradient(revCtx,BRAND),
                borderColor:   BRAND,
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
                borderColor: BRAND,
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

    /** ------- DOW chart ------- **/
    if (dowLabels.length && dowSeries.length) {
        const ctx = document.getElementById('dowChart').getContext('2d');
        new Chart(ctx, {
            type:'bar',
            data:{ labels:dowLabels, datasets:[{ data:dowSeries, backgroundColor: gradient(ctx,BRAND), borderColor: BRAND, borderWidth:1 }] },
            options:{ maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true, ticks:{ callback:v=>v.toLocaleString() }}} }
        });
    }

    /** ------- Sparklines ------- **/
    function createSpark(id, data, color){
        const el = document.getElementById(id);
        if(!el) return;
        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type:'line', data:{ labels: data.map((_,i)=>i+1), datasets:[{ data, borderColor:color, pointRadius:0, borderWidth:2, tension:.35, fill:false }] },
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{enabled:false}}, scales:{ x:{display:false}, y:{display:false} } }
        });
    }
    // derive AOV series
    const aovSeries = labels.map((_,i)=> (orders[i] > 0 ? revenue[i]/orders[i] : 0));
    // Trim to last 30 points for clarity
    const lastN = (arr,n) => arr.slice(Math.max(0, arr.length - n));
    createSpark('sparkRevenue', lastN(revenue, 30), BRAND);
    createSpark('sparkOrders',  lastN(orders, 30),  BRAND);
    createSpark('sparkAov',     lastN(aovSeries,30),BRAND);
    /** ------- CSV export (simple) ------- **/
    function tableToCSV(tableId, filename){
        const rows = Array.from(document.querySelectorAll(`#${tableId} tr`));
        const csv = rows.map(tr => Array.from(tr.querySelectorAll('th,td')).map(td => {
            const t = td.innerText.replace(/\s+/g,' ').trim();
            return '"'+t.replaceAll('"','""')+'"';
        }).join(',')).join('\n');
        const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        a.click();
        URL.revokeObjectURL(a.href);
    }
    document.getElementById('exportTopCsv')?.addEventListener('click', () => tableToCSV('topProductsTable','top-products.csv'));
    document.getElementById('exportPerfCsv')?.addEventListener('click', () => tableToCSV('performanceTable','listing-performance.csv'));

})();
</script>
@endpush
