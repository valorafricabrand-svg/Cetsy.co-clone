<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
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
        // Redirect to payment processing page instead of direct subscription creation
        return view('seller.subscription_pay');
    }

    public function successDeposit(Request $request, $id)
    {
        // Find the user by ID
        $user = \App\Models\User::findOrFail($id);
        $shop = Shop::where('user_id', $user->id)->first();

        // Create new subscription
        $subscription = new Subscription([
            'user_id' => $user->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'amount' => config('subscription.monthly_fee', 1000),
            'payment_method' => $request->get('method', 'paypal'),
            'transaction_id' => $request->get('transaction_id', uniqid())
        ]);

        $subscription->save();

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
            'total_amount'          => config('subscription.monthly_fee', 5),
            'payment_method'        => $method,
            'payment_status'        => 'successful',
            'status'                => '3', // Completed
            'currency'              => 'USD',
            'local_transaction_id'  => $localTxId,
            'payment_name'          => 'subscription_fee',
        ];

        // Create the payment record
        $payment = Payment::create($paymentData);

        return redirect()
            ->route('seller.dashboard')
            ->with('success', 'Your subscription has been activated successfully!');
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