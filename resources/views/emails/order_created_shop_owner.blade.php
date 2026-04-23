<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('emails.order_created_shop_owner.subject', ['order' => $order->id]) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .payment-notice {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn-success {
            background-color: #28a745;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
        .highlight {
            font-weight: bold;
            color: #28a745;
        }
        .warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ __('emails.order_created_shop_owner.title') }}</h2>
        <p>{{ __('emails.order_created_shop_owner.subtitle', ['order' => $order->id]) }}</p>
    </div>

    <div class="content">
        <p>{{ __('emails.order_created_shop_owner.greeting', ['name' => $shopOwner->name]) }}</p>

        <p>{{ __('emails.order_created_shop_owner.intro', ['shop' => $shop->localized_name ?? $shop->name]) }}</p>

        <div class="order-details">
            <h3>{{ __('emails.order_created_shop_owner.details_heading') }}:</h3>
            <p><strong>{{ __('emails.order_created_shop_owner.order_number') }}:</strong> {{ $order->id }}</p>
            <p><strong>{{ __('emails.order_created_shop_owner.customer') }}:</strong> {{ $buyer->name }}</p>
            <p><strong>{{ __('emails.order_created_shop_owner.customer_email') }}:</strong> {{ $order->email }}</p>
            <p><strong>{{ __('emails.order_created_shop_owner.customer_phone') }}:</strong> {{ $order->phone }}</p>
            <p><strong>{{ __('emails.order_created_shop_owner.order_date') }}:</strong> {{ $order->created_at->translatedFormat('F j, Y \\a\\t g:i A') }}</p>
            <p><strong>{{ __('emails.order_created_shop_owner.status') }}:</strong> <span class="warning">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="payment-notice">
            <h4>{{ __('emails.order_created_shop_owner.payment_heading') }}:</h4>
            <p><strong>{{ __('emails.order_created_shop_owner.payment_intro') }}</strong></p>
            <p>{{ __('emails.order_created_shop_owner.payment_note') }}</p>
            <p><strong>{{ __('emails.order_created_shop_owner.payment_total') }}:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span></p>
        </div>

        <h4>{{ __('emails.order_created_shop_owner.items_heading') }}:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->localized_name ?? $item->product->name }}</strong> - {{ __('emails.order_created_shop_owner.quantity_price', ['quantity' => $item->quantity, 'price' => get_currency() . ' ' . number_format($item->price, 2)]) }}</li>
            @endforeach
        </ul>

        <h4>{{ __('emails.order_created_shop_owner.shipping_heading') }}:</h4>
        <p><strong>{{ __('emails.order_created_shop_owner.shipping_address') }}:</strong> {{ $order->shipping_address_1 }}</p>
        @if($order->shipping_address_2)
            <p><strong>{{ __('emails.order_created_shop_owner.shipping_address_2') }}:</strong> {{ $order->shipping_address_2 }}</p>
        @endif
        <p><strong>{{ __('emails.order_created_shop_owner.shipping_city') }}:</strong> {{ $order->shipping_city }}</p>
        @if($order->shipping_state)
            <p><strong>{{ __('emails.order_created_shop_owner.shipping_state') }}:</strong> {{ $order->shipping_state }}</p>
        @endif
        @if($order->shipping_postal_code)
            <p><strong>{{ __('emails.order_created_shop_owner.shipping_postal') }}:</strong> {{ $order->shipping_postal_code }}</p>
        @endif

        @if($order->order_notes)
            <h4>{{ __('emails.order_created_shop_owner.order_notes') }}:</h4>
            <p>{{ $order->order_notes }}</p>
        @endif

        <p><strong>{{ __('emails.order_created_shop_owner.next_steps') }}:</strong></p>
        <ul>
            <li>{{ __('emails.order_created_shop_owner.step_wait') }}</li>
            <li>{{ __('emails.order_created_shop_owner.step_process') }}</li>
            <li>{{ __('emails.order_created_shop_owner.step_prepare') }}</li>
            <li>{{ __('emails.order_created_shop_owner.step_update') }}</li>
        </ul>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-success">
                {{ __('emails.order_created_shop_owner.cta_view') }}
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn">
                {{ __('emails.order_created_shop_owner.cta_all') }}
            </a>
        </div>

        <p>{{ __('emails.order_created_shop_owner.important') }}</p>
    </div>

    <div class="footer">
        <p>{{ __('emails.order_created_shop_owner.footer_thanks') }}</p>
        <p>{{ __('Regards,') }}<br>{{ __('emails.order_created_shop_owner.footer_signature', ['app' => config('app.name')]) }}</p>
    </div>
</body>
</html>
