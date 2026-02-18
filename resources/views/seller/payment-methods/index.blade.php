@extends('theme.'.theme().'.layouts.app')

@section('title', 'Payment Methods')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        
        <div class="flex justify-between items-center mb-4">
            <h2 class="mb-0">Payment Methods</h2>
            <a href="{{ route('seller.shops.show', $shop) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Shop
            </a>
            <a href="{{ route('seller.payment-methods.create') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                <i class="fas fa-plus mr-2"></i>Add Payment Method
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
            <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
                <h5 class="mb-0">My Payment Methods</h5>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm table-striped table-hover mb-0 align-middle">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th>#</th>
                            <th>Payment Type</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentMethods as $paymentMethod)
                            <tr>
                                <td>{{ $paymentMethod->id }}</td>
                                <td>
                                    <div class="flex items-center">
                                        @if($paymentMethod->paymentType->image)
                                            <img src="{{ asset('storage/' . $paymentMethod->paymentType->image) }}" 
                                                 alt="{{ $paymentMethod->paymentType->name }}" 
                                                 class="rounded mr-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="bg-slate-100 text-slate-700 border-slate-200 rounded mr-2 flex items-center justify-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-credit-card text-white" style="font-size: 14px;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $paymentMethod->paymentType->name }}</strong>
                                            @if($paymentMethod->paymentType->description)
                                                <br><span class="text-slate-500 text-xs">{{ $paymentMethod->paymentType->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $paymentMethod->account_name }}</td>
                                <td>
                                    <code>{{ $paymentMethod->account_number }}</code>
                                </td>
                                <td>{{ $paymentMethod->created_at->format('d M Y') }}</td>
                                <td class="text-right">
                                    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 p-1" role="group">
                                        <a href="{{ route('seller.payment-methods.show', $paymentMethod->id) }}" 
                                           class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('seller.payment-methods.edit', $paymentMethod->id) }}" 
                                           class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('seller.payment-methods.destroy', $paymentMethod->id) }}" 
                                              method="POST" 
                                              class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 text-xs rounded-lg" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-slate-500">
                                        <i class="fas fa-credit-card mb-3" style="font-size: 3rem;"></i>
                                        <h5>No Payment Methods Found</h5>
                                        <p class="mb-3">You haven't added any payment methods yet.</p>
                                        <a href="{{ route('seller.payment-methods.create') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                            <i class="fas fa-plus mr-2"></i>Add Your First Payment Method
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentMethods->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $paymentMethods->links() }}
                </div>
            @endif
        </div>

        @if($paymentMethods->count() > 0)
            <div class="mt-4">
                <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle mr-2"></i>Payment Method Information
                    </h6>
                    <p class="mb-0">
                        These are the payment methods you've configured to receive payments from your customers. 
                        Make sure to keep your account information up to date for smooth transactions.
                    </p>
                </div>
            </div>
        @endif

    </div>
</div>
      </div>
    </div>
  </div>
</section>
@endsection 




