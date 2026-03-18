<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RecentAccountSwitcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountSwitchController extends Controller
{
    public function switch(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless($currentUser, 403);

        if (! RecentAccountSwitcher::containsForRequest($request, (int) $user->id)) {
            return back()->with('error', 'That account is not available for quick switching on this device.');
        }

        $this->loginAs($request, $user, $currentUser);

        return redirect()->route('dashboard')
            ->with('success', 'Switched account to ' . ($user->name ?: $user->email) . '.');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless($currentUser, 403);

        $validator = Validator::make($request->all(), [
            'switch_email' => ['required', 'email'],
            'switch_password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'accountSwitch')
                ->withInput($request->except('switch_password'));
        }

        $data = $validator->validated();
        $targetUser = User::query()
            ->where('email', $data['switch_email'])
            ->first();

        if (! $targetUser || ! Hash::check($data['switch_password'], (string) $targetUser->password)) {
            return back()
                ->withErrors(['switch_email' => 'Those account credentials do not match our records.'], 'accountSwitch')
                ->withInput($request->except('switch_password'));
        }

        $this->loginAs($request, $targetUser, $currentUser);

        return redirect()->route('dashboard')
            ->with('success', 'Switched account to ' . ($targetUser->name ?: $targetUser->email) . '.');
    }

    public function forget(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless($currentUser, 403);

        if ((int) $currentUser->id === (int) $user->id) {
            return back()->with('error', 'You cannot remove the account you are currently using.');
        }

        if (! RecentAccountSwitcher::containsForRequest($request, (int) $user->id)) {
            return back()->with('error', 'That account is not in your saved switcher list.');
        }

        RecentAccountSwitcher::forgetForRequest($request, (int) $user->id);

        return back()->with('success', 'Removed ' . ($user->name ?: $user->email) . ' from saved accounts.');
    }

    private function loginAs(Request $request, User $targetUser, User $currentUser): void
    {
        RecentAccountSwitcher::rememberForRequest($request, $currentUser, $targetUser);

        Auth::login($targetUser, true);
        $request->session()->regenerate();

        RecentAccountSwitcher::rememberForRequest($request, $currentUser, $targetUser);
    }
}
