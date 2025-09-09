<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile.
     * Accepts: name (string), phone (nullable), photo (image optional)
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            // Optionally remove old file
            if (!empty($user->photo) && $user->photo_storage === 'public') {
                Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('avatars', 'public');
            $data['photo'] = $path;
            $data['photo_storage'] = 'public';
        }

        $user->update($data);

        return response()->json(['user' => $user->refresh()]);
    }

    /**
     * Change email with verification: requires current_password, new unique email.
     * Sets email_verified_at to null and sends verification notification.
     */
    public function changeEmail(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'current_password' => ['required','string'],
            'email' => ['required','email','max:255','unique:users,email'],
        ]);

        if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $user->email = $data['email'];
        $user->email_verified_at = null;
        $user->save();

        // Send verification email if supported
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            try { $user->sendEmailVerificationNotification(); } catch (\Throwable $e) {}
        }

        // Optionally force re-login by revoking all tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Email updated. Please verify your new email address.',
            'relogin_required' => true,
        ], 200);
    }
}
