<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            // Log the request for debugging
            Log::info('Password update attempt', [
                'user_id' => $request->user()->id,
                'has_current_password' => $request->has('current_password'),
                'has_new_password' => $request->has('password'),
                'has_confirmation' => $request->has('password_confirmation'),
            ]);

            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            // Log validation success
            Log::info('Password validation passed', ['user_id' => $request->user()->id]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log successful update
            Log::info('Password updated successfully', ['user_id' => $request->user()->id]);

            return back()->with('status', 'password-updated')
                        ->with('success', 'Your password has been updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            Log::warning('Password validation failed', [
                'user_id' => $request->user()->id,
                'errors' => $e->errors(),
            ]);

            // Return with validation errors (they will be displayed automatically)
            throw $e;

        } catch (\Exception $e) {
            // Log any other errors
            Log::error('Password update failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Password update failed. Please try again.'])
                        ->withInput($request->except(['password', 'password_confirmation']));
        }
    }
}
