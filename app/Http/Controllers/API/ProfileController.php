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
}

