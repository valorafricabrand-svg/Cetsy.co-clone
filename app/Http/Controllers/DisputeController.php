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

        // Get disputes where user is either buyer or seller
        $query = Dispute::with(['order', 'buyer', 'seller', 'appeal'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)      // User is the buyer
                  ->orWhere('seller_id', $user->id);  // User is the seller
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
                'evidence.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'
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

            // Check if dispute already exists for this order
            if (Dispute::where('order_id', $order->id)->exists()) {
                return back()->withErrors(['error' => 'A dispute already exists for this order.']);
            }

            // Determine if user is buyer or seller
            $isBuyer = $order->user_id === Auth::id();
            $buyerId = $isBuyer ? Auth::id() : $order->user_id;
            $sellerId = $isBuyer ? $order->shop->user_id : Auth::id();

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
            $dispute = DB::transaction(function () use ($data, $order, $evidence, $buyerId, $sellerId) {
                \Log::info('Inside transaction - creating dispute');
                
                $dispute = Dispute::create([
                    'order_id' => $order->id,
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
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

                // Create system message
                DisputeMessage::create([
                    'dispute_id' => $dispute->id,
                    'user_id' => 1, // System user ID
                    'message' => 'Dispute created. Awaiting response.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);

                \Log::info('System message created');

                return $dispute; // Return the dispute from the transaction
            });

            \Log::info('Transaction completed successfully', [
                'dispute_id' => $dispute->id,
                'order_id' => $order->id
            ]);

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
     * Display the specified dispute
     */
    public function show(Dispute $dispute)
    {
        $user = Auth::user();

        // Check if user is authorized to view this dispute (either buyer or seller)
        if ($dispute->buyer_id !== $user->id && $dispute->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to dispute.');
        }

        $dispute->load(['order', 'buyer', 'seller', 'messages.user', 'appeal']);

        // Get dispute messages
        $disputeMessages = $dispute->messages()
            ->when(!$user->isAdmin(), function ($query) {
                return $query->public();
            })
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get order messages (if they exist)
        $orderMessages = collect();
        if ($dispute->order && method_exists($dispute->order, 'messages')) {
            $orderMessages = $dispute->order->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();
        }

        // Combine and sort all messages chronologically
        $allMessages = $disputeMessages->concat($orderMessages)
            ->sortBy('created_at')
            ->values();

        // Add message source indicator
        $allMessages = $allMessages->map(function ($message) use ($dispute) {
            $message->is_dispute_message = $message->dispute_id === $dispute->id;
            $message->is_order_message = !$message->is_dispute_message;
            return $message;
        });

        // Get order details for context
        $order = $dispute->order;
        $orderItems = $order ? $order->items()->with('product')->get() : collect();

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

        // Check if user is authorized to add messages (either buyer or seller)
        if ($dispute->buyer_id !== $user->id && $dispute->seller_id !== $user->id) {
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

        DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'attachments' => $attachments,
            'type' => $messageType,
            'is_internal' => false
        ]);

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
        $user = Auth::user();

        // Check if user is authorized to appeal (either buyer or seller)
        if ($dispute->buyer_id !== $user->id && $dispute->seller_id !== $user->id) {
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
        $user = Auth::user();

        // Check if user is authorized to appeal (either buyer or seller)
        if ($dispute->buyer_id !== $user->id && $dispute->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to dispute.');
        }

        if (!$dispute->canBeAppealed()) {
            return back()->withErrors(['error' => 'This dispute cannot be appealed.']);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:1000',
            'new_evidence.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'
        ]);

        // Handle file uploads
        $newEvidence = [];
        if ($request->hasFile('new_evidence')) {
            foreach ($request->file('new_evidence') as $file) {
                $path = $file->store('disputes/appeals', 'public');
                $newEvidence[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }
        }

        DB::transaction(function () use ($dispute, $data, $newEvidence, $user) {
            // Create appeal
            Appeal::create([
                'dispute_id' => $dispute->id,
                'appealed_by' => $user->id,
                'reason' => $data['reason'],
                'new_evidence' => $newEvidence,
                'status' => Appeal::STATUS_PENDING
            ]);

            // Mark dispute as appealed
            $dispute->markAsAppealed();

            // Create system message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => 'Appeal submitted. Under review.',
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Appeal submitted successfully. It will be reviewed within 48 hours.');
    }
}
