<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>New Review Received</title>
  <style>
    body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px}
    .header{background:#0d6efd;color:#fff;padding:16px 20px;border-radius:8px;margin-bottom:16px}
    .card{background:#fff;border:1px solid #e9ecef;border-radius:8px;padding:16px}
    .muted{color:#6c757d}
    .btn{display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;margin-top:12px}
    .stars{color:#e5780b}
  </style>
  </head>
<body>
  <div class="header">
    <h2>New review received</h2>
  </div>
  <div class="card">
    <p>Hi {{ $seller->name }},</p>
    <p>You just received a new review for <strong>{{ optional($product)->name ?? 'your listing' }}</strong>.</p>
    <p class="stars">
      Rating:
      @for($i=1;$i<=5;$i++)
        @if($i <= (int) $review->rating)
          ★
        @else
          ☆
        @endif
      @endfor
      ({{ (int) $review->rating }}/5)
    </p>
    @if($review->comment)
      <p><strong>Comment:</strong><br>{{ $review->comment }}</p>
    @endif
    <p class="muted">Order #{{ $order->id ?? '' }} | {{ optional($shop)->name }}</p>
    <p>
      <a href="{{ $reviewsUrl }}" class="btn">View reviews</a>
    </p>
  </div>
  <p class="muted">Thanks,<br>Cetsy Team</p>
</body>
</html>

