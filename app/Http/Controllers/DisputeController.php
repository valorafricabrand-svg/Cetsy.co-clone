<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Order;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DisputeInitiatedBuyerMail;
use App\Mail\DisputeInitiatedSellerMail;
use App\Mail\DisputeClosedMail;
use App\Mail\DisputeResponseMail;
use App\Models\Activity;
use App\Models\EvidenceRequest;
use App\Models\Wallet;


class DisputeController extends Controller
{
    /**
     * Display a listing of disputes for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');
        $type = $request->get('type');

        // Get disputes where user is either buyer or specifically involved as seller
        $query = Dispute::with(['order', 'buyer', 'seller', 'appeal'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)      // User is the buyer
                  ->orWhere('seller_id', $user->id);  // User is specifically the seller in this dispute
            });

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get status counts for disputes where user is involved
        $statusCounts = Dispute::where(function ($q) use ($user) {
            $q->where('buyer_id', $user->id)
              ->orWhere('seller_id', $user->id);
        })
        ->selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

        return view('disputes.index', compact('disputes', 'statusCounts'));
    }

    /**
     * Show the form for creating a new dispute
     */
    public function create(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        $error = null;

        // Debug all request parameters
        \Log::info('Dispute create request', [
            'all_params' => $request->all(),
            'order_id_param' => $orderId,
            'user_id' => Auth::user()->id
        ]);

        if ($orderId) {
            try {
                // Add some debugging
                \Log::info('Looking for order', [
                    'order_id' => $orderId,
                    'user_id' => Auth::user()->id,
                    'order_id_type' => gettype($orderId)
                ]);

                // Check if there are any orders in the database
                $totalOrders = Order::count();
                $userOrders = Order::where('user_id', Auth::user()->id)->count();
                $sellerOrders = Order::whereHas('shop', function($query) {
                    $query->where('user_id', Auth::user()->id);
                })->count();
                
                \Log::info('Database order counts', [
                    'total_orders' => $totalOrders,
                    'user_orders_as_buyer' => $userOrders,
                    'user_orders_as_seller' => $sellerOrders
                ]);

                // First, let's check if the order exists at all
                $anyOrder = Order::with('shop')->find($orderId);
                \Log::info('Order lookup result', [
                    'order_exists' => $anyOrder ? 'yes' : 'no',
                    'order_user_id' => $anyOrder ? $anyOrder->user_id : 'N/A',
                    'order_shop_user_id' => $anyOrder && $anyOrder->shop ? $anyOrder->shop->user_id : 'N/A',
                    'current_user_id' => Auth::user()->id
                ]);

                // Check if user is authorized (either buyer or seller)
                $isAuthorized = false;
                if ($anyOrder) {
                    $isAuthorized = $anyOrder->user_id === Auth::user()->id || // Buyer
                                   ($anyOrder->shop && $anyOrder->shop->user_id === Auth::user()->id); // Seller
                }

                if ($isAuthorized) {
                    $order = Order::with(['shop', 'items.product'])->find($orderId);
                } else {
                    if ($anyOrder) {
                        $error = 'Order found but you do not have permission to access it.';
                    } else {
                        $error = 'Order not found. Please check the order ID and try again.';
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error looking up order', [
                    'error' => $e->getMessage(),
                    'order_id' => $orderId
                ]);
                $error = 'An error occurred while looking up the order. Please try again.';
            }
        }

        $disputeTypes = [
            Dispute::TYPE_CUSTOMS_FEES => 'Customs Fees',
            Dispute::TYPE_ITEM_MISREPRESENTATION => 'Item Misrepresentation',
            Dispute::TYPE_SHIPPING_ISSUES => 'Shipping Issues',
            Dispute::TYPE_QUALITY_ISSUES => 'Quality Issues',
            Dispute::TYPE_PAYMENT_ISSUES => 'Payment Issues',
            Dispute::TYPE_OTHER => 'Other'
        ];

        return view('disputes.create', compact('order', 'disputeTypes', 'error'));
    }

    /**
     * Store a newly created dispute
     */
    public function store(Request $request)
    {
        try {
            // Test database connection first
            try {
                DB::connection()->getPdo();
                \Log::info('Database connection successful');
            } catch (\Exception $e) {
                \Log::error('Database connection failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['error' => 'Database connection failed. Please try again.']);
            }

            \Log::info('Starting dispute creation', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $data = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'type' => ['required', Rule::in([
                    Dispute::TYPE_CUSTOMS_FEES,
                    Dispute::TYPE_ITEM_MISREPRESENTATION,
                    Dispute::TYPE_SHIPPING_ISSUES,
                    Dispute::TYPE_QUALITY_ISSUES,
                    Dispute::TYPE_PAYMENT_ISSUES,
                    Dispute::TYPE_OTHER
                ])],
                'description' => 'required|string|max:2000',
                'evidence.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
                'request_return_exchange' => 'nullable|boolean',
            ]);

            \Log::info('Validation passed', ['data' => $data]);

            $order = Order::with('shop')->findOrFail($data['order_id']);

            \Log::info('Order found', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'order_shop_user_id' => $order->shop ? $order->shop->user_id : 'N/A'
            ]);

            // Check if user is authorized (either buyer or seller)
            $isAuthorized = $order->user_id === Auth::id() || // Buyer
                           ($order->shop && $order->shop->user_id === Auth::id()); // Seller

            \Log::info('Authorization check', [
                'is_authorized' => $isAuthorized,
                'current_user_id' => Auth::id(),
                'order_user_id' => $order->user_id,
                'shop_user_id' => $order->shop ? $order->shop->user_id : 'N/A'
            ]);

            if (!$isAuthorized) {
                return back()->withErrors(['error' => 'You can only create disputes for orders you are involved in (as buyer or seller).']);
            }

            // Check if there is an active (not closed) dispute for this order
            $activeDispute = Dispute::where('order_id', $order->id)
                ->where(function ($query) {
                    $query->whereNull('status')
                          ->orWhere('status', '!=', Dispute::STATUS_CLOSED);
                })
                ->latest('created_at')
                ->first();

            if ($activeDispute) {
                \Log::info('Dispute creation blocked - active dispute exists', [
                    'order_id' => $order->id,
                    'existing_dispute_id' => $activeDispute->id,
                    'existing_status' => $activeDispute->status,
                ]);

                return back()->withErrors(['error' => 'A dispute already exists for this order and must be closed before a new one can be created.']);
            }

            // Determine if user is buyer or seller
            $isBuyer = $order->user_id === Auth::id();
            $buyerId = $isBuyer ? Auth::id() : $order->user_id;
            $sellerId = $isBuyer ? $order->shop->user_id : Auth::id();

            // Whether buyer requested a return/exchange
            $wantsExchange = $isBuyer && $request->boolean('request_return_exchange');

            \Log::info('User role determination', [
                'is_buyer' => $isBuyer,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'user_role' => $isBuyer ? 'buyer' : 'seller'
            ]);

            // Handle file uploads
            $evidence = [];
            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('disputes/evidence', 'public');
                    $evidence[] = [
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }

            \Log::info('About to create dispute in transaction');

            // Create the dispute and return it from the transaction
            $dispute = DB::transaction(function () use ($data, $order, $evidence, $buyerId, $sellerId, $wantsExchange) {
                \Log::info('Inside transaction - creating dispute');
                
                $dispute = Dispute::create([
                    'order_id' => $order->id,
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                    'created_by' => Auth::id(),
                    'type' => $data['type'],
                    'status' => Dispute::STATUS_PENDING,
                    'description' => $data['description'],
                    'evidence' => $evidence
                ]);

                \Log::info('Dispute created', ['dispute_id' => $dispute->id]);

                // Create initial system message
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'user_id' => Auth::id(),
                    'message' => $data['description'],
                    'type' => Auth::id() === $buyerId ? DisputeMessage::TYPE_BUYER_MESSAGE : DisputeMessage::TYPE_SELLER_MESSAGE,
                    'is_internal' => false
                ]);

                \Log::info('Initial message created');

                // Create system message (no user_id for system messages)
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'user_id' => null, // No user for system messages
                    'message' => 'Dispute created. Awaiting response.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);

                \Log::info('System message created');

                // If buyer requested return/exchange, reset order to processing and clear previous shipment details
                if ($wantsExchange) {
                    try {
                        if (!in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)) {
                            $order->update([
                                'status'       => Order::STATUS_PROCESSING,
                                'courier'      => null,
                                'tracking_no'  => null,
                                'tracking_url' => null,
                                'shipped_at'   => null,
                                'ship_notes'   => null,
                            ]);

                            DisputeMessage::create([
                                'dispute_id' => $dispute->id,
                                'user_id' => null,
                                'message' => 'Buyer requested a return/exchange. Order reset to processing so seller can ship a replacement and update tracking.',
                                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                                'is_internal' => false
                            ]);
                        } else {
                            \Log::info('Return/exchange requested but order is not eligible for reset', [
                                'order_id' => $order->id,
                                'status' => $order->status,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        \Log::error('Failed to reset order for return/exchange', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                return $dispute; // Return the dispute from the transaction
            });

            \Log::info('Transaction completed successfully', [
                'dispute_id' => $dispute->id,
                'order_id' => $order->id
            ]);

            $dispute->loadMissing(['buyer', 'seller']);
            try {
                if ($dispute->buyer && $dispute->buyer->email) {
                    Mail::to($dispute->buyer->email)->send(new DisputeInitiatedBuyerMail($dispute));
                }

                if ($dispute->seller && $dispute->seller->email) {
                    Mail::to($dispute->seller->email)->send(new DisputeInitiatedSellerMail($dispute));
                }

                \Log::info('Dispute initiation emails dispatched', [
                    'dispute_id' => $dispute->id,
                    'buyer_notified' => (bool) ($dispute->buyer && $dispute->buyer->email),
                    'seller_notified' => (bool) ($dispute->seller && $dispute->seller->email)
                ]);
            } catch (\Throwable $mailException) {
                \Log::error('Failed to send dispute initiation notifications', [
                    'dispute_id' => $dispute->id,
                    'error' => $mailException->getMessage()
                ]);
            }

            try {
                $dispute->loadMissing(['buyer', 'seller']);
                $initiatorName = Auth::user()->name ?? 'a party';
                $otherParty = $dispute->buyer_id === Auth::id() ? $dispute->seller : $dispute->buyer;

                if ($otherParty) {
                    Activity::create([
                        'user_id' => $otherParty->id,
                        'is_read' => false,
                        'description' => 'New dispute #' . $dispute->id . ' opened for order #' . $order->id . ' by ' . $initiatorName . '.',
                        'type' => Activity::TYPE_DISPUTE,
                        'related_id' => $dispute->id,
                        'related_type' => 'dispute'
                    ]);
                }
            } catch (\Throwable $activityException) {
                \Log::error('Failed to create dispute activity for creation event', [
                    'dispute_id' => $dispute->id,
                    'error' => $activityException->getMessage()
                ]);
            }

            return redirect()->route('disputes.show', $dispute->id)
                ->with('success', 'Dispute created successfully. The other party has been notified.');

        } catch (\Exception $e) {
            \Log::error('Error creating dispute', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'An error occurred while creating the dispute: ' . $e->getMessage()]);
        }
    }

    /**
     * Perform basic security checks for appeal submission
     */
    private function performSecurityChecks($user, Dispute $dispute): array
    {
        // Basic rate limiting - prevent appeal spam
        $recentAppeals = Appeal::where('appealed_by', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
        
        if ($recentAppeals >= 5) {
            return [
                'allowed' => false,
                'message' => 'Rate limit exceeded. You can only submit 5 appeals per 24 hours. Please wait before submitting another appeal.'
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Security checks passed'
        ];
    }



    /**
     * Display the specified dispute
     */
    public function show(Dispute $dispute)
    {
        $user = Auth::user();

        // Check if user is authorized to view this dispute (either buyer or specifically involved as seller)
        $isAuthorized = $dispute->buyer_id === $user->id || // User is the buyer
                       $dispute->seller_id === $user->id;   // User is specifically the seller in this dispute

        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to dispute.');
        }

        $dispute->load(['order.shop', 'buyer', 'seller', 'messages.user', 'appeal']);
        // Mark dispute notifications as read for this user
        try {
            Activity::where('user_id', $user->id)
                ->where('type', Activity::TYPE_DISPUTE)
                ->where(function($q) use ($dispute) { $q->where('related_id', $dispute->id)->orWhereNull('related_id'); })
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) { /* non-fatal */ }

        // Get dispute messages
        $disputeMessages = $dispute->messages()
            ->when(!$user->isAdmin(), function ($query) {
                return $query->public();
            })
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get order messages (if they exist) - ONLY from the specific order involved in this dispute
        $orderMessages = collect();
        if ($dispute->order && method_exists($dispute->order, 'messages')) {
            // Double-check that we're only getting messages from this specific order
            $orderMessages = $dispute->order->messages()
                ->where('order_id', $dispute->order->id) // Explicitly filter by order ID
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->filter(function ($message) use ($dispute) {
                    // Additional validation to ensure message belongs to this order
                    return $message->order_id === $dispute->order->id;
                })
                ->map(function ($message) use ($dispute) {
                    // Ensure type is set for existing messages
                    if (empty($message->type)) {
                        $message->type = $message->getTypeAttribute(null);
                    }
                    
                    // Add dispute context to ensure this message belongs to the right order
                    $message->dispute_order_id = $dispute->order->id;
                    
                    // Debug logging (only in debug mode)
                    if (config('app.debug')) { \Log::debug('Order message processed', [
                        'message_id' => $message->id,
                        'order_id' => $message->order_id,
                        'dispute_order_id' => $dispute->order->id,
                        'type' => $message->type,
                        'has_type' => isset($message->type),
                        'has_attachments' => isset($message->attachments),
                        'attachments_count' => is_array($message->attachments) ? count($message->attachments) : 0
                    ]); }
                    
                    return $message;
                });
        }

        // Combine and sort all messages chronologically
        $allMessages = $disputeMessages->concat($orderMessages)
            ->sortBy('created_at')
            ->values()
            ->filter(function ($message) use ($dispute) {
                // Additional validation to ensure messages belong to this dispute's context
                if (isset($message->dispute_id)) {
                    return $message->dispute_id === $dispute->id;
                }
                if (isset($message->order_id)) {
                    return $message->order_id === $dispute->order->id;
                }
                return false; // Skip messages without proper context
            });

        // Add message source indicator and debug user data
        $allMessages = $allMessages->map(function ($message) use ($dispute) {
            $message->is_dispute_message = $message->dispute_id === $dispute->id;
            $message->is_order_message = !$message->is_dispute_message;
            
            // Debug user data (only in debug mode)
            if (config('app.debug')) { \Log::debug('Message user data', [
                'message_id' => $message->id,
                'user_id' => $message->user_id,
                'has_user' => isset($message->user),
                'user_name' => $message->user->name ?? 'NO_USER_NAME',
                'user_photo' => $message->user->profile_photo_url ?? 'NO_USER_PHOTO',
                'is_dispute_message' => $message->is_dispute_message,
                'is_order_message' => $message->is_order_message
            ]); }
            
            return $message;
        });

        // Get order details for context
        $order = $dispute->order;
        $orderItems = $order ? $order->items()->with('product')->get() : collect();

        // Debug logging for message counts
        \Log::info('Dispute messages loaded', [
            'dispute_id' => $dispute->id,
            'order_id' => $order ? $order->id : 'N/A',
            'dispute_messages_count' => $disputeMessages->count(),
            'order_messages_count' => $orderMessages->count(),
            'total_messages_count' => $allMessages->count(),
            'dispute_message_ids' => $disputeMessages->pluck('id')->toArray(),
            'order_message_ids' => $orderMessages->pluck('id')->toArray()
        ]);

        return view('disputes.show', compact(
            'dispute', 
            'allMessages', 
            'disputeMessages', 
            'orderMessages', 
            'order', 
            'orderItems'
        ));
    }

    /**
     * Add a message to the dispute
     */
    public function addMessage(Request $request, Dispute $dispute)
    {
        $user = Auth::user();

        // Check if user is authorized to add messages (either buyer or specifically involved as seller)
        $isAuthorized = $dispute->buyer_id === $user->id || // User is the buyer
                       $dispute->seller_id === $user->id;   // User is specifically the seller in this dispute

        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to dispute.');
        }

        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'
        ]);

        // Determine message type
        $messageType = $dispute->buyer_id === $user->id 
            ? DisputeMessage::TYPE_BUYER_MESSAGE 
            : DisputeMessage::TYPE_SELLER_MESSAGE;

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('disputes/attachments', 'public');
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }
        }

        $messageModel = DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'attachments' => $attachments,
            'type' => $messageType,
            'is_internal' => false
        ]);

        try {
            $dispute->loadMissing(['buyer', 'seller']);
            $recipient = $dispute->buyer_id === $user->id ? $dispute->seller : $dispute->buyer;

            if ($recipient) {
                Activity::create([
                    'user_id' => $recipient->id,
                    'is_read' => false,
                    'description' => $user->name . ' responded to dispute #' . $dispute->id . '.',
                    'type' => Activity::TYPE_DISPUTE,
                    'related_id' => $dispute->id,
                    'related_type' => 'dispute'
                ]);
            }
        } catch (\Throwable $activityException) {
            \Log::error('Failed to create dispute activity for response event', [
                'dispute_id' => $dispute->id,
                'responder_id' => $user->id,
                'error' => $activityException->getMessage(),
            ]);
        }

        // Notify dispute initiator when the other party responds
        if ($dispute->created_by && $dispute->created_by !== $user->id) {
            $dispute->loadMissing(['createdBy']);
            $initiator = $dispute->createdBy;

            if ($initiator && $initiator->email) {
                try {
                    $dispute->loadMissing(['order.shop', 'buyer', 'seller']);
                    Mail::to($initiator->email)->send(new DisputeResponseMail($dispute, $messageModel, $user));

                    \Log::info('Dispute response email sent to initiator', [
                        'dispute_id' => $dispute->id,
                        'initiator_id' => $initiator->id,
                        'responder_id' => $user->id,
                    ]);
                } catch (\Throwable $mailException) {
                    \Log::error('Failed to send dispute response email', [
                        'dispute_id' => $dispute->id,
                        'initiator_id' => $initiator->id ?? null,
                        'responder_id' => $user->id,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            }
        }

        // Update dispute status to under review if it was pending
        if ($dispute->isPending()) {
            $dispute->update(['status' => Dispute::STATUS_UNDER_REVIEW]);
        }

        return back()->with('success', 'Message added successfully.');
    }

    /**
     * Show appeal form for resolved disputes
     */
    public function showAppealForm(Dispute $dispute)
    {
        if (!config('disputes.enable_appeals')) {
            abort(404);
        }
        $user = Auth::user();

        // Check if user is authorized to appeal (either buyer or specifically involved as seller)
        $isAuthorized = $dispute->buyer_id === $user->id || // User is the buyer
                       $dispute->seller_id === $user->id;   // User is specifically the seller in this dispute

        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to dispute.');
        }

        if (!$dispute->canBeAppealed()) {
            return back()->withErrors(['error' => 'This dispute cannot be appealed.']);
        }

        return view('disputes.appeal', compact('dispute'));
    }

    /**
     * Submit an appeal
     */
    public function submitAppeal(Request $request, Dispute $dispute)
    {
        if (!config('disputes.enable_appeals')) {
            abort(404);
        }
        \Log::info('=== APPEAL SUBMISSION STARTED ===', [
            'dispute_id' => $dispute->id,
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'files_count' => $request->hasFile('new_evidence') ? count($request->file('new_evidence')) : 0
        ]);

        try {
            $user = Auth::user();
            \Log::info('User authenticated', ['user_id' => $user->id, 'user_name' => $user->name]);

            // Step 0: Security & Fraud Prevention
            \Log::info('Starting security checks...');
            $securityCheck = $this->performSecurityChecks($user, $dispute);
            \Log::info('Security check result', $securityCheck);
            
            if (!$securityCheck['allowed']) {
                \Log::warning('Security check failed', ['message' => $securityCheck['message']]);
                return back()->withErrors(['error' => $securityCheck['message']]);
            }

            // Check if user is authorized to appeal
            \Log::info('Checking authorization...', [
                'user_id' => $user->id,
                'dispute_buyer_id' => $dispute->buyer_id,
                'dispute_seller_id' => $dispute->seller_id
            ]);
            
            $isAuthorized = $dispute->buyer_id === $user->id || $dispute->seller_id === $user->id;
            if (!$isAuthorized) {
                \Log::error('Authorization failed', [
                    'user_id' => $user->id,
                    'dispute_buyer_id' => $dispute->buyer_id,
                    'dispute_seller_id' => $dispute->seller_id
                ]);
                abort(403, 'Unauthorized access to dispute.');
            }
            \Log::info('Authorization passed');

            // Step 1: Appeal Eligibility Validation
            \Log::info('Starting eligibility validation...');
            $eligibilityCheck = $this->validateAppealEligibility($dispute, $user);
            \Log::info('Eligibility check result', $eligibilityCheck);
            
            if (!$eligibilityCheck['eligible']) {
                \Log::warning('Eligibility check failed', ['message' => $eligibilityCheck['message']]);
                return back()->withErrors(['error' => $eligibilityCheck['message']]);
            }

            // Step 2: Data Validation
            \Log::info('Starting data validation...');
            $data = $request->validate([
                'reason' => 'required|string|max:1000',
                'reason_category' => 'required|in:new_evidence,procedural_error,decision_error,review_concerns,seller_unresponsive,urgent_review,other',
                'new_evidence.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
                'evidence_descriptions.*' => 'nullable|string|max:200'
            ]);
            \Log::info('Data validation passed', ['validated_data' => $data]);

            // Step 3: Process evidence files
            \Log::info('Processing evidence files...');
            \Log::info('Request has files: ' . ($request->hasFile('new_evidence') ? 'YES' : 'NO'));
            \Log::info('Files count: ' . count($request->allFiles()));
            
            $newEvidence = [];
            if ($request->hasFile('new_evidence')) {
                $files = $request->file('new_evidence');
                \Log::info('Files array details', [
                    'files_type' => gettype($files),
                    'files_count' => is_array($files) ? count($files) : 'not array',
                    'files_content' => $files
                ]);
                
                foreach ($files as $index => $file) {
                    \Log::info('Processing file', [
                        'index' => $index,
                        'file_type' => gettype($file),
                        'file_class' => get_class($file),
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'is_valid' => $file->isValid()
                    ]);
                    
                    if ($file->isValid()) {
                        $path = $file->store('disputes/appeals', 'public');
                        $newEvidence[] = [
                            'filename' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ];
                        \Log::info('File stored successfully', ['path' => $path]);
                    } else {
                        \Log::warning('File is not valid', [
                            'index' => $index,
                            'filename' => $file->getClientOriginalName(),
                            'error' => $file->getError()
                        ]);
                    }
                }
            } else {
                \Log::info('No evidence files uploaded');
            }
            \Log::info('Evidence processing completed', ['evidence_count' => count($newEvidence)]);

            // Step 4: Database Transaction
            \Log::info('Starting database transaction...');
            DB::transaction(function () use ($dispute, $data, $newEvidence, $user) {
                \Log::info('Inside transaction - creating appeal...');
                
                // Create appeal
                $appeal = Appeal::create([
                    'dispute_id' => $dispute->id,
                    'appealed_by' => $user->id,
                    'reason' => $data['reason'],
                    'reason_category' => $data['reason_category'],
                    'new_evidence' => $newEvidence,
                    'status' => Appeal::STATUS_PENDING
                ]);
                \Log::info('Appeal created successfully', ['appeal_id' => $appeal->id]);

                // Create evidence requests
                \Log::info('Creating evidence requests...');
                $this->createEvidenceRequestsForBothParties($dispute, $appeal, $user);
                \Log::info('Evidence requests created');

                // Mark dispute as appealed
                \Log::info('Marking dispute as appealed...');
                $dispute->markAsAppealed();
                \Log::info('Dispute marked as appealed');

                // Create system message
                \Log::info('Creating system message...');
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'user_id' => 1, // System user ID
                    'message' => 'Appeal submitted. Under review.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);
                \Log::info('System message created');
            });
            \Log::info('Database transaction completed successfully');

            \Log::info('=== APPEAL SUBMISSION COMPLETED SUCCESSFULLY ===');
            return redirect()->route('disputes.show', $dispute->id)
                ->with('success', 'Appeal submitted successfully. It will be reviewed within 48 hours.');

        } catch (\Exception $e) {
            \Log::error('=== APPEAL SUBMISSION FAILED ===', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'dispute_id' => $dispute->id,
                'user_id' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'An error occurred while submitting the appeal: ' . $e->getMessage()]);
        }
    }

    /**
     * Validate appeal eligibility with comprehensive business rules
     */
    private function validateAppealEligibility(Dispute $dispute, $user): array
    {
        // Check 1: Dispute status validation - Allow appeals at appropriate stages
        $allowedStatuses = [
            Dispute::STATUS_RESOLVED,           // After admin decision
            Dispute::STATUS_UNDER_REVIEW,       // During admin review (if user disagrees with process)
            Dispute::STATUS_PENDING             // If seller doesn't respond within timeframe
        ];
        
        if (!in_array($dispute->status, $allowedStatuses)) {
            return [
                'eligible' => false,
                'message' => 'Appeals can only be submitted for disputes that are pending, under review, or resolved. Current status: ' . ucfirst($dispute->status)
            ];
        }

        // Check 2: Previous appeal check - Ensure only one appeal per dispute
        if ($dispute->appeal()->exists()) {
            return [
                'eligible' => false,
                'message' => 'An appeal has already been submitted for this dispute and is currently under review.'
            ];
        }

        // Check 3: Appeal deadline enforcement - Check if appeal is within time limit
        // Different appeal timeframes based on dispute status
        if ($dispute->status === Dispute::STATUS_RESOLVED) {
            // For resolved disputes, check 7-day appeal deadline
            if ($dispute->isAppealDeadlineExpired()) {
                return [
                    'eligible' => false,
                    'message' => 'Appeal deadline has expired. The appeal period was ' . $dispute->getAppealDeadlineDaysLeft() . ' days from resolution.'
                ];
            }
        } elseif ($dispute->status === Dispute::STATUS_UNDER_REVIEW) {
            // For disputes under review, allow appeals after 5 minutes
            $reviewStartTime = $dispute->updated_at ?? $dispute->created_at;
            $maxReviewTime = 5; // 5 minutes for admin review
            
            if ($reviewStartTime->diffInMinutes(now()) < $maxReviewTime) {
                return [
                    'eligible' => false,
                    'message' => 'Appeals during admin review can only be submitted after ' . $maxReviewTime . ' minutes of review time.'
                ];
            }
        } elseif ($dispute->status === Dispute::STATUS_PENDING) {
            // For pending disputes, allow immediate appeals
            // No time restriction - users can appeal immediately if needed
        }

        // Check 4: User appeal eligibility - Ensure user hasn't appealed before
        if ($dispute->appeal()->where('appealed_by', $user->id)->exists()) {
            return [
                'eligible' => false,
                'message' => 'You have already submitted an appeal for this dispute.'
            ];
        }

        // Check 5: Dispute finality - Ensure dispute is not already final
        if ($dispute->isFinal()) {
            return [
                'eligible' => false,
                'message' => 'This dispute has reached a final decision and cannot be appealed.'
            ];
        }

        // Check 6: Appeal capability flag
        if (!$dispute->can_appeal) {
            return [
                'eligible' => false,
                'message' => 'This dispute is not eligible for appeals.'
            ];
        }

        return [
            'eligible' => true,
            'message' => 'Appeal eligibility validated successfully.'
        ];
    }

    /**
     * Validate evidence requirements for appeals
     */
    private function validateEvidenceRequirements(Request $request): array
    {
        // Check if any evidence files were uploaded
        if (!$request->hasFile('new_evidence') || empty($request->file('new_evidence'))) {
            return [
                'valid' => false,
                'message' => 'At least one piece of evidence is required to submit an appeal. Please upload supporting documents, screenshots, or other relevant files.'
            ];
        }

        $files = $request->file('new_evidence');
        $totalSize = 0;
        $maxTotalSize = 50 * 1024 * 1024; // 50MB total limit
        $validFileTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $invalidFiles = [];

        foreach ($files as $file) {
            // Check file size
            $totalSize += $file->getSize();
            
            // Check file type
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $validFileTypes)) {
                $invalidFiles[] = $file->getClientOriginalName();
            }
        }

        // Validate total size
        if ($totalSize > $maxTotalSize) {
            return [
                'valid' => false,
                'message' => 'Total evidence size (' . number_format($totalSize / (1024 * 1024), 2) . 'MB) exceeds the maximum limit of 50MB.'
            ];
        }

        // Validate file types
        if (!empty($invalidFiles)) {
            return [
                'valid' => false,
                'message' => 'Invalid file type(s): ' . implode(', ', $invalidFiles) . '. Supported types: JPG, PNG, PDF, DOC, DOCX'
            ];
        }

        // Validate minimum evidence count (at least 1 file)
        if (count($files) < 1) {
            return [
                'valid' => false,
                'message' => 'At least one evidence file is required to submit an appeal.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Evidence requirements validated successfully.'
        ];
    }

    /**
     * Process and categorize evidence with enhanced management
     */
    private function processAndCategorizeEvidence(Request $request): array
    {
        $files = $request->file('new_evidence');
        $descriptions = $request->input('evidence_descriptions', []);
        $categorizedEvidence = [];
        
        foreach ($files as $index => $file) {
            $category = $this->categorizeEvidenceFile($file);
            $verification = $this->verifyEvidenceFile($file);
            
            $categorizedEvidence[] = [
                'filename' => $file->getClientOriginalName(),
                'path' => $file->store('disputes/appeals', 'public'),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $descriptions[$index] ?? '',
                'category' => $category,
                'verification_status' => $verification['status'],
                'verification_notes' => $verification['notes'],
                'uploaded_at' => now()->toDateTimeString(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'metadata' => [
                    'original_extension' => $file->getClientOriginalExtension(),
                    'real_path' => $file->getRealPath(),
                    'upload_time' => now()->toISOString()
                ]
            ];
        }
        
        return $categorizedEvidence;
    }

    /**
     * Categorize evidence files based on type and content
     */
    private function categorizeEvidenceFile($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $filename = strtolower($file->getClientOriginalName());
        
        // Payment-related evidence
        if (str_contains($filename, 'payment') || str_contains($filename, 'bank') || 
            str_contains($filename, 'receipt') || str_contains($filename, 'transaction')) {
            return 'payment_proof';
        }
        
        // Communication evidence
        if (str_contains($filename, 'chat') || str_contains($filename, 'message') || 
            str_contains($filename, 'email') || str_contains($filename, 'conversation')) {
            return 'communication_logs';
        }
        
        // Shipping evidence
        if (str_contains($filename, 'shipping') || str_contains($filename, 'tracking') || 
            str_contains($filename, 'delivery') || str_contains($filename, 'package')) {
            return 'shipping_proof';
        }
        
        // Product quality evidence
        if (str_contains($filename, 'product') || str_contains($filename, 'quality') || 
            str_contains($filename, 'damage') || str_contains($filename, 'defect')) {
            return 'product_quality';
        }
        
        // Document evidence
        if (in_array($extension, ['pdf', 'doc', 'docx'])) {
            return 'documents';
        }
        
        // Image evidence
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return 'images';
        }
        
        return 'other';
    }

    /**
     * Verify evidence file for potential manipulation
     */
    private function verifyEvidenceFile($file): array
    {
        $issues = [];
        $status = 'verified';
        
        // Check file size anomalies
        if ($file->getSize() < 100) { // Suspiciously small
            $issues[] = 'File size is unusually small';
            $status = 'suspicious';
        }
        
        // Check for common manipulation indicators
        $content = file_get_contents($file->getRealPath());
        
        // Check for metadata inconsistencies
        if (function_exists('exif_read_data') && in_array($file->getMimeType(), ['image/jpeg', 'image/tiff'])) {
            $exif = @exif_read_data($file->getRealPath());
            if ($exif && isset($exif['DateTime'])) {
                // Check if creation date is suspicious
                $fileDate = \Carbon\Carbon::parse($exif['DateTime']);
                if ($fileDate->isFuture() || $fileDate->diffInDays(now()) > 365) {
                    $issues[] = 'File creation date appears suspicious';
                    $status = 'suspicious';
                }
            }
        }
        
        // Check for common manipulation patterns
        if (str_contains($content, 'photoshop') || str_contains($content, 'edited')) {
            $issues[] = 'File may have been edited';
            $status = 'suspicious';
        }
        
        return [
            'status' => $status,
            'notes' => empty($issues) ? 'File appears genuine' : implode(', ', $issues)
        ];
    }



    /**
     * Initiate mutual resolution
     */
    public function initiateMutualResolution(Request $request, Dispute $dispute)
    {
        if (!config('disputes.enable_mutual_resolution')) {
            abort(404);
        }
        $user = Auth::user();

        // Check if user is authorized
        $isAuthorized = $dispute->buyer_id === $user->id || $dispute->seller_id === $user->id;
        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to dispute.');
        }

        if (!$dispute->canBeMutuallyResolved()) {
            return back()->withErrors(['error' => 'This dispute cannot be resolved mutually at this stage.']);
        }

        $data = $request->validate([
            'terms' => 'required|string|max:1000'
        ]);

        DB::transaction(function () use ($dispute, $data, $user) {
            // Initiate mutual resolution
            $dispute->initiateMutualResolution($data['terms'], $user->id);

            // Create system message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => "Mutual resolution proposed: {$data['terms']}",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        return back()->with('success', 'Mutual resolution proposed. Waiting for the other party to agree.');
    }

    /**
     * Agree to mutual resolution
     */
    public function agreeToMutualResolution(Request $request, Dispute $dispute)
    {
        if (!config('disputes.enable_mutual_resolution')) {
            abort(404);
        }
        $user = Auth::user();

        // Check if user is authorized
        $isAuthorized = $dispute->buyer_id === $user->id || $dispute->seller_id === $user->id;
        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to dispute.');
        }

        if (!$dispute->mutual_resolution_terms) {
            return back()->withErrors(['error' => 'No mutual resolution terms have been proposed.']);
        }

        DB::transaction(function () use ($dispute, $user) {
            // Agree to mutual resolution
            $isResolved = $dispute->agreeToMutualResolution($user->id);

            if ($isResolved) {
                // Create system message for successful resolution
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'user_id' => 1, // System user ID
                    'message' => 'Both parties have agreed to the mutual resolution. Dispute closed.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);
            } else {
                // Create message for partial agreement
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'user_id' => 1, // System user ID
                    'message' => 'One party has agreed to the mutual resolution. Waiting for the other party.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);
            }
        });

        if ($dispute->isMutuallyResolved()) {
            return back()->with('success', 'Dispute has been mutually resolved and closed.');
        } else {
            return back()->with('success', 'You have agreed to the mutual resolution. Waiting for the other party.');
        }
    }

    /**
     * Seller accepts and issues a refund (partial or full) to the buyer's wallet.
     */
    public function refund(Request $request, Dispute $dispute)
    {
        $user = Auth::user();

        // Only seller can issue refunds on this dispute
        $isSeller = ($dispute->seller_id === $user->id)
            || (optional($dispute->order?->shop)->user_id === $user->id);
        if (!$isSeller) {
            abort(403, 'Only the seller can issue a refund for this dispute.');
        }

        // Cannot refund if already resolved/closed
        if ($dispute->isResolved() || $dispute->isClosed()) {
            return back()->withErrors(['error' => 'This dispute has already been resolved or closed.']);
        }

        $data = $request->validate([
            'refund_percent' => 'required|numeric|min:1|max:100',
        ]);

        $order = $dispute->order()->with('shop')->first();
        if (!$order) {
            return back()->withErrors(['error' => 'Order not found for this dispute.']);
        }

        $percent = (float) $data['refund_percent'];
        $baseTotal = (float) ($order->total_amount ?? 0);
        $amount = round($baseTotal * ($percent / 100), 2);
        if ($amount <= 0) {
            return back()->withErrors(['error' => 'Refund amount must be greater than zero.']);
        }

        DB::transaction(function () use ($dispute, $order, $amount, $percent) {
            // Credit buyer wallet
            Wallet::create([
                'user_id'    => $dispute->buyer_id,
                'credit'     => $amount,
                'debit'      => 0,
                'balance'    => 0,
                'reference'  => 'dispute_refund_'.$dispute->id,
                'description'=> 'Dispute refund for Order #'.$order->id,
                'meta'       => ['order_id' => $order->id, 'dispute_id' => $dispute->id, 'percent' => $percent],
            ]);

            // Debit seller wallet
            $sellerId = $dispute->seller_id ?: optional($order->shop)->user_id;
            if ($sellerId) {
                // If seller funds are still on hold for this order, reflect the refund as an on-hold debit
                $hasOnHold = \App\Models\Wallet::where('user_id', $sellerId)
                    ->where('status', 'on_hold')
                    ->where('meta->order_id', $order->id)
                    ->exists();

                Wallet::create([
                    'user_id'    => $sellerId,
                    'credit'     => 0,
                    'debit'      => $amount,
                    'balance'    => 0,
                    'reference'  => 'dispute_refund_'.$dispute->id,
                    'description'=> 'Dispute refund for Order #'.$order->id,
                    'status'     => $hasOnHold ? 'on_hold' : 'completed',
                    'meta'       => ['order_id' => $order->id, 'dispute_id' => $dispute->id, 'percent' => $percent],
                ]);
            }

            // Update dispute as resolved with refund
            $decision = $percent >= 100 ? Dispute::DECISION_BUYER_WINS : Dispute::DECISION_PARTIAL_REFUND;
            $resolution = 'Seller issued a '.rtrim(rtrim(number_format($percent, 2), '0'), '.')."% refund (".get_currency().' '.number_format($amount, 2).") to buyer.";
            $dispute->markAsResolved($resolution, $decision, $amount, $sellerId ?? null);

            // If full refund, mark order as refunded and restock inventory
            if ($percent >= 100) {
                if ($order->status !== \App\Models\Order::STATUS_REFUNDED) {
                    $order->update(['status' => \App\Models\Order::STATUS_REFUNDED]);
                }
                try {
                    $order->loadMissing('items.product');
                    foreach ($order->items as $item) {
                        $product = $item->product; if (!$product) continue;
                        if (strtolower((string)($product->type ?? 'physical')) !== 'physical') continue;
                        $qty = max(1, (int) ($item->quantity ?? 1));
                        if (!is_null($product->stock)) {
                            $product->update(['stock' => ((int)$product->stock) + $qty]);
                        }
                        $variantId = (int) ($item->getAttribute('product_variation_id') ?? 0);
                        if ($variantId > 0) {
                            try { $variant = \App\Models\Variant::find($variantId); if ($variant && !is_null($variant->stock)) { $variant->update(['stock' => ((int)$variant->stock) + $qty]); } } catch (\Throwable $e) { /* ignore */ }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('dispute.refund.restock_failed', ['dispute_id' => $dispute->id, 'order_id' => $order->id, 'error' => $e->getMessage()]);
                }
            }

            // System message log
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id'    => null,
                'message'    => $resolution,
                'type'       => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal'=> false,
            ]);
        });

        return back()->with('success', 'Refund processed and dispute marked as resolved.');
    }

    /**
     * Create automatic evidence request messages for both parties
     */
    private function createEvidenceRequestsForBothParties(Dispute $dispute, Appeal $appeal, $appealingUser): void
    {
        \Log::info('Creating evidence requests for both parties', [
            'dispute_id' => $dispute->id,
            'appeal_id' => $appeal->id,
            'appealing_user_id' => $appealingUser->id
        ]);

        try {
            // Get both parties
            $buyer = $dispute->buyer;
            $seller = $dispute->seller;
            
            \Log::info('Parties identified', [
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->name,
                'seller_id' => $seller->id,
                'seller_name' => $seller->name
            ]);
            
            // Determine who appealed
            $isBuyerAppealing = $appealingUser->id === $buyer->id;
            $appealingPartyName = $isBuyerAppealing ? $buyer->name : ($dispute->order->shop->name ?? $seller->name);
            
            \Log::info('Appeal direction determined', [
                'is_buyer_appealing' => $isBuyerAppealing,
                'appealing_party_name' => $appealingPartyName
            ]);
            
            // Create evidence request for the party who DIDN'T appeal
            $nonAppealingParty = $isBuyerAppealing ? $seller : $buyer;
            $nonAppealingPartyName = $isBuyerAppealing ? ($dispute->order->shop->name ?? $seller->name) : $buyer->name;
            
            \Log::info('Creating evidence request for non-appealing party', [
                'party_id' => $nonAppealingParty->id,
                'party_name' => $nonAppealingPartyName
            ]);
            
            // Create evidence request for the non-appealing party
            $this->createEvidenceRequest($dispute, $appeal, $nonAppealingParty, $appealingPartyName, false);
            
            \Log::info('Creating evidence request for appealing party');
            
            // Create evidence request for the appealing party (additional evidence)
            $this->createEvidenceRequest($dispute, $appeal, $appealingUser, $nonAppealingPartyName, true);
            
            // Create system message in dispute
            \Log::info('Creating system message about evidence requests');
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => null, // System message
                'message' => "Evidence request messages sent to both parties. Appeal #{$appeal->id} is now under review.",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
            
            \Log::info('Evidence requests created successfully for both parties');
            
        } catch (\Exception $e) {
            \Log::error('Failed to create evidence requests', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e; // Re-throw to be caught by the main try-catch
        }
    }

    /**
     * Create evidence request for a specific party
     */
    private function createEvidenceRequest(Dispute $dispute, Appeal $appeal, $recipient, string $otherPartyName, bool $isAppealingParty): void
    {
        \Log::info('Creating evidence request', [
            'dispute_id' => $dispute->id,
            'appeal_id' => $appeal->id,
            'recipient_id' => $recipient->id,
            'recipient_name' => $recipient->name,
            'is_appealing_party' => $isAppealingParty
        ]);

        try {
            $disputeType = $dispute->getTypeLabel();
            $appealReason = $appeal->reason_category;
            $deadline = now()->addHours(24); // 24-hour deadline for evidence submission
            
            \Log::info('Evidence request details', [
                'dispute_type' => $disputeType,
                'appeal_reason' => $appealReason,
                'deadline' => $deadline->toDateTimeString()
            ]);
            
            if ($isAppealingParty) {
                // Evidence request for the party who submitted the appeal
                $message = "**Evidence Request for Appeal #{$appeal->id}**\n\n" .
                          "You have submitted an appeal for dispute #{$dispute->id} ({$disputeType}).\n\n" .
                          "**Appeal Reason:** {$appeal->getReasonCategoryLabel()}\n" .
                          "**Your Explanation:** {$appeal->reason}\n\n" .
                          "**Additional Evidence Request:**\n" .
                          "If you have any additional evidence to support your appeal, please submit it within 24 hours.\n\n" .
                          "**What to Submit:**\n" .
                          "• Additional documentation\n" .
                          "• Updated information\n" .
                          "• Any new evidence discovered\n\n" .
                          "**Deadline:** {$deadline->format('M d, Y \a\t g:i A')}\n\n" .
                          "**Note:** Both parties have been notified and requested to provide evidence.";
                
                $requiredEvidenceTypes = ['additional_documentation', 'updated_information', 'new_evidence'];
            } else {
                // Evidence request for the party who didn't appeal
                $message = "**URGENT: Evidence Request for Appeal #{$appeal->id}**\n\n" .
                          "**{$otherPartyName}** has submitted an appeal for dispute #{$dispute->id} ({$disputeType}).\n\n" .
                          "**Appeal Details:**\n" .
                          "• **Reason:** {$appeal->getReasonCategoryLabel()}\n" .
                          "• **Explanation:** {$appeal->reason}\n" .
                          "• **Evidence Submitted:** " . count($appeal->new_evidence) . " file(s)\n\n" .
                          "**ACTION REQUIRED:**\n" .
                          "You have **24 hours** to provide evidence to support your position.\n\n" .
                          "**What to Submit:**\n" .
                          "• Payment proof (if applicable)\n" .
                          "• Communication logs\n" .
                          "• Shipping documentation\n" .
                          "• Any other relevant evidence\n\n" .
                          "**Deadline:** {$deadline->format('M d, Y \a\t g:i A')}\n\n" .
                          "**Important:** Failure to provide evidence may result in the appeal being decided in favor of the appealing party.";
                
                $requiredEvidenceTypes = ['payment_proof', 'communication_logs', 'shipping_documentation', 'relevant_evidence'];
            }
            
            \Log::info('Evidence request message created', [
                'message_length' => strlen($message),
                'required_evidence_types' => $requiredEvidenceTypes
            ]);
            
            // Create the evidence request record
            $evidenceRequest = EvidenceRequest::create([
                'appeal_id' => $appeal->id,
                'dispute_id' => $dispute->id,
                'requested_from' => $recipient->id,
                'requested_by' => 1, // System user ID
                'message' => $message,
                'status' => EvidenceRequest::STATUS_PENDING,
                'deadline' => $deadline,
                'required_evidence_types' => $requiredEvidenceTypes
            ]);
            
            \Log::info('Evidence request created successfully', [
                'evidence_request_id' => $evidenceRequest->id,
                'recipient_id' => $recipient->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to create evidence request', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'dispute_id' => $dispute->id,
                'appeal_id' => $appeal->id,
                'recipient_id' => $recipient->id
            ]);
            throw $e; // Re-throw to be caught by the main try-catch
        }
    }

    /**
     * Mark dispute as closed by the initiator or admin
     */
    public function markAsClosed(Request $request, Dispute $dispute)
    {
        // Check if the authenticated user is the dispute creator or an admin
        if (Auth::id() !== $dispute->created_by && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Only the dispute creator or admin can mark this dispute as closed.');
        }

        // Check if dispute can be closed
        if ($dispute->status === 'closed' || $dispute->status === 'resolved') {
            return back()->with('error', 'This dispute cannot be closed as it is already ' . $dispute->status . '.');
        }

        try {
            // Get closure notes if provided
            $closureNotes = $request->input('closure_notes');
            
            // Update dispute status
            $dispute->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => Auth::id()
            ]);

            // Create a system message to record the closure
            if (Auth::user()->isAdmin()) {
                $closedBy = 'admin';
            } else {
                $closedBy = 'creator';
            }
            $message = "Dispute marked as closed by the {$closedBy}.";
            if ($closureNotes) {
                $message .= "\n\nClosure Notes: " . $closureNotes;
            }
            
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => null, // System message
                'message' => $message,
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE
            ]);

            // Notify both parties that the dispute has been closed
            try {
                $dispute->loadMissing(['buyer', 'seller', 'closedBy', 'order']);
                $closerName = Auth::user()->name ?? ($dispute->closedBy->name ?? 'support');

                foreach ([$dispute->buyer, $dispute->seller] as $participant) {
                    if ($participant) {
                        Activity::create([
                            'user_id' => $participant->id,
                            'is_read' => false,
                            'description' => 'Dispute #' . $dispute->id . ' has been closed by ' . $closerName . '.',
                            'type' => Activity::TYPE_DISPUTE,
                            'related_id' => $dispute->id,
                            'related_type' => 'dispute'
                        ]);
                    }
                }
            } catch (\Throwable $activityException) {
                \Log::error('Failed to create dispute activity for closure event', [
                    'dispute_id' => $dispute->id,
                    'error' => $activityException->getMessage(),
                ]);
            }

            try {
                $dispute->loadMissing(['buyer', 'seller', 'closedBy', 'order']);
                $closedByUser = $dispute->closedBy;

                $recipients = [];
                if ($dispute->buyer && $dispute->buyer->email) {
                    $recipients[$dispute->buyer->id] = $dispute->buyer;
                }
                if ($dispute->seller && $dispute->seller->email) {
                    $recipients[$dispute->seller->id] = $dispute->seller;
                }

                foreach ($recipients as $recipient) {
                    Mail::to($recipient->email)->send(new DisputeClosedMail($dispute, $recipient, $closedByUser));
                }

                \Log::info('Dispute closure notifications dispatched', [
                    'dispute_id' => $dispute->id,
                    'recipient_ids' => array_keys($recipients),
                ]);
            } catch (\Throwable $mailException) {
                \Log::error('Failed to send dispute closure notifications', [
                    'dispute_id' => $dispute->id,
                    'error' => $mailException->getMessage(),
                ]);
            }


            if (Auth::user()->isAdmin()) {
                $closedBy = 'admin';
            } else {
                $closedBy = 'you (creator)';
            }
            return back()->with('success', "Dispute has been marked as closed successfully by {$closedBy}.");

        } catch (\Exception $e) {
            \Log::error('Failed to mark dispute as closed', [
                'dispute_id' => $dispute->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to mark dispute as closed. Please try again.');
        }
    }
}
