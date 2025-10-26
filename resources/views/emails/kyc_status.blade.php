<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KYC Status Update</title>
</head>
<body>
    @if($status === 'approved')
        <h2>Your KYC has been approved.</h2>
        <p>Congratulations! Your KYC has been approved. You can now access all features.</p>
    @elseif($status === 'rejected')
        <h2>Your KYC has been rejected.</h2>
        @if($admin_notes)
            <p><strong>Reason:</strong> {{ $admin_notes }}</p>
        @endif
    @elseif($status === 'needs_correction')
        <h2>Action Required: KYC Needs Correction</h2>
        <p>Your KYC submission requires some corrections. Please review the notes below and resubmit your documents.</p>
        @if($admin_notes)
            <p><strong>Details:</strong> {{ $admin_notes }}</p>
        @endif
    @else
        <h2>Your KYC status was updated.</h2>
    @endif
    <p>Thank you for using our platform.</p>
</body>
</html>
