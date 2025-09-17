<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispute Created</title>
</head>
<body>
    <h1>Dispute #{{ $dispute->id }} Created</h1>
    <p>Hi {{ $buyer->name }},</p>
    <p>Your dispute for order #{{ $order->id }} with {{ $order->shop->name ?? ($seller->name ?? 'the seller') }} has been submitted successfully.</p>
    <p><strong>Dispute Type:</strong> {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}</p>
    <p>{{ nl2br(e($dispute->description)) }}</p>
    <p>You can follow the conversation and share more evidence at any time.</p>
    <p><a href="{{ route('disputes.show', $dispute->id) }}">View the dispute</a></p>
    <p>Thanks,<br>{{ config('app.name') }} Support</p>
</body>
</html>