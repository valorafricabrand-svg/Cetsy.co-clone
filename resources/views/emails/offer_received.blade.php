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
        .offer-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .price-comparison {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
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
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
        .highlight {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>💰 New Offer Received!</h2>
        <p>A customer has made an offer on your product</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

        <p>Great news! You've received a new offer for your product <strong>"{{ $product->name }}"</strong>.</p>

        <div class="offer-details">
            <h3>Offer Details:</h3>
            <p><strong>Product:</strong> {{ $product->name }}</p>
            <p><strong>Buyer:</strong> {{ $buyer->name }}</p>
            <p><strong>Offer Amount:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($offer->offer_price, 2) }}</span></p>
            <p><strong>Date Received:</strong> {{ $offer->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <div class="price-comparison">
            <h4>Price Comparison:</h4>
            <p><strong>Your Price:</strong> {{ get_currency() }} {{ number_format($product->price, 2) }}</p>
            <p><strong>Offer Price:</strong> {{ get_currency() }} {{ number_format($offer->offer_price, 2) }}</p>
            <p><strong>Difference:</strong> 
                @php
                    $difference = $product->price - $offer->offer_price;
                    $percentage = ($difference / $product->price) * 100;
                @endphp
                {{ get_currency() }} {{ number_format($difference, 2) }} 
                ({{ number_format($percentage, 1) }}% {{ $difference > 0 ? 'lower' : 'higher' }})
            </p>
        </div>

        <p>You can now:</p>
        <ul>
            <li>Accept the offer to proceed with the sale</li>
            <li>Decline the offer if it doesn't meet your expectations</li>
            <li>Counter-offer with a different price</li>
            <li>Contact the buyer to discuss the offer</li>
        </ul>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('seller.offers.show', $offer->id) }}" class="btn btn-success">
                View Offer Details
            </a>
            <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn">
                View Product
            </a>
        </div>

        <p><strong>Quick Tip:</strong> Responding quickly to offers can increase your chances of making a sale. Most buyers appreciate prompt responses!</p>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 