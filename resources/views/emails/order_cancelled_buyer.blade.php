<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Cancelled</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .status { background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #f5c2c7; }
        .order-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545; }
        .btn { display:inline-block; padding:10px 20px; background-color:#007bff; color:#fff; text-decoration:none; border-radius:5px; margin-top:15px; }
        .footer { margin-top:20px; padding-top:20px; border-top:1px solid #e9ecef; font-size:14px; color:#6c757d; }
    </style>
    </head>
<body>
    <div class="header">
        <h2>Order Cancelled</h2>
        <p>Order #{{ $order->id }} has been cancelled</p>
    </div>
    <div class="content">
        <p>Hello <strong>{{ $buyer->name }}</strong>,</p>
        <p>Your order #{{ $order->id }} from <strong>{{ $shop->name }}</strong> has been cancelled.</p>
        @if(!empty($reason))
            <div class="status">
                <strong>Reason provided:</strong>
                <div>{{ $reason }}</div>
            </div>
        @endif
        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Placed:</strong> {{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('F j, Y \a\t g:i A') : 'N/A' }}</p>
        </div>
        <div style="text-align:center; margin: 20px 0;">
            <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn">View Order</a>
            <a href="{{ route('account.orders') }}" class="btn" style="background:#6c757d;">Your Orders</a>
        </div>
        <p>If you didn’t intend to cancel this order or have questions, please contact the seller via messages or reach out to support.</p>
    </div>
    <div class="footer">
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html>

