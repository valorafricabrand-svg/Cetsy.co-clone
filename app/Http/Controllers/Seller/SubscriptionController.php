<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Activity;

class SubscriptionController extends Controller
{
    /**
     * Default index method (needed for resource routes).
     * Reuses show() logic so no error is thrown.
     */
    public function index()
    {
        return $this->show();
    }

    public function show()
    {
        $user = Auth::user();
        $subscription = $user->subscription;
        
        // Fetch all subscription payments for this user
        $subscriptionPayments = Payment::where('user_id', $user->id)
            ->where('payment_name', 'subscription_fee')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('seller.subscription', compact('subscription', 'subscriptionPayments'));
    }

    public function subscribe(Request $request)
    {
        $plan = $request->input('plan', 'monthly');
        
        // Store the selected plan in session for payment processing
        session(['selected_subscription_plan' => $plan]);
        
        // Redirect to payment processing page instead of direct subscription creation
        return view('seller.subscription_pay', compact('plan'));
    }

    public function walletPay(Request $request)
    {
        $user = Auth::user();
        $shop = Shop::where('user_id', $user->id)->first();
        
        // Get the selected plan from session or request
        $plan = session('selected_subscription_plan', $request->input('plan', 'monthly'));
        
        // Calculate subscription fee based on plan
        if ($plan === 'yearly') {
            $subscriptionFee = config('subscription.yearly_fee', 50);
            $duration = 365; // days
        } else {
            $subscriptionFee = config('subscription.monthly_fee', 5);
            $duration = 30; // days
        }

        // Check if user has sufficient wallet balance
        $walletBalance = wallet();
        if ($walletBalance < $subscriptionFee) {
            return redirect()->route('seller.subscription')
                ->with('error', 'Insufficient wallet balance. Please deposit funds first.');
        }

        // Prepare a unique local transaction ID
        $localTxId = 'SUB_' . time() . Str::upper(Str::random(6));
        while (Payment::where('local_transaction_id', $localTxId)->exists()) {
            $localTxId = 'SUB_' . time() . Str::upper(Str::random(6));
        }

        // Create the payment record
        $paymentData = [
            'user_id'               => $user->id,
            'shop_id'               => $shop ? $shop->id : null,
            'total_amount'          => $subscriptionFee,
            'payment_method'        => 'wallet',
            'payment_status'        => 'successful',
            'status'                => '3', // Completed
            'currency'              => 'USD',
            'local_transaction_id'  => $localTxId,
            'payment_name'          => 'subscription_fee',
        ];

        $payment = Payment::create($paymentData);

        // Deduct from user's wallet
        Wallet::create([
            'user_id'    => $user->id,
            'credit'     => 0,
            'debit'      => $subscriptionFee,
            'balance'    => 0,
            'reference'  => $localTxId,
            'method'     => 'wallet',
            'description'=> ucfirst($plan) . ' subscription payment via wallet',
        ]);

        // Create new subscription
        $subscription = new Subscription([
            'user_id' => $user->id,
            'shop_id' => $shop?->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($duration),
            'amount' => $subscriptionFee,
            'payment_method' => 'wallet',
            'transaction_id' => $localTxId,
            'notes' => ucfirst($plan) . ' subscription plan'
        ]);

        $subscription->save();
        if ($shop) { $shop->is_active = true; $shop->save(); }

        // Clear the session
        session()->forget('selected_subscription_plan');

        return redirect()
            ->route('seller.dashboard')
            ->with('success', 'Your ' . ucfirst($plan) . ' subscription has been activated successfully!');
    }

    public function successDeposit(Request $request, $id)
    {
        // Find the user by ID
        $user = \App\Models\User::findOrFail($id);
        $shop = Shop::where('user_id', $user->id)->first();

        // Get the selected plan from session or request
        $plan = session('selected_subscription_plan', $request->get('plan', 'monthly'));
        
        // Calculate subscription fee and duration based on plan
        if ($plan === 'yearly') {
            $subscriptionFee = config('subscription.yearly_fee', 100);
            $duration = 365; // days
        } else {
            $subscriptionFee = config('subscription.monthly_fee', 10);
            $duration = 30; // days
        }

        // Create new subscription
        $subscription = new Subscription([
            'user_id' => $user->id,
            'shop_id' => $shop?->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($duration),
            'amount' => $subscriptionFee,
            'payment_method' => $request->get('method', 'paypal'),
            'transaction_id' => $request->get('transaction_id', uniqid()),
            'notes' => ucfirst($plan) . ' subscription plan'
        ]);

        $subscription->save();
        if ($shop) { $shop->is_active = true; $shop->save(); }

        // Determine payment method: default to 'paypal'
        $method = $request->get('method', 'paypal');

        // Prepare a unique local transaction ID if not provided
        $localTxId = $request->get('transaction_id');
        if (!$localTxId) {
            do {
                $localTxId = 'SUB_' . time() . Str::upper(Str::random(6));
            } while (Payment::where('local_transaction_id', $localTxId)->exists());
        }

        // Build the payment data array
        $paymentData = [
            'user_id'               => $user->id,
            'shop_id'               => $shop ? $shop->id : null,
            'total_amount'          => $subscriptionFee,
            'payment_method'        => $method,
            'payment_status'        => 'successful',
            'status'                => '3', // Completed
            'currency'              => 'USD',
            'local_transaction_id'  => $localTxId,
            'payment_name'          => 'subscription_fee',
        ];

        // Create the payment record
        $payment = Payment::create($paymentData);

        // Clear the session
        session()->forget('selected_subscription_plan');

        // Create activity record for the seller
        Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'You activated a new ' . ucfirst($plan) . ' subscription',
            'type' => \App\Models\Activity::TYPE_SUBSCRIPTION,
            'related_id' => $subscription->id,
            'related_type' => 'subscription'
        ]);

        return redirect()
            ->route('seller.dashboard')
            ->with('success', 'Your ' . ucfirst($plan) . ' subscription has been activated successfully!');
    }

    public function cancel()
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if ($subscription) {
            $plan = $subscription->notes ?? 'current';
            $subscription->update([
                'status' => 'cancelled',
                'notes' => 'Cancelled by user on ' . now()->toDateString()
            ]);

            // Mark shop as inactive when cancelled
            $shop = $user->shop;
            if ($shop) { $shop->is_active = false; $shop->save(); }

            // Create activity record for the seller
            Activity::create([
                'user_id' => $user->id,
                'is_read' => false,
                'description' => 'You cancelled your ' . $plan . ' subscription',
                'type' => \App\Models\Activity::TYPE_SUBSCRIPTION,
                'related_id' => $subscription->id,
                'related_type' => 'subscription'
            ]);
        }

        return redirect()->route('seller.subscription')
            ->with('success', 'Subscription cancelled successfully.');
    }
}
