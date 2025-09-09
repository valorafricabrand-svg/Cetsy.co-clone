<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ $appName }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: #fff; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .btn { display: inline-block; padding: 10px 16px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 12px; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 14px; color: #6c757d; }
    </style>
    </head>
<body>
    <div class="header">
        <h2>Welcome to {{ $appName }}!</h2>
        <p>Your buyer account was created successfully</p>
    </div>
    <div class="content">
        <p>Hello <strong>{{ $user->name }}</strong>,</p>
        <p>Thanks for joining {{ $appName }}. You can now browse products, add items to your cart, and place orders with ease.</p>
        <p>Here are a few quick tips to get started:</p>
        <ul>
            <li>Explore trending products and categories</li>
            <li>Add items to your wishlist to track favorites</li>
            <li>Message sellers with any product questions</li>
        </ul>
        <div style="text-align:center;">
            <a href="{{ url('/') }}" class="btn">Start Shopping</a>
        </div>
        <p>If you didn’t create this account, please contact support immediately.</p>
    </div>
    <div class="footer">
        <p>Happy shopping!<br>The {{ $appName }} Team</p>
    </div>
</body>
</html>

