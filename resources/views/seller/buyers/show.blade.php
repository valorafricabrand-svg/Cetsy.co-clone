@extends('theme.'.theme().'.layouts.app')

@section('title', 'Buyer Details - ' . $buyer->name)

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    {{-- Return to Admin Button (when impersonating) --}}
    @if(session('impersonating'))
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mb-4" role="alert">
            <div class="flex items-center justify-between">
                <div>
                    <i class="fas fa-user-secret mr-2"></i>
                    <strong>Admin Impersonation Active</strong>
                    <br>
                    <span class="text-xs">You are currently logged in as {{ auth()->user()->name }} (Seller)</span>
                </div>
                <a href="{{ route('admin.return-from-impersonation') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400 px-2.5 py-1.5 text-xs rounded-lg">
                    <i class="fas fa-arrow-left mr-1"></i> Return to Admin
                </a>
            </div>
        </div>
    @endif`r`n<div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-x-4 gap-y-4">
        <div class="col-span-12">
            {{-- Page Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <a href="{{ route('seller.buyers.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg mr-3">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Buyers
                    </a>
                    <h2 class="text-lg font-semibold mb-0">Buyer Details</h2>
                </div>
            </div>

            {{-- Buyer Information Card --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-y-4 mb-4">
                <div class="col-span-12 lg:col-span-8">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                        <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
                            <h3 class="text-base font-semibold mb-0">Customer Information</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                <div class="col-span-12 md:col-span-6 lg:col-span-3 text-center mb-3">
                                    <img src="{{ $buyer->get_gravatar(100) }}" 
                                         alt="{{ $buyer->name }}" 
                                         class="rounded-full mb-3" 
                                         width="100" 
                                         height="100">
                                </div>
                                <div class="col-span-12 md:col-span-9">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                        <div class="col-span-12 md:col-span-6 mb-3">
                                            <label class="form-label text-slate-500 text-xs">Name</label>
                                            <div class="font-semibold">{{ $buyer->name }}</div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mb-3">
                                            <label class="form-label text-slate-500 text-xs">Email</label>
                                            <div class="font-semibold">{{ $buyer->email }}</div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mb-3">
                                            <label class="form-label text-slate-500 text-xs">Phone</label>
                                            <div class="font-semibold">{{ $buyer->phone ?? 'Not provided' }}</div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mb-3">
                                            <label class="form-label text-slate-500 text-xs">Member Since</label>
                                            <div class="font-semibold">{{ $buyer->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                        <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
                            <h3 class="text-base font-semibold mb-0">Purchase Summary</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-12 text-center">
                                <div class="col-span-12 md:col-span-6 mb-3">
                                    <div class="text-emerald-600">{{ $orders->count() }}</div>
                                    <div class="text-slate-500 text-xs">Total Orders</div>
                                </div>
                                <div class="col-span-12 md:col-span-6 mb-3">
                                    <div class="text-emerald-600">${{ number_format($totalSpent, 2) }}</div>
                                    <div class="text-slate-500 text-xs">Total Spent</div>
                                </div>
                                <div class="col-span-12 md:col-span-6 mb-3">
                                    <div class="text-amber-600">${{ $orders->count() > 0 ? number_format($totalSpent / $orders->count(), 2) : '0.00' }}</div>
                                    <div class="text-slate-500 text-xs">Avg. Order Value</div>
                                </div>
                                <div class="col-span-12 md:col-span-6 mb-3">
                                    <div class="text-sky-600">{{ $orders->where('created_at', '>=', now()->subDays(30))->count() }}</div>
                                    <div class="text-slate-500 text-xs">Orders (30 days)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orders Table --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
                    <h3 class="text-base font-semibold mb-0">Order History</h3>
                </div>
                <div class="p-4 p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm align-middle mb-0">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th scope="col" class="pl-4">Order ID</th>
                                    <th scope="col">Items</th>
                                    <th scope="col" class="text-center">Total</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-center">Date</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="pl-4">
                                            <span class="font-semibold">#{{ $order->id }}</span>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                @foreach($order->items as $item)
                                                    <div class="flex items-center mb-1">
                                                        <img src="{{ asset('images/default-thumb.jpg') }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             class="rounded mr-2" 
                                                             width="30" 
                                                             height="30">
                                                        <div>
                                                            <div class="font-semibold text-xs">{{ $item->product->name }}</div>
                                                            <div class="text-slate-500 text-xs">Qty: {{ $item->quantity }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="font-semibold text-emerald-600">${{ number_format($order->items->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $order->getStatusBadgeClass() }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-slate-500">{{ $order->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('seller.orders.show', $order->id) }}" 
                                               class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                <i class="fas fa-eye mr-1"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-slate-500 py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-shopping-cart fa-3x text-slate-500"></i>
                                            </div>
                                            <h5>No Orders Found</h5>
                                            <p class="mb-0">This buyer hasn't placed any orders with your shop.</p>
                                        </td>
                                    </tr>
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
  </div>
</section>
@endsection 







