<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('emails.welcome_buyer.subject', ['app' => $appName]) }}</title>
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
        <h2>{{ __('emails.welcome_buyer.title', ['app' => $appName]) }}</h2>
        <p>{{ __('emails.welcome_buyer.subtitle') }}</p>
    </div>
    <div class="content">
        <p>{{ __('emails.welcome_buyer.greeting', ['name' => $user->name]) }}</p>
        <p>{{ __('emails.welcome_buyer.intro', ['app' => $appName]) }}</p>
        <p>{{ __('emails.welcome_buyer.tips_intro') }}</p>
        <ul>
            <li>{{ __('emails.welcome_buyer.tip_browse') }}</li>
            <li>{{ __('emails.welcome_buyer.tip_wishlist') }}</li>
            <li>{{ __('emails.welcome_buyer.tip_message') }}</li>
        </ul>
        <div style="text-align:center;">
            <a href="{{ localized_route('home') }}" class="btn">{{ __('emails.welcome_buyer.cta') }}</a>
        </div>
        <p>{{ __('emails.welcome_buyer.security') }}</p>
    </div>
    <div class="footer">
        <p>{{ __('emails.welcome_buyer.footer') }}<br>{{ __('emails.counter_offer.signature', ['app' => $appName]) }}</p>
    </div>
</body>
</html>
