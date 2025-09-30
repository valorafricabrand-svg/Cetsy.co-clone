<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tracking Updated</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #17a2b8; color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .info { background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #ffeaa7; }
        .order-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #17a2b8; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .btn-info { background-color: #17a2b8; }
        .label { font-weight: bold; color: #17a2b8; }
        ul { padding-left: 18px; }
        li { margin: 6px 0; }
    </style>
    </head>
<body>
    <div class="header">
        <h2>Tracking Updated</h2>
        <p>Order #{{ $order->id }} - {{ $shop->name }}</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $buyer->name }}</strong>,</p>
        <p>The seller has updated the tracking details for your order <strong>#{{ $order->id }}</strong>.</p>

        <div class="info">
            <h3 style="margin-top:0;">Updated Tracking Information</h3>
            <p><span class="label">Courier:</span> {{ $shippingData['courier'] ?? '—' }}</p>
            <p><span class="label">Tracking Number:</span> {{ $shippingData['tracking_no'] ?? '—' }}</p>
            @if(!empty($shippingData['shipped_at']))
                <p><span class="label">Shipped At:</span> 
                    @if($shippingData['shipped_at'] instanceof \Carbon\Carbon)
                        {{ $shippingData['shipped_at']->format('F j, Y \a\t g:i A') }}
                    @else
                        {{ $shippingData['shipped_at'] }}
                    @endif
                </p>
            @endif
            @if(!empty($shippingData['ship_notes']))
                <p><span class="label">Notes:</span> {{ $shippingData['ship_notes'] }}</p>
            @endif
        </div>

        @if(!empty($changed))
            <h4>What changed:</h4>
            <ul>
                @foreach($changed as $field => $diff)
                    <li>
                        <strong>{{ str_replace('_',' ', ucfirst($field)) }}:</strong>
                        @if($field === 'shipped_at')
                            {{ $diff['old'] ?: '—' }} → {{ $diff['new'] ?: '—' }}
                        @else
                            {{ $diff['old'] !== '' ? $diff['old'] : '—' }} → {{ $diff['new'] !== '' ? $diff['new'] : '—' }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-info">View Order</a>
            <a href="{{ route('account.orders') }}" class="btn">All Orders</a>
        </div>

        <p>If you have questions about these updates, you can reply to the seller from your order details page.</p>
        <p>Thank you for shopping with us.</p>
    </div>
</body>
</html>

