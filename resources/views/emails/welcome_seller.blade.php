<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ $appName }} — Seller</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #6f42c1; color: #fff; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .notice { background: #f8f9fa; border-left: 4px solid #6f42c1; padding: 12px; border-radius: 6px; margin: 12px 0; }
        .btn { display: inline-block; padding: 10px 16px; background-color: #6f42c1; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 12px; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 14px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Welcome, Seller!</h2>
        <p>Your seller account at {{ $appName }} is ready</p>
    </div>
    <div class="content">
        <p>Hello <strong>{{ $user->name }}</strong>,</p>
        <p>Great to have you on board. You’re just a few steps away from listing your first product and making sales.</p>
        <div class="notice">
            <p><strong>Next steps for sellers:</strong></p>
            <ol>
                <li>Activate a subscription to start selling</li>
                <li>Create your shop profile and policies</li>
                <li>Add products with clear photos and details</li>
            </ol>
        </div>
        <div style="text-align:center;">
            <a href="{{ route('seller.subscription') }}" class="btn">Activate Subscription</a>
        </div>
        <p>If you didn’t create this account, please contact support immediately.</p>
    </div>
    <div class="footer">
        <p>Wishing you great sales!<br>The {{ $appName }} Team</p>
    </div>
</body>
</html>

