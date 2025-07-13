<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Counter Offer</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ffc107; color: #000; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .product-card { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #dee2e6; }
        .price { font-size: 24px; font-weight: bold; color: #ffc107; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
        .counter-offer-box { background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .comparison { display: flex; justify-content: space-between; margin: 15px 0; }
        .comparison-item { text-align: center; flex: 1; padding: 10px; }
        .original-price { color: #6c757d; text-decoration: line-through; }
        .new-price { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💡 New Counter Offer Available!</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $buyer->name }}</strong>,</p>
            
            <p>The seller has made a counter offer for the following product:</p>
            
            <div class="product-card">
                <h3>{{ $product->name }}</h3>
                <p><strong>Seller:</strong> {{ $seller->name }}</p>
                <p><strong>Date:</strong> {{ now()->format('d M Y, H:i') }}</p>
            </div>
            
            <div class="counter-offer-box">
                <h4>Counter Offer Details</h4>
                
                <div class="comparison">
                    <div class="comparison-item">
                        <h5>Your Original Offer</h5>
                        @php
                            $originalInfo = $counterOffer->extractOriginalOfferInfo();
                        @endphp
                        @if($originalInfo)
                            <p class="original-price">{{ get_currency() }} {{ number_format($originalInfo->offer_price, 2) }}</p>
                        @else
                            <p class="original-price">Original offer not available</p>
                        @endif
                    </div>
                    <div class="comparison-item">
                        <h5>Seller's Counter Offer</h5>
                        <p class="new-price">{{ get_currency() }} {{ number_format($counterOffer->offer_price, 2) }}</p>
                    </div>
                </div>
                
                @if($counterOffer->seller_notes)
                    <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 6px;">
                        <h6>Seller's Message:</h6>
                        <p><em>"{{ $counterOffer->seller_notes }}"</em></p>
                    </div>
                @endif
            </div>
            
            <p>You can now:</p>
            
            <ul>
                <li><strong>Accept</strong> the counter offer if you're happy with the price</li>
                <li><strong>Decline</strong> the counter offer if it doesn't work for you</li>
                <li><strong>Make another counter offer</strong> with your preferred price</li>
                <li><strong>Contact the seller</strong> directly to discuss further</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('listing.show', $product->slug ?? $product->id) }}" class="btn">
                    Respond to Counter Offer
                </a>
            </div>
            
            <p><strong>Note:</strong> Counter offers typically have a limited time window, so please respond promptly to avoid missing out on this opportunity.</p>
            
            <p>Thank you for using our platform!</p>
            
            <p>Best regards,<br>
            <strong>The {{ config('app.name') }} Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email was sent to {{ $buyer->email }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 