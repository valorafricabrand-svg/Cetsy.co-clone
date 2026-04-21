@extends('theme.'.theme().'.layouts.app')

@section('title', 'Add Payment Method')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
 @include('seller.partials.sidebar')
 <div class="space-y-6">
<div class="content">
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
 
 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
 <h2 class="mb-0">Add Payment Method</h2>
 <a href="{{ route('seller.payment-methods.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100">
 <i class="fas fa-arrow-left mr-2"></i>Back to Payment Methods
 </a>
 </div>

 @if(session('error'))
 <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-700" role="alert">
 {{ session('error') }}
 <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="alert" aria-label="Close">&times;</button>
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
 <div class="col-span-12 lg:col-span-8">
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Payment Method Details</h5>
 </div>
 <div class="p-4">
 <form action="{{ route('seller.payment-methods.store') }}" method="POST">
 @csrf
 
 <!-- Payment Type Selection -->
 <div class="mb-4">
 <label for="payment_type_id" class="form-label">Payment Type <span class="text-rose-600">*</span></label>
 <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('payment_type_id') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" 
 id="payment_type_id" name="payment_type_id" required>
 <option value="">Select a payment type</option>
 @foreach($paymentTypes as $paymentType)
 <option value="{{ $paymentType->id }}" 
 {{ old('payment_type_id') == $paymentType->id ? 'selected' : '' }}>
 {{ $paymentType->name }}
 @if($paymentType->description)
 - {{ $paymentType->description }}
 @endif
 </option>
 @endforeach
 </select>
 @error('payment_type_id')
 <div class="invalid-feedback">{{ $message }}</div>
 @enderror
 <span class="form-text text-slate-500 text-xs">
 Choose the type of payment method you want to add (e.g., Bank Transfer, PayPal, etc.)
 </span>
 </div>

 <!-- Account Name -->
 <div class="mb-3">
 <label for="account_name" class="form-label">Account Name <span class="text-rose-600">*</span></label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('account_name') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" 
 id="account_name" name="account_name" 
 value="{{ old('account_name') }}" 
 placeholder="Enter the account holder name" required>
 @error('account_name')
 <div class="invalid-feedback">{{ $message }}</div>
 @enderror
 <span class="form-text text-slate-500 text-xs">
 The name that appears on the account (e.g., John Doe)
 </span>
 </div>

 <!-- Account Number -->
 <div class="mb-4">
 <label for="account_number" class="form-label">Account Number <span class="text-rose-600">*</span></label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('account_number') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" 
 id="account_number" name="account_number" 
 value="{{ old('account_number') }}" 
 placeholder="Enter account number, email, or phone number" required>
 @error('account_number')
 <div class="invalid-feedback">{{ $message }}</div>
 @enderror
 <span class="form-text text-slate-500 text-xs">
 This could be a bank account number, PayPal email, phone number, etc.
 </span>
 </div>

 <hr class="my-4">
 <h6 class="mb-3">Bank Transfer / SWIFT (Optional)</h6>
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="bank_name">Bank Name</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('bank_name') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
 @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <label class="form-label" for="bank_country">Bank Country</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('bank_country') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="bank_country" name="bank_country" value="{{ old('bank_country') }}" placeholder="e.g. US">
 @error('bank_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6 lg:col-span-3">
 <label class="form-label" for="bank_currency">Currency</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('bank_currency') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="bank_currency" name="bank_currency" value="{{ old('bank_currency') }}" placeholder="e.g. USD">
 @error('bank_currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="bank_routing_number">Routing/Sort Code</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('bank_routing_number') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="bank_routing_number" name="bank_routing_number" value="{{ old('bank_routing_number') }}">
 @error('bank_routing_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="swift_bic">SWIFT/BIC</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('swift_bic') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="swift_bic" name="swift_bic" value="{{ old('swift_bic') }}">
 @error('swift_bic')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="iban">IBAN</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('iban') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="iban" name="iban" value="{{ old('iban') }}">
 @error('iban')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="bank_address">Bank Address</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('bank_address') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="bank_address" name="bank_address" value="{{ old('bank_address') }}">
 @error('bank_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 </div>

 <hr class="my-4">
 <h6 class="mb-3">Wise (Optional)</h6>
 <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="wise_email">Wise Email</label>
 <input type="email" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('wise_email') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="wise_email" name="wise_email" value="{{ old('wise_email') }}">
 @error('wise_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="wise_recipient_id">Wise Recipient ID</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('wise_recipient_id') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="wise_recipient_id" name="wise_recipient_id" value="{{ old('wise_recipient_id') }}">
 @error('wise_recipient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 <div class="col-span-12 md:col-span-6">
 <label class="form-label" for="wise_profile_id">Wise Profile ID</label>
 <input type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('wise_profile_id') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror" id="wise_profile_id" name="wise_profile_id" value="{{ old('wise_profile_id') }}">
 @error('wise_profile_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
 </div>
 </div>

 <!-- Submit Buttons -->
 <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
 <a href="{{ route('seller.payment-methods.index') }}" class="inline-flex w-full items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 sm:w-auto">
 Cancel
 </a>
 <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 sm:w-auto">
 <i class="fas fa-save mr-2"></i>Add Payment Method
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>

 <div class="col-span-12 lg:col-span-4">
 <!-- Payment Type Preview -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">Selected Payment Type</h5>
 </div>
 <div class="p-4">
 <div id="paymentTypePreview" class="text-center py-4" style="display: none;">
 <div id="paymentTypeImage" class="mb-3">
 <!-- Payment type image will be displayed here -->
 </div>
 <h6 id="paymentTypeName" class="mb-2"></h6>
 <p id="paymentTypeDescription" class="text-slate-500 text-xs mb-0"></p>
 </div>
 <div id="noPaymentTypeSelected" class="text-center py-4">
 <i class="fas fa-credit-card text-slate-500 mb-2" style="font-size: 2rem;"></i>
 <p class="text-slate-500 mb-0">Select a payment type to see details</p>
 </div>
 </div>
 </div>

 <!-- Help Information -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 mt-4">
 <div class="border-b border-slate-200 px-4 py-3 bg-slate-50">
 <h5 class="mb-0">
 <i class="fas fa-info-circle mr-2"></i>Help
 </h5>
 </div>
 <div class="p-4">
 <div class="mb-3">
 <h6 class="text-emerald-600">Why add payment methods?</h6>
 <p class="text-xs text-slate-500 mb-0">
 Adding payment methods allows customers to pay you directly through your preferred channels.
 </p>
 </div>
 <div class="mb-3">
 <h6 class="text-emerald-600">Security</h6>
 <p class="text-xs text-slate-500 mb-0">
 Your payment information is securely stored and only used for processing transactions.
 </p>
 </div>
 <div>
 <h6 class="text-amber-600">Important</h6>
 <p class="text-xs text-slate-500 mb-0">
 Make sure to provide accurate account information to avoid payment delays.
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
 const paymentTypeSelect = document.getElementById('payment_type_id');
 const paymentTypePreview = document.getElementById('paymentTypePreview');
 const noPaymentTypeSelected = document.getElementById('noPaymentTypeSelected');
 const paymentTypeImage = document.getElementById('paymentTypeImage');
 const paymentTypeName = document.getElementById('paymentTypeName');
 const paymentTypeDescription = document.getElementById('paymentTypeDescription');

 // Payment types data (you might want to pass this from the controller)
 const paymentTypes = @json($paymentTypes);

 paymentTypeSelect.addEventListener('change', function() {
 const selectedValue = this.value;
 
 if (selectedValue) {
 const selectedPaymentType = paymentTypes.find(pt => pt.id == selectedValue);
 
 if (selectedPaymentType) {
 // Update preview content
 if (selectedPaymentType.image) {
 paymentTypeImage.innerHTML = `
 <img src="/storage/${selectedPaymentType.image}" 
 alt="${selectedPaymentType.name}" 
 class="h-auto max-w-full rounded" 
 style="max-height: 80px;">
 `;
 } else {
 paymentTypeImage.innerHTML = `
 <div class="bg-slate-100 text-slate-700 border-slate-200 rounded flex items-center justify-center mx-auto" 
 style="width: 80px; height: 80px;">
 <i class="fas fa-credit-card text-white" style="font-size: 2rem;"></i>
 </div>
 `;
 }
 
 paymentTypeName.textContent = selectedPaymentType.name;
 paymentTypeDescription.textContent = selectedPaymentType.description || 'No description available';
 
 // Show preview, hide placeholder
 paymentTypePreview.style.display = 'block';
 noPaymentTypeSelected.style.display = 'none';
 }
 } else {
 // Hide preview, show placeholder
 paymentTypePreview.style.display = 'none';
 noPaymentTypeSelected.style.display = 'block';
 }
 });

 // Trigger change event on page load if there's a selected value
 if (paymentTypeSelect.value) {
 paymentTypeSelect.dispatchEvent(new Event('change'));
 }
});
</script>
@endpush 






