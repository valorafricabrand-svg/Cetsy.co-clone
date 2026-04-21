@extends('theme.'.theme().'.layouts.app')

@section('title', 'Payment Method Details')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
 <h2 class="mb-0">Payment Method Details</h2>
 <div class="flex flex-wrap gap-2">
 <a href="{{ route('seller.payment-methods.edit', $paymentMethod->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
 <i class="fas fa-edit mr-2"></i>Edit
 </a>
 <a href="{{ route('seller.payment-methods.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">
 <i class="fas fa-arrow-left mr-2"></i>Back to Payment Methods
 </a>
 </div>
 </div>

 @if(session('success'))
 <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800" role="alert">
 {{ session('success') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
 <div class="col-span-12 lg:col-span-8">
 <!-- Payment Method Information -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Payment Method Information</h5>
 </div>
 <div class="p-4">
 <dl class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-0">
 <dt class="col-span-12 md:col-span-6 lg:col-span-3">ID</dt>
 <dd class="col-span-12 md:col-span-9">{{ $paymentMethod->id }}</dd>

 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Payment Type</dt>
 <dd class="col-span-12 md:col-span-9">
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
 </dd>

 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Account Name</dt>
 <dd class="col-span-12 md:col-span-9">
 <strong>{{ $paymentMethod->account_name }}</strong>
 </dd>

 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Account Number</dt>
 <dd class="col-span-12 md:col-span-9">
 <code class="text-base break-all">{{ $paymentMethod->account_number }}</code>
 </dd>

 @if($paymentMethod->bank_name || $paymentMethod->bank_country || $paymentMethod->bank_currency || $paymentMethod->bank_routing_number || $paymentMethod->swift_bic || $paymentMethod->iban || $paymentMethod->bank_address)
 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Bank Details</dt>
 <dd class="col-span-12 md:col-span-9">
 <div class="text-xs text-slate-500">
 @if($paymentMethod->bank_name)<div><strong>Bank:</strong> {{ $paymentMethod->bank_name }}</div>@endif
 @if($paymentMethod->bank_country)<div><strong>Country:</strong> {{ $paymentMethod->bank_country }}</div>@endif
 @if($paymentMethod->bank_currency)<div><strong>Currency:</strong> {{ $paymentMethod->bank_currency }}</div>@endif
 @if($paymentMethod->bank_routing_number)<div><strong>Routing/Sort:</strong> {{ $paymentMethod->bank_routing_number }}</div>@endif
 @if($paymentMethod->swift_bic)<div><strong>SWIFT/BIC:</strong> {{ $paymentMethod->swift_bic }}</div>@endif
 @if($paymentMethod->iban)<div><strong>IBAN:</strong> {{ $paymentMethod->iban }}</div>@endif
 @if($paymentMethod->bank_address)<div><strong>Bank Address:</strong> {{ $paymentMethod->bank_address }}</div>@endif
 </div>
 </dd>
 @endif

 @if($paymentMethod->wise_email || $paymentMethod->wise_recipient_id || $paymentMethod->wise_profile_id)
 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Wise Details</dt>
 <dd class="col-span-12 md:col-span-9">
 <div class="text-xs text-slate-500">
 @if($paymentMethod->wise_email)<div><strong>Email:</strong> {{ $paymentMethod->wise_email }}</div>@endif
 @if($paymentMethod->wise_recipient_id)<div><strong>Recipient ID:</strong> {{ $paymentMethod->wise_recipient_id }}</div>@endif
 @if($paymentMethod->wise_profile_id)<div><strong>Profile ID:</strong> {{ $paymentMethod->wise_profile_id }}</div>@endif
 </div>
 </dd>
 @endif

 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Status</dt>
 <dd class="col-span-12 md:col-span-9">
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">Active</span>
 </dd>

 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Created</dt>
 <dd class="col-span-12 md:col-span-9">{{ $paymentMethod->created_at->format('d M Y, h:i A') }}</dd>

 <dt class="col-span-12 md:col-span-6 lg:col-span-3">Last Updated</dt>
 <dd class="col-span-12 md:col-span-9">{{ $paymentMethod->updated_at->format('d M Y, h:i A') }}</dd>
 </dl>
 </div>
 </div>

 <!-- Payment Type Details -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Payment Type Details</h5>
 </div>
 <div class="p-4">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
 <div class="col-span-12 md:col-span-6">
 <strong>Type Name:</strong><br>
 <span class="text-slate-500">{{ $paymentMethod->paymentType->name }}</span>
 </div>
 <div class="col-span-12 md:col-span-6">
 <strong>Status:</strong><br>
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $paymentMethod->paymentType->status === 'active' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-700 border-slate-300' }}">
 {{ ucfirst($paymentMethod->paymentType->status) }}
 </span>
 </div>
 </div>
 @if($paymentMethod->paymentType->description)
 <div class="mt-3">
 <strong>Description:</strong><br>
 <span class="text-slate-500">{{ $paymentMethod->paymentType->description }}</span>
 </div>
 @endif
 </div>
 </div>
 </div>

 <div class="col-span-12 lg:col-span-4">
 <!-- Payment Type Image -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Payment Type Image</h5>
 </div>
 <div class="p-4 text-center">
 @if($paymentMethod->paymentType->image)
 <img src="{{ asset('storage/' . $paymentMethod->paymentType->image) }}" 
 alt="{{ $paymentMethod->paymentType->name }}" 
 class="h-auto max-w-full rounded shadow-sm" 
 style="max-height: 200px;">
 <div class="mt-2">
 <span class="text-slate-500 text-xs">{{ $paymentMethod->paymentType->name }}</span>
 </div>
 @else
 <div class="py-4">
 <i class="fas fa-credit-card text-slate-500 mb-2" style="font-size: 3rem;"></i>
 <p class="text-slate-500 mb-0">No image available</p>
 </div>
 @endif
 </div>
 </div>

 <!-- Quick Actions -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Quick Actions</h5>
 </div>
 <div class="p-4">
 <div class="grid gap-2">
 <a href="{{ route('seller.payment-methods.edit', $paymentMethod->id) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
 <i class="fas fa-edit mr-2"></i>Edit Payment Method
 </a>
 <form action="{{ route('seller.payment-methods.destroy', $paymentMethod->id) }}" 
 method="POST" 
 class="grid"
 onsubmit="return confirm('Are you sure you want to delete this payment method? This action cannot be undone.')">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-rose-600 text-rose-700 hover:bg-rose-50">
 <i class="fas fa-trash mr-2"></i>Delete Payment Method
 </button>
 </form>
 </div>
 </div>
 </div>

 <!-- Information Card -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">
 <i class="fas fa-info-circle mr-2"></i>Information
 </h5>
 </div>
 <div class="p-4">
 <div class="mb-3">
 <h6 class="text-emerald-600">Payment Processing</h6>
 <p class="text-xs text-slate-500 mb-0">
 This payment method will be used to deposit funds into and withdraw funds from your digital wallet.
 </p>
 </div>
 <div class="mb-3">
 <h6 class="text-emerald-600">Security</h6>
 <p class="text-xs text-slate-500 mb-0">
 Your payment information is securely stored and encrypted.
 </p>
 </div>
 <div>
 <h6 class="text-amber-600">Important</h6>
 <p class="text-xs text-slate-500 mb-0">
 Keep your account information up to date for smooth transactions.
 </p>
 </div>
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






