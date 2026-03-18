<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\SubscriptionService;
use App\Support\RecentAccountSwitcher;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        RecentAccountSwitcher::rememberForRequest($request, $user);

        // If seller and no active subscription, start trial or redirect to subscription
        if ($user->isSeller() && !$user->hasActiveSubscription()) {
            $trial = SubscriptionService::startTrialIfEligible($user);
            if (!$trial) {
                $message = $user->subscription
                    ? 'Your subscription has expired. Please renew to continue using seller features.'
                    : 'Please choose a plan to start selling on our platform.';
                return redirect()->route('seller.subscription')
                    ->with('error', $message);
            }
            $request->session()->flash(
                'success',
                'Your free seller trial is active until ' . $trial->end_date->format('F j, Y') . '.'
            );
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
