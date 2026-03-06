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
    @php($order->loadMissing(['items.product', 'items.review']))
    @php
        $statusNow = strtolower((string) ($order->status ?? ''));
        $canReviewPhysicalNow = in_array($statusNow, ['delivered', 'completed'], true);
        $canReviewDigitalNow = in_array($statusNow, ['completed', 'delivered'], true);

        $reviewableItem = $order->items->first(function ($item) use ($canReviewPhysicalNow, $canReviewDigitalNow) {
            if ($item->review) {
                return false;
            }

            $product = optional($item->product);
            $isDigital = strtolower((string) ($product->type ?? '')) === 'digital';

            if ($isDigital) {
                return $canReviewDigitalNow && !empty($item->downloaded_at);
            }

            return $canReviewPhysicalNow;
        });

        $reviewUrl = \Illuminate\Support\Facades\Route::has('buyer.orders.show')
            ? route('buyer.orders.show', array_filter([
                'order' => $order->id,
                'review_item' => optional($reviewableItem)->id,
            ]))
            : null;
    @endphp
</head>
<body>
    <div class="header">
        <h2>🎉 Your Order Has Been Delivered!</h2>
        <p>Order #{{ $order->id }} - Successfully Received</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $buyer->name }}</strong>,</p>

        <p>Great news! Your order #{{ $order->id }} from <strong>"{{ $shop->name }}"</strong> has been successfully delivered to your address.</p>

        <div class="delivery-success">
            <h3>✅ Order Delivered Successfully!</h3>
            <p><strong>Delivery Date:</strong> {{ $order->delivered_at ? \Illuminate\Support\Carbon::parse($order->delivered_at)->format('F j, Y \a\t g:i A') : 'N/A' }}</p>
            <p><strong>Order Status:</strong> <span class="success">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="order-details">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Shop:</strong> {{ $shop->name }}</p>
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

        @if($order->courier && $order->tracking_no)
            <h4>Shipping Information:</h4>
            <p><strong>Courier:</strong> {{ $order->courier }}</p>
            <p><strong>Tracking Number:</strong> {{ $order->tracking_no }}</p>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-success">
                View Order Details
            </a>
            <a href="{{ route('account.orders') }}" class="btn">
                View All Orders
            </a>
        </div>

        <p><strong>What you can do now:</strong></p>
        <ol>
            <li>Check your delivered items to ensure everything is as expected</li>
            <li>Leave a review for the seller and products if you're satisfied</li>
            <li>Contact the seller if you have any issues with your order</li>
            <li>Consider purchasing from the same shop again</li>
        </ol>

        <p><strong>Review Your Purchase:</strong></p>
        <p>
            Your feedback helps other customers make informed decisions and helps sellers improve their products and service.
            Consider leaving a
            @if($reviewUrl)
                <a href="{{ $reviewUrl }}" style="color: #28a745; font-weight: 700; text-decoration: underline;">review</a>
            @else
                <strong style="color: #28a745;">review</strong>
            @endif
            for your experience!
        </p>

        <p><strong>Need help?</strong> If you have any questions about your delivered order or need to report an issue, you can:</p>
        <ul>
            <li>Contact the seller directly through the messaging system</li>
            <li>Reach out to our customer support team</li>
            <li>Check our help center for common questions</li>
        </ul>

        <p><strong>Thank you for shopping with us!</strong> We hope you're satisfied with your purchase.</p>
    </div>

    <div class="footer">
        <p>Thank you for shopping with us!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 
