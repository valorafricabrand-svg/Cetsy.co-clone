<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Added to Your Favorites!</title>
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
        <span class="icon">💚</span>
        <h2>Added to Your Favorites!</h2>
        <div style="font-size:1.1rem;opacity:0.95;">You just favorited a product on Cetsy!</div>
    </div>

    <div class="content">
        <div class="congrats">Way to go, {{ $buyer->name }}! 🎉</div>
        <div>You've just added <strong>"{{ $product->name }}"</strong> to your favorites. Keep track of all the products you love in one place.</div>

        <div class="product-info">
            <h3>Product Details</h3>
            <p><strong>Product Name:</strong> {{ $product->name }}</p>
            <p><strong>Price:</strong> {{ get_currency() }} {{ number_format($product->price, 2) }}</p>
            <p><strong>Seller:</strong> {{ $seller->name }}</p>
            <p><strong>Date Added:</strong> {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn">View Product</a>
    </div>

    <div class="footer">
        <p>Thank you for being part of our green community! 🌱</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html>
