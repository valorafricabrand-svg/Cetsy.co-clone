<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Dispute Response</title>
</head>
<body>
    <h1>Update on Dispute #{{ $dispute->id }}</h1>
    <p>Hi {{ $initiator->name }},</p>
    <p>{{ $responder->name }} just replied to dispute #{{ $dispute->id }} for order #{{ $order->id }}{{ $order->shop ? ' with ' . $order->shop->name : '' }}.</p>
    <p><strong>Latest message:</strong></p>
    <p>{!! $messageModel->message !!}</p>
    <p>You can reply or upload additional evidence from the dispute dashboard.</p>
    <p><a href="{{ route('disputes.show', $dispute->id) }}">Open the dispute conversation</a></p>
    <p>Thanks,<br>{{ config('app.name') }} Support</p>
</body>
</html>
