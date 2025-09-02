<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payout Rejected</title>
  <style>body{font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#333;padding:20px}.header{background:#dc3545;color:#fff;padding:16px;border-radius:8px 8px 0 0}.content{background:#f8f9fa;padding:18px;border-radius:0 0 8px 8px}</style>
</head>
<body>
  <div class="header"><h2 style="margin:0">Payout Rejected</h2></div>
  <div class="content">
    <p>Hi {{ $user->name }},</p>
    <p>We couldn’t process your payout request (#{{ $payout->id }}) for {{ get_currency() }} {{ number_format($payout->amount,2) }}.</p>
    <p><strong>Reason:</strong> {{ $reason }}</p>
    <p>The requested amount{{ data_get($payout->meta,'fee',0)>0 ? ' and fee' : '' }} has been refunded to your wallet.</p>
  </div>
</body>
</html>

