<?php
// app/Http/Controllers/OfferController.php
namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Mail\OfferReceivedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Activity;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => ['required','exists:products,id'],
            'offer_price' => ['required','numeric','min:1'],
        ]);

        // Load product and compute the effective price buyers see
        $product = Product::with('shop.user')->findOrFail($data['product_id']);
        $finalPrice = (float) ($product->discounted_price ?? $product->price);

        // Disallow offers that exceed the listing price
        if ((float) $data['offer_price'] > $finalPrice) {
            return back()->withErrors([
                'offer_price' => 'Offer cannot exceed the listing price (' . get_currency() . ' ' . number_format($finalPrice, 2) . ').'
            ])->withInput();
        }

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

        // Notify shop owner via email (best-effort) and always create an activity
        if ($product && $product->shop && $product->shop->user) {
            try {
                Mail::to($product->shop->user->email)
                    ->send(new OfferReceivedMail($offer, $product, $request->user(), $product->shop->user));
            } catch (\Throwable $e) {
                \Log::warning('offer.email_failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            }

            // Create activity record for the seller regardless of email success
            try {
                Activity::create([
                    'user_id' => $product->shop->user->id,
                    'is_read' => false,
                    'description' => 'You received a new offer of $' . number_format($offer->offer_price, 2) . ' for ' . $offer->product->name . ' from ' . $offer->buyer->name,
                    'type' => \App\Models\Activity::TYPE_OFFER,
                    'related_id' => $offer->id,
                    'related_type' => 'offer'
                ]);
            } catch (\Throwable $e) {
                \Log::error('offer.activity_failed', ['offer_id' => $offer->id, 'seller_id' => $product->shop->user->id, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Offer submitted! The seller will review it soon.');
    }
}
