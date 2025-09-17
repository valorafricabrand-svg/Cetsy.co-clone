<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Dispute Opened</title>
</head>
<body>
    <h1>New Dispute #{{ $dispute->id }}</h1>
    <p>Hi {{ $seller->name ?? ($order->shop->name ?? 'Seller') }},</p>
    <p>{{ $buyer->name }} has opened a dispute for order #{{ $order->id }} involving your shop {{ $order->shop->name ?? '' }}.</p>
    <p><strong>Dispute Type:</strong> {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}</p>
    <p><strong>Buyer Message:</strong></p>
    <p>{!! nl2br(e($dispute->description)) !!}</p>
    <p>Please respond promptly in the dispute thread to share your side and any evidence.</p>
    <p><a href="{{ route('disputes.show', $dispute->id) }}">Review the dispute</a></p>
    <p>Thank you,<br>{{ config('app.name') }} Support</p>
</body>
</html>
