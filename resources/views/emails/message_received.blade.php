<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Message Received</title>
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
            background-color: #17a2b8;
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
        .message-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #17a2b8;
        }
        .message-content {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #bbdefb;
            font-style: italic;
        }
        .product-info {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
        .sender-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Message Received!</h2>
        <p>You have a new message</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $receiver->name }}</strong>,</p>

        <p>You've received a new message from <strong>{{ $sender->name }}</strong>.</p>

        <div class="sender-info">
            <h4>From:</h4>
            <p><strong>Name:</strong> {{ $sender->name }}</p>
            <p><strong>Email:</strong> {{ $sender->email }}</p>
            <p><strong>Date:</strong> {{ $messageModel->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>

        @if($product)
        <div class="product-info">
            <h4>About Product:</h4>
            <p><strong>Product:</strong> {{ $product->name }}</p>
            <p><strong>Price:</strong> {{ get_currency() }} {{ number_format($product->price, 2) }}</p>
        </div>
        @endif

        <div class="message-details">
            <h4>Message:</h4>
            <div class="message-content">
                "{{ $messageModel->body }}"
            </div>
        </div>

        <p>You can now:</p>
        <ul>
            <li>Reply to the customer directly</li>
            <li>View the conversation in your dashboard</li>
            <li>Check the product details if mentioned</li>
            <li>Consider this as a potential sales opportunity</li>
        </ul>

        <div style="text-align: center; margin: 20px 0;">
             <a href="{{ $messageUrl ?? route('notifications.index') }}" class="btn">
                View Message Details
            </a>
            @if($product)
            <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn btn-secondary">
                View Product
            </a>
            @endif
        </div>

        <p><strong>Quick Tip:</strong> Responding promptly to customer inquiries can significantly increase your chances of making a sale. Most customers appreciate quick and helpful responses!</p>
    </div>

    <div class="footer">
        <p>Thank you for being part of our marketplace!</p>
        <p>Best regards,<br>The Cetsy Team</p>
    </div>
</body>
</html> 



