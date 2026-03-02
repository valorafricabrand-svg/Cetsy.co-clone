<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user (buyer or seller).
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:6',
            'user_type' => 'required|in:buyer,seller',
            'phone'     => 'nullable|string|max:20',
            'country_id'=> 'nullable|exists:countries,id',
        ]);

        $isSeller = $request->user_type === User::TYPE_SELLER;
        $autoApproveSellerSignups = function_exists('setting_bool')
            ? setting_bool('seller_signup_auto_approve', (bool) env('SELLER_SIGNUP_AUTO_APPROVE', true))
            : (bool) env('SELLER_SIGNUP_AUTO_APPROVE', true);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'user_type'  => $request->user_type,
            'phone'      => $request->phone,
            'country_id' => $request->country_id,
            'is_active'  => $isSeller ? $autoApproveSellerSignups : true,
        ]);

        // Auto-create a basic shop for sellers so they can list products via API.
        if ($user->user_type === User::TYPE_SELLER && !$user->shop) {
            $base = Str::slug($user->name . ' Shop');
            $slug = $base;
            $i = 1;
            while (Shop::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }
            Shop::create([
                'user_id' => $user->id,
                'name'    => $slug,
                'slug'    => $slug,
            ]);
            // refresh relation
            $user->load('shop');
        }

        return response()->json([
            'token' => $user->createToken('cetsy_token')->plainTextToken,
            'user'  => $user,
        ], 201);
    }

    /**
     * Login an existing user.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('cetsy_token')->plainTextToken,
            'user'  => $user,
        ]);
    }

    /**
     * Return the authenticated user.
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout and revoke the user's token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
