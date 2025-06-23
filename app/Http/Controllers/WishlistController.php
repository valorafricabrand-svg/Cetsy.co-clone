<?php

// app/Http/Controllers/WishlistController.php
namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use App\Mail\ProductWishlistedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        // Create the wishlist item
        Wishlist::create([
            'user_id'    => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        // Send email to shop owner
        try {
            $product = Product::with('shop.user')->find($data['product_id']);
            
            if ($product && $product->shop && $product->shop->user) {
                Mail::to($product->shop->user->email)
                    ->send(new ProductWishlistedMail(
                        $product,
                        $request->user(),
                        $product->shop->user
                    ));
            }
        } catch (\Exception $e) {
            // Log the error but don't break the user experience
            \Log::error('Failed to send wishlist notification email: ' . $e->getMessage());
        }

        return back()->with('success', 'Added to Favorites!');
    }


        public function remove(Wishlist $wishlist)
    {
        // Prevent users from deleting someone else's item
        if ($wishlist->user_id !== auth()->id()) {
            abort(403);
        }

        $wishlist->delete();

        return back()->with('success', 'Item removed from Favorites.');
    }
}
