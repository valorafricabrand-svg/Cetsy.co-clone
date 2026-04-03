@extends('theme.'.theme().'.layouts.app')

@section('title', 'Create New Dispute')

@push('styles')
<style>
  .create-dispute-page { overflow-x: hidden; }
  .create-dispute-page .tox,
  .create-dispute-page .tox-tinymce,
  .create-dispute-page .tox-editor-container {
    max-width: 100% !important;
  }
</style>
@endpush

@section('main')
<div class="create-dispute-page mx-auto w-full max-w-5xl px-3 sm:px-4">
    <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-12">
        <div class="col-span-12 w-full md:col-span-8 md:col-start-3">
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
                        @php
                            $viewerIsBuyerForDispute = !$order || (int) ($order->user_id ?? 0) === (int) auth()->id();
                            $requestedResolution = old('requested_resolution', old('request_return_exchange') ? 'return_exchange' : 'review');
                        @endphp
                        
                        <!-- Order Selection -->
                        <div class="mb-3">
                            <label for="order_id" class="mb-1 block text-sm font-medium text-slate-700">Select Order *</label>
                            @if($order)
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                                    <div class="flex items-center justify-between gap-2">
                                        <strong>Order #{{ $order->order_number ?: $order->id }}</strong>
                                        <span class="text-xs text-slate-500">{{ $order->items->count() }} item(s)</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Total: ${{ number_format((float) $order->total_amount, 2) }}
                                    </div>

                                    @if($order->items->isNotEmpty())
                                        <div class="mt-3 space-y-2">
                                            @foreach($order->items as $item)
                                                @php
                                                    $product = optional($item->product);
                                                    $thumb = product_thumb_url($product);
                                                @endphp
                                                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-2">
                                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-100">
                                                        @if(!empty($thumb))
                                                            <img src="{{ $thumb }}" alt="{{ $product->name ?? 'Item' }}" class="h-full w-full object-cover">
                                                        @else
                                                            <div class="flex h-full w-full items-center justify-center text-slate-300">
                                                                <i class="bi bi-image"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0 grow">
                                                        <div class="truncate text-xs font-semibold text-slate-800">{{ $product->name ?? 'Product item' }}</div>
                                                        <div class="text-[11px] text-slate-500">
                                                            Qty: {{ (int)($item->quantity ?? 1) }}
                                                            <span class="mx-1">|</span>
                                                            ${{ number_format((float)($item->price ?? 0), 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
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

                        @if($viewerIsBuyerForDispute)
                            <!-- Preferred Resolution -->
                            <div class="mb-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                                <div class="mb-2 text-sm font-semibold text-emerald-800">Preferred Resolution</div>
                                <div class="space-y-3">
                                    <label for="resolution_review" class="flex items-start gap-3 rounded-lg border border-transparent px-1 py-1">
                                        <input class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" type="radio" id="resolution_review" name="requested_resolution" value="review" {{ $requestedResolution === 'review' ? 'checked' : '' }}>
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-800">Let Cetsy review first</span>
                                            <span class="block text-xs text-slate-600">Open the dispute without requesting a specific refund or replacement yet.</span>
                                        </span>
                                    </label>

                                    <label for="resolution_full_refund" class="flex items-start gap-3 rounded-lg border border-transparent px-1 py-1">
                                        <input class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" type="radio" id="resolution_full_refund" name="requested_resolution" value="full_refund" {{ $requestedResolution === 'full_refund' ? 'checked' : '' }}>
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-800">Request Refund</span>
                                            <span class="block text-xs text-slate-600">Ask for a full refund of the order.</span>
                                        </span>
                                    </label>

                                    <label for="resolution_partial_refund" class="flex items-start gap-3 rounded-lg border border-transparent px-1 py-1">
                                        <input class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" type="radio" id="resolution_partial_refund" name="requested_resolution" value="partial_refund" {{ $requestedResolution === 'partial_refund' ? 'checked' : '' }}>
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-800">Request Partial Refund</span>
                                            <span class="block text-xs text-slate-600">Ask for a partial refund. The final amount can be agreed during the dispute.</span>
                                        </span>
                                    </label>

                                    <label for="resolution_return_exchange" class="flex items-start gap-3 rounded-lg border border-transparent px-1 py-1">
                                        <input class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" type="radio" id="resolution_return_exchange" name="requested_resolution" value="return_exchange" {{ $requestedResolution === 'return_exchange' ? 'checked' : '' }}>
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-800">Request Return / Exchange</span>
                                            <span class="block text-xs text-slate-600">Reset the order to <strong>Processing</strong> so the seller can ship a replacement and update tracking details.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif

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

                        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-between">
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

    if (fileInput) {
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

    // Character counter for description
    const description = document.getElementById('description');
    if (!description) return;
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

    // TinyMCE can overflow on mobile; keep native textarea for small screens.
    if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches) {
      el.setAttribute('rows', '8');
      return;
    }

    const start = function(){
      try{ const i=tinymce.get('description'); if(i) i.remove(); }catch(_){}
      tinymce.init({
        selector:'#description',
        min_height:240,
        max_height:420,
        menubar:false,
        plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen help wordcount quickbars autoresize',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
        toolbar_mode: 'sliding',
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



