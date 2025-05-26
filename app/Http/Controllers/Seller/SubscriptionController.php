<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        
        return view('seller.subscription', compact('subscription'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Create new subscription
        $subscription = new Subscription([
            'user_id' => $user->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'amount' => config('subscription.monthly_fee', 1000), // Default to 1000 if not configured
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id
        ]);

        $subscription->save();

        return redirect()->route('seller.dashboard')
            ->with('success', 'Subscription activated successfully!');
    }

    public function cancel()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'notes' => 'Cancelled by user on ' . now()->toDateString()
            ]);
        }

        return redirect()->route('seller.subscription')
            ->with('success', 'Subscription cancelled successfully.');
    }
} 