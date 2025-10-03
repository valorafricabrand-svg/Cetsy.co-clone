<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\KycStatusMail;
use App\Mail\SupportKycSubmittedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KycController extends Controller
{
    public function show()
    {
        $kyc = auth()->user()->kyc;
        if (!$kyc || in_array($kyc->status, ['rejected','needs_correction'], true)) {
            return redirect()->route('seller.kyc.info');
        }
        return view('seller.kyc', compact('kyc'));
    }

    // Step 1: Info - GET
    public function info()
    {
        $kyc = auth()->user()->kyc;
        if ($kyc && $kyc->status !== 'rejected') {
            return redirect()->route('seller.kyc');
        }
        $step1 = session('kyc.step1', []);
        return view('seller.kyc_info', compact('kyc', 'step1'));
    }

    // Step 1: Info - POST
    public function postInfo(Request $request)
    {
        $kyc = auth()->user()->kyc;
        if ($kyc && $kyc->status !== 'rejected') {
            return redirect()->route('seller.kyc');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:50',
            'id_number'  => 'required|string|max:50',
            'id_type'    => 'required|string|max:50',
        ]);

        session(['kyc.step1' => $validated]);
        return redirect()->route('seller.kyc.documents');
    }

    // Step 2: Documents - GET
    public function documents()
    {
        $kyc = auth()->user()->kyc;
        if ($kyc && $kyc->status !== 'rejected') {
            return redirect()->route('seller.kyc');
        }
        if (!session()->has('kyc.step1')) {
            return redirect()->route('seller.kyc.info')->with('error', 'Please complete your details first.');
        }
        return view('seller.kyc_documents');
    }

    // Step 2: Documents - POST (final submit)
    public function postDocuments(Request $request)
    {
        // Check subscription first
        if (!auth()->user()->hasActiveSubscription()) {
            return redirect()->route('seller.subscription')
                ->with('error', 'Please subscribe first to access KYC verification.');
        }

        $step1 = session('kyc.step1');
        if (!$step1) {
            return redirect()->route('seller.kyc.info')->with('error', 'Please complete your details first.');
        }

        $request->validate([
            'id_front' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'id_back'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'selfie'   => 'required|image|max:2048',
        ]);

        \DB::beginTransaction();
        try {
            $user = auth()->user();
            $kyc = new Kyc(['user_id' => $user->id]);
            $kyc->fill($step1);

            if ($request->hasFile('id_front')) {
                $kyc->id_front = $request->file('id_front')->store('kyc/id_fronts', 'public');
            }
            if ($request->hasFile('id_back')) {
                $kyc->id_back = $request->file('id_back')->store('kyc/id_backs', 'public');
            }
            if ($request->hasFile('selfie')) {
                $kyc->selfie = $request->file('selfie')->store('kyc/selfies', 'public');
            }

            $kyc->status = 'pending';
            $kyc->admin_notes = null;
            $kyc->save();

            // Seller activity
            Activity::create([
                'user_id' => $user->id,
                'is_read' => false,
                'description' => 'You submitted a new KYC application',
                'type' => Activity::TYPE_KYC,
                'related_id' => $kyc->id,
                'related_type' => 'kyc'
            ]);

            // Admin activities (per-admin)
            $adminLink = null;
            try {
                $adminLink = route('admin.kyc.show', $kyc->id);
            } catch (\Throwable $e) {
                try { $adminLink = route('admin.kyc.index'); } catch (\Throwable $e2) { $adminLink = null; }
            }
            $admins = User::where('user_type', User::TYPE_ADMIN)->get(['id']);
            foreach ($admins as $admin) {
                Activity::create([
                    'user_id' => $admin->id,
                    'is_read' => false,
                    'description' => 'Seller ' . ($user->name ?? ('#'.$user->id)) . ' submitted a KYC, awaiting admin approval.',
                    'type' => Activity::TYPE_KYC,
                    'related_id' => $kyc->id,
                    'related_type' => 'kyc',
                    'link' => $adminLink,
                    'causer_type' => get_class($user),
                    'causer_id' => $user->id,
                    'subject_type' => get_class($kyc),
                    'subject_id' => $kyc->id,
                ]);
            }

            // Support email
            try {
                Mail::to('hello@cetsy.co')->send(new SupportKycSubmittedMail($kyc));
            } catch (\Throwable $mailEx) {
                \Log::warning('Failed to send support KYC submitted email', [
                    'kyc_id' => $kyc->id,
                    'user_id' => $user->id,
                    'error' => $mailEx->getMessage(),
                ]);
            }

            \DB::commit();
            session()->forget('kyc.step1');
            return redirect()->route('seller.kyc')->with('success', 'KYC submitted. We will review your documents soon.');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('KYC submission failed (step2)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while submitting KYC. Please try again.');
        }
    }

    public function submit(Request $request)
    {
        // Check subscription first
        if (!auth()->user()->hasActiveSubscription()) {
            return redirect()->route('seller.subscription')
                ->with('error', 'Please subscribe first to access KYC verification.');
        }

        // Build validation rules (allow keeping existing files)
        $existingKyc = auth()->user()->kyc;
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:50',
            'id_number'  => 'required|string|max:50',
            'id_type'    => 'required|string|max:50',
            'id_front'   => ($existingKyc && $existingKyc->id_front ? 'nullable' : 'required') . '|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'id_back'    => ($existingKyc && $existingKyc->id_back ? 'nullable' : 'required') . '|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'selfie'     => ($existingKyc && $existingKyc->selfie ? 'nullable' : 'required') . '|image|max:2048',
        ];

        $request->validate($rules);

        \DB::beginTransaction();
        try {
            $user = auth()->user();
            $kyc = $existingKyc ?: new \App\Models\Kyc(['user_id' => $user->id]);
            $kyc->fill($request->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'id_number',
                'id_type',
            ]));
            if ($request->hasFile('id_front')) {
                $kyc->id_front = $request->file('id_front')->store('kyc/id_fronts', 'public');
            }
            if ($request->hasFile('id_back')) {
                $kyc->id_back = $request->file('id_back')->store('kyc/id_backs', 'public');
            }
            if ($request->hasFile('selfie')) {
                $kyc->selfie = $request->file('selfie')->store('kyc/selfies', 'public');
            }
            $kyc->status = 'pending';
            $kyc->admin_notes = null;
            $kyc->save();

            // Create activity record for the seller
            Activity::create([
                'user_id' => $user->id,
                'is_read' => false,
                'description' => 'You submitted a new KYC application',
                'type' => \App\Models\Activity::TYPE_KYC,
                'related_id' => $kyc->id,
                'related_type' => 'kyc'
            ]);

            // Create admin-facing activity log (per-admin targeting)
            $adminLink = null;
            try {
                $adminLink = route('admin.kyc.show', $kyc->id);
            } catch (\Throwable $e) {
                try { $adminLink = route('admin.kyc.index'); } catch (\Throwable $e2) { $adminLink = null; }
            }
            $admins = User::where('user_type', User::TYPE_ADMIN)->get(['id']);
            foreach ($admins as $admin) {
                Activity::create([
                    'user_id' => $admin->id,
                    'is_read' => false,
                    'description' => 'Seller ' . ($user->name ?? ('#'.$user->id)) . ' submitted a KYC, awaiting admin approval.',
                    'type' => \App\Models\Activity::TYPE_KYC,
                    'related_id' => $kyc->id,
                    'related_type' => 'kyc',
                    'link' => $adminLink,
                    'causer_type' => get_class($user),
                    'causer_id' => $user->id,
                    'subject_type' => get_class($kyc),
                    'subject_id' => $kyc->id,
                ]);
            }

            // Notify support via email
            try {
                Mail::to('hello@cetsy.co')->send(new SupportKycSubmittedMail($kyc));
            } catch (\Throwable $mailEx) {
                \Log::warning('Failed to send support KYC submitted email', [
                    'kyc_id' => $kyc->id,
                    'user_id' => $user->id,
                    'error' => $mailEx->getMessage(),
                ]);
            }

            \DB::commit();
            return redirect()->route('seller.kyc')->with('success', 'KYC submitted. We will review your documents soon.');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('KYC submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
       return redirect()->back()->with('error', 'An error occurred while updating KYC status. Please try again.');
        }
    }

    public function index(Request $request)
    {
        

        $perPage   = (int) $request->input('per_page', 10);
        $status    = $request->input('status', 'pending');
        $search    = $request->input('q');
        $sortField = $request->input('sort', 'created_at');
        $sortDir   = $request->input('dir', 'desc');

        $allowedSorts = ['id','created_at','status','id_number'];
        if (! in_array($sortField, $allowedSorts, true)) $sortField = 'created_at';
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $kycs = Kyc::with('user:id,name,email')
            ->status($status)
            ->search($search)
            ->orderBy($sortField, $sortDir)
            ->paginate($perPage)
            ->appends($request->query());

        $counts = Kyc::selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.kyc.index', compact(
            'kycs','counts','status','search','perPage','sortField','sortDir'
        ));
    }

    public function update(Request $request, Kyc $kyc)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,needs_correction',
            'admin_notes' => 'nullable|string'
        ], [
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status value.',
        ]);

        DB::beginTransaction();
        try {
            $kyc->update($validated);

            // Try to send the email, but don't fail the transaction if mail fails
            try {
                Mail::to($kyc->user->email)->send(new KycStatusMail($validated['status'], $validated['admin_notes'] ?? null));

                // Create activity record for the seller
                $desc = match ($validated['status']) {
                    'approved' => 'Your KYC application has been approved',
                    'rejected' => 'Your KYC application has been rejected',
                    'needs_correction' => 'Your KYC requires corrections. Please review the notes and resubmit.',
                    default => 'Your KYC status was updated',
                };

                Activity::create([
                    'user_id' => $kyc->user->id,
                    'is_read' => false,
                    'description' => $desc,
                    'type' => \App\Models\Activity::TYPE_KYC,
                    'related_id' => $kyc->id,
                    'related_type' => 'kyc'
                ]);
            } catch (\Throwable $mailException) {
                Log::error('Failed to send KYC status email', [
                    'user_id' => $kyc->user->id,
                    'kyc_id' => $kyc->id,
                    'error' => $mailException->getMessage(),
                ]);
                // Optionally, you can add a flash message to notify admin that mail failed
                session()->flash('warning', 'KYC updated, but failed to send email notification.');
            }

            DB::commit();
            return redirect()->back()->with('success', 'KYC status updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update KYC status', [
                'user_id' => $kyc->user->id,
                'kyc_id' => $kyc->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating KYC status. Please try again.');
        }
    }

    public function showDetails(Kyc $kyc)
    {
        return view('admin.kyc.show', compact('kyc'));
    }

       public function bulk(Request $request)
    {
        $request->validate([
            'ids_json'    => 'required|string',
            'status'      => 'required|in:approved,rejected,pending,needs_correction',
            'admin_notes' => 'nullable|string',
        ]);

        $ids = json_decode($request->input('ids_json', '[]'), true);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No records selected.');
        }

        // authorize – tweak to your role logic
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $status = $request->status;
        $notes  = $request->admin_notes;

        $kycs = Kyc::with('user')->whereIn('id', $ids)->get();
        if ($kycs->isEmpty()) {
            return back()->with('error', 'No matching KYC records found.');
        }

        DB::beginTransaction();
        try {
            foreach ($kycs as $kyc) {
                $kyc->status = $status;
                $kyc->admin_notes = $notes;
                $kyc->save();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Bulk KYC update failed', [
                'ids' => $ids,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update selected KYC records.');
        }

        // Post-commit: send emails and create activities per seller
        $emailableStatuses = ['approved','rejected','needs_correction'];
        $sent = 0; $failed = 0; $notified = 0;
        foreach ($kycs as $kyc) {
            try {
                if (in_array($status, $emailableStatuses, true)) {
                    Mail::to($kyc->user->email)->send(new KycStatusMail($status, $notes));
                    $sent++;
                }

                $desc = match ($status) {
                    'approved' => 'Your KYC application has been approved',
                    'rejected' => 'Your KYC application has been rejected',
                    'needs_correction' => 'Your KYC requires corrections. Please review the notes and resubmit.',
                    'pending' => 'Your KYC status was set to pending',
                    default => 'Your KYC status was updated',
                };
                Activity::create([
                    'user_id' => $kyc->user->id,
                    'is_read' => false,
                    'description' => $desc,
                    'type' => Activity::TYPE_KYC,
                    'related_id' => $kyc->id,
                    'related_type' => 'kyc'
                ]);
                $notified++;
            } catch (\Throwable $ex) {
                $failed++;
                Log::warning('Bulk KYC notify/email failed', [
                    'kyc_id' => $kyc->id,
                    'user_id' => $kyc->user->id,
                    'error' => $ex->getMessage(),
                ]);
            }
        }

        $msg = sprintf(
            'KYC records updated: %d. Emails sent: %d. Notifications: %d. Failures: %d',
            $kycs->count(), $sent, $notified, $failed
        );
        return back()->with('success', $msg);
    }
}
