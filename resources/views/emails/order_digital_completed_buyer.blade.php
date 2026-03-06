<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Download Completed - Payment Released</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 620px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 24px; border-radius: 0 0 8px 8px; }
        .muted { color: #6c757d; }
        .card { background: #fff; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; margin-top: 16px; }
        .row { display: flex; justify-content: space-between; margin: 6px 0; }
        .label { color: #6c757d; }
        .value { font-weight: 600; }
        .btn { display: inline-block; background: #0d6efd; color: #fff; padding: 10px 16px; border-radius: 6px; text-decoration: none; margin-top: 12px; }
    </style>
    @php($currency = get_currency())
    @php($shopName = $shop->name ?? 'Seller')
    @php($sellerName = $seller->name ?? 'Seller')
    @php($amount = number_format($order->total_amount ?? 0, 2))
    @php($order->loadMissing(['items.product', 'items.review']))
    @php
        $reviewableItem = $order->items->first(function ($item) {
            $product = optional($item->product);
            $isDigital = strtolower((string) ($product->type ?? '')) === 'digital';

            return $isDigital && !empty($item->downloaded_at) && !$item->review;
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
        <h2 style="margin:0">Digital Download Completed - Payment Released</h2>
        <div class="muted">Order #{{ $order->id }}</div>
    </div>

    <div class="content">
        <p>Hi {{ $buyer->name }},</p>
        <p>Your first download for Order <strong>#{{ $order->id }}</strong> is complete. The order is now marked as completed and payment has been released to <strong>{{ $sellerName }}</strong> ({{ $shopName }}).</p>

        <div class="card">
            <div class="row"><span class="label">Order ID</span><span class="value">#{{ $order->id }}</span></div>
            <div class="row"><span class="label">Seller</span><span class="value">{{ $sellerName }}</span></div>
            <div class="row"><span class="label">Amount</span><span class="value">{{ $currency }} {{ $amount }}</span></div>
            <div class="row"><span class="label">Status</span><span class="value">Completed</span></div>
        </div>

        <p>
            Enjoy your item! When you're ready, please consider leaving a
            @if($reviewUrl)
                <a href="{{ $reviewUrl }}" style="color: #0d6efd; font-weight: 700; text-decoration: underline;">review</a>.
            @else
                <strong style="color: #0d6efd;">review</strong>.
            @endif
        </p>

        <p class="muted" style="margin-top:16px">If you experience any issues with the file, you can contact the seller or reply to this email.</p>
    </div>
</body>
</html>
