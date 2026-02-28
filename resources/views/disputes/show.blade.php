@extends('theme.'.theme().'.layouts.app')

@section('title', 'Dispute Details')

@section('main')
<div class="content">
    {{-- Dispute Summary Header --}}
    <div class="grid grid-cols-12 gap-4 mb-4">
        <div class="col-span-12">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
                <div class="p-4 sm:p-5">
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-12 md:col-span-8">
                            <div class="flex items-center mb-2">
                                <h2 class="mb-0 mr-3">
                                    <i class="bi bi-exclamation-triangle text-amber-600"></i>
                                    Dispute #{{ $dispute->id }}
                                </h2>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $dispute->getStatusBadgeClass() }} fs-6 px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                </span>
                            </div>
                            <p class="text-slate-500 mb-0">
                                <i class="bi bi-calendar3"></i> Created {{ $dispute->created_at->diffForHumans() }}
                                @if($dispute->isResolved())
                                    • <i class="bi bi-check-circle text-emerald-600"></i> Resolved {{ $dispute->resolved_at->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        <div class="col-span-12 md:col-span-4 text-md-end">
                            <div class="flex flex-col sm:flex-row gap-2 justify-content-md-end">
                                <a href="{{ route('disputes.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs">
                                    <i class="bi bi-arrow-left"></i> Back to Disputes
                                </a>
                                @if(config('disputes.enable_appeals') && $dispute->canBeAppealed())
                                    <a href="{{ route('disputes.appeal.create', $dispute->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400 px-3 py-1.5 text-xs">
                                        <i class="bi bi-gavel"></i> Appeal
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        // Show banner if any admin message exists in this dispute
        try {
            $adminIntervened = ($dispute->messages ?? collect())->contains(function($m){
                return ($m->type ?? null) === \App\Models\DisputeMessage::TYPE_ADMIN_MESSAGE;
            });
        } catch (\Throwable $e) { $adminIntervened = false; }
    @endphp
    @if($adminIntervened)
        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 flex items-center" role="alert">
            <i class="bi bi-shield-check mr-2"></i>
            <div>Customer Support is now intervening in this dispute. Please continue communication here.</div>
        </div>
    @endif

    {{-- Seller notice: buyer requested return/exchange and order reset to processing --}}
    @php
        $isSellerUser = auth()->check() && (($dispute->seller_id ?? null) === auth()->id() || optional($dispute->order?->shop)->user_id === auth()->id());
        $exchangeRequested = false;
        try {
            $msgs = $dispute->messages ?? collect();
            $exchangeRequested = $msgs->contains(function($m){
                $typeOk = ($m->type ?? null) === \App\Models\DisputeMessage::TYPE_SYSTEM_MESSAGE;
                $text   = strtolower((string)($m->message ?? ''));
                return $typeOk && (str_contains($text,'return/exchange') || str_contains($text,'reset to processing') || str_contains($text,'exchange'));
            });
        } catch (\Throwable $e) { $exchangeRequested = false; }
    @endphp
    @if($isSellerUser && $exchangeRequested)
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 flex items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill mr-2"></i>
            <div>
                The buyer has requested a order refund and your order has been restored to processing state, please ship that product again.
            </div>
        </div>
    @endif

    {{-- Pending refund proposal notice and actions --}}
    @php
        $pendingRefund = method_exists($dispute, 'getPendingRefund') ? $dispute->getPendingRefund() : null;
        $isBuyerUser = auth()->check() && $dispute->buyer_id === auth()->id();
    @endphp
    @if($pendingRefund)
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800" role="alert">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <div>
                    <strong>Pending Refund Proposal:</strong>
                    Seller proposed a {{ rtrim(rtrim(number_format($pendingRefund['percent'] ?? 0, 2), '0'), '.') }}% refund
                    ({{ get_currency() }} {{ number_format($pendingRefund['amount'] ?? 0, 2) }}).
                </div>
                @if($isBuyerUser)
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('disputes.refund-proposal.accept', $dispute->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500">
                                <i class="bi bi-check-circle"></i> Accept
                            </button>
                        </form>
                        <form method="POST" action="{{ route('disputes.refund-proposal.decline', $dispute->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50">
                                <i class="bi bi-x-circle"></i> Decline
                            </button>
                        </form>
                    </div>
                @else
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-slate-900">Awaiting buyer response</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Contact Support (buyer & seller) and Admin Request Evidence --}}
    @php $isResolvedOrClosed = $dispute->isResolved() || $dispute->isClosed(); @endphp
    @if(!$isResolvedOrClosed)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4 border-warning">
            <div class="border-b border-slate-200 px-4 py-3 bg-amber-100 text-slate-900 font-semibold flex justify-between items-center">
                <span><i class="bi bi-life-preserver mr-1"></i> Need Help?</span>
                @if(auth()->check() && (auth()->id() === $dispute->buyer_id || auth()->id() === $dispute->seller_id) && !$adminIntervened)
                    <form method="POST" action="{{ route('disputes.contact-support', $dispute->id) }}" class="m-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-900 text-slate-900 hover:bg-slate-100">
                            <i class="bi bi-headset"></i> Contact Support
                        </button>
                    </form>
                @endif
            </div>
            @if(auth()->check() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                <div class="p-4 sm:p-5">
                    <div class="mb-0 text-xs text-slate-500">
                        Assigned Admin: <strong>{{ $dispute->assignedAdmin->name ?? 'None' }}</strong>
                        @if(empty($dispute->assignedAdmin))
                            <form method="POST" action="{{ route('disputes.assign-admin', $dispute->id) }}" class="d-inline ml-2">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs btn-outline-info">Assign to Me</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Pending evidence requests for current user --}}
    @if(isset($evidenceRequests) && auth()->check())
        @php $myPendingReqs = $evidenceRequests->where('requested_from', auth()->id())->where('status','pending'); @endphp
        @foreach($myPendingReqs as $req)
            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 flex justify-between items-center">
                <div>
                    <i class="bi bi-info-circle mr-1"></i>
                    <strong>Additional Information Requested:</strong>
                    {{ $req->message }}
                    @if($req->deadline)
                        <span class="ml-2 text-xs text-slate-500">Deadline: {{ $req->deadline->format('M d, Y') }}</span>
                    @endif
                </div>
                <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50" data-ui-toggle="collapse" data-ui-target="#evidenceRespond-{{ $req->id }}">
                    Provide Info
                </button>
            </div>
            <div id="evidenceRespond-{{ $req->id }}" class="collapse mb-3">
                <form action="{{ route('disputes.evidence-requests.respond', $req->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3">
                    @csrf
                    <div class="mb-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
                        <textarea name="response_notes" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Add any explanation or notes..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Attachments</label>
                        <input type="file" name="submitted_evidence[]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                        <div class="mt-1 text-xs text-slate-500">Max 10MB per file. Supported: JPG, PNG, PDF, DOC, DOCX</div>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                        <i class="bi bi-upload"></i> Submit Response
                    </button>
                </form>
            </div>
        @endforeach
    @endif

    {{-- Dispute Statistics --}}
    <div class="grid grid-cols-12 gap-4 mb-4">
        <div class="col-span-12 md:col-span-3 sm:col-span-6 mb-3">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 bg-primary text-white">
                <div class="p-4 sm:p-5 text-center">
                    <i class="bi bi-chat-dots fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $allMessages->count() }}</h4>
                    <p class="mb-0 text-xs">Total Messages</p>
                </div>
            </div>
        </div>
        <div class="col-span-12 md:col-span-3 sm:col-span-6 mb-3">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 bg-sky-100 text-white">
                <div class="p-4 sm:p-5 text-center">
                    <i class="bi bi-chat fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $orderMessages->count() }}</h4>
                    <p class="mb-0 text-xs">Order Messages</p>
                </div>
            </div>
        </div>
        <div class="col-span-12 md:col-span-3 sm:col-span-6 mb-3">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 bg-amber-100 text-slate-900">
                <div class="p-4 sm:p-5 text-center">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $disputeMessages->count() }}</h4>
                    <p class="mb-0 text-xs">Dispute Messages</p>
                </div>
            </div>
        </div>
        <div class="col-span-12 md:col-span-3 sm:col-span-6 mb-3">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 bg-success text-white">
                <div class="p-4 sm:p-5 text-center">
                    <i class="bi bi-paperclip fs-1 mb-2"></i>
                    <h4 class="mb-1">{{ $dispute->evidence ? count($dispute->evidence) : 0 }}</h4>
                    <p class="mb-0 text-xs">Evidence Files</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Evidence Files Summary Section --}}
    @php
        $totalEvidenceFiles = 0;
        $totalEvidenceSize = 0;
        
        // Count initial dispute evidence
        if ($dispute->evidence && count($dispute->evidence) > 0) {
            $totalEvidenceFiles += count($dispute->evidence);
            $totalEvidenceSize += collect($dispute->evidence)->sum('size');
        }
        
        // Count appeal evidence
        if ($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty()) {
            foreach($dispute->appeal->evidenceRequests as $evidenceRequest) {
                if ($evidenceRequest->submitted_evidence && count($evidenceRequest->submitted_evidence) > 0) {
                    $totalEvidenceFiles += count($evidenceRequest->submitted_evidence);
                    $totalEvidenceSize += collect($evidenceRequest->submitted_evidence)->sum('size');
                }
            }
        }
        
        // Count message attachments
        foreach($allMessages as $message) {
            if (isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0) {
                $totalEvidenceFiles += count($message->attachments);
                $totalEvidenceSize += collect($message->attachments)->sum('size');
            }
        }
    @endphp
    
    @if($totalEvidenceFiles > 0)
        <div class="grid grid-cols-12 gap-4 mb-4">
            <div class="col-span-12">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 bg-slate-100">
                    <div class="p-4 sm:p-5">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            <div class="col-span-12 md:col-span-8">
                                <h5 class="mb-2">
                                    <i class="bi bi-files text-primary"></i> 
                                    Evidence Files Overview
                                </h5>
                                <p class="text-slate-500 mb-0">
                                    Total {{ $totalEvidenceFiles }} files ({{ number_format($totalEvidenceSize / 1024 / 1024, 2) }} MB) available for review
                                </p>
                            </div>
                            <div class="col-span-12 md:col-span-4 text-md-end">
                                <div class="flex gap-2 justify-content-md-end">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary fs-6 px-3 py-2">
                                        <i class="bi bi-paperclip"></i> {{ $dispute->evidence ? count($dispute->evidence) : 0 }} Initial
                                    </span>
                                    @if($dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty())
                                        @php
                                            $appealEvidenceCount = 0;
                                            foreach($dispute->appeal->evidenceRequests as $evidenceRequest) {
                                                if ($evidenceRequest->submitted_evidence) {
                                                    $appealEvidenceCount += count($evidenceRequest->submitted_evidence);
                                                }
                                            }
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-slate-900 fs-6 px-3 py-2">
                                            <i class="bi bi-gavel"></i> {{ $appealEvidenceCount }} Appeal
                                        </span>
                                    @endif
                                    @php
                                        $messageAttachmentsCount = 0;
                                        foreach($allMessages as $message) {
                                            if (isset($message->attachments) && is_array($message->attachments)) {
                                                $messageAttachmentsCount += count($message->attachments);
                                            }
                                        }
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sky-100 text-slate-900 fs-6 px-3 py-2">
                                        <i class="bi bi-chat"></i> {{ $messageAttachmentsCount }} Messages
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-8">
            <!-- Order Context Header -->
            @if($order)
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                <div class="border-b border-slate-200 px-4 py-3 bg-slate-100">
                    <div class="flex justify-between items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-box"></i> Order Context
                            </h5>
                            <small class="text-slate-500">
                                This dispute is related to Order #{{ $order->id }} from {{ $order->shop->name ?? 'the shop' }}
                            </small>
                        </div>
                        @php
                            $authUser  = auth()->user();
                            $sellerId  = optional($order->shop)->user_id;
                            $asSeller  = $authUser && ((int)$authUser->id === (int)$sellerId || (method_exists($authUser,'isSeller') ? $authUser->isSeller() : false));
                            $orderHref = $asSeller && \Illuminate\Support\Facades\Route::has('seller.orders.show')
                                ? route('seller.orders.show', $order->id)
                                : (\Illuminate\Support\Facades\Route::has('buyer.orders.show')
                                    ? route('buyer.orders.show', $order->id)
                                    : route('orders.show', $order->id));
                        @endphp
                        <a href="{{ $orderHref }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                            <i class="bi bi-eye"></i> View Full Order
                        </a>
                    </div>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 md:col-span-6">
                            <h6>Order Details</h6>
                            <p class="mb-2">
                                <strong>Order #:</strong> {{ $order->id }}<br>
                                <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                                <strong>Status:</strong> 
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->getStatusBadgeClass() }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <h6>Order Items</h6>
                            @if($orderItems->isNotEmpty())
                                @foreach($orderItems->take(3) as $item)
                                    @php
                                        $product = $item->product ?? null;
                                        $thumb = null;
                                        if ($product) {
                                            $thumb = function_exists('product_thumb_url')
                                                ? product_thumb_url($product)
                                                : ($product->featured_image
                                                    ? (str_starts_with($product->featured_image, 'http')
                                                        ? $product->featured_image
                                                        : asset('storage/' . ltrim($product->featured_image, '/')))
                                                    : null);
                                        }
                                    @endphp
                                    <div class="flex items-center mb-2">
                                        @if($thumb)
                                            <img src="{{ $thumb }}"
                                                 alt="{{ $product->name ?? 'Product image' }}"
                                                 class="rounded mr-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 loading="lazy"
                                                 onerror="this.src='{{ asset('storage/placeholder.jpg') }}';">
                                        @else
                                            <div class="bg-slate-100 rounded mr-2 flex items-center justify-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-slate-500"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <small class="block">{{ Str::limit($product->name ?? 'Product', 30) }}</small>
                                            <small class="text-slate-500">Qty: {{ $item->quantity }}</small>
                                        </div>
                                    </div>
                                @endforeach
                                @if($orderItems->count() > 3)
                                    <small class="text-slate-500">+{{ $orderItems->count() - 3 }} more items</small>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Dispute Header -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                <div class="border-b border-slate-200 px-4 py-3">
                    <div class="flex justify-between items-center">
                        <h4 class="mb-0">Dispute #{{ $dispute->id }}</h4>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $dispute->getStatusBadgeClass() }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                        </span>
                    </div>
                </div>
                <div class="p-4 sm:p-5">
                    {{-- Initial Dispute Description - Prominently Displayed --}}
                    <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800 mb-4">
                        <div class="flex items-start">
                            <i class="bi bi-exclamation-triangle-fill text-amber-600 mr-3" style="font-size: 1.5rem; margin-top: 2px;"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-2">
                                    <strong>Initial Dispute Description</strong>
                                    <small class="text-slate-500 ml-2">by {{ $dispute->buyer_id === auth()->id() ? 'You' : $dispute->buyer->name }}</small>
                                </h6>
                                <p class="mb-3 fs-6">{!! $dispute->description !!}</p>
                                
                                {{-- Initial Evidence Display --}}
                                @if($dispute->evidence && count($dispute->evidence) > 0)
                                    <div class="mt-3">
                                        <h6 class="mb-2"><i class="bi bi-paperclip"></i> Initial Evidence ({{ count($dispute->evidence) }})</h6>
                                        <div class="grid grid-cols-12 gap-4 gap-2">
                                            @foreach($dispute->evidence as $file)
                                                <div class="md:col-span-3 sm:col-span-4 col-span-6">
                                                    <div class="evidence-item border rounded p-2 text-center">
                                                        @if(in_array($file['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                            <img src="{{ Storage::url($file['path']) }}" 
                                                                 alt="{{ $file['filename'] }}" 
                                                                 class="img-fluid rounded mb-2" 
                                                                 style="max-height: 80px; width: 100%; object-fit: cover;"
                                                                 onclick="openImageModal('{{ Storage::url($file['path']) }}', '{{ $file['filename'] }}')">
                                                        @elseif(in_array($file['mime_type'], ['application/pdf']))
                                                            <div class="bg-danger text-white rounded p-3 mb-2">
                                                                <i class="bi bi-file-pdf fs-4"></i>
                                                            </div>
                                                        @elseif(in_array($file['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                            <div class="bg-primary text-white rounded p-3 mb-2">
                                                                <i class="bi bi-file-word fs-4"></i>
                                                            </div>
                                                        @else
                                                            <div class="bg-slate-200 text-white rounded p-3 mb-2">
                                                                <i class="bi bi-file-earmark fs-4"></i>
                                                            </div>
                                                        @endif
                                                        <div class="text-xs text-truncate" title="{{ $file['filename'] }}">
                                                            {{ Str::limit($file['filename'], 20) }}
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            {{ number_format($file['size'] / 1024, 1) }} KB
                                                        </div>
                                                        <a href="{{ Storage::url($file['path']) }}" 
                                                           target="_blank" 
                                                           class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50 mt-1">
                                                            <i class="bi bi-download"></i> View
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 md:col-span-6">
                            <h6>Dispute Type</h6>
                            <p class="mb-3">{{ $dispute->getTypeLabel() }}</p>
                            
                            <h6>Order</h6>
                            <p class="mb-3">
                                @php
                                    $o = $dispute->order; $sellerId2 = optional($o->shop)->user_id; $asSeller2 = $authUser && (int)$authUser->id === (int)$sellerId2;
                                    $orderHref2 = $asSeller2 && \Illuminate\Support\Facades\Route::has('seller.orders.show')
                                        ? route('seller.orders.show', $o->id)
                                        : (\Illuminate\Support\Facades\Route::has('buyer.orders.show')
                                            ? route('buyer.orders.show', $o->id)
                                            : route('orders.show', $o->id));
                                @endphp
                                <a href="{{ $orderHref2 }}" class="no-underline">
                                    Order #{{ $dispute->order->id }}
                                </a>
                            </p>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <h6>Created</h6>
                            <p class="mb-3">{{ $dispute->created_at->format('M d, Y \a\t g:i A') }}</p>
                            
                                                         <h6>Parties</h6>
                             <p class="mb-3">
                                 <strong>Buyer:</strong> {{ $dispute->buyer->name }}<br>
                                 <strong>Shop:</strong> {{ $dispute->order->shop->name ?? $dispute->seller->name }}
                             </p>
                        </div>
                    </div>

                    @if($dispute->isResolved())
                        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                            <h6 class="alert-heading">Resolution</h6>
                            <p class="mb-2">{{ $dispute->resolution }}</p>
                            <strong>Decision:</strong> {{ $dispute->getDecisionLabel() }}
                            @if($dispute->refund_amount)
                                <br><strong>Refund Amount:</strong> ${{ number_format($dispute->refund_amount, 2) }}
                            @endif
                            <br><strong>Resolved:</strong> {{ $dispute->resolved_at->format('M d, Y \a\t g:i A') }}
                        </div>
                    @endif


                    
                    
                </div>
            </div>

            

            {{-- Seller Refund Action --}}
            @php
                $isSeller = auth()->check() && (($dispute->seller_id ?? null) === auth()->id() || optional($dispute->order->shop)->user_id === auth()->id());
                $isAdmin  = auth()->check() && (method_exists(auth()->user(), 'isAdmin') ? auth()->user()->isAdmin() : false);
                $canRefund = $isSeller || $isAdmin;
            @endphp
            @if($canRefund && !$dispute->isResolved() && !$dispute->isClosed())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4 border-warning">
                    <div class="border-b border-slate-200 px-4 py-3 bg-amber-100 flex justify-between items-center flex-wrap gap-2">
                        <h6 class="mb-0 flex items-center gap-2">
                            <i class="bi bi-cash-coin"></i> Issue Refund to Buyer
                        </h6>
                        <div class="flex items-center gap-2">
                          @php
                              $fullRefundLabel = $isAdmin ? 'Issue Full Refund (100%)' : 'Accept Full Refund (100%)';
                              $partialRefundLabel = $isAdmin ? 'Propose Partial Refund' : 'Offer Partial Refund';
                          @endphp
                          <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-emerald-600 text-white hover:bg-emerald-500" data-ui-toggle="modal" data-ui-target="#fullRefundModal-{{ $dispute->id }}">
                            <i class="bi bi-check2-circle"></i> {{ $fullRefundLabel }}
                          </button>
                          <button class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs bg-slate-900 text-white hover:bg-slate-700" data-ui-toggle="modal" data-ui-target="#refundModal-{{ $dispute->id }}">
                            <i class="bi bi-sliders"></i> {{ $partialRefundLabel }}
                          </button>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5">
                        <p class="mb-2">You can resolve this dispute by issuing a partial or full refund to the buyer's wallet.</p>
                        <ul class="mb-0 text-xs text-slate-500">
                            <li>Refund is credited to the buyer and debited from the seller's wallet.</li>
                            <li>For non-delivery, consider a full (100%) refund.</li>
                            <li>For damaged or not as described, you may agree on a partial refund.</li>
                        </ul>
                    </div>
                </div>

                <!-- Refund Modal -->
                <div class="modal" id="refundModal-{{ $dispute->id }}" tabindex="-1" aria-labelledby="refundModalLabel-{{ $dispute->id }}" aria-hidden="true">
                  <div class="modal-dialog">
                    <form method="POST" action="{{ route('disputes.refund', $dispute) }}" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                      @csrf
                      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <h5 class="text-base font-semibold text-slate-900" id="refundModalLabel-{{ $dispute->id }}">Confirm Refund</h5>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                      </div>
                      <div class="px-4 py-4">
                        @php $orderTotal = (float)($dispute->order->total_amount ?? 0); @endphp
                        <div class="mb-3">
                          <label for="refund-percent-{{ $dispute->id }}" class="mb-1 block text-sm font-medium text-slate-700">Refund Percentage (%)</label>
                          <input type="number" name="refund_percent" id="refund-percent-{{ $dispute->id }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" value="50" min="1" max="100" step="0.01" required>
                          <div class="mt-1 text-xs text-slate-500">Order Total: {{ get_currency() }} {{ number_format($orderTotal, 2) }}. Set the percentage to refund to the buyer.</div>
                        </div>
                        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800" id="refund-amount-box-{{ $dispute->id }}">
                          <i class="bi bi-calculator"></i>
                          <strong>Refund Amount:</strong>
                          <span id="refund-amount-{{ $dispute->id }}">{{ get_currency() }} {{ number_format($orderTotal, 2) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-0">This will credit the buyer's wallet and debit the seller's wallet. This action cannot be undone.</p>
                      </div>
                      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400">
                          <i class="bi bi-check2-circle"></i> Confirm Refund
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Full Refund Confirm Modal -->
                <div class="modal" id="fullRefundModal-{{ $dispute->id }}" tabindex="-1" aria-labelledby="fullRefundModalLabel-{{ $dispute->id }}" aria-hidden="true">
                  <div class="modal-dialog">
                    <form method="POST" action="{{ route('disputes.refund', $dispute) }}" class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                      @csrf
                      <input type="hidden" name="refund_percent" value="100">
                      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <h5 class="text-base font-semibold text-slate-900" id="fullRefundModalLabel-{{ $dispute->id }}">Confirm Full Refund</h5>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                      </div>
                      <div class="px-4 py-4">
                        @php $fullVerb = $isAdmin ? 'issue' : 'accept'; @endphp
                        Are you sure you want to {{ $fullVerb }} a full refund (100%)? This will credit the buyer and debit the seller's wallet.
                      </div>
                      <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                          <i class="bi bi-check2-circle"></i> Confirm Full Refund
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                @push('scripts')
                <script>
                (function(){
                  document.addEventListener('DOMContentLoaded', function(){
                    var input = document.getElementById('refund-percent-{{ $dispute->id }}');
                    var amountEl = document.getElementById('refund-amount-{{ $dispute->id }}');
                    var total = {{ $orderTotal }};
                    var currency = @json(get_currency());
                    function fmt(n){ try { return n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}); } catch(e){ return (Math.round(n*100)/100).toFixed(2); } }
                    function recalc(){
                      var p = parseFloat(input.value || '0');
                      if (isNaN(p) || p < 0) p = 0;
                      if (p > 100) p = 100;
                      var amt = total * (p/100);
                      amountEl.textContent = currency + ' ' + fmt(amt);
                    }
                    input.addEventListener('input', recalc);
                    recalc();
                  });
                })();
                </script>
                @endpush
            @endif

            {{-- Mutual Resolution Section (disabled via config) --}}
            @if(config('disputes.enable_mutual_resolution') && $dispute->canBeMutuallyResolved())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4 border-success">
                    <div class="border-b border-slate-200 px-4 py-3 bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-handshake"></i> Mutual Resolution
                        </h6>
                    </div>
                    <div class="p-4 sm:p-5">
                        @if($dispute->mutual_resolution_terms)
                            {{-- Show existing mutual resolution proposal --}}
                            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-3">
                                <h6 class="alert-heading">Proposed Resolution Terms</h6>
                                <p class="mb-2">{{ $dispute->mutual_resolution_terms }}</p>
                                <small class="text-slate-500">
                                    Proposed by: {{ $dispute->buyer_agreed_at && !$dispute->seller_agreed_at ? $dispute->buyer->name : $dispute->seller->name }}
                                </small>
                            </div>

                            {{-- Show agreement status --}}
                            <div class="grid grid-cols-12 gap-4 mb-3">
                                <div class="col-span-12 md:col-span-6">
                                    <div class="flex items-center">
                                        <i class="bi bi-person-circle mr-2"></i>
                                        <span>Buyer ({{ $dispute->buyer->name }})</span>
                                        @if($dispute->buyer_agreed_at)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success ml-2">
                                                <i class="bi bi-check-circle"></i> Agreed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 ml-2">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <div class="flex items-center">
                                        <i class="bi bi-shop mr-2"></i>
                                        <span>Seller ({{ $dispute->seller->name }})</span>
                                        @if($dispute->seller_agreed_at)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-success ml-2">
                                                <i class="bi bi-check-circle"></i> Agreed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 ml-2">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Agree button for the party who hasn't agreed yet --}}
                            @if(($dispute->buyer_id === auth()->id() && !$dispute->buyer_agreed_at) || 
                                 ($dispute->seller_id === auth()->id() && !$dispute->seller_agreed_at))
                                <form action="{{ route('disputes.mutual-resolution.agree', $dispute->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                        <i class="bi bi-check-circle"></i> I Agree to These Terms
                                    </button>
                                </form>
                            @endif

                            @if($dispute->buyer_agreed_at && $dispute->seller_agreed_at)
                                <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">
                                    <i class="bi bi-check-circle"></i>
                                    <strong>Both parties have agreed!</strong> This dispute will be automatically resolved.
                                </div>
                            @else
                                {{-- Appeal Button for Mutual Resolution Failure --}}
                                <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Mutual Resolution Pending</strong>
                                    <p class="mb-0 mt-2">Waiting for both parties to agree on the proposed terms.</p>
                                </div>
                            @endif
                        @else
                            {{-- Show form to propose mutual resolution --}}
                            <p class="text-slate-500 mb-3">
                                If you and the other party have reached an agreement, you can propose mutual resolution terms here.
                            </p>
                            
                            <form action="{{ route('disputes.mutual-resolution.initiate', $dispute->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="terms" class="mb-1 block text-sm font-medium text-slate-700">Resolution Terms</label>
                                    <textarea name="terms" id="terms" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                                        placeholder="Describe the agreed resolution terms..." required></textarea>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Clearly state what both parties have agreed to resolve this dispute.
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                    <i class="bi bi-handshake"></i> Propose Mutual Resolution
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Show mutual resolution status if already resolved (respect config) --}}
            @if(config('disputes.enable_mutual_resolution') && $dispute->isMutuallyResolved())
                <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 mb-4">
                    <h6 class="alert-heading">
                        <i class="bi bi-handshake"></i> Mutually Resolved
                    </h6>
                    <p class="mb-2"><strong>Agreed Terms:</strong> {{ $dispute->mutual_resolution_terms }}</p>
                    <p class="mb-0">
                        <strong>Resolved:</strong> {{ $dispute->resolved_at->format('M d, Y \a\t g:i A') }}
                    </p>
                </div>
            @endif

            

            <!-- All Message Attachments Section -->
            @php
                $allAttachments = collect();
                foreach($allMessages as $message) {
                    if (isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0) {
                        foreach($message->attachments as $attachment) {
                            $attachment['message_sender'] = $message->user->name ?? 'Unknown User';
                            $attachment['message_date'] = $message->created_at;
                            $attachment['message_content'] = Str::limit($message->message ?? $message->body ?? '', 100);
                            $allAttachments->push($attachment);
                        }
                    }
                }
            @endphp
            
            @if($allAttachments->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4 border-secondary">
                    <div class="border-b border-slate-200 px-4 py-3 bg-slate-200 text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-paperclip"></i> All Message Attachments
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-900 ml-2">{{ $allAttachments->count() }} file(s)</span>
                        </h6>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div class="rounded-xl border px-4 py-3 text-sm border-slate-200 bg-slate-100 text-slate-700">
                            <h6 class="alert-heading">Message Attachments Overview</h6>
                            <p class="mb-0">All files attached to messages in this dispute. These files are visible to all parties involved.</p>
                        </div>
                        
                        <div class="grid grid-cols-12 gap-4 gap-3">
                            @foreach($allAttachments as $attachment)
                                <div class="md:col-span-4 sm:col-span-6 col-span-12">
                                    <div class="evidence-item border rounded p-3 text-center h-full">
                                        @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                            <img src="{{ Storage::url($attachment['path']) }}" 
                                                 alt="{{ $attachment['filename'] }}" 
                                                 class="img-fluid rounded mb-2" 
                                                 style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                                 onclick="openImageModal('{{ Storage::url($attachment['path']) }}', '{{ $attachment['filename'] }}')"
                                                 title="Click to view full size">
                                        @elseif(in_array($attachment['mime_type'], ['application/pdf']))
                                            <div class="bg-danger text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-pdf fs-1"></i>
                                            </div>
                                        @elseif(in_array($attachment['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                            <div class="bg-primary text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-word fs-1"></i>
                                            </div>
                                        @else
                                            <div class="bg-slate-200 text-white rounded p-3 mb-2">
                                                <i class="bi bi-file-earmark fs-1"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="evidence-info">
                                            <div class="font-bold text-truncate mb-2" title="{{ $attachment['filename'] }}">
                                                {{ Str::limit($attachment['filename'], 25) }}
                                            </div>
                                            <div class="text-xs text-slate-500 mb-2">
                                                {{ number_format($attachment['size'] / 1024, 1) }} KB
                                            </div>
                                            <div class="text-xs text-slate-600 mb-2">
                                                <i class="bi bi-person"></i> {{ $attachment['message_sender'] }}
                                            </div>
                                            <div class="text-xs text-slate-500 mb-2" title="{{ $attachment['message_content'] }}">
                                                <i class="bi bi-chat"></i> {{ $attachment['message_content'] }}
                                            </div>
                                            <div class="text-xs text-slate-500 mb-2">
                                                <i class="bi bi-clock"></i> {{ $attachment['message_date']->format('M d, Y') }}
                                            </div>
                                            <div class="flex gap-1 justify-center">
                                                <a href="{{ Storage::url($attachment['path']) }}" 
                                                   target="_blank" 
                                                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ Storage::url($attachment['path']) }}" 
                                                   download="{{ $attachment['filename'] }}"
                                                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Attachments Summary --}}
                        <div class="mt-4 p-3 bg-slate-100 rounded">
                            <h6 class="mb-2">Attachments Summary</h6>
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-6">
                                    <strong>Total Files:</strong> {{ $allAttachments->count() }}
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <strong>Total Size:</strong> {{ number_format($allAttachments->sum('size') / 1024 / 1024, 2) }} MB
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>File Types:</strong>
                                @php
                                    $attachmentTypes = $allAttachments->groupBy('mime_type')->map(function($group) {
                                        return $group->count() . ' ' . pathinfo($group->first()['filename'], PATHINFO_EXTENSION);
                                    })->join(', ');
                                @endphp
                                {{ $attachmentTypes }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Unified Messages Section -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-chat-dots"></i> Complete Communication History
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200 ml-2">{{ $disputeMessages->count() }} messages</span>
                            </h5>
                            @if($order)
                                <small class="text-slate-500">
                                    <i class="bi bi-box"></i> Order #{{ $order->id }} - {{ $order->shop->name ?? 'Shop' }}
                                </small>
                            @endif
                        </div>
                        <div class="inline-flex flex-wrap gap-2 inline-flex flex-wrap gap-1" role="group">
                            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50 active" data-filter="dispute">
                                <i class="bi bi-exclamation-triangle"></i> Dispute ({{ $disputeMessages->count() }})
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-5">
                    @if($order)
                        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Communication Context:</strong> This communication history shows dispute-specific messages related to <strong>Order #{{ $order->id }}</strong> 
                            from <strong>{{ $order->shop->name ?? 'the shop' }}</strong>. It includes only dispute-related communications and evidence uploads.
                            
                            <div class="mt-2 text-xs">
                                <strong>Message Breakdown:</strong>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 mr-2">{{ $disputeMessages->count() }} Dispute Messages</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-200">{{ $disputeMessages->count() }} Total Messages</span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="messages-container" style="max-height: 600px; overflow-y: auto;">
                        @forelse($disputeMessages as $message)
                            @php
                                // Validate that this message belongs to the current dispute's order
                                if (isset($message->order_id) && $order && $message->order_id !== $order->id) {
                                    continue; // Skip messages from other orders
                                }
                                
                                // Validate dispute messages belong to this dispute
                                if (isset($message->dispute_id) && $message->dispute_id !== $dispute->id) {
                                    continue; // Skip messages from other disputes
                                }
                                
                                // Safely determine message type and class
                                $messageType = $message->type ?? 'unknown';
                                $messageClass = 'default-message';
                                if ($messageType === 'buyer_message') $messageClass = 'buyer-message';
                                elseif ($messageType === 'seller_message') $messageClass = 'seller-message';
                                elseif ($messageType === 'system_message') $messageClass = 'system-message';
                                
                                // Determine user role for proper class assignment
                                $userRoleForClass = 'User';
                                
                                // Check for system messages first (regardless of user_id)
                                if ($message->type === 'system_message') {
                                    $userRoleForClass = 'System';
                                } elseif ($message->user_id) {
                                    if ($message->user_id === $dispute->buyer_id) {
                                        $userRoleForClass = 'Buyer';
                                    } elseif ($message->user_id === $dispute->seller_id) {
                                        $userRoleForClass = 'Seller';
                                    } else {
                                        $userRoleForClass = 'Other';
                                    }
                                }
                                
                                // Assign message class based on user role
                                if ($userRoleForClass === 'Buyer') $messageClass = 'buyer-message';
                                elseif ($userRoleForClass === 'Seller') $messageClass = 'seller-message';
                                elseif ($userRoleForClass === 'System') $messageClass = 'system-message';
                                elseif ($userRoleForClass === 'Other') $messageClass = 'other-message';
                                
                                // Safely determine if it's a dispute or order message
                                $isDisputeMessage = isset($message->is_dispute_message) ? $message->is_dispute_message : false;
                                $isOrderMessage = !$isDisputeMessage;
                                
                                // Safely get user info and determine profile image
                                $userName = $message->user->name ?? 'Unknown User';
                                $userPhoto = null;
                                $userRole = 'User';
                                
                                // Determine user role and profile image based on dispute context
                                
                                // Check for system messages first (regardless of user_id)
                                if ($message->type === 'system_message') {
                                    $userRole = 'System';
                                    $userName = 'System';
                                    $userPhoto = null;
                                } elseif ($message->user_id) {
                                    if ($message->user_id === $dispute->buyer_id) {
                                        $userRole = 'Buyer';
                                        // Get buyer's profile photo using the correct method
                                        if ($message->user->photo) {
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            // Use gravatar as fallback
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    } elseif ($message->user_id === $dispute->seller_id) {
                                        // For sellers, always show shop name if available, otherwise show "Seller"
                                        if ($order && $order->shop && $order->shop->name) {
                                            $userName = $order->shop->name;
                                        } else {
                                            $userName = 'Seller';
                                        }
                                        $userRole = 'Seller';
                                        
                                        // Get shop's profile photo (shop logo/image)
                                        if ($order && $order->shop && $order->shop->logo) {
                                            $userPhoto = asset('storage/' . $order->shop->logo);
                                        } elseif ($message->user->photo) {
                                            // Fallback to user's personal photo if shop logo not available
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            // Use gravatar as final fallback
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    } else {
                                        // For any other users (shouldn't happen in normal disputes), show as "Other"
                                        $userRole = 'Other';
                                        $userName = $message->user->name ?? 'Unknown User';
                                        if ($message->user->photo) {
                                            $userPhoto = avatar_img_url($message->user->photo, $message->user->photo_storage);
                                        } else {
                                            $userPhoto = $message->user->get_gravatar(32);
                                        }
                                    }
                                }
                                
                                // Safely get message content
                                $messageContent = $message->message ?? $message->body ?? 'No message content';
                                
                                // Safely get attachments
                                $hasAttachments = isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0;
                                $attachmentsCount = $hasAttachments ? count($message->attachments) : 0;
                                
                                // Debug: Display profile image info (temporary)
                                if (app()->environment('local')) {
                                    echo "<!-- DEBUG: userPhoto = $userPhoto, userRole = $userRole -->";
                                }
                            @endphp
                            
                            <div class="message mb-4 {{ $messageClass }} $isDisputeMessage ? 'dispute-message' : 'order-message'" 
                                 data-message-type="{{ $isDisputeMessage ? 'dispute' : 'order' }}">
                                
                                {{-- Message Header with Source Badge --}}
                                <div class="message-header flex justify-between items-center mb-3">
                                    <div class="flex items-center">
                                        @if($message->is_dispute_message)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-slate-900 mr-2">
                                                <i class="bi bi-exclamation-triangle"></i> Dispute
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-sky-100 text-slate-900 mr-2">
                                                <i class="bi bi-chat"></i> Order
                                            </span>
                                        @endif
                                        
                                                                                <div class="flex items-center">
                                            @if($userPhoto && $userName !== 'Unknown User')
                                                <img src="{{ $userPhoto }}" 
                                                     alt="{{ $userName }}" 
                                                     class="rounded-full mr-2" 
                                                     width="32" height="32"
                                                     style="object-fit: cover;"
                                                     onerror="this.style.display='none'; this.nextElementSibling.nextElementSibling.style.display='block';">
                                                <strong>{{ $userName }}</strong>
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $userRole === 'Buyer' ? 'bg-primary' : ($userRole === 'System' ? 'bg-secondary' : ($userRole === 'Seller' ? 'bg-success' : 'bg-warning')) }} ml-2">
                                                    {{ $userRole }}
                                                </span>
                                            @else
                                                <div class="rounded-full avatar-fallback mr-2" 
                                                     style="width: 32px; height: 32px; {{ $userRole === 'Buyer' ? 'background-color: #e3f2fd; color: #1976d2;' : ($userRole === 'System' ? 'background-color: #6c757d; color: white;' : ($userRole === 'Seller' ? 'background-color: #f3e5f5; color: #7b1fa2;' : 'background-color: #fff3cd; color: #856404;')) }}">
                                                    @if($userRole === 'Buyer')
                                                        <i class="bi bi-person-fill"></i>
                                                    @elseif($userRole === 'System')
                                                        <i class="bi bi-robot"></i>
                                                    @elseif($userRole === 'Seller')
                                                        <i class="bi bi-shop"></i>
                                                    @else
                                                        <i class="bi bi-person"></i>
                                                    @endif
                                                </div>
                                                <strong>{{ $userName }}</strong>
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $userRole === 'Buyer' ? 'bg-primary' : ($userRole === 'System' ? 'bg-secondary' : ($userRole === 'Seller' ? 'bg-success' : 'bg-warning')) }} ml-2">
                                                    {{ $userRole }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-slate-500 text-xs">
                                        <i class="bi bi-clock"></i>
                                        {{ $message->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </div>

                                {{-- Message Content --}}
                                <div class="message-content p-3 rounded">
                                    <p class="mb-3">
                                        {!! $messageContent !!}
                                    </p>
                                    
                                    {{-- Attachments Display --}}
                                    @if($hasAttachments)
                                        <div class="attachments-section border-top pt-3">
                                            <h6 class="mb-3">
                                                <i class="bi bi-paperclip"></i> 
                                                Attachments ({{ $attachmentsCount }})
                                            </h6>
                                            <div class="grid grid-cols-12 gap-4 gap-3">
                                                @foreach($message->attachments ?? [] as $attachment)
                                                    <div class="md:col-span-4 sm:col-span-6 col-span-12">
                                                        <div class="attachment-item border rounded p-3 text-center h-full">
                                                            @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']))
                                                                <img src="{{ Storage::url($attachment['path']) }}" 
                                                                     alt="{{ $attachment['filename'] }}" 
                                                                     class="img-fluid rounded mb-2" 
                                                                     style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                                                     onclick="openImageModal('{{ Storage::url($attachment['path']) }}', '{{ $attachment['filename'] }}')"
                                                                     title="Click to view full size">
                                                            @elseif(in_array($attachment['mime_type'], ['application/pdf']))
                                                                <div class="bg-danger text-white rounded p-3 mb-2">
                                                                    <i class="bi bi-file-pdf fs-1"></i>
                                                                </div>
                                                            @elseif(in_array($attachment['mime_type'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                                                <div class="bg-primary text-white rounded p-3 mb-2">
                                                                    <i class="bi bi-file-word fs-1"></i>
                                                                </div>
                                                            @else
                                                                <div class="bg-slate-200 text-white rounded p-3 mb-2">
                                                                    <i class="bi bi-file-earmark fs-1"></i>
                                                                </div>
                                                            @endif
                                                            
                                                            <div class="attachment-info">
                                                                <div class="font-bold text-truncate" title="{{ $attachment['filename'] }}">
                                                                    {{ Str::limit($attachment['filename'], 25) }}
                                                                </div>
                                                                <div class="text-xs text-slate-500 mb-2">
                                                                    {{ number_format($attachment['size'] / 1024, 1) }} KB
                                                                </div>
                                                                <div class="flex gap-1 justify-center">
                                                                    <a href="{{ Storage::url($attachment['path']) }}" 
                                                                       target="_blank" 
                                                                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                                                        <i class="bi bi-eye"></i> View
                                                                    </a>
                                                                    <a href="{{ Storage::url($attachment['path']) }}" 
                                                                       download="{{ $attachment['filename'] }}"
                                                                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-slate-300 text-slate-700 hover:bg-slate-50">
                                                                        <i class="bi bi-download"></i> Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-slate-500 py-5">
                                <i class="bi bi-chat-dots fs-1 mb-3"></i>
                                <h5>No messages yet</h5>
                                <p>
                                    @if($order)
                                        No communication history found for Order #{{ $order->id }}.
                                    @else
                                        No communication history found for this dispute.
                                    @endif
                                    Start the conversation by sending a message below.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Add Message Form -->
                    @if($dispute->status !== 'final')
                        <hr class="my-4">
                        <form action="{{ route('disputes.messages.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="mb-1 block text-sm font-medium text-slate-700">
                                    <i class="bi bi-chat-dots"></i> Add Message to Dispute
                                </label>
                                <textarea name="message" id="message" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('message') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                    placeholder="Type your message here... Be clear and provide any relevant details or evidence." required></textarea>
                                @error('message')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="attachments" class="mb-1 block text-sm font-medium text-slate-700">
                                    <i class="bi bi-paperclip"></i> Attachments (Optional)
                                </label>
                                <input type="file" name="attachments[]" id="attachments" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                                    multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                                <div class="mt-1 text-xs text-slate-500">
                                    <i class="bi bi-info-circle"></i>
                                    Max 10MB per file. Supported: JPG, PNG, WebP, PDF, DOC, DOCX
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                    <i class="bi bi-send"></i> Send Message
                                </button>
                                <small class="text-slate-500">
                                    <i class="bi bi-clock"></i> 
                                    Messages are sent immediately and visible to both parties
                                </small>
                            </div>
                        </form>
                    @else
                        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-center">
                            <i class="bi bi-info-circle"></i>
                            This dispute is final and no further messages can be added.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-span-12 md:col-span-4">
            <!-- Quick Actions -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="d-grid gap-2">
                        <a href="{{ route('disputes.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50">
                            <i class="fas fa-arrow-left"></i> Back to Disputes
                        </a>
                        
                        @if($order)
                        <a href="{{ route('orders.chat.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition btn-outline-info">
                            <i class="bi bi-chat"></i> Order Chat
                        </a>

                        @php
                            $viewerIsBuyer = auth()->check() && auth()->id() === ($dispute->buyer_id ?? null);
                            $orderIsShipped = $order && ($order->status === \App\Models\Order::STATUS_SHIPPED);
                            $disputeClosedOrResolved = $dispute->isClosed() || $dispute->isResolved() || $dispute->isMutuallyResolved();
                        @endphp
                        @if($viewerIsBuyer && $orderIsShipped && $disputeClosedOrResolved)
                            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400" data-ui-toggle="modal" data-ui-target="#deliverModal-{{ $order->id }}">
                                <i class="bi bi-check2-circle"></i> Mark Delivered
                            </button>
                        @endif
                        @if($viewerIsBuyer && $order && $order->status === \App\Models\Order::STATUS_DELIVERED)
                            <a href="{{ route('buyer.orders.show', $order->id) }}#reviews" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-amber-500 text-amber-700 hover:bg-amber-50">
                                <i class="bi bi-star"></i> Leave a Review
                            </a>
                        @endif
                        @endif
                        
                        @if($dispute->status !== 'resolved' && $dispute->status !== 'closed' && $dispute->status !== 'final' && (auth()->id() === $dispute->created_by || auth()->user()->isAdmin()))
                            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500" data-ui-toggle="modal" data-ui-target="#closeDisputeModal">
                                <i class="bi bi-check-circle"></i> 
                                @if(auth()->user()->isAdmin())
                                    Close Dispute (Admin)
                                @elseif(auth()->id() === $dispute->created_by)
                                    Close Dispute 
                                @endif
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dispute Timeline -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h6 class="mb-0">Timeline</h6>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Dispute Created</h6>
                                <small class="text-slate-500">{{ $dispute->created_at->format('M d, Y g:i A') }}</small>
                                @if($dispute->createdBy)
                                    <br><small class="text-slate-500">by {{ $dispute->createdBy->name }}</small>
                                @endif
                            </div>
                        </div>

                        @if($dispute->status === 'pending')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-amber-100"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Pending</h6>
                                    <small class="text-slate-500">Awaiting response from the other party</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->status === 'under_review')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-sky-100"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Under Review</h6>
                                    <small class="text-slate-500">Being reviewed by Cetsy support team</small>
                                </div>
                            </div>
                        @endif

                        @if(config('disputes.enable_appeals') && $dispute->status === 'appealed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-amber-100"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Appealed</h6>
                                    <small class="text-slate-500">Dispute has been appealed for review</small>
                                </div>
                            </div>
                        @endif

                        @if(config('disputes.enable_appeals') && $dispute->status === 'appeal_under_review')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-amber-100"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Appeal Under Review</h6>
                                    <small class="text-slate-500">Appeal is being reviewed by support team</small>
                                </div>
                            </div>
                        @endif

                        @if(config('disputes.enable_appeals') && $dispute->status === 'appeal_approved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Appeal Approved</h6>
                                    <small class="text-slate-500">Appeal has been approved</small>
                                </div>
                            </div>
                        @endif

                        @if(config('disputes.enable_appeals') && $dispute->status === 'appeal_rejected')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Appeal Rejected</h6>
                                    <small class="text-slate-500">Appeal has been rejected</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->mutual_resolution_terms)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Mutual Resolution Proposed</h6>
                                    <small class="text-slate-500">Terms: {{ Str::limit($dispute->mutual_resolution_terms, 50) }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->buyer_agreed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Buyer Agreed to Resolution</h6>
                                    <small class="text-slate-500">{{ $dispute->buyer_agreed_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->seller_agreed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Seller Agreed to Resolution</h6>
                                    <small class="text-slate-500">{{ $dispute->seller_agreed_at->format('M d, Y g:i A') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->status === 'mutually_resolved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Mutually Resolved</h6>
                                    <small class="text-slate-500">Both parties agreed to resolution terms</small>
                                </div>
                            </div>
                        @endif

                        @if($dispute->status === 'resolved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Resolved</h6>
                                    <small class="text-slate-500">{{ $dispute->resolved_at ? $dispute->resolved_at->format('M d, Y g:i A') : 'Resolution completed' }}</small>
                                    @if($dispute->resolvedBy)
                                        <br><small class="text-slate-500">by {{ $dispute->resolvedBy->name }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($dispute->status === 'closed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Closed</h6>
                                    <small class="text-slate-500">{{ $dispute->closed_at ? $dispute->closed_at->format('M d, Y g:i A') : 'Dispute closed' }}</small>
                                    @if($dispute->closedBy)
                                        <br><small class="text-slate-500">by {{ $dispute->closedBy->name }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($dispute->status === 'final')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-dark"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status: Final Decision</h6>
                                    <small class="text-slate-500">No further appeals possible</small>
                                </div>
                            </div>
                        @endif

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Appeal Modal --}}
@if(config('disputes.enable_appeals'))
<div class="modal" id="appealModal" tabindex="-1" aria-labelledby="appealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 bg-amber-100 text-slate-900">
                <h5 class="text-base font-semibold text-slate-900" id="appealModalLabel">
                    <i class="bi bi-gavel"></i> Submit Appeal
                </h5>
                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form action="{{ route('disputes.appeal.store', $dispute->id) }}" method="POST" enctype="multipart/form-data" id="appealForm">
                @csrf
                <div class="px-4 py-4">
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                        <h6 class="alert-heading">Appeal Information</h6>
                        @if($dispute->status === 'resolved')
                            <p class="mb-0">Please provide a detailed reason for your appeal and any new evidence to support your case.</p>
                        @elseif($dispute->status === 'under_review')
                            <p class="mb-0">Please provide a detailed reason for your appeal regarding the admin review process and any new evidence to support your case.</p>
                        @elseif($dispute->status === 'pending')
                            <p class="mb-0">Please provide a detailed reason for your appeal regarding the seller's lack of response and any new evidence to support your case.</p>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason_category" class="mb-1 block text-sm font-medium text-slate-700">Appeal Reason Category *</label>
                        <select name="reason_category" id="reason_category" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" required>
                            <option value="">Select a reason category</option>
                            @if($dispute->status === 'resolved')
                                <option value="new_evidence">New Evidence Available</option>
                                <option value="decision_error">Decision Based on Incorrect Information</option>
                                <option value="procedural_error">Procedural Error in Review</option>
                                <option value="other">Other Reasons</option>
                            @elseif($dispute->status === 'under_review')
                                <option value="procedural_error">Admin Review Taking Too Long</option>
                                <option value="new_evidence">New Evidence Available</option>
                                <option value="review_concerns">Concerns About Review Process</option>
                                <option value="other">Other Reasons</option>
                            @elseif($dispute->status === 'pending')
                                <option value="seller_unresponsive">Seller Not Responding</option>
                                <option value="urgent_review">Urgent Review Required</option>
                                <option value="new_evidence">New Evidence Available</option>
                                <option value="other">Other Reasons</option>
                            @endif
                        </select>
                        <div class="mt-1 text-xs text-slate-500">
                            Choose the category that best describes your appeal reason.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="mb-1 block text-sm font-medium text-slate-700">Appeal Reason *</label>
                        <textarea name="reason" id="reason" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                            placeholder="Please explain why you believe the decision should be reconsidered. Provide specific reasons and any new information..." required></textarea>
                        <div class="mt-1 text-xs text-slate-500">
                            Be specific about why you disagree with the decision. Provide new evidence or information that wasn't available during the initial review.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_evidence" class="mb-1 block text-sm font-medium text-slate-700">New Evidence *</label>
                        <input type="file" name="new_evidence[]" id="new_evidence" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                            multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                        <div class="mt-1 text-xs text-slate-500">
                            Upload supporting documents, screenshots, or photos for your appeal. 
                            <strong>Required:</strong> At least 1 file. Max 10MB per file. Total limit: 50MB. Supported: JPG, PNG, PDF, DOC, DOCX
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="evidence_descriptions" class="mb-1 block text-sm font-medium text-slate-700">Evidence Descriptions (Optional)</label>
                        <div id="evidence-descriptions-container">
                            <div class="evidence-description-item mb-2">
                                <input type="text" name="evidence_descriptions[]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                                    placeholder="Describe what this evidence proves (optional)">
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            Add descriptions for your evidence files to help reviewers understand their relevance.
                        </div>
                    </div>

                    @if($dispute->appeal_deadline)
                        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                            <h6 class="alert-heading">Appeal Deadline</h6>
                            <p class="mb-0">
                                You have <strong>{{ $dispute->getAppealDeadlineDaysLeft() }} days</strong> remaining to submit your appeal.
                                <br>
                                <small class="text-slate-500">Deadline: {{ $dispute->appeal_deadline->format('M d, Y \a\t g:i A') }}</small>
                            </p>
                        </div>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400">
                        <i class="bi bi-gavel"></i> Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Evidence Response Modals --}}
@if(config('disputes.enable_appeals') && $dispute->appeal && $dispute->appeal->evidenceRequests->isNotEmpty())
    @foreach($dispute->appeal->evidenceRequests as $evidenceRequest)
        @if($evidenceRequest->status === 'pending' && $evidenceRequest->requested_from === auth()->id())
            <div class="modal" id="evidenceResponseModal-{{ $evidenceRequest->id }}" tabindex="-1" aria-labelledby="evidenceResponseModalLabel-{{ $evidenceRequest->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 bg-primary text-white">
                            <h5 class="text-base font-semibold text-slate-900" id="evidenceResponseModalLabel-{{ $evidenceRequest->id }}">
                                <i class="bi bi-upload"></i> Submit Evidence Response
                            </h5>
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                        </div>
                        <form action="{{ route('disputes.evidence-requests.respond', $evidenceRequest->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="px-4 py-4">
                                <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                                    <h6 class="alert-heading">Evidence Request Details</h6>
                                    <p class="mb-0">{{ $evidenceRequest->message }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="response_notes" class="mb-1 block text-sm font-medium text-slate-700">Response Notes</label>
                                    <textarea name="response_notes" id="response_notes" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                                        placeholder="Please provide any additional context or explanation for your evidence..." required></textarea>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Explain how your evidence supports your position in this dispute.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="submitted_evidence" class="mb-1 block text-sm font-medium text-slate-700">Evidence Files</label>
                                    <input type="file" name="submitted_evidence[]" id="submitted_evidence" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                                        multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Upload supporting documents, screenshots, or photos. Max 10MB per file. Supported: JPG, PNG, PDF, DOC, DOCX
                                    </div>
                                </div>

                                <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                                    <h6 class="alert-heading">Important</h6>
                                    <p class="mb-0">
                                        <strong>Deadline:</strong> {{ $evidenceRequest->deadline->format('M d, Y \a\t g:i A') }}
                                        <br>
                                        <strong>Time Remaining:</strong> {{ $evidenceRequest->getDeadlineDaysLeft() }} days
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                                <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Cancel</button>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                    <i class="bi bi-upload"></i> Submit Evidence Response
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

@push('styles')
<style>
.message.buyer-message { 
    background-color: #e3f2fd; 
    border-left: 4px solid #2196f3;
}
.message.seller-message { 
    background-color: #f3e5f5; 
    border-left: 4px solid #9c27b0;
}
.message.admin-message { 
    background-color: #fff3e0; 
    border-left: 4px solid #ff9800;
}
.message.system-message { 
    background-color: #f1f8e9; 
    border-left: 4px solid #4caf50;
}

.message.other-message { 
    background-color: #fff3cd; 
    border-left: 4px solid #ffc107;
}

.message.default-message {
    background-color: #f8f9fa;
    border-left: 4px solid #6c757d;
}

.message-source-badge {
    opacity: 0.8;
}

.dispute-message {
    border-left: 4px solid #ffc107;
}

.order-message {
    border-left: 4px solid #17a2b8;
}

/* Enhanced Message Styling */
.message {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

/* Profile Image Styling */
.message-header img.rounded-circle {
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.message-header img.rounded-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Hide fallback avatar by default when image is present */
.message-header img.rounded-circle + strong + .badge + .avatar-fallback {
    display: none;
}

/* Fallback Avatar Styling */
.avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.avatar-fallback:hover {
    transform: scale(1.05);
}

.message:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.message-header {
    background-color: rgba(255,255,255,0.8);
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
    margin: -12px -16px 16px -16px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.message-content {
    background-color: rgba(255,255,255,0.9);
    border-radius: 8px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

/* Attachment Styling */
.attachments-section {
    background-color: rgba(255,255,255,0.7);
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
}

.attachment-item {
    background-color: white;
    transition: all 0.3s ease;
    cursor: pointer;
}

.attachment-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.attachment-item img {
    transition: all 0.3s ease;
}

.attachment-item img:hover {
    transform: scale(1.05);
}

/* Evidence Item Styling */
.evidence-item {
    background-color: white;
    transition: all 0.3s ease;
}

 .evidence-item:hover {
     transform: translateY(-2px);
     box-shadow: 0 4px 12px rgba(0,0,0,0.15);
 }

 .evidence-status-card {
     background-color: #f8f9fa;
     transition: all 0.3s ease;
     min-height: 80px;
     display: flex;
     flex-direction: column;
     justify-content: center;
 }

 .evidence-status-card:hover {
     background-color: #e9ecef;
     box-shadow: 0 2px 8px rgba(0,0,0,0.1);
 }

/* Evidence Response Item Styling */
.evidence-response-item {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.evidence-response-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.evidence-response-item .badge {
    font-size: 0.75rem;
}

/* Timeline Styling */
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}

/* Filter Button Styling */
.btn-group .btn {
    border-radius: 6px;
    margin-right: 4px;
}

.btn-group .btn.active {
    font-weight: 600;
}

/* Message Container */
.messages-container {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.messages-container::-webkit-scrollbar {
    width: 8px;
}

.messages-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.messages-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .attachment-item {
        margin-bottom: 16px;
    }
    
    .btn-group {
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 4px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to bottom of messages
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Message filtering
    const filterButtons = document.querySelectorAll('[data-filter]');
    const messages = document.querySelectorAll('.message');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter messages
            messages.forEach(message => {
                const messageType = message.getAttribute('data-message-type');
                if (filter === 'all' || messageType === filter) {
                    message.style.display = 'block';
                    message.style.animation = 'fadeIn 0.3s ease-in';
                } else {
                    message.style.display = 'none';
                }
            });
        });
    });

    // File size validation
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        const maxSize = 10 * 1024 * 1024; // 10MB

        fileInput.addEventListener('change', function() {
            const files = this.files;
            let totalSize = 0;
            
            for (let i = 0; i < files.length; i++) {
                totalSize += files[i].size;
                
                if (files[i].size > maxSize) {
                    alert(`File "${files[i].name}" is too large. Maximum size is 10MB.`);
                    this.value = '';
                    return;
                }
            }
            
            // Show total size info
            if (files.length > 0) {
                const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);
                const infoText = this.nextElementSibling;
                infoText.innerHTML = `<i class="bi bi-info-circle"></i> ${files.length} file(s) selected. Total size: ${totalSizeMB}MB. Max 10MB per file.`;
            }
        });
    }

    // Enhanced message animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const messageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all messages for animation
    messages.forEach(message => {
        message.style.opacity = '0';
        message.style.transform = 'translateY(20px)';
        message.style.transition = 'all 0.5s ease';
        messageObserver.observe(message);
    });

    // Auto-scroll to new messages
    const scrollToBottom = () => {
        if (messagesContainer) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }
    };

    // Scroll to bottom when new message is added
    const messageForm = document.querySelector('form');
    if (messageForm) {
        messageForm.addEventListener('submit', () => {
            setTimeout(scrollToBottom, 100);
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit message
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const messageForm = document.querySelector('form');
            if (messageForm && document.activeElement.tagName === 'TEXTAREA') {
                e.preventDefault();
                messageForm.submit();
            }
        }
    });

    // Enhanced attachment preview
    const attachmentItems = document.querySelectorAll('.attachment-item img');
    attachmentItems.forEach(img => {
        img.addEventListener('click', function() {
            const src = this.src;
            const alt = this.alt;
            openImageModal(src, alt);
        });
    });

    // Evidence descriptions dynamic fields
    const evidenceInput = document.getElementById('new_evidence');
    const descriptionsContainer = document.getElementById('evidence-descriptions-container');
    
    if (evidenceInput && descriptionsContainer) {
        evidenceInput.addEventListener('change', function() {
            const files = this.files;
            const currentDescriptions = descriptionsContainer.querySelectorAll('.evidence-description-item');
            
            // Remove existing description fields
            currentDescriptions.forEach(item => item.remove());
            
            // Add new description fields for each file
            for (let i = 0; i < files.length; i++) {
                const descriptionItem = document.createElement('div');
                descriptionItem.className = 'evidence-description-item mb-2';
                descriptionItem.innerHTML = `
                    <div class="flex w-full items-stretch">
                        <span class="inline-flex items-center rounded-l-xl border border-slate-300 bg-slate-100 px-3 text-sm text-slate-600">${i + 1}</span>
                        <input type="text" name="evidence_descriptions[]" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                            placeholder="Describe what ${files[i].name} proves (optional)">
                    </div>
                `;
                descriptionsContainer.appendChild(descriptionItem);
            }
        });
    }
});

// Image Modal Function
function openImageModal(imageSrc, imageAlt) {
    // Create modal HTML
    const modalHTML = `
        <div class="modal" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <h5 class="text-base font-semibold text-slate-900" id="imageModalLabel">${imageAlt}</h5>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="px-4 py-4 text-center">
                        <img src="${imageSrc}" alt="${imageAlt}" class="img-fluid" style="max-height: 70vh;">
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                        <a href="${imageSrc}" target="_blank" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                            <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                        </a>
                        <a href="${imageSrc}" download="${imageAlt}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50" data-ui-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show modal with shared UI handlers (no Bootstrap runtime required)
    const modal = document.getElementById('imageModal');
    if (!modal) return;
    modal.classList.add('is-open');
    document.body.classList.add('overflow-hidden');
    modal.dispatchEvent(new Event('shown.bs.modal'));

    // Clean up modal after it's hidden
    modal.addEventListener('hidden.bs.modal', function() {
        this.remove();
    }, { once: true });
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message {
        animation: fadeIn 0.5s ease-out;
    }
    
    .attachment-item img {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .attachment-item img:hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);

// Evidence submission form handling
document.addEventListener('DOMContentLoaded', function() {
    const evidenceForm = document.getElementById('submitEvidenceModal')?.querySelector('form');
    if (evidenceForm) {
        evidenceForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
            submitBtn.disabled = true;
            
            // Re-enable button after a delay (in case of errors)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
    }
});
</script>
@endpush

{{-- Appeal Modal --}}
@if(config('disputes.enable_appeals') && $dispute->canBeAppealed() && (auth()->id() === $dispute->buyer_id || auth()->id() === $dispute->seller_id))
<div class="modal" id="appealModal" tabindex="-1" aria-labelledby="appealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h5 class="text-base font-semibold text-slate-900" id="appealModalLabel">
                    <i class="bi bi-gavel"></i> Appeal to Support Team
                </h5>
                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form action="{{ route('disputes.appeal.store', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="px-4 py-4">
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                        <i class="bi bi-info-circle"></i>
                        <strong>Appeal Process:</strong> You can appeal to our support team for intervention at any time. Please provide a clear reason and supporting evidence for your appeal.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="mb-1 block text-sm font-medium text-slate-700">Appeal Reason <span class="text-rose-600">*</span></label>
                        <textarea name="reason" id="reason" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                            placeholder="Please explain why you need support team intervention and what you hope to achieve..." required></textarea>
                        <div class="mt-1 text-xs text-slate-500">
                            Clearly state your reasons for appealing and what you hope to achieve.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_evidence" class="mb-1 block text-sm font-medium text-slate-700">Supporting Evidence</label>
                        <input type="file" name="new_evidence[]" id="new_evidence" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                            multiple accept="image/*,.pdf,.doc,.docx">
                        <div class="mt-1 text-xs text-slate-500">
                            Upload screenshots, documents, or any other evidence to support your appeal. (Max 5 files, 5MB each)
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-amber-500 text-slate-900 hover:bg-amber-400">
                        <i class="bi bi-gavel"></i> Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Evidence Submission Modal --}}
@if(config('disputes.enable_appeals') && $dispute->appeal && $dispute->appeal->status === 'evidence_requested')
    @php
        $userEvidenceRequest = null;
        if (auth()->id() === $dispute->buyer_id) {
            $userEvidenceRequest = $dispute->appeal->buyerEvidenceRequest;
        } elseif (auth()->id() === $dispute->seller_id) {
            $userEvidenceRequest = $dispute->appeal->sellerEvidenceRequest;
        }
    @endphp
    
    @if($userEvidenceRequest && $userEvidenceRequest->status !== 'submitted')
        <div class="modal" id="submitEvidenceModal" tabindex="-1" aria-labelledby="submitEvidenceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <h5 class="text-base font-semibold text-slate-900" id="submitEvidenceModalLabel">
                            <i class="bi bi-upload"></i> Submit Evidence
                        </h5>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <form action="{{ route('evidence-requests.submit', $userEvidenceRequest->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="px-4 py-4">
                            <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                                <i class="bi bi-info-circle"></i>
                                <strong>Evidence Request:</strong> {{ $userEvidenceRequest->request_message }}
                            </div>
                            
                            <div class="grid grid-cols-12 gap-4 mb-3">
                                <div class="col-span-12 md:col-span-6">
                                    <h6>Required Evidence Types</h6>
                                    @foreach($userEvidenceRequest->getRequiredEvidenceTypesList() as $evidenceType)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary mr-2 mb-2">{{ $evidenceType }}</span>
                                    @endforeach
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <h6>Deadline Information</h6>
                                    <p class="mb-1"><strong>Deadline:</strong> {{ $userEvidenceRequest->deadline->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="mb-0">
                                        <span class="text-{{ $userEvidenceRequest->getDaysUntilDeadline() <= 3 ? 'warning' : 'success' }}">
                                            {{ $userEvidenceRequest->getDaysUntilDeadline() }} days remaining
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="evidence_description" class="mb-1 block text-sm font-medium text-slate-700">Evidence Description <span class="text-rose-600">*</span></label>
                                <textarea name="evidence_description" id="evidence_description" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('evidence_description') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                    placeholder="Please describe the evidence you are submitting and how it supports your case..." required>{{ old('evidence_description') }}</textarea>
                                @error('evidence_description')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                                <div class="mt-1 text-xs text-slate-500">
                                    Provide a clear description of your evidence and how it relates to the dispute.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="evidence_files" class="mb-1 block text-sm font-medium text-slate-700">Evidence Files <span class="text-rose-600">*</span></label>
                                <input type="file" name="evidence_files[]" id="evidence_files" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('evidence_files.*') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                    multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.mp4,.mov" required>
                                @error('evidence_files.*')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                                <div class="mt-1 text-xs text-slate-500">
                                    <strong>Accepted formats:</strong> Images (JPG, PNG), Documents (PDF, DOC, DOCX), Videos (MP4, MOV)<br>
                                    <strong>Maximum file size:</strong> 50MB per file<br>
                                    <strong>Multiple files:</strong> You can select multiple files at once
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="additional_notes" class="mb-1 block text-sm font-medium text-slate-700">Additional Notes</label>
                                <textarea name="additional_notes" id="additional_notes" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('additional_notes') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                    placeholder="Any additional information or context you'd like to provide...">{{ old('additional_notes') }}</textarea>
                                @error('additional_notes')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Important:</strong> Once you submit evidence, you cannot modify it. Please ensure all files are correct before submission.
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                            <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Cancel</button>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                                <i class="bi bi-upload"></i> Submit Evidence
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif

    {{-- Buyer Post-Dispute Action: Mark Delivered (when dispute closed/resolved and order is shipped) --}}
    @php
        $viewerIsBuyer = auth()->check() && auth()->id() === ($dispute->buyer_id ?? null);
        $orderForAction = isset($order) && $order ? $order : $dispute->order;
        $orderIsShipped = $orderForAction && ($orderForAction->status === \App\Models\Order::STATUS_SHIPPED);
        $disputeClosedOrResolved = $dispute->isClosed() || $dispute->isResolved() || $dispute->isMutuallyResolved();
    @endphp
    @if($viewerIsBuyer && $orderForAction && $orderIsShipped && $disputeClosedOrResolved)
        @once
            {{-- Include the buyer deliver modal once for this page --}}
            @include('seller.orders.modals.delivered', ['order' => $orderForAction])
        @endonce
    @endif

{{-- Close Dispute Modal --}}
<div class="modal" id="closeDisputeModal" tabindex="-1" aria-labelledby="closeDisputeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 bg-success text-white">
                <h5 class="text-base font-semibold text-slate-900" id="closeDisputeModalLabel">
                    <i class="bi bi-check-circle"></i> Close Dispute
                </h5>
                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 text-white hover:bg-white/20 hover:text-white" data-ui-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form action="{{ route('disputes.close', $dispute->id) }}" method="POST">
                @csrf
                <div class="px-4 py-4">
                    @if(auth()->user()->isAdmin())
                        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                            <i class="bi bi-shield-check"></i>
                            <strong>Admin Action:</strong> You are closing this dispute as an administrator.
                        </div>
                    @endif
                    
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                        <i class="bi bi-info-circle"></i>
                        <strong>Closing Dispute:</strong> This action will mark the dispute as closed. Only the dispute creator or admin users can close disputes. Please ensure all issues have been resolved before proceeding.
                    </div>
                    
                    <div class="mb-3">
                        <label for="closure_notes" class="mb-1 block text-sm font-medium text-slate-700">Additional Notes (Optional)</label>
                        <textarea name="closure_notes" id="closure_notes" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" 
                            placeholder="Provide any additional details about the resolution or closure..."></textarea>
                        <div class="mt-1 text-xs text-slate-500">
                            Optional: Add any additional context about how the dispute was resolved.
                        </div>
                    </div>

                    <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Important:</strong> Once closed, this dispute cannot be reopened. Make sure all parties are satisfied with the resolution.
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                        <i class="bi bi-check-circle"></i> Confirm Close Dispute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection






