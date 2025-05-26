<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KYC Status Update</title>
</head>
<body>
    <h2>Your KYC has been {{ $status }}.</h2>
    @if($status === 'approved')
        <p>Congratulations! Your KYC has been approved. You can now access all features.</p>
    @else
        <p>Unfortunately, your KYC has been rejected.</p>
        @if($admin_notes)
            <p><strong>Reason:</strong> {{ $admin_notes }}</p>
        @endif
    @endif
    <p>Thank you for using our platform.</p>
</body>
</html>
