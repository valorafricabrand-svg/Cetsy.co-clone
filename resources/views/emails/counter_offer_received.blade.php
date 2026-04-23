@extends('emails.layout')

@section('content')
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #17a2b8; margin-bottom: 10px;">{{ __('emails.counter_offer_received.title') }}</h2>
        <p style="color: #666; font-size: 16px;">{{ __('emails.counter_offer_received.subtitle') }}</p>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #333; margin-bottom: 15px;">{{ __('emails.counter_offer_received.details_heading') }}</h3>

        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            @if($offer->product->media && $offer->product->media->count() > 0)
                <img src="{{ $offer->product->media->first()->getUrl() }}"
                     alt="{{ $offer->product->localized_name ?? $offer->product->name }}"
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
            @endif
            <div>
                <h4 style="margin: 0; color: #333;">{{ $offer->product->localized_name ?? $offer->product->name }}</h4>
                <p style="margin: 5px 0; color: #666;">{{ $offer->product->shop->localized_name ?? $offer->product->shop->name ?? __('emails.counter_offer_received.unknown_shop') }}</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <strong style="color: #666;">{{ __('emails.counter_offer_received.counter_offer_price') }}:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #17a2b8;">{{ $offer->formatted_price }}</div>
            </div>
            <div>
                <strong style="color: #666;">{{ __('emails.counter_offer_received.original_price') }}:</strong>
                <div style="font-size: 18px; font-weight: bold; color: #333;">{{ get_currency() }} {{ number_format($offer->product->price, 2) }}</div>
            </div>
        </div>

        @if($offer->buyer_notes)
            <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 15px;">
                <strong style="color: #666;">{{ __('emails.counter_offer_received.buyer_message') }}:</strong>
                <p style="margin: 5px 0; color: #333;">{{ $offer->buyer_notes }}</p>
            </div>
        @endif
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('seller.offers.index') }}"
           style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            {{ __('emails.counter_offer_received.cta') }}
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;">
        <p>{{ __('emails.counter_offer_received.footer_intro') }}</p>
        <p>{{ __('emails.counter_offer_received.footer_tip') }}</p>
    </div>
</div>
@endsection
