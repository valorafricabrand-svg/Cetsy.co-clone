<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Offer Declined</title>
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
            background-color: #dc3545;
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
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .offer-details h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .offer-details p {
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>❌ Your Counter Offer Has Been Declined</h2>
        <p>A buyer has declined your counter offer.</p>
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #dc3545; margin-bottom: 10px;">❌ Counter Offer Declined</h2>
        <p style="color: #666; font-size: 16px;">A buyer has declined your counter offer.</p>
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
                <strong style="color: #666;">Counter Offer Price:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #dc3545;">{{ $offer->formatted_price }}</div>
            </div>
            <div>
                <strong style="color: #666;">Original Price:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #333;">{{ get_currency() }} {{ number_format($offer->product->price, 2) }}</div>
            </div>
        </div>

        @if($offer->buyer_notes)
            <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 15px;">
                <strong style="color: #666;">Buyer's Message:</strong>
                <p style="margin: 5px 0; color: #333;">{{ $offer->buyer_notes }}</p>
            </div>
        @endif
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('seller.offers.index') }}" 
           style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            View All Offers
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;">
        <p>Don't worry! You can still negotiate with other buyers or adjust your pricing strategy.</p>
        <p>Consider reviewing your pricing or making a new counter offer to other interested buyers.</p>
    </div>
</div>
</body>
</html>