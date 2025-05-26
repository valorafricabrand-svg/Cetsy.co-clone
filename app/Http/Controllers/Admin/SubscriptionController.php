<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function deactivateExpired(): RedirectResponse
    {
        $count = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'inactive']);

        return Redirect::back()->with('success', "Deactivated $count expired subscriptions.");
    }
} 