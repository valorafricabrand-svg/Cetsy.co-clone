<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use App\Mail\OfferAcceptedMail;
use App\Mail\OfferDeclinedMail;
use App\Mail\CounterOfferMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $shop = $user->shop;

        // If the seller doesn't have a shop, handle gracefully
        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to view offers.');
        }

        // Get all product IDs for this shop
        $productIds = $shop->products()->pluck('id');

        // Get all offers for these products with relationships
        $query = Offer::whereIn('product_id', $productIds)
            ->with(['product.media', 'buyer', 'originalOffer', 'counterOffers'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by product
        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        // Filter by offer type
        if ($request->filled('type')) {
            if ($request->type === 'original') {
                $query->where('is_counter_offer', false);
            } elseif ($request->type === 'counter') {
                $query->where('is_counter_offer', true);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('offer_price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('offer_price', '<=', $request->price_max);
        }

        $offers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Get summary statistics
        $allOffers = Offer::whereIn('product_id', $productIds);
        $stats = [
            'total' => (clone $allOffers)->count(),
            'pending' => (clone $allOffers)->where('status', 'pending')->count(),
            'accepted' => (clone $allOffers)->where('status', 'accepted')->count(),
            'declined' => (clone $allOffers)->where('status', 'declined')->count(),
            'expired' => (clone $allOffers)->where('status', 'expired')->count(),
            'total_value' => (clone $allOffers)->sum('offer_price'),
            'avg_value' => (clone $allOffers)->avg('offer_price'),
            'this_month' => (clone $allOffers)->whereMonth('created_at', now()->month)->count(),
        ];

        // Get products for filter dropdown
        $products = $shop->products()->select('id', 'name')->get();

        return view('seller.offers.index', compact('offers', 'stats', 'products'));
    }

    public function show($id)
    {
        $offer = Offer::with(['product.media', 'buyer', 'originalOffer', 'counterOffers'])->findOrFail($id);
        
        // Ensure the offer belongs to the seller's product
        $user = auth()->user();
        $shop = $user->shop;
        
        if (!$shop || !$shop->products()->where('id', $offer->product_id)->exists()) {
            abort(403, 'Unauthorized access to this offer.');
        }

        // Get offer history
        $offerHistory = $offer->getOfferHistory();

        return view('seller.offers.show', compact('offer', 'offerHistory'));
    }

    public function accept($id)
    {
        $offer = Offer::with(['product', 'buyer'])->findOrFail($id);
        
        // Ensure the offer belongs to the seller's product
        $user = auth()->user();
        $shop = $user->shop;
        
        if (!$shop || !$shop->products()->where('id', $offer->product_id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if offer can be accepted
        if (!$offer->canBeAccepted()) {
            return back()->with('warning', 'This offer cannot be accepted.');
        }

        try {
            DB::beginTransaction();

            // Update offer status
            $offer->update(['status' => 'accepted']);

            // Create order from the accepted offer
            $order = $this->createOrderFromOffer($offer);

            DB::commit();

            // Send email notification to buyer
            $emailSent = false;
            try {
                Mail::to($offer->buyer->email)
                    ->send(new OfferAcceptedMail($offer, $order));
                $emailSent = true;
                \Log::info('Offer accepted email sent successfully', [
                    'offer_id' => $offer->id,
                    'buyer_email' => $offer->buyer->email,
                    'seller_id' => $user->id,
                    'order_id' => $order->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send offer accepted email', [
                    'offer_id' => $offer->id,
                    'buyer_email' => $offer->buyer->email,
                    'error' => $e->getMessage()
                ]);
            }

            // Create activity record for the buyer (outside of email try-catch)
            try {
                Activity::create([
                    'user_id' => $offer->buyer->id,
                    'is_read' => false,
                    'description' => 'Your offer of $' . number_format($offer->offer_price, 2) . ' for ' . $offer->product->name . ' has been accepted by ' . $user->name,
                    'type' => \App\Models\Activity::TYPE_OFFER,
                    'related_id' => $offer->id,
                    'related_type' => 'offer'
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create activity record for offer acceptance', [
                    'offer_id' => $offer->id,
                    'buyer_id' => $offer->buyer->id,
                    'error' => $e->getMessage()
                ]);
            }

            $message = 'Offer accepted successfully. Order #' . $order->id . ' has been created. ';
            // Provide a quick Pay Now link you can share with the buyer
            $payUrl = route('pay_now', $order->id);
            $message .= 'Share this Pay Now link with the buyer: ' . $payUrl . '.';
            if ($emailSent) {
                $message .= ' The buyer has been notified via email.';
            } else {
                $message .= ' Email notification failed to send.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to accept offer and create order', [
                'offer_id' => $offer->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to accept offer. Please try again.');
        }
    }

    /**
     * Create an order from an accepted offer
     */
    private function createOrderFromOffer(Offer $offer)
    {
        $buyer = $offer->buyer;
        $product = $offer->product;
        $shop = $product->shop;

        // Create the order
        $order = new Order();
        $order->user_id = $buyer->id;
        $order->shop_id = $shop->id;
        
        // Set customer details
        $order->full_name = $buyer->name;
        $order->email = $buyer->email;
        $order->phone = $buyer->phone ?? 'N/A';
        
        // Set shipping address (use buyer's default address if available, otherwise use basic info)
        $order->shipping_country_id = $buyer->country_id ?? 1; // Default to first country
        $order->shipping_address_1 = $buyer->address ?? 'Address to be provided';
        $order->shipping_address_2 = null;
        $order->shipping_city = $buyer->city ?? 'City to be provided';
        $order->shipping_state = null;
        $order->shipping_postal_code = null;
        
        // Set billing same as shipping for now
        $order->billing_same_as_shipping = true;
        $order->billing_country_id = $order->shipping_country_id;
        $order->billing_address_1 = $order->shipping_address_1;
        $order->billing_address_2 = $order->shipping_address_2;
        $order->billing_city = $order->shipping_city;
        $order->billing_state = $order->shipping_state;
        $order->billing_postal_code = $order->shipping_postal_code;
        
        // Set order details
        $order->shipping_method = 'standard';
        $order->payment_method = 'pending';
        $order->order_notes = "Order created from accepted offer #{$offer->id}";
        
        // Calculate totals
        // Business rule: If seller is accepting a counter offer, bill the original offer amount
        // unless the buyer accepted the counter (handled on buyer side).
        $subtotal = (float) $offer->offer_price;
        if ($offer->is_counter_offer) {
            if (method_exists($offer, 'extractOriginalOfferInfo')) {
                $orig = $offer->extractOriginalOfferInfo();
                if ($orig && isset($orig->offer_price)) {
                    $subtotal = (float) $orig->offer_price;
                }
            }
        }
        $shippingCost = 0; // Default shipping cost, can be calculated based on shipping profile
        $totalAmount = $subtotal + $shippingCost;
        
        $order->subtotal = $subtotal;
        $order->shipping_cost = $shippingCost;
        $order->total_amount = $totalAmount;
        $order->status = 'pending';
        
        $order->save();

        // Create order item
        $orderItem = new OrderItem();
        $orderItem->order_id = $order->id;
        $orderItem->product_id = $product->id;
        $orderItem->product_variation_id = null; // No variation for now
        $orderItem->quantity = 1; // Default to 1 quantity
        $orderItem->price = $offer->offer_price;
        $orderItem->shipping_profile_id = null; // Default shipping profile
        $orderItem->shipping_cost = $shippingCost;
        $orderItem->save();

        // Link the order back to the offer
        $offer->update(['order_id' => $order->id]);

        // Create activity record for the buyer
        Activity::create([
            'user_id' => $buyer->id,
            'is_read' => false,
            'description' => 'You received a new order from ' . $shop->user->name,
            'type' => \App\Models\Activity::TYPE_ORDER,
            'related_id' => $order->id,
            'related_type' => 'order'
        ]);

        return $order;
    }

    public function decline($id, Request $request)
    {
        $offer = Offer::with(['product', 'buyer'])->findOrFail($id);
        
        // Ensure the offer belongs to the seller's product
        $user = auth()->user();
        $shop = $user->shop;
        
        if (!$shop || !$shop->products()->where('id', $offer->product_id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if offer can be declined
        if (!$offer->canBeDeclined()) {
            return back()->with('warning', 'This offer cannot be declined.');
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Update offer status
        $offer->update([
            'status' => 'declined',
            'seller_notes' => $data['reason'] ?? null,
        ]);

        // Send email notification to buyer
        $emailSent = false;
        try {
            Mail::to($offer->buyer->email)
                ->send(new OfferDeclinedMail($offer, $offer->product, $user, $offer->buyer));
            $emailSent = true;
            \Log::info('Offer declined email sent successfully', [
                'offer_id' => $offer->id,
                'buyer_email' => $offer->buyer->email,
                'seller_id' => $user->id
            ]);

            // Create activity record for the buyer
            Activity::create([
                'user_id' => $offer->buyer->id,
                'is_read' => false,
                'description' => 'Your offer of $' . number_format($offer->offer_price, 2) . ' for ' . $offer->product->name . ' has been declined',
                'type' => \App\Models\Activity::TYPE_OFFER,
                'related_id' => $offer->id,
                'related_type' => 'offer'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send offer declined email', [
                'offer_id' => $offer->id,
                'buyer_email' => $offer->buyer->email,
                'error' => $e->getMessage()
            ]);
        }

        $message = 'Offer declined successfully.';
        if ($emailSent) {
            $message .= ' The buyer has been notified via email.';

        } else {
            $message .= ' Email notification failed to send.';
        }

        return back()->with('success', $message);
    }

    public function counterOffer($id, Request $request)
    {
        $offer = Offer::with(['product', 'buyer'])->findOrFail($id);
        
        // Ensure the offer belongs to the seller's product
        $user = auth()->user();
        $shop = $user->shop;
        
        if (!$shop || !$shop->products()->where('id', $offer->product_id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if offer can be countered
        if (!$offer->canBeCountered()) {
            return back()->with('warning', 'This offer cannot be countered.');
        }

        $data = $request->validate([
            'counter_price' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
        ]);

        // Alternative approach: Instead of creating a new offer record,
        // we'll update the existing offer with counter offer details
        // and store the original offer data in the notes
        $originalPrice = $offer->offer_price;
        $originalNotes = $offer->buyer_notes;
        
        // Update the existing offer with counter offer details
        $offer->update([
            'offer_price' => $data['counter_price'],
            'status' => 'pending',
            'is_counter_offer' => true,
            'original_offer_id' => $offer->id, // Self-reference to indicate this is now a counter
            'seller_notes' => $data['message'] ?? null,
            'buyer_notes' => "Original offer: " . get_currency() . " " . number_format($originalPrice, 2) . 
                            ($originalNotes ? " | Original notes: " . $originalNotes : ""),
        ]);

        // Send email notification to buyer
        $emailSent = false;
        try {
            Mail::to($offer->buyer->email)
                ->send(new CounterOfferMail($offer, $offer->product, $user, $offer->buyer));
            $emailSent = true;
            \Log::info('Counter offer email sent successfully', [
                'offer_id' => $offer->id,
                'buyer_email' => $offer->buyer->email,
                'seller_id' => $user->id,
                'counter_price' => $data['counter_price']
            ]);

            // Create activity record for the buyer
            Activity::create([
                'user_id' => $offer->buyer->id,
                'is_read' => false,
                'description' => 'You received a new offer of $' . number_format($offer->offer_price, 2) . ' for ' . $offer->product->name . ' from ' . $offer->buyer->name,
                'type' => \App\Models\Activity::TYPE_OFFER,
                'related_id' => $offer->id,
                'related_type' => 'offer'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send counter offer email', [
                'offer_id' => $offer->id,
                'buyer_email' => $offer->buyer->email,
                'error' => $e->getMessage()
            ]);
        }

        $message = 'Counter offer sent successfully.';
        if ($emailSent) {
            $message .= ' The buyer has been notified via email.';
        } else {
            $message .= ' Email notification failed to send.';
        }

        return back()->with('success', $message);
    }

    public function bulkAction(Request $request)
    {
        // Log the request immediately
        \Log::info('Bulk action endpoint hit', [
            'method' => $request->method(),
            'url' => $request->url(),
            'all_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        try {
            $data = $request->validate([
                'action' => 'required|in:accept,decline,expire',
                'offer_ids' => 'required|array',
                'offer_ids.*' => 'exists:offers,id',
                'reason' => 'nullable|string|max:500',
            ]);

            // Debug logging
            \Log::info('Bulk action request received', [
                'action' => $request->action,
                'offer_ids' => $request->offer_ids,
                'all_data' => $request->all()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Bulk action validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Bulk action unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }

        $user = auth()->user();
        $shop = $user->shop;
        
        if (!$shop) {
            return back()->with('error', 'Shop not found.');
        }

        $productIds = $shop->products()->pluck('id');
        
        // Debug logging
        \Log::info('Processing bulk action', [
            'product_ids' => $productIds,
            'requested_offer_ids' => $data['offer_ids']
        ]);
        
        $offers = Offer::whereIn('id', $data['offer_ids'])
            ->whereIn('product_id', $productIds)
            ->where('status', 'pending')
            ->with(['product', 'buyer'])
            ->get();
            
        \Log::info('Found offers for bulk action', [
            'count' => $offers->count(),
            'offer_ids' => $offers->pluck('id')->toArray()
        ]);

        $processed = 0;
        $emailsSent = 0;
        $ordersCreated = 0;
        
        try {
            DB::beginTransaction();
            
            foreach ($offers as $offer) {
                try {
                    switch ($data['action']) {
                        case 'accept':
                            if ($offer->canBeAccepted()) {
                                // Update offer status
                                $offer->update(['status' => 'accepted']);
                                
                                // Create order from the accepted offer
                                $order = $this->createOrderFromOffer($offer);
                                $ordersCreated++;
                                
                                // Send email notification
                                try {
                                    Mail::to($offer->buyer->email)
                                        ->send(new OfferAcceptedMail($offer, $order));

                                    // Create activity record for the buyer
                                    Activity::create([
                                        'user_id' => $offer->buyer->id,
                                        'is_read' => false,
                                        'description' => "Your offer of $" . number_format($offer->offer_price, 2) . " for " . $offer->product->name . " has been accepted",
                                        'type' => \App\Models\Activity::TYPE_OFFER,
                                        'related_id' => $offer->id,
                                        'related_type' => 'offer'
                                    ]);
                                    $emailsSent++;
                                } catch (\Exception $e) {
                                    \Log::error('Failed to send offer accepted email: ' . $e->getMessage());
                                }
                                
                                $processed++;
                            }
                            break;
                            
                        case 'decline':
                            if ($offer->canBeDeclined()) {
                                $offer->update([
                                    'status' => 'declined',
                                    'seller_notes' => $data['reason'] ?? null,
                                ]);
                                
                                // Send email notification
                                try {
                                    Mail::to($offer->buyer->email)
                                        ->send(new OfferDeclinedMail($offer, $offer->product, $user, $offer->buyer));
                                    $emailsSent++;

                                    // Create activity record for the buyer
                                    Activity::create([
                                        'user_id' => $offer->buyer->id,
                                        'is_read' => false,
                                        'description' => "Your offer of $" . number_format($offer->offer_price, 2) . " for " . $offer->product->name . " has been declined",
                                        'type' => \App\Models\Activity::TYPE_OFFER,
                                        'related_id' => $offer->id,
                                        'related_type' => 'offer'
                                    ]);
                                } catch (\Exception $e) {
                                    \Log::error('Failed to send offer declined email: ' . $e->getMessage());
                                }
                                
                                $processed++;
                            }
                            break;
                            
                        case 'expire':
                            if ($offer->isPending()) {
                                $offer->update(['status' => 'expired']);
                                $processed++;
                            }
                            break;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to process offer in bulk action', [
                        'offer_id' => $offer->id,
                        'action' => $data['action'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to process bulk offer actions', [
                'action' => $data['action'],
                'offer_ids' => $data['offer_ids'],
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to process bulk actions. Please try again.');
        }

        $action = ucfirst($data['action']);
        $message = "{$processed} offers {$data['action']}ed successfully.";
        
        if ($ordersCreated > 0) {
            $message .= " {$ordersCreated} orders created.";
        }
        
        if ($emailsSent > 0) {
            $message .= " {$emailsSent} email notifications sent.";
        }
        
        \Log::info('Bulk action completed', [
            'action' => $data['action'],
            'processed' => $processed,
            'orders_created' => $ordersCreated,
            'emails_sent' => $emailsSent,
            'message' => $message
        ]);
        
        return back()->with('success', $message);
    }

    public function create()
    {
        // Show form to create a new offer
        return view('seller.offers.create');
    }

    public function store(Request $request)
    {
        // Store a new offer
        // ...
        return redirect()->route('seller.offers.index')->with('success', 'Offer created successfully.');
    }

    public function edit($id)
    {
        // Show form to edit an offer
        return view('seller.offers.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Update the offer
        // ...
        return redirect()->route('seller.offers.index')->with('success', 'Offer updated successfully.');
    }

    public function destroy($id)
    {
        // Delete the offer
        // ...
        return redirect()->route('seller.offers.index')->with('success', 'Offer deleted successfully.');
    }

    public function testBulkAction(Request $request)
    {
        return response()->json([
            'message' => 'Bulk action endpoint is accessible',
            'data' => $request->all()
        ]);
    }
} 
