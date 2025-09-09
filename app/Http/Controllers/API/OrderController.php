<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use App\Models\ShippingProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * List authenticated user's recent orders with items summary.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orders = \App\Models\Order::with(['items.product:id,name'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, [
                'id','status','total_amount','subtotal','created_at','shop_id','payment_method'
            ]);

        return response()->json($orders);
    }
    /**
     * Create an order from a cart payload.
     *
     * Expected JSON:
     * {
     *   items: [
     *     { product_id, qty, variant_id?, shipping_profile_id? }
     *   ],
     *   shipping: {
     *     full_name, email, phone,
     *     country_id, address_1, address_2?, city, state?, postal_code?
     *   },
     *   payment_method: "cod" | "mpesa" | "paypal"
     * }
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.variant_id' => 'nullable|exists:variants,id',
            'items.*.shipping_profile_id' => 'nullable|exists:shipping_profiles,id',

            'shipping.full_name' => 'required|string|max:255',
            'shipping.email' => 'required|email',
            'shipping.phone' => 'required|string|max:30',
            'shipping.country_id' => 'required|integer',
            'shipping.address_1' => 'required|string|max:255',
            'shipping.address_2' => 'nullable|string|max:255',
            'shipping.city' => 'required|string|max:120',
            'shipping.state' => 'nullable|string|max:120',
            'shipping.postal_code' => 'nullable|string|max:30',

            'payment_method' => 'required|string|in:cod,mpesa,paypal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        return DB::transaction(function () use ($payload, $user) {
            $subtotal = 0.0;
            $shippingTotal = 0.0;

            // Infer a shop from the first product (single-shop order assumption)
            $firstProduct = Product::findOrFail($payload['items'][0]['product_id']);
            $shopId = $firstProduct->shop_id;

            $order = Order::create([
                'user_id' => $user->id,
                'shop_id' => $shopId,
                'full_name' => $payload['shipping']['full_name'],
                'email' => $payload['shipping']['email'],
                'phone' => $payload['shipping']['phone'],
                'shipping_country_id' => $payload['shipping']['country_id'],
                'shipping_address_1' => $payload['shipping']['address_1'],
                'shipping_address_2' => $payload['shipping']['address_2'] ?? null,
                'shipping_city' => $payload['shipping']['city'],
                'shipping_state' => $payload['shipping']['state'] ?? null,
                'shipping_postal_code' => $payload['shipping']['postal_code'] ?? null,
                'billing_same_as_shipping' => true,
                'billing_country_id' => $payload['shipping']['country_id'],
                'billing_address_1' => $payload['shipping']['address_1'],
                'billing_address_2' => $payload['shipping']['address_2'] ?? null,
                'billing_city' => $payload['shipping']['city'],
                'billing_state' => $payload['shipping']['state'] ?? null,
                'billing_postal_code' => $payload['shipping']['postal_code'] ?? null,
                'shipping_method' => 'standard',
                'payment_method' => $payload['payment_method'],
                'subtotal' => 0,
                'total_amount' => 0,
                'tax_amount' => 0,
                'status' => Order::STATUS_PENDING,
            ]);

            foreach ($payload['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['qty'];

                $unit = (float) ($product->discount_price ?? $product->price);
                $variationSummary = null;
                if (!empty($item['variant_id'])) {
                    $variant = Variant::with('options')->findOrFail($item['variant_id']);
                    $unit = (float) ($variant->price ?? $unit);
                    $variationSummary = $variant->options->pluck('value')->implode(' / ');
                }

                $line = $unit * $qty;
                $subtotal += $line;

                $spId = $item['shipping_profile_id'] ?? null;
                $shipCost = 0.0;
                if ($spId) {
                    $sp = ShippingProfile::find($spId);
                    if ($sp) {
                        $shipCost = (float) $sp->base_rate * $qty;
                    }
                }
                $shippingTotal += $shipCost;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $unit,
                    'shipping_profile_id' => $spId,
                    'shipping_cost' => $shipCost,
                    'variation_summary' => $variationSummary,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal + $shippingTotal,
            ]);

            return response()->json([
                'message' => 'Order created',
                'order_id' => $order->id,
                'subtotal' => $subtotal,
                'shipping' => $shippingTotal,
                'total' => $order->total_amount,
                'status' => $order->status,
            ], 201);
        });
    }
}
