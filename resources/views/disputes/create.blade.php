@extends('theme.'.theme().'.layouts.app')

@section('title', 'Create New Dispute')

@section('main')
<div class="content">
    <div class="grid grid-cols-12 gap-4 justify-center">
        <div class="md:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h4 class="mb-0">Create New Dispute</h4>
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

                    @if($error)
                        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $error }}
                        </div>
                    @endif

                

                    <form action="{{ route('disputes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Order Selection -->
                        <div class="mb-3">
                            <label for="order_id" class="mb-1 block text-sm font-medium text-slate-700">Select Order *</label>
                            @if($order)
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    <strong>Order #{{ $order->order_number }}</strong>
                                    <br>
                                    <small class="text-slate-500">
                                        {{ $order->items->count() }} item(s) - 
                                        Total: ${{ number_format($order->total_amount, 2) }}
                                    </small>
                                </div>
                            @else
                                <select name="order_id" id="order_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('order_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
                                    <option value="">Select an order...</option>
                                    @foreach(auth()->user()->orders()->where('status', '!=', 'cancelled')->get() as $userOrder)
                                        <option value="{{ $userOrder->id }}" {{ old('order_id') == $userOrder->id ? 'selected' : '' }}>
                                            Order #{{ $userOrder->order_number }} - 
                                            {{ $userOrder->items->count() }} item(s) - 
                                            ${{ number_format($userOrder->total_amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('order_id')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <!-- Dispute Type -->
                        <div class="mb-3">
                            <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Dispute Type *</label>
                            <select name="type" id="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500 @error('type') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
                                <option value="">Select dispute type...</option>
                                @foreach($disputeTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description *</label>
                            <textarea name="description" id="description" rows="5" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                placeholder="Please provide a detailed description of the issue..." required>{{ old('description') }}</textarea>
                            <div class="mt-1 text-xs text-slate-500" id="description-counter">
                                Be specific about what went wrong and provide as much detail as possible.
                            </div>
                            @error('description')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Evidence Upload -->
                        <div class="mb-3">
                            <label for="evidence" class="mb-1 block text-sm font-medium text-slate-700">Supporting Evidence</label>
                            <input type="file" name="evidence[]" id="evidence" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('evidence.*') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <div class="mt-1 text-xs text-slate-500">
                                Upload relevant documents, screenshots, or photos. Max 10MB per file. 
                                Supported formats: JPG, PNG, PDF, DOC, DOCX
                            </div>
                            @error('evidence.*')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Return / Exchange Option -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="request_return_exchange" name="request_return_exchange" value="1" {{ old('request_return_exchange') ? 'checked' : '' }}>
                                <label class="form-check-label font-bold text-amber-600" for="request_return_exchange">
                                    Request a return/exchange (reset order to Processing)
                                </label>
                            </div>
                            <div class="text-xs text-amber-600 font-semibold mt-1">
                                If selected, we will reset the order status to <strong class="text-amber-600">Processing</strong> so the seller can ship a replacement and update tracking details. Previous tracking info (if any) will be cleared.
                            </div>
                        </div>

                        <!-- Important Information -->
                        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                            <h6 class="alert-heading font-bold">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Important Information
                            </h6>
                            <ul class="mb-0">
                                <li class="font-semibold">All communications must be conducted through Cetsy's messaging system</li>
                                <li class="font-semibold">Disputes will be reviewed within 3 Working days</li>
                                <li class="font-semibold">Provide clear evidence to support your case</li>
                            </ul>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('disputes.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500">
                                <i class="fas fa-arrow-left"></i> Back to Disputes
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                <i class="fas fa-paper-plane"></i> Submit Dispute
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File size validation
    const fileInput = document.getElementById('evidence');
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

    // Character counter for description
    const description = document.getElementById('description');
    const maxLength = 2000;
    
    description.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        const counter = document.getElementById('description-counter');
        if (!counter) return;
        if (remaining < 100) {
            counter.innerHTML = `<span class="${remaining < 0 ? 'text-rose-600' : 'text-amber-600'}">${remaining} characters remaining</span>`;
        } else {
            counter.textContent = 'Be specific about what went wrong and provide as much detail as possible.';
        }
    });
});
</script>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
(function(){
  function onReady(fn){ if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', fn); } else { fn(); } }
  onReady(function(){
    const el = document.getElementById('description');
    if(!el) return;
    const start = function(){
      try{ const i=tinymce.get('description'); if(i) i.remove(); }catch(_){}
      tinymce.init({
        selector:'#description',
        height:300,
        menubar:false,
        plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen help wordcount quickbars autoresize',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
        branding:false,
        browser_spellcheck:true,
        gecko_spellcheck:true,
        elementpath:false,
        base_url: '{{ asset('assets/js/tinymce') }}',
        setup(editor){ editor.on('change', () => editor.save()); }
      });
    };
    if(window.tinymce){ start(); }
    else {
      const s=document.createElement('script');
      s.src='https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js';
      s.referrerPolicy='origin';
      s.onload=start;
      s.onerror=function(){ console.warn('TinyMCE CDN failed to load'); };
      document.head.appendChild(s);
    }
  });
})();
</script>
@endpush
@endsection




