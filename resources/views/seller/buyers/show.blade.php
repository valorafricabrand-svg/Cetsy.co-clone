@extends('theme.'.theme().'.layouts.app')

@section('title', 'Buyer Details - ' . $buyer->name)

@section('main')
@php
    $buyerThumb = $buyer->avatarOrLogoUrl();
    $orderCount = $orders->count();
    $recentOrderCount = $orders->filter(function ($order) {
        return $order->created_at && $order->created_at->gte(now()->subDays(30));
    })->count();
    $avgOrderValue = $orderCount > 0 ? $totalSpent / $orderCount : 0;
    $currencySymbol = shop_currency(auth()->user()->shop ?? null);

    $statusTone = static function ($status): string {
        return match ((string) $status) {
            \App\Models\Order::STATUS_PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
            \App\Models\Order::STATUS_PROCESSING => 'border-sky-200 bg-sky-50 text-sky-700',
            \App\Models\Order::STATUS_SHIPPED => 'border-indigo-200 bg-indigo-50 text-indigo-700',
            \App\Models\Order::STATUS_DELIVERED,
            \App\Models\Order::STATUS_COMPLETED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Models\Order::STATUS_CANCELLED,
            'refunded' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-50 text-slate-700',
        };
    };

    $orderTotal = static function ($order): float {
        return (float) $order->items->sum(function ($item) {
            return (float) $item->price * (int) $item->quantity;
        });
    };
@endphp
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
        @if(session('impersonating'))
          <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <div class="font-semibold">
                  <i class="fas fa-user-secret mr-2"></i>
                  Admin Impersonation Active
                </div>
                <div class="mt-1 text-xs">You are currently logged in as {{ auth()->user()->name }} (Seller)</div>
              </div>
              <a href="{{ route('admin.return-from-impersonation') }}" class="inline-flex items-center justify-center rounded-xl border border-amber-500 bg-amber-500 px-3 py-2 text-xs font-semibold text-slate-900 hover:bg-amber-400">
                <i class="fas fa-arrow-left mr-1"></i>
                Return to Admin
              </a>
            </div>
          </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
              @if ($buyerThumb)
                <img src="{{ $buyerThumb }}"
                     alt="{{ $buyer->name }}"
                     class="h-16 w-16 shrink-0 rounded-2xl object-cover ring-1 ring-slate-200 sm:h-20 sm:w-20"
                     width="80"
                     height="80">
              @else
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-bold tracking-wide text-emerald-700 ring-1 ring-emerald-200 sm:h-20 sm:w-20 sm:text-2xl">
                  {{ $buyer->avatarInitials() }}
                </div>
              @endif
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Buyer Details</h1>
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                    {{ $orderCount }} {{ \Illuminate\Support\Str::plural('order', $orderCount) }}
                  </span>
                </div>
                <p class="mt-2 text-base font-semibold text-slate-900">{{ $buyer->name }}</p>
                <p class="mt-1 break-words text-sm text-slate-500">{{ $buyer->email }}</p>
                <p class="mt-2 text-sm text-slate-500">View the buyer profile, purchase summary, and order history in a mobile-friendly layout.</p>
              </div>
            </div>

            <div class="flex flex-wrap gap-2">
              <a href="{{ route('seller.buyers.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Buyers
              </a>
            </div>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Total Orders</p>
            <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $orderCount }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Total Spent</p>
            <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $currencySymbol }} {{ number_format($totalSpent, 2) }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">Average Order</p>
            <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $currencySymbol }} {{ number_format($avgOrderValue, 2) }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-sky-700">Last 30 Days</p>
            <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $recentOrderCount }}</p>
          </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,1fr)]">
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Customer Information</h2>
            </div>
            <div class="p-4">
              <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Name</p>
                  <p class="mt-2 text-sm font-semibold text-slate-900 break-words">{{ $buyer->name }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Email</p>
                  <p class="mt-2 text-sm font-semibold text-slate-900 break-words">{{ $buyer->email }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Phone</p>
                  <p class="mt-2 text-sm font-semibold text-slate-900 break-words">{{ $buyer->phone ?? 'Not provided' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Member Since</p>
                  <p class="mt-2 text-sm font-semibold text-slate-900">{{ $buyer->created_at->format('M d, Y') }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Purchase Summary</h2>
            </div>
            <div class="p-4">
              <div class="space-y-3">
                <div class="flex flex-col gap-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                  <span class="text-sm text-slate-500">Total Orders</span>
                  <span class="text-sm font-semibold text-slate-900">{{ $orderCount }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                  <span class="text-sm text-slate-500">Lifetime Spend</span>
                  <span class="text-sm font-semibold text-slate-900">{{ $currencySymbol }} {{ number_format($totalSpent, 2) }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                  <span class="text-sm text-slate-500">Average Order</span>
                  <span class="text-sm font-semibold text-slate-900">{{ $currencySymbol }} {{ number_format($avgOrderValue, 2) }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                  <span class="text-sm text-slate-500">Orders in 30 Days</span>
                  <span class="text-sm font-semibold text-slate-900">{{ $recentOrderCount }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
              <h2 class="text-base font-semibold text-slate-900">Order History</h2>
              <p class="text-xs text-slate-500">Responsive buyer order history matching the rest of the seller area.</p>
            </div>
          </div>

          @if($orders->isNotEmpty())
            <div class="space-y-3 p-4 md:hidden">
              @foreach ($orders as $order)
                @php
                  $total = $orderTotal($order);
                  $quantityTotal = $order->items->sum('quantity');
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                  <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                      <p class="text-sm font-bold text-slate-900">Order #{{ $order->id }}</p>
                      <p class="mt-1 text-xs text-slate-500">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusTone($order->status) }}">
                      {{ $order->getSellerStatusLabel() }}
                    </span>
                  </div>

                  <div class="mt-4 space-y-3">
                    @foreach($order->items as $item)
                      @php
                        $product = $item->product;
                        $thumbUrl = $product ? product_thumb_url($product) : null;
                      @endphp
                      <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white">
                          @if($thumbUrl)
                            <img src="{{ $thumbUrl }}" alt="{{ $product->name ?? 'Order item' }}" class="h-full w-full object-cover" loading="lazy">
                          @else
                            <div class="flex h-full w-full items-center justify-center text-slate-400">
                              <i class="fas fa-box-open"></i>
                            </div>
                          @endif
                        </div>
                        <div class="min-w-0 flex-1">
                          <p class="text-sm font-semibold leading-5 text-slate-900 break-words">{{ $product->name ?? 'Product removed' }}</p>
                          <p class="mt-1 text-xs text-slate-500">Qty: {{ $item->quantity }}</p>
                          <p class="mt-1 text-xs font-semibold text-emerald-700">{{ $currencySymbol }} {{ number_format((float) $item->price * (int) $item->quantity, 2) }}</p>
                        </div>
                      </div>
                    @endforeach
                  </div>

                  <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                      <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Total</p>
                      <p class="mt-2 text-sm font-bold text-slate-900">{{ $currencySymbol }} {{ number_format($total, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                      <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Items</p>
                      <p class="mt-2 text-sm font-bold text-slate-900">{{ $quantityTotal }}</p>
                    </div>
                  </div>

                  <div class="mt-4">
                    <a href="{{ route('seller.orders.show', $order->id) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                      <i class="fas fa-eye mr-2"></i>
                      View Order
                    </a>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="hidden overflow-x-auto md:block">
              <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left font-semibold">Order ID</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold">Items</th>
                    <th scope="col" class="px-4 py-3 text-center font-semibold">Total</th>
                    <th scope="col" class="px-4 py-3 text-center font-semibold">Status</th>
                    <th scope="col" class="px-4 py-3 text-center font-semibold">Date</th>
                    <th scope="col" class="px-4 py-3 text-center font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                  @foreach ($orders as $order)
                    @php $total = $orderTotal($order); @endphp
                    <tr class="align-top">
                      <td class="whitespace-nowrap px-4 py-4">
                        <span class="font-semibold text-slate-900">#{{ $order->id }}</span>
                      </td>
                      <td class="px-4 py-4">
                        <div class="space-y-3">
                          @foreach($order->items as $item)
                            @php
                              $product = $item->product;
                              $thumbUrl = $product ? product_thumb_url($product) : null;
                            @endphp
                            <div class="flex items-start gap-3">
                              <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                @if($thumbUrl)
                                  <img src="{{ $thumbUrl }}" alt="{{ $product->name ?? 'Order item' }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                  <div class="flex h-full w-full items-center justify-center text-slate-400">
                                    <i class="fas fa-box-open"></i>
                                  </div>
                                @endif
                              </div>
                              <div class="min-w-0">
                                <p class="font-semibold leading-5 text-slate-900 break-words">{{ $product->name ?? 'Product removed' }}</p>
                                <p class="mt-1 text-xs text-slate-500">Qty: {{ $item->quantity }}</p>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </td>
                      <td class="whitespace-nowrap px-4 py-4 text-center">
                        <span class="font-semibold text-emerald-700">{{ $currencySymbol }} {{ number_format($total, 2) }}</span>
                      </td>
                      <td class="whitespace-nowrap px-4 py-4 text-center">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusTone($order->status) }}">
                          {{ $order->getSellerStatusLabel() }}
                        </span>
                      </td>
                      <td class="whitespace-nowrap px-4 py-4 text-center text-slate-500">
                        {{ $order->created_at->format('M d, Y') }}
                      </td>
                      <td class="whitespace-nowrap px-4 py-4 text-center">
                        <a href="{{ route('seller.orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                          <i class="fas fa-eye mr-1"></i>
                          View Order
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="p-8 text-center text-slate-500">
              <div class="mb-3 text-4xl text-slate-300">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <h3 class="text-base font-semibold text-slate-900">No Orders Found</h3>
              <p class="mt-2 text-sm">This buyer hasn't placed any orders with your shop yet.</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
