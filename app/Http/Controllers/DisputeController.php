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
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of disputes for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');
        $type = $request->get('type');

        $query = Dispute::with(['order', 'buyer', 'seller', 'appeal'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            });

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(15);

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

        if ($orderId) {
            $order = Order::with(['shop', 'items.product'])
                ->where('user_id', Auth::id())
                ->findOrFail($orderId);
        }

        $disputeTypes = [
            Dispute::TYPE_CUSTOMS_FEES => 'Customs Fees',
            Dispute::TYPE_ITEM_MISREPRESENTATION => 'Item Misrepresentation',
            Dispute::TYPE_SHIPPING_ISSUES => 'Shipping Issues',
            Dispute::TYPE_QUALITY_ISSUES => 'Quality Issues',
            Dispute::TYPE_PAYMENT_ISSUES => 'Payment Issues',
            Dispute::TYPE_OTHER => 'Other'
        ];

        return view('disputes.create', compact('order', 'disputeTypes'));
    }

    /**
     * Store a newly created dispute
     */
    public function store(Request $request)
    {
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

        $order = Order::with('shop')->findOrFail($data['order_id']);

        // Check if user is the buyer of this order
        if ($order->user_id !== Auth::id()) {
            return back()->withErrors(['error' => 'You can only create disputes for your own orders.']);
        }

        // Check if dispute already exists for this order
        if (Dispute::where('order_id', $order->id)->exists()) {
            return back()->withErrors(['error' => 'A dispute already exists for this order.']);
        }

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

        DB::transaction(function () use ($data, $order, $evidence) {
            $dispute = Dispute::create([
                'order_id' => $order->id,
                'buyer_id' => Auth::id(),
                'seller_id' => $order->shop->user_id,
                'type' => $data['type'],
                'status' => Dispute::STATUS_PENDING,
                'description' => $data['description'],
                'evidence' => $evidence
            ]);

            // Create initial system message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => Auth::id(),
                'message' => $data['description'],
                'type' => DisputeMessage::TYPE_BUYER_MESSAGE,
                'is_internal' => false
            ]);

            // Create system message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => 'Dispute created. Awaiting seller response.',
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Dispute created successfully. The seller has been notified.');
    }

    /**
     * Display the specified dispute
     */
    public function show(Dispute $dispute)
    {
        $user = Auth::user();

        // Check if user is authorized to view this dispute
        if ($dispute->buyer_id !== $user->id && $dispute->seller_id !== $user->id) {
            abort(403, 'Unauthorized access to dispute.');
        }

        $dispute->load(['order', 'buyer', 'seller', 'messages.user', 'appeal']);

        // Get messages (public for users, all for admins)
        $messages = $dispute->messages()
            ->when(!$user->isAdmin(), function ($query) {
                return $query->public();
            })
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('disputes.show', compact('dispute', 'messages'));
    }

    /**
     * Add a message to the dispute
     */
    public function addMessage(Request $request, Dispute $dispute)
    {
        $user = Auth::user();

        // Check if user is authorized to add messages
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

        // Check if user is authorized to appeal
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

        // Check if user is authorized to appeal
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
