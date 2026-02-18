@extends('theme.'.theme().'.layouts.app')

@section('title', 'Submit Appeal')

@section('main')
<div class="content">
    <div class="grid grid-cols-12 gap-4 justify-center">
        <div class="md:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h4 class="mb-0">Submit Appeal</h4>
                </div>
                <div class="p-4 sm:p-5">
                    @if($errors->any())
                        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Dispute Summary -->
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-4">
                        <h6 class="alert-heading">Dispute Summary</h6>
                        <p class="mb-2"><strong>Type:</strong> {{ $dispute->getTypeLabel() }}</p>
                        <p class="mb-2"><strong>Order:</strong> #{{ $dispute->order->order_number }}</p>
                        <p class="mb-2"><strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}</p>
                        @if($dispute->refund_amount)
                            <p class="mb-0"><strong>Refund Amount:</strong> ${{ number_format($dispute->refund_amount, 2) }}</p>
                        @endif
                    </div>

                    <!-- Appeal Deadline Warning -->
                    @if($dispute->isAppealDeadlineExpired())
                        <div class="rounded-xl border px-4 py-3 text-sm border-rose-200 bg-rose-50 text-rose-800">
                            <h6 class="alert-heading">Appeal Deadline Expired</h6>
                            <p class="mb-0">The appeal deadline has passed. You can no longer appeal this decision.</p>
                        </div>
                    @else
                        @if($dispute->appeal_deadline)
                            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                                <h6 class="alert-heading">Appeal Deadline</h6>
                                <p class="mb-0">
                                    You have <strong>{{ $dispute->getAppealDeadlineDaysLeft() }} days</strong> remaining to submit your appeal.
                                    <br>
                                    <small class="text-slate-500">Deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}</small>
                                </p>
                            </div>
                        @else
                            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                                <h6 class="alert-heading">Appeal Available</h6>
                                <p class="mb-0">You can submit an appeal at any time. There is no deadline for this dispute.</p>
                            </div>
                        @endif

                        <form action="{{ route('disputes.appeal.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Appeal Reason -->
                            <div class="mb-3">
                                <label for="reason" class="mb-1 block text-sm font-medium text-slate-700">Appeal Reason *</label>
                                <textarea name="reason" id="reason" rows="5" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('reason') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                    placeholder="Please explain why you believe the decision should be reconsidered. Provide specific reasons and any new information..." required>{{ old('reason') }}</textarea>
                                <div class="mt-1 text-xs text-slate-500">
                                    Be specific about why you disagree with the decision. Provide new evidence or information that wasn't available during the initial review.
                                </div>
                                @error('reason')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- New Evidence Upload -->
                            <div class="mb-3">
                                <label for="new_evidence" class="mb-1 block text-sm font-medium text-slate-700">New Evidence (Optional)</label>
                                <input type="file" name="new_evidence[]" id="new_evidence" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('new_evidence.*') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                    multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <div class="mt-1 text-xs text-slate-500">
                                    Upload any new documents, screenshots, or photos that support your appeal. 
                                    Max 10MB per file. Supported formats: JPG, PNG, PDF, DOC, DOCX
                                </div>
                                @error('new_evidence.*')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Appeal Process Information -->
                            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                                <h6 class="alert-heading">Appeal Process</h6>
                                <ul class="mb-0">
                                    <li>Your appeal will be reviewed by a senior support team member</li>
                                    <li>Review process takes up to 48 hours</li>
                                    <li>If approved, the dispute will be reopened for further review</li>
                                    <li>If rejected, the original decision becomes final</li>
                                    <li>This is your only opportunity to appeal</li>
                                </ul>
                            </div>

                            <!-- Important Notes -->
                            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                                <h6 class="alert-heading">Important Notes</h6>
                                <ul class="mb-0">
                                    <li>Appeals are only considered for new evidence or procedural errors</li>
                                    <li>Simply disagreeing with the decision is not sufficient grounds</li>
                                    <li>All communications must remain professional and respectful</li>
                                    <li>The appeal decision is final and binding</li>
                                </ul>
                            </div>

                            <div class="flex justify-between">
                                <a href="{{ route('disputes.show', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500">
                                    <i class="fas fa-arrow-left"></i> Back to Dispute
                                </a>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400">
                                    <i class="fas fa-gavel"></i> Submit Appeal
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File size validation
    const fileInput = document.getElementById('new_evidence');
    if (fileInput) {
        const maxSize = 10 * 1024 * 1024; // 10MB

        fileInput.addEventListener('change', function() {
            const files = this.files;
            for (let i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    alert(`File "${files[i].name}" is too large. Maximum size is 10MB.`);
                    this.value = '';
                    return;
                }
            }
        });
    }

    // Character counter for reason
    const reason = document.getElementById('reason');
    if (reason) {
        const maxLength = 1000;
        
        reason.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            const counter = this.parentNode.querySelector('.form-text');
            if (remaining < 100) {
                counter.innerHTML = `<span class="text-${remaining < 0 ? 'danger' : 'warning'}">${remaining} characters remaining</span>`;
            }
        });
    }

    // Countdown timer for appeal deadline
    @if($dispute->appeal_deadline && !$dispute->isAppealDeadlineExpired())
        const deadline = new Date('{{ $dispute->appeal_deadline }}').getTime();
        
        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            
            const deadlineElement = document.querySelector('.alert-warning p');
            if (deadlineElement) {
                if (distance > 0) {
                    deadlineElement.innerHTML = `
                        You have <strong>${days} days, ${hours} hours, ${minutes} minutes</strong> remaining to submit your appeal.
                        <br>
                        <small class="text-slate-500">Deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}</small>
                    `;
                } else {
                    deadlineElement.innerHTML = '<strong>Appeal deadline has expired!</strong>';
                    clearInterval(countdown);
                    location.reload(); // Refresh to show expired state
                }
            }
        }, 60000); // Update every minute
    @endif
});
</script>
@endpush
@endsection




