@extends('layouts.app')

@section('title', 'KYC Details')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <a href="{{ route('admin.kyc.index') }}" class="inline-block mb-4 text-indigo-600 hover:underline">&larr; Back to KYC List</a>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">KYC Details</h2>
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-700 mb-2">Seller Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Name</div>
                    <div class="text-base text-gray-900 font-medium">{{ $kyc->first_name }} {{ $kyc->last_name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="text-base text-gray-900 font-medium">{{ $kyc->email }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Phone</div>
                    <div class="text-base text-gray-900 font-medium">{{ $kyc->phone }}</div>
                </div>
            </div>
        </div>
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-700 mb-2">KYC Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">ID Type</div>
                    <div class="text-base text-gray-900 font-medium">{{ ucfirst($kyc->id_type) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">ID Number</div>
                    <div class="text-base text-gray-900 font-medium">{{ $kyc->id_number }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="text-base text-gray-900 font-medium capitalize">{{ $kyc->status }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Submitted At</div>
                    <div class="text-base text-gray-900 font-medium">{{ $kyc->created_at->format('M d, Y H:i') }}</div>
                </div>
                @if($kyc->admin_notes)
                <div class="sm:col-span-2">
                    <div class="text-sm text-gray-500">Admin Notes</div>
                    <div class="text-base text-gray-900 font-medium">{{ $kyc->admin_notes }}</div>
                </div>
                @endif
            </div>
        </div>
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-700 mb-2">Documents</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500 mb-1">ID Front</div>
                    <a href="{{ Storage::url($kyc->id_front) }}" target="_blank" class="block border rounded p-2 hover:shadow">
                        <img src="{{ Storage::url($kyc->id_front) }}" alt="ID Front" class="w-full h-32 object-contain mb-2" onerror="this.style.display='none'">
                        <span class="text-indigo-600 hover:underline">View File</span>
                    </a>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">ID Back</div>
                    <a href="{{ Storage::url($kyc->id_back) }}" target="_blank" class="block border rounded p-2 hover:shadow">
                        <img src="{{ Storage::url($kyc->id_back) }}" alt="ID Back" class="w-full h-32 object-contain mb-2" onerror="this.style.display='none'">
                        <span class="text-indigo-600 hover:underline">View File</span>
                    </a>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">Selfie</div>
                    <a href="{{ Storage::url($kyc->selfie) }}" target="_blank" class="block border rounded p-2 hover:shadow">
                        <img src="{{ Storage::url($kyc->selfie) }}" alt="Selfie" class="w-full h-32 object-contain mb-2" onerror="this.style.display='none'">
                        <span class="text-indigo-600 hover:underline">View File</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 