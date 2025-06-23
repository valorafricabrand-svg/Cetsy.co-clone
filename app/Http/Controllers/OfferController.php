<?php
// app/Http/Controllers/OfferController.php
namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Mail\OfferReceivedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => ['required','exists:products,id'],
            'offer_price' => ['required','numeric','min:1'],
        ]);

        $offer = Offer::updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'buyer_id'   => $request->user()->id,
            ],
            [
                'offer_price' => $data['offer_price'],
                'status'      => 'pending',
            ]
        );

        // Send email to shop owner
        try {
            $product = Product::with('shop.user')->find($data['product_id']);
            
            if ($product && $product->shop && $product->shop->user) {
                Mail::to($product->shop->user->email)
                    ->send(new OfferReceivedMail(
                        $offer,
                        $product,
                        $request->user(),
                        $product->shop->user
                    ));
            }
        } catch (\Exception $e) {
            // Log the error but don't break the user experience
            \Log::error('Failed to send offer notification email: ' . $e->getMessage());
        }

        return back()->with('success', 'Offer submitted! The seller will review it soon.');
    }
}
