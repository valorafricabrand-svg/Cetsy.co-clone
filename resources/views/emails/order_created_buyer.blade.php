<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
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
            background-color: #007bff;
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
            border-left: 4px solid #007bff;
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
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn-primary {
            background-color: #007bff;
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
        <h2>✅ Order Confirmed!</h2>
        <p>Order #{{ $order->id }} - Payment Required</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $buyer->name }}</strong>,</p>

        <p>Thank you for your order! Your order has been successfully created and is now pending payment.</p>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Shop:</strong> {{ $shop->name }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Order Status:</strong> <span class="warning">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="payment-notice">
            <h4>💳 Payment Required:</h4>
            <p><strong>To complete your order, please make the payment now.</strong></p>
            <p><strong>Total Amount:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span></p>
            <p>Your order will be processed and shipped once payment is confirmed.</p>
        </div>

        <h4>Order Items:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->name }}</strong> - Qty: {{ $item->quantity }} - Price: {{ get_currency() }} {{ number_format($item->price, 2) }}</li>
            @endforeach
        </ul>

        <h4>Order Summary:</h4>
        <p><strong>Subtotal:</strong> {{ get_currency() }} {{ number_format($order->subtotal, 2) }}</p>
        <p><strong>Shipping Cost:</strong> {{ get_currency() }} {{ number_format($order->shipping_cost, 2) }}</p>
        <p><strong>Total:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span></p>

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

        @if($order->order_notes)
            <h4>Order Notes:</h4>
            <p>{{ $order->order_notes }}</p>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('pay_now', $order->id) }}" class="btn">
                Pay Now
            </a>
            <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-primary">
                View Order Details
            </a>
        </div>

        <p><strong>What happens next?</strong></p>
        <ol>
            <li>Complete your payment using the "Pay Now" button above</li>
            <li>Once payment is confirmed, the seller will be notified</li>
            <li>The seller will process and ship your order</li>
            <li>You'll receive tracking information when your order ships</li>
        </ol>

        <p><strong>Need help?</strong> If you have any questions about your order or payment, please don't hesitate to contact us.</p>
    </div>

    <div class="footer">
        <p>Thank you for shopping with us!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 