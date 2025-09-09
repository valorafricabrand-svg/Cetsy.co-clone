<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Country;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeBuyerMail;
use App\Mail\WelcomeSellerMail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $countries = Country::all();
        return view('auth.register', compact('countries'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role'     => ['required', 'in:buyer,seller'],
            'name'     => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'phone'    => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms'    => ['accepted'],
        ]);

        $user = User::create([
            'user_type'     => $request->role,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'country_id' => $request->country_id,
            'phone' => $request->phone,
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Send welcome email based on user type
        try {
            if ($user->user_type === 'seller') {
                Mail::to($user->email)->send(new WelcomeSellerMail($user));
            } else {
                Mail::to($user->email)->send(new WelcomeBuyerMail($user));
            }
        } catch (\Throwable $e) {
            // Avoid blocking registration on mail failures; consider logging
            \Log::warning('Welcome email failed: ' . $e->getMessage());
        }

        // If seller, redirect to subscription page
        if ($user->user_type === 'seller') {
            return redirect()->route('seller.subscription')
                ->with('info', 'Please subscribe to start selling on our platform.');
        }

        return redirect()->route('dashboard');
    }
}
