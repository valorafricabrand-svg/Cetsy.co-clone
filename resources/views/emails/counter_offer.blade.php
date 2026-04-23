<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.counter_offer.subject') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ffc107; color: #000; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .product-card { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #dee2e6; }
        .price { font-size: 24px; font-weight: bold; color: #ffc107; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
        .counter-offer-box { background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .comparison { display: flex; justify-content: space-between; margin: 15px 0; }
        .comparison-item { text-align: center; flex: 1; padding: 10px; }
        .original-price { color: #6c757d; text-decoration: line-through; }
        .new-price { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('emails.counter_offer.title') }}</h1>
        </div>

        <div class="content">
            <p>{{ __('emails.counter_offer.greeting', ['name' => $buyer->name]) }}</p>

            <p>{{ __('emails.counter_offer.intro') }}</p>

            <div class="product-card">
                <h3>{{ $product->localized_name ?? $product->name }}</h3>
                <p><strong>{{ __('emails.counter_offer.seller_label') }}:</strong> {{ $seller->name }}</p>
                <p><strong>{{ __('emails.counter_offer.date_label') }}:</strong> {{ now()->translatedFormat('d M Y, H:i') }}</p>
            </div>

            <div class="counter-offer-box">
                <h4>{{ __('emails.counter_offer.details_heading') }}</h4>

                <div class="comparison">
                    <div class="comparison-item">
                        <h5>{{ __('emails.counter_offer.original_offer_label') }}</h5>
                        @php
                            $originalInfo = $counterOffer->extractOriginalOfferInfo();
                        @endphp
                        @if($originalInfo)
                            <p class="original-price">{{ get_currency() }} {{ number_format($originalInfo->offer_price, 2) }}</p>
                        @else
                            <p class="original-price">{{ __('emails.counter_offer.original_missing') }}</p>
                        @endif
                    </div>
                    <div class="comparison-item">
                        <h5>{{ __('emails.counter_offer.counter_offer_label') }}</h5>
                        <p class="new-price">{{ get_currency() }} {{ number_format($counterOffer->offer_price, 2) }}</p>
                    </div>
                </div>

                @if($counterOffer->seller_notes)
                    <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 6px;">
                        <h6>{{ __('emails.counter_offer.seller_message_label') }}:</h6>
                        <p><em>"{{ $counterOffer->seller_notes }}"</em></p>
                    </div>
                @endif
            </div>

            <p>{{ __('emails.counter_offer.actions_intro') }}</p>

            <ul>
                <li>{{ __('emails.counter_offer.action_accept') }}</li>
                <li>{{ __('emails.counter_offer.action_decline') }}</li>
                <li>{{ __('emails.counter_offer.action_counter') }}</li>
                <li>{{ __('emails.counter_offer.action_contact') }}</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ localized_route('listing.show', $product->slug ?? $product->id) }}" class="btn">
                    {{ __('emails.counter_offer.cta') }}
                </a>
            </div>

            <p>{{ __('emails.counter_offer.note') }}</p>

            <p>{{ __('emails.counter_offer.thanks') }}</p>

            <p>{{ __('Regards,') }}<br>
            <strong>{{ __('emails.counter_offer.signature', ['app' => config('app.name')]) }}</strong></p>
        </div>

        <div class="footer">
            <p>{{ __('emails.counter_offer.sent_to', ['email' => $buyer->email]) }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
