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
        <h2>🚚 Order Shipped!</h2>
        <p>Order #{{ $order->id }} - Successfully Shipped</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

        <p>Great job! You have successfully shipped order #{{ $order->id }} from your shop <strong>"{{ $shop->name }}"</strong>.</p>

        <div class="shipping-success">
            <h3>✅ Order Shipped Successfully!</h3>
            <p><strong>Shipping Date:</strong> {{ $order->shipped_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Order Status:</strong> <span class="success">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="tracking-info">
            <h4>📦 Shipping Information:</h4>
            <p><strong>Courier:</strong> {{ $shippingData['courier'] }}</p>
            <p><strong>Tracking Number:</strong> <span class="highlight">{{ $shippingData['tracking_no'] }}</span></p>
            @if(isset($shippingData['ship_notes']) && $shippingData['ship_notes'])
                <p><strong>Shipping Notes:</strong> {{ $shippingData['ship_notes'] }}</p>
            @endif
        </div>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Customer:</strong> {{ $buyer->name }}</p>
            <p><strong>Customer Email:</strong> {{ $order->email }}</p>
            <p><strong>Customer Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <h4>Order Items:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->name }}</strong> - Qty: {{ $item->quantity }} - Price: {{ get_currency() }} {{ number_format($item->price, 2) }}</li>
            @endforeach
        </ul>

        <h4>Shipping Address:</h4>
        <p><strong>Address:</strong> {{ $order->shipping_address_1 }}</p>
        @if($order->shipping_address_2)
            <p><strong>Address 2:</strong> {{ $order->shipping_address_2 }}</p>
        @endif
        <p><strong>City:</strong> {{ $order->shipping_city }}</p>
        @if($order->shipping_state)
            <p><strong>State:</strong> {{ $order->shipping_state }}</p>
        @endif
        @if($order->shipping_postal_code)
            <p><strong>Postal Code:</strong> {{ $order->shipping_postal_code }}</p>
        @endif

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>The customer has been notified that their order has been shipped</li>
            <li>Monitor the tracking information for delivery updates</li>
            <li>Once delivered, the customer can mark the order as received</li>
            <li>Follow up with the customer if needed</li>
        </ol>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-info">
                View Order Details
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn">
                View All Orders
            </a>
        </div>

        <p><strong>Important:</strong> The customer will be able to track their package using the tracking number provided. Make sure to respond promptly if they have any questions about the shipment.</p>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 