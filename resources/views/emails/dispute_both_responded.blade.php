<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispute Update</title>
</head>
<body>
    <h1>Dispute #{{ $dispute->id }} Update</h1>
    <p>Hi {{ $recipient->name }},</p>
    <p>You and {{ $otherParty->name }} have both replied to dispute #{{ $dispute->id }} for order #{{ $order->id }}. Our team now has responses from each side.</p>
    <p>Please continue sharing updates and evidence through the dispute thread so we can resolve the issue quickly.</p>
    <p><a href="{{ route('disputes.show', $dispute->id) }}">Open the dispute conversation</a></p>
    <p>Regards,<br>{{ config('app.name') }} Support</p>
</body>
</html>
