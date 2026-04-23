<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('emails.welcome_seller.subject', ['app' => $appName]) }}</title>
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
        <h2>{{ __('emails.welcome_seller.title') }}</h2>
        <p>{{ __('emails.welcome_seller.subtitle', ['app' => $appName]) }}</p>
    </div>
    <div class="content">
        <p>{{ __('emails.welcome_seller.greeting', ['name' => $user->name]) }}</p>
        <p>{{ __('emails.welcome_seller.intro') }}</p>
        <div class="notice">
            <p><strong>{{ __('emails.welcome_seller.next_steps') }}</strong></p>
            <ol>
                <li>{{ __('emails.welcome_seller.step_subscription') }}</li>
                <li>{{ __('emails.welcome_seller.step_profile') }}</li>
                <li>{{ __('emails.welcome_seller.step_products') }}</li>
            </ol>
        </div>
        <div style="text-align:center;">
            <a href="{{ route('seller.subscription') }}" class="btn">{{ __('emails.welcome_seller.cta') }}</a>
        </div>
        <p>{{ __('emails.welcome_seller.security') }}</p>
    </div>
    <div class="footer">
        <p>{{ __('emails.welcome_seller.footer') }}<br>{{ __('emails.counter_offer.signature', ['app' => $appName]) }}</p>
    </div>
</body>
</html>
