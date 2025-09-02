<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Completed — Payment Released</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 620px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 24px; border-radius: 0 0 8px 8px; }
        .muted { color: #6c757d; }
        .card { background: #fff; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; margin-top: 16px; }
        .row { display: flex; justify-content: space-between; margin: 6px 0; }
        .label { color: #6c757d; }
        .value { font-weight: 600; }
    </style>
    @php($currency = get_currency())
    @php($shopName = $shop->name ?? 'Seller')
    @php($sellerName = $seller->name ?? 'Seller')
    @php($amount = number_format($order->total_amount ?? 0, 2))
</head>
<body>
    <div class="header">
        <h2 style="margin:0">Order Completed — Payment Released</h2>
        <div class="muted">Order #{{ $order->id }}</div>
    </div>

    <div class="content">
        <p>Hi {{ $buyer->name }},</p>
        <p>We’ve marked your order <strong>#{{ $order->id }}</strong> as completed and released payment to <strong>{{ $sellerName }}</strong> ({{ $shopName }}).
           This happens automatically after a brief grace period following shipment.</p>

        <div class="card">
            <div class="row"><span class="label">Order ID</span><span class="value">#{{ $order->id }}</span></div>
            <div class="row"><span class="label">Seller</span><span class="value">{{ $sellerName }}</span></div>
            <div class="row"><span class="label">Amount</span><span class="value">{{ $currency }} {{ $amount }}</span></div>
            <div class="row"><span class="label">Status</span><span class="value">Completed</span></div>
        </div>

        <p class="muted" style="margin-top:16px">If there’s an issue with your order, you can still reach out to the seller or contact support.</p>
        <p>Thanks for shopping on {{ config('app.name') }}!</p>
    </div>
</body>
</html>

