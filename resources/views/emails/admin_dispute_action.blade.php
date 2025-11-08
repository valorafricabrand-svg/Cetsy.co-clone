<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispute Update</title>
    <style>
        .small { color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Dispute #{{ $dispute->id }} Update</h1>
    <p>Hi {{ $recipient->name }},</p>
    <p>
        Our support team{{ $admin ? ' member '.$admin->name : '' }} has {{ $action }} this dispute for order #{{ $order->id }}.
    </p>
    <p><strong>Decision:</strong> {{ $decisionLabel }}</p>
    @php($outcome = $favorOutcome)
    @if(in_array($outcome, ['Buyer favored','Seller favored']))
        <p>Based on the provided evidence, the case favored the {{ strtolower(str_replace(' favored', '', $outcome)) }}.</p>
    @else
        <p>Outcome: {{ $outcome }}.</p>
    @endif
    @if($dispute->refund_amount)
        <p><strong>Refund Amount:</strong> {{ get_currency() }} {{ number_format((float)$dispute->refund_amount, 2) }}</p>
    @endif
    @if($dispute->resolution)
        <p><strong>Resolution Summary:</strong></p>
        <p>{!! nl2br(e($dispute->resolution)) !!}</p>
    @endif
    <p><a href="{{ route('disputes.show', $dispute->id) }}">View dispute timeline</a></p>
    <p>Thank you,<br>{{ config('app.name') }} Support Team</p>
    <p class="small">You’re receiving this because you are a party to this dispute.</p>
</body>
</html>

