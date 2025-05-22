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

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
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
        'email'    => ['required', 'string', 'email', 'lowercase', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'user_type'     => $request->role,
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));
    Auth::login($user);

    // If seller, maybe redirect to shop creation?
    if ($user->user_type === 'seller') {
        return redirect()->route('shops.create');
    }

    return redirect()->route('dashboard');
}
}
