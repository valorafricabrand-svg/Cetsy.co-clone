<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Shipped</title>
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
            background-color: #17a2b8;
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
        .shipping-success {
            background-color: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #bee5eb;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #17a2b8;
        }
        .tracking-info {
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
        .btn-info {
            background-color: #17a2b8;
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
            color: #17a2b8;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🚚 Your Order Has Been Shipped!</h2>
        <p>Order #{{ $order->id }} - On Its Way to You</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $buyer->name }}</strong>,</p>

        <p>Exciting news! Your order #{{ $order->id }} from <strong>"{{ $shop->name }}"</strong> has been shipped and is on its way to you.</p>

        <div class="shipping-success">
            <h3>✅ Order Shipped Successfully!</h3>
            <p><strong>Shipping Date:</strong> {{ $order->shipped_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Order Status:</strong> <span class="success">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="tracking-info">
            <h4>📦 Tracking Information:</h4>
            <p><strong>Courier:</strong> {{ $shippingData['courier'] }}</p>
            <p><strong>Tracking Number:</strong> <span class="highlight">{{ $shippingData['tracking_no'] }}</span></p>
            @if(isset($shippingData['ship_notes']) && $shippingData['ship_notes'])
                <p><strong>Shipping Notes:</strong> {{ $shippingData['ship_notes'] }}</p>
            @endif
        </div>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Shop:</strong> {{ $shop->name }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <h4>Order Items:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->name }}</strong> - Qty: {{ $item->quantity }} - Price: {{ get_currency() }} {{ number_format($item->price, 2) }}</li>
            @endforeach
        </ul>

        <h4>Shipping Address:</h4>
        <p>{{ $order->shipping_address_1 }}</p>
        @if($order->shipping_address_2)
            <p>{{ $order->shipping_address_2 }}</p>
        @endif
        <p>{{ $order->shipping_city }}</p>
        @if($order->shipping_state)
            <p>{{ $order->shipping_state }}</p>
        @endif
        @if($order->shipping_postal_code)
            <p>{{ $order->shipping_postal_code }}</p>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-info">
                Track Your Order
            </a>
            <a href="{{ route('account.orders') }}" class="btn">
                View All Orders
            </a>
        </div>

        <p><strong>What happens next?</strong></p>
        <ol>
            <li>Use the tracking number above to monitor your package</li>
            <li>Your package will be delivered to your shipping address</li>
            <li>Once received, you can mark the order as delivered</li>
            <li>Leave a review for the seller if you're satisfied</li>
        </ol>

        <p><strong>Estimated Delivery:</strong></p>
        <ul>
            <li>Local delivery: 1-3 business days</li>
            <li>National delivery: 3-7 business days</li>
            <li>International delivery: 7-14 business days</li>
        </ul>

        <p><strong>Need help?</strong> If you have any questions about your shipment or need to contact the seller, you can use the messaging system in your order details.</p>

        <p><strong>Important:</strong> Please ensure someone is available to receive the package at your shipping address during business hours.</p>
    </div>

    <div class="footer">
        <p>Thank you for shopping with us!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 