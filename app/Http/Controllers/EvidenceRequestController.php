<?php

namespace App\Http\Controllers;

use App\Models\EvidenceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EvidenceRequestController extends Controller
{
    public function index()
    {
        $evidenceRequests = EvidenceRequest::with(['appeal.dispute', 'appeal.appealedBy'])
            ->forRecipient(Auth::id())
            ->latest('created_at')
            ->paginate(12);

        return view('evidence-requests.index', compact('evidenceRequests'));
    }

    public function show(EvidenceRequest $evidenceRequest)
    {
        abort_unless($this->canAccess($evidenceRequest), 403, 'Unauthorized access to evidence request.');

        $evidenceRequest->load([
            'appeal.dispute',
            'appeal.appealedBy',
            'appeal.buyerEvidenceRequest',
            'appeal.sellerEvidenceRequest',
        ]);

        return view('evidence-requests.show', compact('evidenceRequest'));
    }

    /**
     * Handle evidence request response submission.
     */
    public function respond(Request $request, EvidenceRequest $evidenceRequest)
    {
        if (! $evidenceRequest->isOwnedBy(Auth::id())) {
            abort(403, 'Unauthorized access to evidence request.');
        }

        if (! $evidenceRequest->isPending()) {
            return back()->withErrors([
                'error' => 'This evidence request has already been responded to or is closed.',
            ]);
        }

        $data = Validator::make($request->all(), [
            'response_notes' => 'nullable|string|max:1000',
            'evidence_description' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:1000',
            'submitted_evidence.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov|max:51200',
            'evidence_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov|max:51200',
        ])->validate();

        $description = trim((string) ($data['evidence_description'] ?? $data['response_notes'] ?? ''));
        if ($description === '') {
            throw ValidationException::withMessages([
                'evidence_description' => 'Please describe the evidence you are submitting.',
            ]);
        }

        $uploadedFiles = $request->file('submitted_evidence', []);
        if (empty($uploadedFiles)) {
            $uploadedFiles = $request->file('evidence_files', []);
        }

        $uploadedFiles = array_values(array_filter(Arr::wrap($uploadedFiles)));
        if ($uploadedFiles === []) {
            throw ValidationException::withMessages([
                'evidence_files' => 'Please upload at least one evidence file.',
            ]);
        }

        $submittedFiles = [];
        foreach ($uploadedFiles as $file) {
            $path = $file->store('disputes/evidence-responses', 'public');
            $submittedFiles[] = [
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        $evidenceRequest->markAsSubmitted(
            $description,
            $submittedFiles,
            $data['additional_notes'] ?? null
        );

        $disputeId = $evidenceRequest->resolveDisputeId();
        if ($disputeId !== null) {
            \App\Models\DisputeMessage::create([
                'dispute_id' => $disputeId,
                'user_id' => Auth::id(),
                'message' => "Evidence response submitted for request #{$evidenceRequest->id}. Notes: {$description}",
                'type' => \App\Models\DisputeMessage::TYPE_SYSTEM_MESSAGE,
                'is_internal' => false,
            ]);
        }

        return back()->with(
            'success',
            'Evidence submitted successfully. Your files are now available for review.'
        );
    }

    private function canAccess(EvidenceRequest $evidenceRequest): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $evidenceRequest->isOwnedBy($user->id);
    }
}
