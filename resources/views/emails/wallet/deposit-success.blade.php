<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Deposit Successful</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .amount {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
            margin: 20px 0;
        }
        .details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>Wallet Deposit Successful!</h1>
        <p>Your wallet has been credited successfully</p>
    </div>
    
    <div class="content">
        <p>Dear <strong>{{ $user->name }}</strong>,</p>
        
        <p>Great news! Your wallet deposit has been processed successfully. Here are the details of your transaction:</p>
        
        <div class="amount">
            {{ get_currency() }} {{ number_format($amount, 2) }}
        </div>
        
        <div class="details">
            <div class="detail-row">
                <span class="label">Transaction ID:</span>
                <span class="value">{{ $reference }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ ucfirst($wallet->method) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date & Time:</span>
                <span class="value">{{ $wallet->created_at->format('M d, Y g:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value" style="color: #28a745; font-weight: bold;">Completed</span>
            </div>
        </div>
        
        <p>Your wallet balance has been updated. You can now use these funds for:</p>
        <ul>
            <li>Paying listing fees</li>
            <li>Making purchases</li>
            <li>Processing orders</li>
            <li>Other platform transactions</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="{{ route('wallet.index') }}" class="btn">View Wallet</a>
        </div>
        
        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>This transaction is final and cannot be reversed</li>
            <li>Keep this email for your records</li>
            <li>If you have any questions, please contact our support team</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Thank you for using {{ config('app.name') }}!</p>
        <p>This is an automated message, please do not reply to this email.</p>
        <p>If you didn't make this transaction, please contact us immediately.</p>
    </div>
</body>
</html> 