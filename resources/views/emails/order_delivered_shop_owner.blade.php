<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Delivered</title>
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
        .delivery-success {
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
        <h2>🎉 Order Delivered!</h2>
        <p>Order #{{ $order->id }} - Successfully Delivered</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>

        <p>Excellent news! Order #{{ $order->id }} from your shop <strong>"{{ $shop->name }}"</strong> has been successfully delivered to the customer.</p>

        <div class="delivery-success">
            <h3>✅ Order Delivered Successfully!</h3>
            <p><strong>Delivery Date:</strong> {{ $order->delivered_at ? \Illuminate\Support\Carbon::parse($order->delivered_at)->format('F j, Y \a\t g:i A') : 'N/A' }}</p>
            <p><strong>Order Status:</strong> <span class="success">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Customer:</strong> {{ $buyer->name }}</p>
            <p><strong>Customer Email:</strong> {{ $order->email }}</p>
            <p><strong>Customer Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('F j, Y \a\t g:i A') : 'N/A' }}</p>
            <p><strong>Shipped Date:</strong> {{ $order->shipped_at ? \Illuminate\Support\Carbon::parse($order->shipped_at)->format('F j, Y \a\t g:i A') : 'N/A' }}</p>
        </div>

        <h4>Order Items:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->name }}</strong> - Qty: {{ $item->quantity }} - Price: {{ get_currency() }} {{ number_format($item->price, 2) }}</li>
            @endforeach
        </ul>

        <h4>Delivery Address:</h4>
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

        @if($order->courier && $order->tracking_no)
            <h4>Shipping Information:</h4>
            <p><strong>Courier:</strong> {{ $order->courier }}</p>
            <p><strong>Tracking Number:</strong> {{ $order->tracking_no }}</p>
        @endif

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>The customer has been notified of the successful delivery</li>
            <li>The customer can now leave a review for your products</li>
            <li>Monitor for any customer feedback or issues</li>
            <li>Consider following up with the customer for future business</li>
        </ol>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-success">
                View Order Details
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn">
                View All Orders
            </a>
        </div>

        <p><strong>Congratulations!</strong> You have successfully completed another order. This helps build your reputation and customer trust.</p>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 