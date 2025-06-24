<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Order Received</title>
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
        .order-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .payment-notice {
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
        .warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🛒 New Order Received!</h2>
        <p>Order #{{ $order->id }} - Pending Payment</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

        <p>Great news! You've received a new order for your shop <strong>"{{ $shop->name }}"</strong>.</p>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Customer:</strong> {{ $buyer->name }}</p>
            <p><strong>Customer Email:</strong> {{ $order->email }}</p>
            <p><strong>Customer Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Order Status:</strong> <span class="warning">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="payment-notice">
            <h4>⚠️ Payment Status:</h4>
            <p><strong>This order is currently pending payment.</strong></p>
            <p>The customer will need to complete the payment before you can process and ship the order.</p>
            <p><strong>Total Amount:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span></p>
        </div>

        <h4>Order Items:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->name }}</strong> - Qty: {{ $item->quantity }} - Price: {{ get_currency() }} {{ number_format($item->price, 2) }}</li>
            @endforeach
        </ul>

        <h4>Shipping Information:</h4>
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

        @if($order->order_notes)
            <h4>Order Notes:</h4>
            <p>{{ $order->order_notes }}</p>
        @endif

        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Wait for the customer to complete payment</li>
            <li>Once payment is confirmed, you can process the order</li>
            <li>Prepare the items for shipping</li>
            <li>Update the order status when shipped</li>
        </ul>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-success">
                View Order Details
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn">
                View All Orders
            </a>
        </div>

        <p><strong>Important:</strong> Do not ship the order until payment has been confirmed. You will receive another notification when the payment is completed.</p>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 