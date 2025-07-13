<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    public function getAvailableProducts()
    {
        $user = Auth::user();
        
        // Get products that the buyer hasn't made an offer on yet
        $productsWithOffers = Offer::where('buyer_id', $user->id)
            ->pluck('product_id')
            ->toArray();
        
        $availableProducts = Product::with('media')
            ->where('is_active', true)
            ->whereNotIn('id', $productsWithOffers)
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'currency' => get_currency(),
                    'image' => $product->media->first() ? $product->media->first()->getUrl() : null
                ];
            });
        
        return response()->json([
            'success' => true,
            'products' => $availableProducts
        ]);
    }

    public function showDetails($offerId)
    {
        $offer = Offer::with(['product.media', 'product.shop.user', 'counterOffers'])
            ->where('buyer_id', Auth::id())
            ->findOrFail($offerId);

        return view('buyer.offers.details', compact('offer'));
    }

    public function createNewOffer(Request $request, $productId)
    {
        $product = Product::with('shop.user')->findOrFail($productId);
        
        $data = $request->validate([
            'offer_price' => 'required|numeric|min:0|max:' . $product->price,
            'message' => 'nullable|string|max:500'
        ]);

        try {
            // Check if buyer already has a pending offer for this product
            $existingOffer = Offer::where('product_id', $productId)
                ->where('buyer_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if ($existingOffer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending offer for this product.'
                ], 400);
            }

            // Create new offer
            $offer = Offer::create([
                'product_id' => $productId,
                'buyer_id' => Auth::id(),
                'offer_price' => $data['offer_price'],
                'status' => 'pending',
                'is_counter_offer' => false,
                'buyer_notes' => $data['message'] ?? null
            ]);

            // Send email notification to seller
            $this->sendNewOfferEmail($offer);

            return response()->json([
                'success' => true,
                'message' => 'Your offer has been submitted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating offer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function respondToCounterOffer(Request $request, $offerId)
    {
        $offer = Offer::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($offerId);

        $data = $request->validate([
            'response' => 'required|in:accept,decline,counter',
            'counter_price' => 'required_if:response,counter|numeric|min:0',
            'message' => 'nullable|string|max:500'
        ]);

        try {
            switch ($data['response']) {
                case 'accept':
                    $offer->update([
                        'status' => 'accepted',
                        'buyer_notes' => $data['message'] ?? 'Buyer accepted the counter offer.'
                    ]);
                    $this->sendAcceptanceEmail($offer);
                    break;

                case 'decline':
                    $offer->update([
                        'status' => 'declined',
                        'buyer_notes' => $data['message'] ?? 'Buyer declined the counter offer.'
                    ]);
                    $this->sendDeclineEmail($offer);
                    break;

                case 'counter':
                    // Update the existing offer as a counter offer
                    $originalPrice = $offer->offer_price;
                    $offer->update([
                        'offer_price' => $data['counter_price'],
                        'is_counter_offer' => true,
                        'original_offer_id' => $offer->id, // self-reference for tracking
                        'buyer_notes' => $data['message'] ?? 'Buyer made a counter offer.',
                        'seller_notes' => "Original offer: $" . number_format($originalPrice, 2),
                        'status' => 'pending',
                    ]);
                    $this->sendCounterOfferEmail($offer);
                    break;
            }

            // For normal form submission, redirect back with a flash message
            return redirect()->back()->with('success', 'Response submitted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing response: ' . $e->getMessage());
        }
    }

    private function sendNewOfferEmail($offer)
    {
        try {
            $seller = $offer->product->shop->user;
            if ($seller) {
                \Mail::to($seller->email)->send(new \App\Mail\NewOfferReceivedMail($offer));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send new offer email: ' . $e->getMessage());
        }
    }

    private function sendAcceptanceEmail($offer)
    {
        try {
            $seller = $offer->product->shop->user;
            if ($seller) {
                \Mail::to($seller->email)->send(new \App\Mail\OfferAcceptedMail($offer));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send offer acceptance email: ' . $e->getMessage());
        }
    }

    private function sendDeclineEmail($offer)
    {
        try {
            $seller = $offer->product->shop->user;
            if ($seller) {
                \Mail::to($seller->email)->send(new \App\Mail\OfferDeclinedMail($offer));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send offer decline email: ' . $e->getMessage());
        }
    }

    private function sendCounterOfferEmail($offer)
    {
        try {
            $seller = $offer->product->shop->user;
            if ($seller) {
                \Mail::to($seller->email)->send(new \App\Mail\CounterOfferReceivedMail($offer));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send counter offer email: ' . $e->getMessage());
        }
    }
} 