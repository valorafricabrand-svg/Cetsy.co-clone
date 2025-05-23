@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-2xl font-bold mb-4">KYC Verification</h2>
  @if(session('success'))
    <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
  @endif
  @if($kyc && $kyc->status !== 'rejected')
    <div class="mb-4">
      <p>Status: <span class="font-semibold">{{ ucfirst($kyc->status) }}</span></p>
      @if($kyc->status === 'pending')
        <p class="text-yellow-600">Your KYC is under review.</p>
      @elseif($kyc->status === 'approved')
        <p class="text-green-600">Your KYC is approved. You can now access all seller features.</p>
      @endif
    </div>
    @if($kyc->admin_notes)
      <div class="mb-4 p-2 bg-gray-100 rounded">
        <strong>Admin Notes:</strong> {{ $kyc->admin_notes }}
      </div>
    @endif
  @endif

  @if(!$kyc || $kyc->status === 'rejected')
    <form action="{{ route('seller.kyc.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label class="block font-medium">First Name</label>
        <input type="text" name="first_name" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block font-medium">Last Name</label>
        <input type="text" name="last_name" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block font-medium">Email</label>
        <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block font-medium">Phone</label>
        <input type="text" name="phone" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block font-medium">ID Number</label>
        <input type="text" name="id_number" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block font-medium">ID Type</label>
        <select name="id_type" class="w-full border rounded px-3 py-2" required>
          <option value="">Select ID Type</option>
          <option value="national_id">National ID</option>
          <option value="passport">Passport</option>
          <option value="driver_license">Driver's License</option>
        </select>
      </div>
      <div>
        <label class="block font-medium">Upload ID Front (PDF/JPG/PNG)</label>
        <input type="file" name="id_front" class="w-full" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <div>
        <label class="block font-medium">Upload ID Back (PDF/JPG/PNG)</label>
        <input type="file" name="id_back" class="w-full" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <div>
        <label class="block font-medium">Upload Selfie</label>
        <input type="file" name="selfie" class="w-full" accept=".jpg,.jpeg,.png" required>
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit KYC</button>
    </form>
  @endif
</div>
@endsection