<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Submitted</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color:#1f2937; }
        .container { max-width: 640px; margin:0 auto; padding:24px; }
        .btn { display:inline-block; background:#2563eb; color:#fff; padding:10px 14px; text-decoration:none; border-radius:6px; }
        .muted { color:#6b7280; font-size: 12px; }
        .card { border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-top:12px; }
        .row { margin-bottom: 6px; }
        .label { width: 140px; display:inline-block; color:#6b7280; }
        .value { color:#111827; }
    </style>
    </head>
<body>
<div class="container">
    <h2>New KYC Submission</h2>
    <p>A seller has submitted a KYC and is awaiting admin approval.</p>

    <div class="card">
        <div class="row"><span class="label">Seller:</span> <span class="value">{{ $kyc->user->name ?? trim(($kyc->first_name ?? '') . ' ' . ($kyc->last_name ?? '')) }}</span></div>
        <div class="row"><span class="label">Email:</span> <span class="value">{{ $kyc->email ?? ($kyc->user->email ?? 'N/A') }}</span></div>
        <div class="row"><span class="label">Phone:</span> <span class="value">{{ $kyc->phone ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">ID Type:</span> <span class="value">{{ $kyc->id_type ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">ID Number:</span> <span class="value">{{ $kyc->id_number ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">Submitted at:</span> <span class="value">{{ optional($kyc->created_at)->toDayDateTimeString() }}</span></div>
        <div class="row"><span class="label">Status:</span> <span class="value">{{ ucfirst($kyc->status) }}</span></div>
    </div>

    <p style="margin-top:16px;">
        <a class="btn" href="{{ $adminShowUrl }}">View KYC Details</a>
        &nbsp;&nbsp;
        <a class="btn" style="background:#10b981" href="{{ $adminIndexUrl }}">Open KYC Queue</a>
    </p>

    <p class="muted">This is an automated notification for support.</p>
</div>
</body>
</html>

