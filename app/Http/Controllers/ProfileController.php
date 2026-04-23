<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Cookie;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $countries = Country::all();
        
        return view('profile.edit', [
            'user' => $request->user(),
            'countries' => $countries,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        
        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                \Storage::disk('public')->delete($user->photo);
            }
            
            // Store new photo
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            $data['photo'] = $photoPath;
            $data['photo_storage'] = 'public';
        }
        
        $user->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $preferredLocale = normalize_locale((string) ($request->user()->preferred_locale ?: ''));

        if ($preferredLocale && $request->hasSession()) {
            $request->session()->put('locale', $preferredLocale);
            app()->setLocale($preferredLocale);
        }

        $response = Redirect::route('profile.edit')->with('status', 'profile-updated');

        if ($preferredLocale) {
            $response->withCookie(new Cookie(
                (string) config('locales.cookie', 'locale'),
                $preferredLocale,
                now()->addDays(180),
                '/'
            ));
        }

        return $response;
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
