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
        return view('seller.kyc', compact('kyc'));
    }

    public function submit(Request $request)
    {
        // Check subscription first
        if (!auth()->user()->hasActiveSubscription()) {
            return redirect()->route('seller.subscription')
                ->with('error', 'Please subscribe first to access KYC verification.');
        }

        \DB::beginTransaction();
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'id_number' => 'required|string|max:50',
                'id_type' => 'required|string|max:50',
                'id_front' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'id_back' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'selfie' => 'required|image|max:2048',
            ]);

            $user = auth()->user();
            $kyc = $user->kyc ?: new \App\Models\Kyc(['user_id' => $user->id]);
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
            $admins = User::where('user_type', User::TYPE_ADMIN)->get(['id']);
            foreach ($admins as $admin) {
                Activity::create([
                    'user_id' => $admin->id,
                    'is_read' => false,
                    'description' => 'Seller ' . ($user->name ?? ('#'.$user->id)) . ' submitted a KYC, awaiting admin approval.',
                    'type' => \App\Models\Activity::TYPE_KYC,
                    'related_id' => $kyc->id,
                    'related_type' => 'kyc',
                    'link' => route('admin.kyc.show', $kyc->id),
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
            'status' => 'required|in:approved,rejected',
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
                Activity::create([
                    'user_id' => $kyc->user->id,
                    'is_read' => false,
                    'description' => 'Your KYC application has been ' . $validated['status'],
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
            'status'      => 'required|in:approved,rejected,pending',
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

        DB::transaction(function () use ($ids, $request) {
            Kyc::whereIn('id', $ids)->update([
                'status'      => $request->status,
                'admin_notes' => $request->admin_notes,
                'updated_at'  => now(),
            ]);
        });

        return back()->with('success', 'KYC records updated: '.count($ids));
    }
}
