<?php

namespace App\Http\Controllers;

use App\Models\EvidenceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvidenceRequestController extends Controller
{
    /**
     * Handle evidence request response submission
     */
    public function respond(Request $request, EvidenceRequest $evidenceRequest)
    {
        // Check if user is authorized to respond to this evidence request
        if ($evidenceRequest->requested_from !== Auth::id()) {
            abort(403, 'Unauthorized access to evidence request.');
        }

        // Check if evidence request is still pending
        if ($evidenceRequest->status !== EvidenceRequest::STATUS_PENDING) {
            return back()->withErrors(['error' => 'This evidence request has already been responded to or is closed.']);
        }

        // Validate request
        $data = $request->validate([
            'response_notes' => 'required|string|max:1000',
            'submitted_evidence.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'
        ]);

        // Handle file uploads
        $submittedEvidence = [];
        if ($request->hasFile('submitted_evidence')) {
            foreach ($request->file('submitted_evidence') as $file) {
                $path = $file->store('disputes/evidence-responses', 'public');
                $submittedEvidence[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString()
                ];
            }
        }

        // Update evidence request
        $evidenceRequest->update([
            'status' => EvidenceRequest::STATUS_RESPONDED,
            'response_notes' => $data['response_notes'],
            'submitted_evidence' => $submittedEvidence,
            'responded_at' => now()
        ]);

        // Create system message in dispute
        \App\Models\DisputeMessage::create([
            'dispute_id' => $evidenceRequest->dispute_id,
            'user_id' => Auth::id(),
            'message' => "Evidence response submitted for request #{$evidenceRequest->id}. Notes: {$data['response_notes']}",
            'type' => \App\Models\DisputeMessage::TYPE_SYSTEM_MESSAGE,
            'is_internal' => false
        ]);

        return back()->with('success', 'Evidence response submitted successfully. Your evidence is now under review.');
    }
}
