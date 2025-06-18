<?php

// app/Http/Controllers/WishlistController.php
namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /** Add or remove – toggle */
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $wishlist = Wishlist::where('user_id', $request->user()->id)
                            ->where('product_id', $data['product_id'])
                            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return back()->with('success', 'Removed from Favorites.');
        }

        Wishlist::create([
            'user_id'    => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        return back()->with('success', 'Added to Favorites!');
    }


        public function remove(Wishlist $wishlist)
    {
        // Prevent users from deleting someone else’s item
        if ($wishlist->user_id !== auth()->id()) {
            abort(403);
        }

        $wishlist->delete();

        return back()->with('success', 'Item removed from Favorites.');
    }
}
