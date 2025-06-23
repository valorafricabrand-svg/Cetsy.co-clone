<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Added to Wishlist</title>
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
        <p>Your product has been added to someone's wishlist!</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

        <p>We're excited to let you know that your product <strong>"{{ $product->name }}"</strong> has been added to a customer's wishlist!</p>

        <div class="product-info">
            <h3>Product Details:</h3>
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

        <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn">View Product</a>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 