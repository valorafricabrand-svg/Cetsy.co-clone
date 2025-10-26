<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Added to Favorites</title>
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
            background-color: #f8f9fa;
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
        .product-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🎉 Great News!</h2>
        <p>Your product has been added to someone's Favorites!</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

            <p><strong>{{ $wishlister->name }}</strong> favorited your item <strong>“{{ $product->name }}”</strong>.</p>

        <div class="product-info">
            <h3>Product Details:</h3>
            @if(method_exists($product, 'media') && $product->media && $product->media->count() > 0)
                <div style="margin:8px 0 12px 0;">
                    <img src="{{ $product->media->first()->getUrl() }}" alt="{{ $product->name }}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e9ecef;">
                </div>
            @endif
            <p><strong>Product Name:</strong> {{ $product->name }}</p>
            <p><strong>Price:</strong> {{ get_currency() }} {{ number_format($product->price, 2) }}</p>
            <p><strong>Added by:</strong> {{ $wishlister->name }}</p>
            <p><strong>Date Added:</strong> {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <p>This is a great opportunity to:</p>
        <ul>
            <li>Consider offering a special discount to encourage purchase</li>
            <li>Update your product listing with better images or descriptions</li>
            <li>Check if your inventory is sufficient</li>
            <li>Engage with potential customers</li>
        </ul>

        @php
            $prefill = "Hi {$wishlister->name}, thanks for favoriting ‘{$product->name}’. Can I answer any questions or offer help?";
            $conversationId = ($product->id ?? '0') . '-' . ($wishlister->id ?? '0');
            $messageUrl = route('seller.messages.show', $conversationId) . '?prefill=' . urlencode($prefill);
        @endphp
        <a href="{{ $messageUrl }}" class="btn">Message {{ $wishlister->name }}</a>
        <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn" style="background-color:#6c63ff;">View Product</a>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 
