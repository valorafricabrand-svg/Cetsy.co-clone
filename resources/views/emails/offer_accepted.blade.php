<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Offer Accepted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;                          
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .offer-details {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .offer-details img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        .offer-details h4 {
            margin: 0;
            color: #333;
        }
        .offer-details p {
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🎉 Your Offer Has Been Accepted!</h2>
        <p>Great news! The seller has accepted your offer.</p>
    </div>

<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #28a745; margin-bottom: 10px;">🎉 Your Offer Has Been Accepted!</h2>
        <p style="color: #666; font-size: 16px;">Great news! The seller has accepted your offer.</p>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #333; margin-bottom: 15px;">Offer Details</h3>
        
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            @if($offer->product->media && $offer->product->media->count() > 0)
                <img src="{{ $offer->product->media->first()->getUrl() }}" 
                     alt="{{ $offer->product->name }}" 
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
            @endif
            <div>
                <h4 style="margin: 0; color: #333;">{{ $offer->product->name }}</h4>
                <p style="margin: 5px 0; color: #666;">{{ $offer->product->shop->name ?? 'Unknown Shop' }}</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <strong style="color: #666;">Accepted Price:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #28a745;">{{ $offer->formatted_price }}</div>
            </div>
            <div>
                <strong style="color: #666;">Original Price:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #333;">{{ get_currency() }} {{ number_format($offer->product->price, 2) }}</div>
            </div>
        </div>

        @if($offer->buyer_notes)
            <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 15px;">
                <strong style="color: #666;">Your Message:</strong>
                <p style="margin: 5px 0; color: #333;">{{ $offer->buyer_notes }}</p>
            </div>
        @endif
    </div>

    @if($order)
    <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
        <h3 style="color: #28a745; margin-bottom: 15px;">📦 Order Created</h3>
        
        <div style="margin-bottom: 15px;">
            <strong style="color: #333;">Order Number:</strong>
            <div style="font-size: 16px; font-weight: bold; color: #28a745;">#{{ $order->id }}</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <strong style="color: #666;">Subtotal:</strong>
                <div style="font-size: 16px; font-weight: bold; color: #333;">{{ get_currency() }} {{ number_format($order->subtotal, 2) }}</div>
            </div>
            <div>
                <strong style="color: #666;">Shipping:</strong>
                <div style="font-size: 16px; font-weight: bold; color: #333;">{{ get_currency() }} {{ number_format($order->shipping_cost, 2) }}</div>
            </div>
        </div>

        <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 15px;">
            <strong style="color: #333;">Total Amount:</strong>
            <div style="font-size: 20px; font-weight: bold; color: #28a745;">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</div>
        </div>
    </div>
    @endif

    <div style="text-align: center; margin-top: 30px;">
        @if($order)
            <a href="{{ route('pay_now', $order->id) }}" 
               style="background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin-right: 10px;">
                💳 Pay Now
            </a>
            <a href="{{ route('buyer.orders.show', $order->id) }}" 
               style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                📋 View Order
            </a>
        @else
            <a href="{{ route('buyer.offers') }}" 
               style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                View Offer Details
            </a>
        @endif
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;">
        @if($order)
            <p><strong>Next Steps:</strong></p>
            <ol style="text-align: left; max-width: 400px; margin: 0 auto;">
                <li>Click "Pay Now" to complete your payment</li>
                <li>Once payment is confirmed, the seller will process your order</li>
                <li>You'll receive tracking information when your order ships</li>
            </ol>
        @else
            <p>This offer has been accepted and is ready for processing.</p>
            <p>Please contact the seller to arrange the transaction details.</p>
        @endif
    </div>
</div>
</body>
</html>
