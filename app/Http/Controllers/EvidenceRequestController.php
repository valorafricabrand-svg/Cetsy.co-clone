<?php

namespace App\Http\Controllers;

use App\Models\EvidenceRequest;
use App\Models\DisputeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EvidenceRequestController extends Controller
{
    /**
     * Display the evidence request for a user
     */
    public function show(EvidenceRequest $evidenceRequest)
    {
        $user = Auth::user();

        // Check if user is authorized to view this evidence request
        if ($evidenceRequest->user_id !== $user->id) {
            abort(403, 'Unauthorized access to evidence request.');
        }

        return view('evidence-requests.show', compact('evidenceRequest'));
    }

    /**
     * Submit evidence for an evidence request
     */
    public function submit(Request $request, EvidenceRequest $evidenceRequest)
    {
        $user = Auth::user();

        // Check if user is authorized to submit evidence for this request
        if ($evidenceRequest->user_id !== $user->id) {
            abort(403, 'Unauthorized access to evidence request.');
        }

        // Check if evidence request is still pending
        if (!$evidenceRequest->isPending()) {
            return back()->withErrors(['error' => 'Evidence has already been submitted or the deadline has passed.']);
        }

        // Check if deadline has passed
        if ($evidenceRequest->isDeadlineExpired()) {
            $evidenceRequest->markAsOverdue();
            return back()->withErrors(['error' => 'The deadline for submitting evidence has passed.']);
        }

        $request->validate([
            'evidence_files.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov|max:51200', // 50MB max
            'evidence_description' => 'required|string|max:2000',
            'additional_notes' => 'nullable|string|max:1000'
        ]);

        // Handle file uploads
        $submittedEvidence = [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('evidence-requests', 'public');
                $submittedEvidence[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()
                ];
            }
        }

        // Add description and notes to evidence
        $evidenceData = [
            'files' => $submittedEvidence,
            'description' => $request->evidence_description,
            'additional_notes' => $request->additional_notes,
            'submitted_at' => now()
        ];

        DB::transaction(function () use ($evidenceRequest, $evidenceData, $user) {
            // Submit evidence
            $evidenceRequest->submitEvidence($evidenceData);

            // Create system message
            DisputeMessage::create([
                'dispute_id' => $evidenceRequest->appeal->dispute_id,
                'user_id' => 1, // System user ID
                'message' => "{$evidenceRequest->getPartyTypeLabel()} ({$user->name}) has submitted evidence for the appeal.",
                'type' => DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false
            ]);
        });

        return redirect()->route('evidence-requests.show', $evidenceRequest->id)
            ->with('success', 'Evidence submitted successfully. Our team will review it shortly.');
    }

    /**
     * Get user's pending evidence requests
     */
    public function index()
    {
        $user = Auth::user();
        
        $evidenceRequests = EvidenceRequest::where('user_id', $user->id)
            ->with(['appeal.dispute'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('evidence-requests.index', compact('evidenceRequests'));
    }
}
