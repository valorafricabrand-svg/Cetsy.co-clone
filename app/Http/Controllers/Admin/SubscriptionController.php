<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\Subscription;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function deactivateExpired(): RedirectResponse
    {
        $count = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'inactive']);

        // Create activity record for the seller
        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You deactivated expired subscriptions'
        ]);

        return Redirect::back()->with('success', "Deactivated $count expired subscriptions.");
    }
} 