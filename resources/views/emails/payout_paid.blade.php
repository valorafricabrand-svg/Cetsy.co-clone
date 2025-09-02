<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payout Sent</title>
  <style>body{font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#333;padding:20px}.header{background:#198754;color:#fff;padding:16px;border-radius:8px 8px 0 0}.content{background:#f8f9fa;padding:18px;border-radius:0 0 8px 8px}</style>
</head>
<body>
  <div class="header"><h2 style="margin:0">Payout Sent</h2></div>
  <div class="content">
    <p>Hi {{ $user->name }},</p>
    <p>We’ve sent your payout (#{{ $payout->id }}) of {{ get_currency() }} {{ number_format($payout->amount,2) }}.</p>
    @php($ref = data_get($payout->meta,'txn_reference'))
    @if($ref)
      <p><strong>Reference:</strong> {{ $ref }}</p>
    @endif
    <p>It may take some time to reflect depending on your provider.</p>
  </div>
</body>
</html>

