<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Received</title>
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
        .payment-success {
            background-color: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
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
        .success {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>💰 Payment Received!</h2>
        <p>Order #{{ $order->id }} - Payment Successful</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

        <p>Excellent news! Payment has been received for order #{{ $order->id }} from your shop <strong>"{{ $shop->name }}"</strong>.</p>

        <div class="payment-success">
            <h3>✅ Payment Confirmed!</h3>
            <p><strong>Payment Status:</strong> <span class="success">Successful</span></p>
            <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
            <p><strong>Transaction ID:</strong> {{ $payment->local_transaction_id }}</p>
            <p><strong>Payment Date:</strong> {{ $payment->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Amount Received:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($payment->total_amount, 2) }}</span></p>
        </div>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Customer:</strong> {{ $buyer->name }}</p>
            <p><strong>Customer Email:</strong> {{ $order->email }}</p>
            <p><strong>Customer Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Order Status:</strong> <span class="success">{{ ucfirst($order->status) }}</span></p>
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
        <ol>
            <li>Process the order and prepare items for shipping</li>
            <li>Update the order status to "Processing"</li>
            <li>Ship the order and provide tracking information</li>
            <li>Update the order status to "Shipped"</li>
        </ol>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-success">
                Process Order
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn">
                View All Orders
            </a>
        </div>

        <p><strong>Important:</strong> The customer has paid for this order. Please process and ship it promptly to maintain good customer satisfaction.</p>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 