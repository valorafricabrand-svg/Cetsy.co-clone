<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Expiry Reminder</title>
  <style>
    body{font-family: Arial, Helvetica, sans-serif; color:#111827}
    .container{max-width:640px;margin:0 auto;padding:24px}
    .btn{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:10px 14px;border-radius:6px}
    .muted{color:#6b7280;font-size:12px}
    .card{border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-top:12px}
  </style>
</head>
<body>
  <div class="container">
    <h2>Subscription Expiry Reminder</h2>
    <p>Your seller subscription will expire in <strong>{{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}</strong>, on <strong>{{ $subscription->end_date->format('F j, Y') }}</strong>.</p>
    <div class="card">
      <p class="muted">Plan amount: {{ get_currency() }} {{ number_format($subscription->amount,2) }}</p>
      <p class="muted">Status: {{ ucfirst($subscription->status) }}</p>
    </div>
    <p style="margin-top:16px;">
      <a href="{{ $manageUrl }}" class="btn">Manage Subscription</a>
    </p>
    <p class="muted">If you have already renewed, please ignore this message.</p>
  </div>
</body>
</html>

