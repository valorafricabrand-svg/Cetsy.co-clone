<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Offer Received</title>
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
        .product-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .offer-details {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🎉 New Offer Received!</h2>
        <p>A buyer has made an offer on your product.</p>
    </div>

<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #333; margin-bottom: 10px;">New Offer Received!</h2>
        <p style="color: #666; font-size: 16px;">A buyer has made an offer on your product.</p>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #333; margin-bottom: 15px;">Product Details</h3>
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            @if($product->media && $product->media->count() > 0)
                <img src="{{ asset('storage/' . $product->media->first()->url) }}" 
                     alt="{{ $product->name }}" 
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
            @endif
            <div>
                <h4 style="margin: 0; color: #333;">{{ $product->name }}</h4>
                <p style="margin: 5px 0; color: #666;">Original Price: {{ get_currency() }} {{ number_format($product->price, 2) }}</p>
            </div>
        </div>
    </div>

    <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2d5a2d; margin-bottom: 15px;">Offer Details</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-weight: bold; color: #333;">Buyer's Offer:</span>
            <span style="font-size: 18px; font-weight: bold; color: #28a745;">{{ get_currency() }} {{ number_format($offer->offer_price, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="color: #666;">Savings:</span>
            <span style="color: #28a745; font-weight: bold;">{{ get_currency() }} {{ number_format($product->price - $offer->offer_price, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #666;">Discount:</span>
            <span style="color: #28a745; font-weight: bold;">{{ number_format((($product->price - $offer->offer_price) / $product->price) * 100, 1) }}%</span>
        </div>
    </div>

    @if($offer->buyer_notes)
    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h4 style="color: #856404; margin-bottom: 10px;">Buyer's Message</h4>
        <p style="color: #856404; margin: 0; font-style: italic;">"{{ $offer->buyer_notes }}"</p>
    </div>
    @endif

    <div style="background: #d1ecf1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h4 style="color: #0c5460; margin-bottom: 10px;">Buyer Information</h4>
        <p style="margin: 5px 0; color: #0c5460;"><strong>Name:</strong> {{ $buyer->name }}</p>
        <p style="margin: 5px 0; color: #0c5460;"><strong>Email:</strong> {{ $buyer->email }}</p>
        <p style="margin: 5px 0; color: #0c5460;"><strong>Offer Date:</strong> {{ $offer->created_at->format('M d, Y H:i') }}</p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('seller.offers.index') }}" 
           style="display: inline-block; background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            View All Offers
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
        <p style="color: #666; font-size: 14px;">
            You can accept, decline, or make a counter offer from your seller dashboard.
        </p>
    </div>
</div>
</body>
</html>