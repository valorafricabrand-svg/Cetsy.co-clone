<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Appeal;
use App\Notifications\EvidenceRequestNotification;
use App\Notifications\AppealResolvedNotification;
use App\Notifications\AppealClosedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminDisputeActionMail;
use Carbon\Carbon;
use App\Models\EvidenceRequest;

class DisputeController extends Controller
{
    /**
     * Display a listing of all disputes
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $type = $request->get('type');
        $priority = $request->get('priority');

        $query = Dispute::with(['order', 'buyer', 'seller', 'appeal'])
            ->orderByDesc('id');

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
            ")->orderBy('id', 'desc');
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
        return redirect()->route('disputes.show', $dispute->id);
    }

    /**
     * Quick resolve & close without a separate view.
     * This replaces the old form flow (admin.disputes.resolve view).
     */
    public function showResolveForm(Dispute $dispute)
    {
        if (!$dispute->isUnderReview() && !$dispute->isPending()) {
            return back()->withErrors(['error' => 'This dispute cannot be resolved at this stage.']);
        }

        DB::transaction(function () use ($dispute) {
            // Record final resolution details then close it
            $dispute->markAsResolved(
                'Closed by admin',
                Dispute::DECISION_NO_ACTION,
                null,
                Auth::id()
            );

            $dispute->update([
                'status'     => Dispute::STATUS_CLOSED,
                'closed_at'  => now(),
                'closed_by'  => Auth::id(),
                'can_appeal' => false,
            ]);

            // Internal admin note
            DisputeMessage::create([
                'dispute_id'   => $dispute->id,
                'user_id'      => Auth::id(),
                'message'      => 'Dispute resolved (no action) and closed by admin.',
                'type'         => DisputeMessage::TYPE_ADMIN_MESSAGE,
                'is_internal'  => true,
            ]);

            // Public system message
            DisputeMessage::create([
                'dispute_id'   => $dispute->id,
                'user_id'      => 1, // System user ID
                'message'      => 'Dispute closed by admin. No action taken.',
                'type'         => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal'  => false,
            ]);
        });

        // Notify both parties via email
        try {
            $dispute->loadMissing(['buyer','seller']);
            $admin = Auth::user();
            foreach (['buyer','seller'] as $role) {
                $user = $dispute->{$role};
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new AdminDisputeActionMail($dispute, $user, $admin, 'closed'));
                }
            }
        } catch (\Throwable $e) { \Log::error('admin.dispute.mail_failed', ['id'=>$dispute->id, 'error'=>$e->getMessage()]); }

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Dispute resolved and closed successfully.');
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
            // First, record the resolution details (for audit trail)
            $dispute->markAsResolved(
                $data['resolution'],
                $data['decision'],
                $data['refund_amount'],
                Auth::id()
            );

            if ($data['admin_notes']) {
                $dispute->update(['admin_notes' => $data['admin_notes']]);
            }

            // Immediately close the dispute as part of admin resolution
            $dispute->update([
                'status'    => \App\Models\Dispute::STATUS_CLOSED,
                'closed_at' => now(),
                'closed_by' => Auth::id(),
                'can_appeal'=> false,
            ]);

            // Admin internal note
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => Auth::id(),
                'message' => "Dispute resolved and closed: {$data['resolution']}",
                'type' => DisputeMessage::TYPE_ADMIN_MESSAGE,
                'is_internal' => true
            ]);

            // Public system messages
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => "Dispute resolved. {$dispute->getDecisionLabel()} — {$dispute->getFavorOutcomeLabel()}",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => 1, // System user ID
                'message' => "Dispute closed by admin.",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        // Notify both parties via email
        try {
            $dispute->loadMissing(['buyer','seller']);
            $admin = Auth::user();
            foreach (['buyer','seller'] as $role) {
                $user = $dispute->{$role};
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new AdminDisputeActionMail($dispute, $user, $admin, 'closed'));
                }
            }
        } catch (\Throwable $e) { \Log::error('admin.dispute.mail_failed', ['id'=>$dispute->id, 'error'=>$e->getMessage()]); }

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Dispute resolved and closed successfully.');
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
        $dispute_type = $request->get('dispute_type');
        $date_range = $request->get('date_range');

        $query = Appeal::with(['dispute', 'appealedBy', 'reviewedBy'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($dispute_type) {
            $query->whereHas('dispute', function($q) use ($dispute_type) {
                $q->where('type', $dispute_type);
            });
        }

        if ($date_range) {
            switch ($date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month);
                    break;
            }
        }

        $appeals = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'total_appeals' => Appeal::count(),
            'pending_appeals' => Appeal::where('status', 'pending')->count(),
            'evidence_requested' => Appeal::where('status', 'evidence_requested')->count(),
            'resolved_today' => Appeal::whereDate('created_at', today())
                ->whereIn('status', ['approved', 'rejected'])->count(),
        ];

        return view('admin.appeals.index', compact('appeals', 'stats'));
    }

    /**
     * Show appeal details
     */
    public function showAppeal(Appeal $appeal)
    {
        $appeal->load(['dispute', 'appealedBy', 'reviewedBy']);
        
        return view('admin.appeals.show', compact('appeal'));
    }

    /**
     * Review and resolve an appeal (Binance-style)
     */
    public function reviewAppeal(Request $request, Appeal $appeal)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'review_notes' => 'required|string|max:2000',
            'dispute_resolution' => 'nullable|string|max:1000',
            'refund_amount' => 'nullable|numeric|min:0|max:999999.99',
            'resolution_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::transaction(function () use ($appeal, $data) {
                // Update appeal status
                $appeal->update([
                    'status' => $data['decision'] === 'approved' ? Appeal::STATUS_APPROVED : Appeal::STATUS_REJECTED,
                    'decision' => $data['decision'],
                    'review_notes' => $data['review_notes'],
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now()
                ]);

                // Update dispute based on appeal decision
                if ($data['decision'] === 'approved') {
                    // Appeal approved - update dispute resolution
                    $disputeData = [
                        'status' => 'appeal_approved',
                        'resolved_at' => now(),
                        'resolution' => $data['dispute_resolution'] ?? 'Appeal approved by Cetsy support team.',
                        'decision' => 'appeal_approved'
                    ];

                    if (isset($data['refund_amount']) && $data['refund_amount'] > 0) {
                        $disputeData['refund_amount'] = $data['refund_amount'];
                    }

                    $appeal->dispute->update($disputeData);

                    // Create system message
                    DisputeMessage::create([
                        'dispute_id' => $appeal->dispute_id,
                        'user_id' => 1, // System user ID
                        'message' => "Appeal APPROVED. {$data['review_notes']}",
                        'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                        'is_internal' => false
                    ]);

                } else {
                    // Appeal rejected - mark dispute as final
                    $appeal->dispute->update([
                        'status' => 'appeal_rejected',
                        'resolved_at' => now(),
                        'resolution' => $data['dispute_resolution'] ?? 'Appeal rejected by Cetsy support team.',
                        'decision' => 'appeal_rejected',
                        'can_appeal' => false
                    ]);

                    // Create system message
                    DisputeMessage::create([
                        'dispute_id' => $appeal->dispute_id,
                        'user_id' => 1, // System user ID
                        'message' => "Appeal REJECTED. {$data['review_notes']}",
                        'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                        'is_internal' => false
                    ]);
                }

                // Send notifications to both parties
                $appeal->dispute->buyer->notify(new AppealResolvedNotification($appeal));
                $appeal->dispute->seller->notify(new AppealResolvedNotification($appeal));
            });

            \Log::info('Appeal reviewed successfully', [
                'appeal_id' => $appeal->id,
                'decision' => $data['decision'],
                'reviewed_by' => auth()->id()
            ]);

            return redirect()->route('admin.appeals.show', $appeal->id)
                ->with('success', "Appeal {$data['decision']} successfully. Dispute has been resolved.");

        } catch (\Exception $e) {
            \Log::error('Error reviewing appeal: ' . $e->getMessage(), [
                'appeal_id' => $appeal->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to review appeal. Please try again.']);
        }
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
                'message' => "Dispute finalized. {$dispute->getDecisionLabel()} — {$dispute->getFavorOutcomeLabel()}",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        // Notify both parties via email (finalized)
        try {
            $dispute->loadMissing(['buyer','seller']);
            $admin = Auth::user();
            foreach (['buyer','seller'] as $role) {
                $user = $dispute->{$role};
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new AdminDisputeActionMail($dispute, $user, $admin, 'finalized'));
                }
            }
        } catch (\Throwable $e) { \Log::error('admin.dispute.mail_failed', ['id'=>$dispute->id, 'error'=>$e->getMessage()]); }

        return redirect()->route('admin.admin-disputes.show', $dispute->id)
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
            'mutually_resolved_disputes' => Dispute::mutuallyResolved()->count(),
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

    /**
     * Request evidence from both parties (Binance-style)
     */
    public function requestEvidence(Request $request, Appeal $appeal)
    {
        // Debug: Log the incoming request
        \Log::info('Evidence request received', [
            'appeal_id' => $appeal->id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'evidence_types' => 'required|array|min:1',
            'evidence_types.*' => 'string|in:screenshots,documents,photos,videos,receipts,communication_logs,bank_statements,tracking_info,other',
            'deadline_days' => 'required|integer|min:1|max:30'
        ]);

        // Ensure proper types and validate
        $data['deadline_days'] = (int) $data['deadline_days'];
        $data['evidence_types'] = array_map('strval', $data['evidence_types']);
        
        // Debug: Log the processed data
        \Log::info('Evidence request data processed', [
            'deadline_days' => $data['deadline_days'],
            'evidence_types' => $data['evidence_types'],
            'message' => $data['message']
        ]);
        
        // Additional validation
        if ($data['deadline_days'] < 1 || $data['deadline_days'] > 30) {
            \Log::warning('Invalid deadline days', ['deadline_days' => $data['deadline_days']]);
            return back()->withErrors(['deadline_days' => 'Deadline must be between 1 and 30 days.']);
        }
        
        if (empty($data['evidence_types'])) {
            \Log::warning('No evidence types selected');
            return back()->withErrors(['evidence_types' => 'At least one evidence type must be selected.']);
        }

        $deadline = Carbon::now()->addDays($data['deadline_days']);

        try {
            DB::transaction(function () use ($appeal, $data, $deadline) {
                // Create evidence request for buyer
                $buyerEvidenceRequest = EvidenceRequest::create([
                    'appeal_id' => $appeal->id,
                    'dispute_id' => $appeal->dispute_id,
                    'requested_from' => $appeal->dispute->buyer_id,
                    'requested_by' => auth()->id(),
                    'message' => $data['message'],
                    'required_evidence_types' => $data['evidence_types'],
                    'deadline' => $deadline,
                    'status' => 'pending'
                ]);

                // Create evidence request for seller
                $sellerEvidenceRequest = EvidenceRequest::create([
                    'appeal_id' => $appeal->id,
                    'dispute_id' => $appeal->dispute_id,
                    'requested_from' => $appeal->dispute->seller_id,
                    'requested_by' => auth()->id(),
                    'message' => $data['message'],
                    'required_evidence_types' => $data['evidence_types'],
                    'deadline' => $deadline,
                    'status' => 'pending'
                ]);

                // Send notifications to both parties
                $appeal->dispute->buyer->notify(new EvidenceRequestNotification($buyerEvidenceRequest));
                $appeal->dispute->seller->notify(new EvidenceRequestNotification($sellerEvidenceRequest));

                // Update appeal status
                $appeal->update(['status' => Appeal::STATUS_EVIDENCE_REQUESTED]);

                // Create system message
                DisputeMessage::create([
                    'dispute_id' => $appeal->dispute_id,
                    'user_id' => 1, // System user ID
                    'message' => "Evidence requested from both parties. Deadline: {$deadline->format('M d, Y \a\t g:i A')}",
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);
            });

            \Log::info('Evidence request successful', [
                'appeal_id' => $appeal->id,
                'deadline' => $deadline->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('admin.appeals.show', $appeal->id)
                ->with('success', 'Evidence requested from both parties successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error requesting evidence: ' . $e->getMessage(), [
                'appeal_id' => $appeal->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to request evidence. Please try again.']);
        }
    }

    /**
     * Close appeal without resolution (for cases where evidence is insufficient)
     */
    public function closeAppeal(Request $request, Appeal $appeal)
    {
        $data = $request->validate([
            'closure_reason' => 'required|string|max:1000',
            'closure_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::transaction(function () use ($appeal, $data) {
                // Update appeal status to closed
                $appeal->update([
                    'status' => 'closed',
                    'review_notes' => "Appeal closed: {$data['closure_reason']}",
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now()
                ]);

                // Update dispute to maintain current status but mark appeal as closed
                $appeal->dispute->update([
                    'can_appeal' => false
                ]);

                // Create system message
                DisputeMessage::create([
                    'dispute_id' => $appeal->dispute_id,
                    'user_id' => 1, // System user ID
                    'message' => "Appeal CLOSED: {$data['closure_reason']}",
                    'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                    'is_internal' => false
                ]);

                // Send notifications to both parties
                $appeal->dispute->buyer->notify(new AppealClosedNotification($appeal));
                $appeal->dispute->seller->notify(new AppealClosedNotification($appeal));
            });

            \Log::info('Appeal closed successfully', [
                'appeal_id' => $appeal->id,
                'closure_reason' => $data['closure_reason'],
                'closed_by' => auth()->id()
            ]);

            return redirect()->route('admin.appeals.show', $appeal->id)
                ->with('success', 'Appeal closed successfully.');

        } catch (\Exception $e) {
            \Log::error('Error closing appeal: ' . $e->getMessage(), [
                'appeal_id' => $appeal->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to close appeal. Please try again.']);
        }
    }
}
