<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;
use Illuminate\Support\Facades\Mail;
use App\Mail\KycStatusMail;
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

            \DB::commit();
            return redirect()->route('seller.kyc')->with('success', 'KYC submitted. We will review your documents soon.');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('KYC submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return response()->view('errors.500', ['message' => 'An error occurred while submitting your KYC. Please try again later.'], 500);
        }
    }

    public function index()
    {
        $pendingKycs = Kyc::where('status', 'pending')->paginate(10);
        return view('admin.kyc.index', compact('pendingKycs'));
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
}
