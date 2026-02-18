@extends('theme.'.theme().'.layouts.app')

@section('title', 'My Buyers')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    {{-- Return to Admin Button (when impersonating) --}}
    @if(session('impersonating'))
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 alert-dismissible fade show mb-4" role="alert">
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
                <h2 class="h5 font-semibold mb-0">My Buyers</h2>
                <div class="flex items-center">
                    <span class="text-slate-500 mr-3">
                        <i class="fas fa-users mr-2 text-sky-600" style="color: #027333;"></i>
                        {{ $buyers->count() }} Total Buyers
                    </span>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-y-4 mb-4">
                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                        <div class="p-4 text-center">
                            <div class="mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-emerald-600">{{ $buyers->count() }}</div>
                            <div class="text-slate-500 text-xs">Total Buyers</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                        <div class="p-4 text-center">
                            <div class="mb-2">
                                <i class="fas fa-shopping-cart fa-xl text-emerald-600"></i>
                            </div>
                            <div class="text-emerald-600">{{ $buyers->sum('total_orders') }}</div>
                            <div class="text-slate-500 text-xs">Total Orders</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                        <div class="p-4 text-center">
                            <div class="mb-2">
                                <i class="fas fa-dollar-sign fa-xl text-amber-600"></i>
                            </div>
                            <div class="text-amber-600">${{ number_format($buyers->sum('total_spent'), 2) }}</div>
                            <div class="text-slate-500 text-xs">Total Revenue</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                        <div class="p-4 text-center">
                            <div class="mb-2">
                                <i class="fas fa-chart-line fa-xl text-sky-600"></i>
                            </div>
                            <div class="text-sky-600">${{ $buyers->count() > 0 ? number_format($buyers->sum('total_spent') / $buyers->count(), 2) : '0.00' }}</div>
                            <div class="text-slate-500 text-xs">Avg. Customer Value</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Buyers Table --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                <div class="border-b border-slate-200 px-4 py-3 bg-white border-0">
                    <h3 class="h6 font-semibold mb-0">Buyer List</h3>
                </div>
                <div class="p-4 p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm table-hover align-middle mb-0">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th scope="col" class="pl-4">Buyer</th>
                                    <th scope="col" class="text-center">Total Orders</th>
                                    <th scope="col" class="text-center">Total Spent</th>
                                    <th scope="col" class="text-center">First Order</th>
                                    <th scope="col" class="text-center">Last Order</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($buyers as $buyerData)
                                    <tr>
                                        <td class="pl-4">
                                            <div class="flex items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <img src="{{ $buyerData['customer']->get_gravatar(40) }}" 
                                                         alt="{{ $buyerData['customer']->name }}" 
                                                         class="rounded-full" 
                                                         width="40" 
                                                         height="40">
                                                </div>
                                                <div>
                                                    <div class="font-semibold">{{ $buyerData['customer']->name }}</div>
                                                    <div class="text-slate-500 text-xs">{{ $buyerData['customer']->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600">{{ $buyerData['total_orders'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="font-semibold text-emerald-600">${{ number_format($buyerData['total_spent'], 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-slate-500">{{ $buyerData['first_order_date']->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-slate-500">{{ $buyerData['last_order_date']->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('seller.buyers.show', $buyerData['customer']->id) }}" 
                                               class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                <i class="fas fa-eye mr-1"></i>
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-slate-500 py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-users fa-3x text-slate-500"></i>
                                            </div>
                                            <h5>No Buyers Yet</h5>
                                            <p class="mb-0">You haven't received any orders from buyers yet.</p>
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






