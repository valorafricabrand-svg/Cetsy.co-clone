<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class PasswordController extends Controller
{
    /**
     * Request a password reset link (JSON response for mobile).
     */
    public function forgot(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => __($status),
                'message' => 'Reset link sent successfully.'
            ], 200);
        }

        return response()->json([
            'message' => __($status)
        ], 422);
    }

    /**
     * Reset the user's password (JSON response for mobile).
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => __($status),
                'message' => 'Password has been reset.'
            ], 200);
        }

        return response()->json([
            'message' => __($status)
        ], 422);
    }

    /**
     * Change the authenticated user's password.
     */
    public function change(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors'  => ['current_password' => ['The current password is incorrect.']]
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Optionally keep current token; revoke other tokens
        if (method_exists($user, 'tokens')) {
            $currentId = optional($request->user()->currentAccessToken())->id;
            $user->tokens()
                ->when($currentId, fn($q) => $q->where('id', '!=', $currentId))
                ->delete();
        }

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
