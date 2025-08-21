<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DisputeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of all disputes
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $type = $request->get('type');
        $priority = $request->get('priority');

        $query = Dispute::with(['order', 'buyer', 'seller', 'appeal'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        // Priority: appealed disputes first, then under review, then pending
        if ($priority === 'high') {
            $query->orderByRaw("
                CASE 
                    WHEN status = 'appealed' THEN 1
                    WHEN status = 'under_review' THEN 2
                    WHEN status = 'pending' THEN 3
                    ELSE 4
                END
            ")->orderBy('created_at', 'desc');
        }

        $disputes = $query->paginate(20);

        $statusCounts = Dispute::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $typeCounts = Dispute::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('admin.disputes.index', compact('disputes', 'statusCounts', 'typeCounts'));
    }

    /**
     * Display the specified dispute
     */
    public function show(Dispute $dispute)
    {
        $dispute->load(['order', 'buyer', 'seller', 'messages.user', 'appeal', 'resolvedBy']);

        $messages = $dispute->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.disputes.show', compact('dispute', 'messages'));
    }

    /**
     * Show the form for resolving a dispute
     */
    public function showResolveForm(Dispute $dispute)
    {
        if (!$dispute->isUnderReview() && !$dispute->isPending()) {
            return back()->withErrors(['error' => 'This dispute cannot be resolved at this stage.']);
        }

        return view('admin.disputes.resolve', compact('dispute'));
    }

    /**
     * Resolve a dispute
     */
    public function resolve(Request $request, Dispute $dispute)
    {
        if (!$dispute->isUnderReview() && !$dispute->isPending()) {
            return back()->withErrors(['error' => 'This dispute cannot be resolved at this stage.']);
        }

        $data = $request->validate([
            'resolution' => 'required|string|max:2000',
            'decision' => ['required', Rule::in([
                Dispute::DECISION_BUYER_WINS,
                Dispute::DECISION_SELLER_WINS,
                Dispute::DECISION_PARTIAL_REFUND,
                Dispute::DECISION_NO_ACTION
            ])],
            'refund_amount' => 'nullable|numeric|min:0|max:999999.99',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($dispute, $data) {
            $dispute->markAsResolved(
                $data['resolution'],
                $data['decision'],
                $data['refund_amount'],
                Auth::id()
            );

            if ($data['admin_notes']) {
                $dispute->update(['admin_notes' => $data['admin_notes']]);
            }

            // Create admin message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => Auth::id(),
                'message' => "Dispute resolved: {$data['resolution']}",
                'type' => DisputeMessage::TYPE_ADMIN_MESSAGE,
                'is_internal' => true
            ]);

            // Create public system message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => "Dispute has been resolved. {$dispute->getDecisionLabel()}",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('success', 'Dispute resolved successfully.');
    }

    /**
     * Add an admin message to the dispute
     */
    public function addMessage(Request $request, Dispute $dispute)
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'is_internal' => 'boolean'
        ]);

        DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'user_id' => Auth::id(),
            'message' => $data['message'],
            'type' => DisputeMessage::TYPE_ADMIN_MESSAGE,
            'is_internal' => $data['is_internal'] ?? false
        ]);

        return back()->with('success', 'Message added successfully.');
    }

    /**
     * Display appeals listing
     */
    public function appeals(Request $request)
    {
        $status = $request->get('status');

        $query = Appeal::with(['dispute', 'appealedBy', 'reviewedBy'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $appeals = $query->paginate(20);

        $statusCounts = Appeal::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.disputes.appeals', compact('appeals', 'statusCounts'));
    }

    /**
     * Show appeal details
     */
    public function showAppeal(Appeal $appeal)
    {
        $appeal->load(['dispute', 'appealedBy', 'reviewedBy']);
        
        return view('admin.disputes.show-appeal', compact('appeal'));
    }

    /**
     * Review an appeal
     */
    public function reviewAppeal(Request $request, Appeal $appeal)
    {
        if (!$appeal->isPending()) {
            return back()->withErrors(['error' => 'This appeal has already been reviewed.']);
        }

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'review_notes' => 'required|string|max:1000'
        ]);

        DB::transaction(function () use ($appeal, $data) {
            if ($data['decision'] === 'approved') {
                $appeal->approve($data['review_notes'], Auth::id());
                
                // Create system message
                DisputeMessage::create([
                    'dispute_id' => $appeal->dispute_id,
                    'user_id' => 1, // System user ID
                    'message' => 'Appeal approved. Dispute under review.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);
            } else {
                $appeal->reject($data['review_notes'], Auth::id());
                
                // Create system message
                DisputeMessage::create([
                    'dispute_id' => $appeal->dispute_id,
                    'user_id' => 1, // System user ID
                    'message' => 'Appeal rejected. Dispute closed.',
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);
            }
        });

        return redirect()->route('admin.disputes.appeals')
            ->with('success', 'Appeal reviewed successfully.');
    }

    /**
     * Finalize an appealed dispute
     */
    public function finalizeDispute(Request $request, Dispute $dispute)
    {
        if (!$dispute->isAppealed()) {
            return back()->withErrors(['error' => 'This dispute cannot be finalized at this stage.']);
        }

        $data = $request->validate([
            'final_resolution' => 'required|string|max:2000',
            'final_decision' => ['required', Rule::in([
                Dispute::DECISION_BUYER_WINS,
                Dispute::DECISION_SELLER_WINS,
                Dispute::DECISION_PARTIAL_REFUND,
                Dispute::DECISION_NO_ACTION
            ])],
            'final_refund_amount' => 'nullable|numeric|min:0|max:999999.99',
            'final_admin_notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($dispute, $data) {
            $dispute->update([
                'status' => Dispute::STATUS_FINAL,
                'resolution' => $data['final_resolution'],
                'decision' => $data['final_decision'],
                'refund_amount' => $data['final_refund_amount'],
                'admin_notes' => $data['final_admin_notes'],
                'can_appeal' => false
            ]);

            // Create admin message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => Auth::id(),
                'message' => "Final resolution: {$data['final_resolution']}",
                'type' => DisputeMessage::TYPE_ADMIN_MESSAGE,
                'is_internal' => true
            ]);

            // Create public system message
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => "Dispute finalized. {$dispute->getDecisionLabel()}",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('success', 'Dispute finalized successfully.');
    }

    /**
     * Get dispute statistics
     */
    public function statistics()
    {
        $stats = [
            'total_disputes' => Dispute::count(),
            'pending_disputes' => Dispute::pending()->count(),
            'under_review_disputes' => Dispute::underReview()->count(),
            'resolved_disputes' => Dispute::resolved()->count(),
            'appealed_disputes' => Dispute::appealed()->count(),
            'final_disputes' => Dispute::final()->count(),
            'total_appeals' => Appeal::count(),
            'pending_appeals' => Appeal::pending()->count(),
            'approved_appeals' => Appeal::approved()->count(),
            'rejected_appeals' => Appeal::rejected()->count(),
        ];

        // Monthly dispute trends
        $monthlyTrends = Dispute::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Dispute types distribution
        $typeDistribution = Dispute::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return view('admin.disputes.statistics', compact('stats', 'monthlyTrends', 'typeDistribution'));
    }
}
