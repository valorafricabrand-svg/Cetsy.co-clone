<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Cancelled by Buyer</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .status { background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #f5c2c7; }
        .order-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545; }
        .footer { margin-top:20px; padding-top:20px; border-top:1px solid #e9ecef; font-size:14px; color:#6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Order Cancelled by Buyer</h2>
        <p>Order #{{ $order->id }} was cancelled</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $shopOwner->name }}</strong>,</p>
        <p>The buyer <strong>{{ $buyer->name }}</strong> cancelled order #{{ $order->id }} for <strong>{{ $shop->name }}</strong>.</p>

        @if(!empty($reason))
            <div class="status">
                <strong>Reason from buyer:</strong>
                <div>{{ $reason }}</div>
            </div>
        @endif

        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Placed:</strong> {{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('F j, Y \a\t g:i A') : 'N/A' }}</p>
        </div>

        <p>You can message the buyer if needed to clarify or propose alternatives.</p>
    </div>

    <div class="footer">
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html>

