<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('emails.order_created_buyer.subject', ['order' => $order->id]) }}</title>
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
            background-color: #007bff;
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
            border-left: 4px solid #007bff;
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
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn-primary {
            background-color: #007bff;
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
        <h2>{{ __('emails.order_created_buyer.title') }}</h2>
        <p>{{ __('emails.order_created_buyer.subtitle', ['order' => $order->id]) }}</p>
    </div>

    <div class="content">
        <p>{{ __('emails.order_created_buyer.greeting', ['name' => $buyer->name]) }}</p>

        <p>{{ __('emails.order_created_buyer.intro') }}</p>

        <div class="order-details">
            <h3>{{ __('emails.order_created_buyer.details_heading') }}:</h3>
            <p><strong>{{ __('emails.order_created_buyer.order_number') }}:</strong> {{ $order->id }}</p>
            <p><strong>{{ __('emails.order_created_buyer.shop') }}:</strong> {{ $shop->localized_name ?? $shop->name }}</p>
            <p><strong>{{ __('emails.order_created_buyer.order_date') }}:</strong> {{ $order->created_at->translatedFormat('F j, Y \\a\\t g:i A') }}</p>
            <p><strong>{{ __('emails.order_created_buyer.status') }}:</strong> <span class="warning">{{ ucfirst($order->status) }}</span></p>
        </div>

        <div class="payment-notice">
            <h4>{{ __('emails.order_created_buyer.payment_heading') }}:</h4>
            <p><strong>{{ __('emails.order_created_buyer.payment_intro') }}</strong></p>
            <p><strong>{{ __('emails.order_created_buyer.payment_total') }}:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span></p>
            <p>{{ __('emails.order_created_buyer.payment_processing') }}</p>
        </div>

        <h4>{{ __('emails.order_created_buyer.items_heading') }}:</h4>
        <ul>
            @foreach($order->items as $item)
                <li><strong>{{ $item->product->localized_name ?? $item->product->name }}</strong> - {{ __('emails.order_created_buyer.quantity_price', ['quantity' => $item->quantity, 'price' => get_currency() . ' ' . number_format($item->price, 2)]) }}</li>
            @endforeach
        </ul>

        <h4>{{ __('emails.order_created_buyer.summary_heading') }}:</h4>
        <p><strong>{{ __('emails.order_created_buyer.subtotal') }}:</strong> {{ get_currency() }} {{ number_format($order->subtotal, 2) }}</p>
        <p><strong>{{ __('emails.order_created_buyer.shipping_cost') }}:</strong> {{ get_currency() }} {{ number_format($order->shipping_cost, 2) }}</p>
        <p><strong>{{ __('emails.order_created_buyer.total') }}:</strong> <span class="highlight">{{ get_currency() }} {{ number_format($order->total_amount, 2) }}</span></p>

        <h4>{{ __('emails.order_created_buyer.shipping_heading') }}:</h4>
        <p>{{ $order->shipping_address_1 }}</p>
        @if($order->shipping_address_2)
            <p>{{ $order->shipping_address_2 }}</p>
        @endif
        <p>{{ $order->shipping_city }}</p>
        @if($order->shipping_state)
            <p>{{ $order->shipping_state }}</p>
        @endif
        @if($order->shipping_postal_code)
            <p>{{ $order->shipping_postal_code }}</p>
        @endif

        @if($order->order_notes)
            <h4>{{ __('emails.order_created_buyer.order_notes') }}:</h4>
            <p>{{ $order->order_notes }}</p>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('pay_now', $order->id) }}" class="btn">
                {{ __('emails.order_created_buyer.cta_pay') }}
            </a>
            <a href="{{ route('buyer.orders.show', $order->id) }}" class="btn btn-primary">
                {{ __('emails.order_created_buyer.cta_view') }}
            </a>
        </div>

        <p><strong>{{ __('emails.order_created_buyer.next_steps') }}</strong></p>
        <ol>
            <li>{{ __('emails.order_created_buyer.step_pay') }}</li>
            <li>{{ __('emails.order_created_buyer.step_notify') }}</li>
            <li>{{ __('emails.order_created_buyer.step_ship') }}</li>
            <li>{{ __('emails.order_created_buyer.step_tracking') }}</li>
        </ol>

        <p>{{ __('emails.order_created_buyer.help') }}</p>
    </div>

    <div class="footer">
        <p>{{ __('emails.order_created_buyer.footer_thanks') }}</p>
        <p>{{ __('Regards,') }}<br>{{ __('emails.order_created_buyer.footer_signature', ['app' => config('app.name')]) }}</p>
    </div>
</body>
</html>
