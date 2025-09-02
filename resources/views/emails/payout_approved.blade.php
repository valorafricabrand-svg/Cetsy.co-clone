<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payout Approved</title>
  <style>body{font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#333;padding:20px}.header{background:#0d6efd;color:#fff;padding:16px;border-radius:8px 8px 0 0}.content{background:#f8f9fa;padding:18px;border-radius:0 0 8px 8px}</style>
</head>
<body>
  <div class="header"><h2 style="margin:0">Payout Approved</h2></div>
  <div class="content">
    <p>Hi {{ $user->name }},</p>
    <p>Your payout request (#{{ $payout->id }}) for {{ get_currency() }} {{ number_format($payout->amount,2) }} was approved. We’ll notify you once the funds are sent.</p>
  </div>
</body>
</html>

