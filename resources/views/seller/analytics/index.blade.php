@extends('theme.'.theme().'.layouts.app')
@section('title','Analytics Dashboard')

@push('styles')
<style>
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
    :root {
        --brand: {{ optional(auth()->user()->shop)->primary_color && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', auth()->user()->shop->primary_color) ? auth()->user()->shop->primary_color : '#27b105' }};
    }
    .analytics-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg,#e9ecef,#fff);
        box-shadow: 0 2px 6px rgba(0,0,0,.1);
        border-radius: 50%;
        border: 2px solid var(--brand);
    }
    .analytics-icon i { color: var(--brand) !important; }
    .range-label { font-size: .825rem; color: #64748b; }
    #chartToggle button.active {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
    }
    @media (prefers-color-scheme: dark) {
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

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6">
        <div class="mb-4 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Analytics Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor your shop performance at a glance.</p>
            <div class="range-label">Range: <strong>{{ $rangeLabel }}</strong></div>
          </div>

          <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center" id="rangeForm">
            <select name="range" id="rangeSelect" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 sm:w-auto sm:min-w-[180px]">
              <option value="today" {{ $range=='today' ? 'selected' : '' }}>Today</option>
              <option value="yesterday" {{ $range=='yesterday' ? 'selected' : '' }}>Yesterday</option>
              <option value="week" {{ $range=='week' ? 'selected' : '' }}>Last 7 Days</option>
              <option value="2weeks" {{ $range=='2weeks' ? 'selected' : '' }}>Last 14 Days</option>
              <option value="1month" {{ $range=='1month' ? 'selected' : '' }}>Last 1 Month</option>
              <option value="2months" {{ $range=='2months' ? 'selected' : '' }}>Last 2 Months</option>
              <option value="3months" {{ $range=='3months' ? 'selected' : '' }}>Last 3 Months</option>
              <option value="6months" {{ $range=='6months' ? 'selected' : '' }}>Last 6 Months</option>
              <option value="custom" {{ $range=='custom' ? 'selected' : '' }}>Custom</option>
            </select>

            <div id="customRange" class="{{ $range!='custom' ? 'hidden' : 'flex' }} items-center gap-2">
              <input type="date" name="start" value="{{ $startDate }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
              <span class="text-slate-500">to</span>
              <input type="date" name="end" value="{{ $endDate }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
              <button class="inline-flex items-center justify-center rounded-xl border border-emerald-600 bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700" type="submit">Apply</button>
            </div>
          </form>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
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

        <div class="mb-4 flex flex-wrap gap-2">
          <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900">Best Day: <strong class="ml-1">{{ $bestDay ? date('M j, Y', strtotime($bestDay)) : '-' }}</strong> <span class="ml-1">({{ shop_currency() }} {{ number_format($bestRevenue,2) }})</span></span>
          <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900">Avg Daily Revenue: <strong class="ml-1">{{ shop_currency() }} {{ number_format($avgDailyRevenue,2) }}</strong></span>
          <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-900">Overall Conversion: <strong class="ml-1">{{ number_format($overallConversion,2) }}%</strong></span>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm glass">
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h6 class="mb-0 font-semibold"><i class="fas fa-chart-area mr-2" style="color:var(--brand)"></i>Revenue &amp; Orders</h6>
            <div id="chartToggle" class="inline-flex items-center gap-1 rounded-xl border border-slate-300 p-1 text-xs" role="group">
              <button type="button" class="active inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-target="revenue">Revenue</button>
              <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-target="orders">Orders</button>
            </div>
          </div>
          <div class="p-4">
            <canvas id="revenueChart" class="h-72 w-full"></canvas>
            <canvas id="ordersChart" class="hidden h-72 w-full"></canvas>
          </div>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm glass">
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h6 class="mb-0 font-semibold"><i class="fas fa-calendar-week mr-2" style="color:var(--brand)"></i>Sales by Day of Week</h6>
          </div>
          <div class="p-4">
            <canvas id="dowChart" class="h-64 w-full"></canvas>
          </div>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div>
            <div class="glass h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 font-semibold">
                <span><i class="fas fa-ranking-star mr-2" style="color:var(--brand)"></i>Top Products</span>
                <button type="button" id="exportTopCsv" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"><i class="fas fa-file-csv mr-1"></i>Export</button>
              </div>
              <div class="p-0">
                <div class="overflow-x-auto">
                  <table id="topProductsTable" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                      <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($topProducts as $p)
                        <tr class="border-t border-slate-100">
                          <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                              <div class="relative h-10 w-10 shrink-0">
                                <img src="{{ $p->thumbnail_url ?? asset('storage/placeholder.jpg') }}" class="h-10 w-10 rounded object-cover" alt="{{ $p->name ?? 'Product' }}" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
                                <div class="absolute inset-0 hidden items-center justify-center rounded border border-slate-200 bg-slate-100 text-slate-400">
                                  <i class="fa-solid fa-image"></i>
                                </div>
                              </div>
                              <span>{{ Str::limit($p->name, 30) }}</span>
                            </div>
                          </td>
                          <td class="px-4 py-3 text-right">{{ $p->qty_sold }}</td>
                          <td class="px-4 py-3 text-right">{{ shop_currency() }} {{ number_format($p->revenue,2) }}</td>
                        </tr>
                      @empty
                        <tr><td colspan="3" class="py-4 text-center text-slate-500">No products in this range.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="glass h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 font-semibold">
                <span><i class="fas fa-bolt mr-2" style="color:var(--brand)"></i>Listing Performance</span>
                <button type="button" id="exportPerfCsv" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"><i class="fas fa-file-csv mr-1"></i>Export</button>
              </div>
              <div class="p-0">
                <div class="overflow-x-auto">
                  <table id="performanceTable" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                      <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-right">Views</th>
                        <th class="px-4 py-3 text-right">Sales</th>
                        <th class="px-4 py-3" style="width:130px">Conversion</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($performance as $p)
                        @php
                          $conv = (float) $p->conversion;
                          $bar = $conv >= 5 ? 'bg-emerald-500' : ($conv >= 2 ? 'bg-amber-500' : 'bg-rose-500');
                          $width = max(0, min(100, $conv));
                        @endphp
                        <tr class="border-t border-slate-100">
                          <td class="px-4 py-3">{{ Str::limit($p->name, 25) }}</td>
                          <td class="px-4 py-3 text-right">{{ $p->views ?? 0 }}</td>
                          <td class="px-4 py-3 text-right">{{ $p->sales ?? 0 }}</td>
                          <td class="px-4 py-3">
                            <div class="h-1.5 w-full rounded-full bg-slate-100">
                              <div class="h-1.5 rounded-full {{ $bar }}" style="width: {{ $width }}%" title="{{ number_format($conv, 2) }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500">{{ $conv }}%</span>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-500">No listing activity in this range.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script>
(() => {
    const BRAND = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#27b105';

    const rangeSelect = document.getElementById('rangeSelect');
    const customRange = document.getElementById('customRange');
    rangeSelect?.addEventListener('change', () => {
        const isCustom = rangeSelect.value === 'custom';
        customRange.classList.toggle('hidden', !isCustom);
        customRange.classList.toggle('flex', isCustom);
        if (!isCustom) {
            rangeSelect.form.submit();
        }
    });

    const labels = @json($chart['labels']);
    const revenue = @json($chart['revenue']);
    const orders = @json($chart['orders']);
    const dowLabels = @json($dowLabels ?? []);
    const dowSeries = @json($dowSeries ?? []);

    const gradient = (ctx, color) => {
        const g = ctx.createLinearGradient(0, 0, 0, 200);
        g.addColorStop(0, `${color}CC`);
        g.addColorStop(1, `${color}00`);
        return g;
    };

    const revCtx = document.getElementById('revenueChart')?.getContext('2d');
    const ordCtx = document.getElementById('ordersChart')?.getContext('2d');
    if (!revCtx || !ordCtx) return;

    const revenueChart = new Chart(revCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: revenue,
                backgroundColor: gradient(revCtx, BRAND),
                borderColor: BRAND,
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } } },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `${c.raw.toLocaleString()}` } } }
        }
    });

    const ordersChart = new Chart(ordCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Orders',
                data: orders,
                borderColor: BRAND,
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: .3,
                pointRadius: 3
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.raw } } }
        }
    });

    document.querySelectorAll('#chartToggle button').forEach(btn => {
        btn.addEventListener('click', e => {
            document.querySelectorAll('#chartToggle button').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');

            const target = e.currentTarget.dataset.target;
            const revenueCanvas = document.getElementById('revenueChart');
            const ordersCanvas = document.getElementById('ordersChart');

            revenueCanvas.classList.toggle('hidden', target !== 'revenue');
            ordersCanvas.classList.toggle('hidden', target !== 'orders');

            requestAnimationFrame(() => {
                revenueChart.resize();
                ordersChart.resize();
            });
        });
    });

    if (dowLabels.length && dowSeries.length) {
        const ctx = document.getElementById('dowChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dowLabels,
                    datasets: [{ data: dowSeries, backgroundColor: gradient(ctx, BRAND), borderColor: BRAND, borderWidth: 1 }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } } }
                }
            });
        }
    }

    function createSpark(id, data, color) {
        const el = document.getElementById(id);
        if (!el) return;

        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i + 1),
                datasets: [{ data, borderColor: color, pointRadius: 0, borderWidth: 2, tension: .35, fill: false }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    const aovSeries = labels.map((_, i) => (orders[i] > 0 ? revenue[i] / orders[i] : 0));
    const lastN = (arr, n) => arr.slice(Math.max(0, arr.length - n));
    createSpark('sparkRevenue', lastN(revenue, 30), BRAND);
    createSpark('sparkOrders', lastN(orders, 30), BRAND);
    createSpark('sparkAov', lastN(aovSeries, 30), BRAND);

    function tableToCSV(tableId, filename) {
        const rows = Array.from(document.querySelectorAll(`#${tableId} tr`));
        const csv = rows
            .map(tr => Array.from(tr.querySelectorAll('th,td')).map(td => {
                const t = td.innerText.replace(/\s+/g, ' ').trim();
                return `"${t.replaceAll('"', '""')}"`;
            }).join(','))
            .join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    document.getElementById('exportTopCsv')?.addEventListener('click', () => tableToCSV('topProductsTable', 'top-products.csv'));
    document.getElementById('exportPerfCsv')?.addEventListener('click', () => tableToCSV('performanceTable', 'listing-performance.csv'));
})();
</script>
@endpush
