<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispute Closed</title>
</head>
<body>
    <h1>Dispute #{{ $dispute->id }} Closed</h1>
    <p>Hi {{ $recipient->name }},</p>
    <p>The dispute for order #{{ $order->id }} has been closed by {{ $closedBy->name ?? 'our support team' }} on {{ optional($dispute->closed_at)->format('M d, Y \a\t g:i A') ?? now()->format('M d, Y') }}.</p>
    <p><strong>Resolution Summary:</strong></p>
    <p>{!! nl2br(e($dispute->resolution ?? 'No additional notes were provided.')) !!}</p>
    <p>If you require any follow-up, please reach out to support and reference dispute #{{ $dispute->id }}.</p>
    <p><a href="{{ route('disputes.show', $dispute->id) }}">View dispute timeline</a></p>
    <p>Thank you,<br>{{ config('app.name') }} Support Team</p>
</body>
</html>
