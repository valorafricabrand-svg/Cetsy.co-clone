<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Verify your payout</title>
  </head>
  <body style="font-family: Arial, sans-serif; line-height: 1.5;">
    <h2>Verify your payout request</h2>
    <p>Hello {{ $user->name }},</p>
    <p>To continue with your payout request, use this verification code:</p>
    <p style="font-size: 24px; font-weight: bold;">{{ $code }}</p>
    <p>This code expires in 10 minutes. If you did not initiate this action, you can ignore this email.</p>
    <p>Thank you,<br>{{ config('app.name') }}</p>
  </body>
  </html>

