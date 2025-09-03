<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Your Payout</title>
  <style>body{font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#333;padding:20px}.header{background:#0d6efd;color:#fff;padding:16px;border-radius:8px 8px 0 0}.content{background:#f8f9fa;padding:18px;border-radius:0 0 8px 8px}.code{font-size:28px;font-weight:700;letter-spacing:4px;background:#fff;padding:10px 16px;border-radius:8px;display:inline-block;margin:8px 0}</style>
</head>
<body>
  <div class="header"><h2 style="margin:0">Verify Your Payout</h2></div>
  <div class="content">
    <p>Hi {{ $user->name }},</p>
    <p>Use the code below to verify your payout request #{{ $payout->id }} for {{ get_currency() }} {{ number_format($payout->amount,2) }}.</p>
    <div class="code">{{ $code }}</div>
    <p>This code expires in 10 minutes.</p>
    <p>You can also enter the code on the verification page.</p>
  </div>
</body>
</html>

