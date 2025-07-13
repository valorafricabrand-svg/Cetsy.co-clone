<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer Accepted</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .product-card { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #dee2e6; }
        .price { font-size: 24px; font-weight: bold; color: #28a745; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Your Offer Has Been Accepted!</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $buyer->name }}</strong>,</p>
            
            <p>Great news! The seller has accepted your offer for the following product:</p>
            
            <div class="product-card">
                <h3>{{ $product->name }}</h3>
                <p><strong>Your Offer:</strong> <span class="price">{{ get_currency() }} {{ number_format($offer->offer_price, 2) }}</span></p>
                <p><strong>Seller:</strong> {{ $seller->name }}</p>
                <p><strong>Date Accepted:</strong> {{ now()->format('d M Y, H:i') }}</p>
            </div>
            
            <p>Your offer has been accepted and the seller is ready to proceed with the transaction. You can now:</p>
            
            <ul>
                <li>Contact the seller to arrange payment and delivery</li>
                <li>Review the product details and seller information</li>
                <li>Complete the transaction through the platform</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('listing.show', $product->slug ?? $product->id) }}" class="btn">
                    View Product Details
                </a>
            </div>
            
            <p>If you have any questions about this transaction, please don't hesitate to contact our support team.</p>
            
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