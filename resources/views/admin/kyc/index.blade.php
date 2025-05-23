@extends('layouts.app')

@section('title', 'KYC Management')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Pending KYC Verifications</h2>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="w-full overflow-x-auto">
            <table class="min-w-max min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Type</th>
                        <th class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                        <th class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Submitted</th>
                        <th class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Documents</th>
                        <th class="px-2 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingKycs as $kyc)
                        <tr>
                            <td class="px-2 sm:px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.kyc.showDetails', $kyc) }}" class="block group">
                                    <div class="text-sm font-medium text-indigo-700 group-hover:underline">
                                        {{ $kyc->first_name }} {{ $kyc->last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $kyc->email }}</div>
                                    <div class="text-sm text-gray-500">{{ $kyc->phone }}</div>
                                </a>
                            </td>
                            <td class="px-2 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ucfirst($kyc->id_type) }}
                            </td>
                            <td class="px-2 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $kyc->id_number }}
                            </td>
                            <td class="px-2 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                {{ $kyc->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-2 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                <div class="flex flex-wrap space-x-2">
                                    <a href="{{ Storage::url($kyc->id_front) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">ID Front</a>
                                    <a href="{{ Storage::url($kyc->id_back) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">ID Back</a>
                                    <a href="{{ Storage::url($kyc->selfie) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Selfie</a>
                                </div>
                            </td>
                            <td class="px-2 sm:px-6 py-4 whitespace-nowrap text-sm font-medium align-middle bg-gray-50">
                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-stretch sm:items-center w-full">
                                    <button
                                        onclick="showKycModal({{ $kyc->id }}, 'approve')"
                                        class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-green-400 text-white font-bold text-sm sm:text-base px-3 sm:px-5 py-2 rounded-lg shadow border border-green-700 transition w-full sm:w-auto"
                                        aria-label="Approve KYC"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approve
                                    </button>
                                    <button
                                        onclick="showKycModal({{ $kyc->id }}, 'reject')"
                                        class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-400 text-white font-bold text-sm sm:text-base px-3 sm:px-5 py-2 rounded-lg shadow border border-red-700 transition w-full sm:w-auto"
                                        aria-label="Reject KYC"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No pending KYC verifications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pendingKycs->links() }}
        </div>
    </div>
</div>

<!-- KYC Modal -->
<div id="kycModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-200 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 pt-12 relative animate-fade-in min-h-[220px] sm:min-h-[260px] flex flex-col justify-between overflow-y-auto" style="max-height:90vh;">
        <!-- Close Button -->
        <button type="button" onclick="hideKycModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none text-2xl z-10" aria-label="Close">
            &times;
        </button>
        <!-- Modal Title -->
        <h3 id="kycModalTitle" class="text-2xl font-bold text-gray-800 mb-6 mt-0">Approve KYC Verification</h3>
        <!-- Modal Form -->
        <form id="kycModalForm" action="" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="kycModalStatus" value="approved">
            <div id="kycModalNotesDiv" class="hidden">
                <label for="kycModalNotes" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                <textarea name="admin_notes" id="kycModalNotes" rows="3"
                    class="w-full rounded-md border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 shadow-sm p-2"
                    placeholder="Enter reason for rejection..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pb-2">
                <button type="button" onclick="hideKycModal()"
                    class="px-5 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold transition">
                    Cancel
                </button>
                <button type="submit" id="kycModalSubmitBtn"
                    class="px-5 py-2 rounded-lg font-semibold text-white transition
                        bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-green-400
                    ">
                    Approve
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showKycModal(kycId, action) {
        const modal = document.getElementById('kycModal');
        const form = document.getElementById('kycModalForm');
        const statusInput = document.getElementById('kycModalStatus');
        const notesDiv = document.getElementById('kycModalNotesDiv');
        const notesInput = document.getElementById('kycModalNotes');
        const title = document.getElementById('kycModalTitle');
        const submitBtn = document.getElementById('kycModalSubmitBtn');

        form.action = `/admin/kyc/${kycId}`;
        if (action === 'approve') {
            statusInput.value = 'approved';
            notesDiv.classList.add('hidden');
            notesInput.required = false;
            title.textContent = 'Approve KYC Verification';
            submitBtn.textContent = 'Approve';
            submitBtn.className = 'px-5 py-2 rounded-lg font-semibold text-white transition bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-green-400';
        } else {
            statusInput.value = 'rejected';
            notesDiv.classList.remove('hidden');
            notesInput.required = true;
            title.textContent = 'Reject KYC Verification';
            submitBtn.textContent = 'Reject';
            submitBtn.className = 'px-5 py-2 rounded-lg font-semibold text-white transition bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-400';
        }
        notesInput.value = '';
        modal.classList.remove('hidden');
    }

    function hideKycModal() {
        const modal = document.getElementById('kycModal');
        modal.classList.add('hidden');
    }
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(40px);}
    to { opacity: 1; transform: translateY(0);}
}
.animate-fade-in {
    animation: fade-in 0.3s ease;
}
</style> 

@endsection

