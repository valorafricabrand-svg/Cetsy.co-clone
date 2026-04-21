@extends('theme.'.theme().'.layouts.app')
@section('title','Analytics Dashboard')

@push('styles')
<style>
    .glass {
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.98));
        border: 1px solid var(--analytics-border);
        box-shadow: var(--analytics-shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .glass:hover {
        transform: translateY(-2px);
        box-shadow: 0 1.25rem 2.5rem rgba(15,23,42,.14);
    }
    :root {
        --brand: {{ optional(auth()->user()->shop)->primary_color && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', auth()->user()->shop->primary_color) ? auth()->user()->shop->primary_color : '#27b105' }};
        --analytics-surface: #ffffff;
        --analytics-surface-soft: #f8fafc;
        --analytics-border: rgba(148,163,184,.28);
        --analytics-text: #0f172a;
        --analytics-muted: #475569;
        --analytics-subtle: #64748b;
        --analytics-grid: rgba(148,163,184,.18);
        --analytics-shadow: 0 1rem 2.5rem rgba(15,23,42,.08);
        color-scheme: light;
    }
    .analytics-shell {
        color: var(--analytics-text);
    }
    .analytics-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(180deg,#ffffff,#f1f5f9);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 0.75rem 1.5rem rgba(15,23,42,.08);
        border-radius: 50%;
        border: 2px solid var(--brand);
    }
    .analytics-icon i { color: var(--brand) !important; }
    .range-label { font-size: .825rem; color: var(--analytics-muted); font-weight: 500; }
    .analytics-chip {
        border-color: var(--analytics-border);
        background: var(--analytics-surface-soft);
        color: var(--analytics-text);
    }
    .analytics-panel-title {
        color: var(--analytics-text);
    }
    .analytics-panel-subtle {
        color: var(--analytics-subtle);
    }
    .analytics-table thead {
        background: var(--analytics-surface-soft);
        color: var(--analytics-muted);
    }
    .analytics-table tbody {
        color: var(--analytics-text);
    }
    .analytics-table tbody tr {
        border-color: rgba(226,232,240,.85);
    }
    .analytics-select,
    .analytics-date {
        border-color: rgba(148,163,184,.45);
        background: #fff;
        color: var(--analytics-text);
        box-shadow: 0 1px 2px rgba(15,23,42,.04);
    }
    .analytics-select:focus,
    .analytics-date:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 4px rgba(16,185,129,.12);
    }
    .analytics-toggle {
        border-color: rgba(148,163,184,.35);
        background: rgba(248,250,252,.9);
    }
    .analytics-toggle button {
        color: var(--analytics-muted);
    }
    .analytics-summary-row {
        background: linear-gradient(180deg, rgba(248,250,252,.92), rgba(255,255,255,.96));
        border-top: 1px solid rgba(226,232,240,.9);
        border-bottom: 1px solid rgba(226,232,240,.9);
    }
    .analytics-summary-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 9999px;
        border: 1px solid rgba(148,163,184,.24);
        background: #fff;
        padding: .5rem .85rem;
        font-size: .8125rem;
        font-weight: 700;
        color: var(--analytics-text);
        box-shadow: 0 1px 2px rgba(15,23,42,.04);
        transition: border-color .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease;
    }
    .analytics-summary-pill.active {
        border-color: var(--brand);
        background: #f0fdf4;
        color: var(--analytics-text);
        box-shadow: 0 0 0 3px rgba(16,185,129,.12);
    }
    .analytics-chart-stage {
        position: relative;
        min-height: 18rem;
    }
    .analytics-chart-empty {
        border: 1px dashed rgba(148,163,184,.45);
        border-radius: 1rem;
        background: rgba(248,250,252,.96);
        padding: 2rem 1.25rem;
        text-align: center;
    }
    .analytics-chart-empty p:first-child {
        margin: 0;
        color: var(--analytics-text);
        font-size: 1rem;
        font-weight: 700;
    }
    .analytics-chart-empty p:last-child {
        margin: .4rem 0 0;
        color: var(--analytics-muted);
        font-size: .925rem;
    }
    #chartToggle button.active {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
    }
    @media (max-width: 640px) {
        .analytics-summary-pill {
            width: 100%;
            justify-content: space-between;
        }
        .analytics-chart-stage {
            min-height: 15.5rem;
        }
    }
</style>
@endpush

@section('main')
<section class="analytics-shell bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')

      <div class="space-y-6">
        <div class="mb-4 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Analytics Dashboard</h1>
            <p class="mt-1 text-sm text-slate-600">Monitor your shop performance at a glance.</p>
            <div class="range-label">Range: <strong>{{ $rangeLabel }}</strong></div>
          </div>

          <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center" id="rangeForm">
            <select name="range" id="rangeSelect" class="analytics-select w-full rounded-xl border px-3 py-2 text-sm focus:outline-none sm:w-auto sm:min-w-[180px]">
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
              <input type="date" name="start" value="{{ $startDate }}" class="analytics-date rounded-xl border px-3 py-2 text-sm placeholder:text-slate-400 focus:outline-none">
              <span class="text-slate-600">to</span>
              <input type="date" name="end" value="{{ $endDate }}" class="analytics-date rounded-xl border px-3 py-2 text-sm placeholder:text-slate-400 focus:outline-none">
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
          <span class="analytics-chip inline-flex items-center rounded-full border px-3 py-2 text-sm font-semibold">Best Day <strong class="ml-1">{{ $bestDay ? date('M j, Y', strtotime($bestDay)) : '-' }}</strong> <span class="ml-1">({{ shop_currency() }} {{ number_format($bestRevenue,2) }})</span></span>
          <span class="analytics-chip inline-flex items-center rounded-full border px-3 py-2 text-sm font-semibold">Avg Daily Revenue <strong class="ml-1">{{ shop_currency() }} {{ number_format($avgDailyRevenue,2) }}</strong></span>
          <span class="analytics-chip inline-flex items-center rounded-full border px-3 py-2 text-sm font-semibold">Overall Conversion <strong class="ml-1">{{ number_format($overallConversion,2) }}%</strong></span>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm glass">
          <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h6 class="analytics-panel-title mb-0 font-semibold"><i class="fas fa-chart-area mr-2" style="color:var(--brand)"></i>Revenue &amp; Orders</h6>
              <p id="chartModeDescription" class="analytics-panel-subtle mt-1 text-sm">Showing <span class="font-semibold text-slate-900">daily paid revenue</span> for <span class="font-semibold text-slate-900">{{ $rangeLabel }}</span>.</p>
            </div>
            <div class="flex flex-col items-stretch gap-2 sm:items-end">
              <div id="chartToggle" class="analytics-toggle inline-flex items-center gap-1 rounded-xl border p-1 text-xs" role="group" aria-label="Switch analytics chart metric">
                <button type="button" class="active inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-target="revenue" aria-pressed="true">Revenue</button>
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-target="orders" aria-pressed="false">Orders</button>
              </div>
              <p id="chartToggleStatus" class="analytics-panel-subtle text-xs font-semibold">Showing revenue trend</p>
            </div>
          </div>
          <div class="analytics-summary-row px-4 py-3">
            <div class="flex flex-wrap gap-2">
              <span id="summaryRevenue" class="analytics-summary-pill active">Revenue in range <strong>{{ shop_currency() }} {{ number_format($kpi->total_sales,2) }}</strong></span>
              <span id="summaryOrders" class="analytics-summary-pill">Paid orders <strong>{{ number_format($kpi->total_orders) }}</strong></span>
            </div>
          </div>
          <div class="p-4">
            <div id="chartMessage" class="analytics-chart-empty hidden"></div>
            <div id="chartCanvasWrap" class="analytics-chart-stage">
              <canvas id="revenueChart" class="h-72 w-full"></canvas>
              <canvas id="ordersChart" class="hidden h-72 w-full"></canvas>
            </div>
            <noscript>
              <div class="analytics-chart-empty mt-4">
                <p>Charts need JavaScript enabled.</p>
                <p>Revenue tracks paid sales totals per day, and Orders tracks the paid order count per day.</p>
              </div>
            </noscript>
          </div>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm glass">
          <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <h6 class="analytics-panel-title mb-0 font-semibold"><i class="fas fa-calendar-week mr-2" style="color:var(--brand)"></i>Sales by Day of Week</h6>
          </div>
          <div class="p-4">
            <div id="dowMessage" class="analytics-chart-empty hidden"></div>
            <div id="dowChartWrap" class="analytics-chart-stage min-h-[16rem]">
              <canvas id="dowChart" class="h-64 w-full"></canvas>
            </div>
          </div>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div>
            <div class="glass h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 font-semibold sm:flex-row sm:items-center sm:justify-between">
                <span class="analytics-panel-title"><i class="fas fa-ranking-star mr-2" style="color:var(--brand)"></i>Top Products</span>
                <button type="button" id="exportTopCsv" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"><i class="fas fa-file-csv mr-1"></i>Export</button>
              </div>
              <div class="p-0">
                <div class="overflow-x-auto">
                  <table id="topProductsTable" class="analytics-table min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
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
                        <tr><td colspan="3" class="analytics-panel-subtle py-4 text-center">No products in this range.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="glass h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 font-semibold sm:flex-row sm:items-center sm:justify-between">
                <span class="analytics-panel-title"><i class="fas fa-bolt mr-2" style="color:var(--brand)"></i>Listing Performance</span>
                <button type="button" id="exportPerfCsv" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"><i class="fas fa-file-csv mr-1"></i>Export</button>
              </div>
              <div class="p-0">
                <div class="overflow-x-auto">
                  <table id="performanceTable" class="analytics-table min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
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
                            <span class="analytics-panel-subtle text-xs">{{ $conv }}%</span>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="4" class="analytics-panel-subtle py-4 text-center">No listing activity in this range.</td></tr>
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
<script>
window.__sellerAnalytics = {
    brand: getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#27b105',
    currency: @json(shop_currency()),
    rangeLabel: @json($rangeLabel),
    labels: @json($chart['labels']),
    revenue: @json($chart['revenue']),
    orders: @json($chart['orders']),
    dowLabels: @json($dowLabels ?? []),
    dowSeries: @json($dowSeries ?? []),
};
</script>
@vite('resources/js/seller-analytics.js')
@endpush
