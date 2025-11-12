<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Seller Responded to Your Review</title>
  <style>
    body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px}
    .header{background:#198754;color:#fff;padding:16px 20px;border-radius:8px;margin-bottom:16px}
    .card{background:#fff;border:1px solid #e9ecef;border-radius:8px;padding:16px}
    .muted{color:#6c757d}
    .btn{display:inline-block;background:#198754;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;margin-top:12px}
    .stars{color:#e5780b}
  </style>
  </head>
<body>
  <div class="header">
    <h2>Seller responded to your review</h2>
  </div>
  <div class="card">
    <p>Hi {{ $buyer->name }},</p>
    <p>The seller has replied to your review for <strong>{{ optional($product)->name ?? 'your order' }}</strong>.</p>
    <p class="stars">
      Your rating:
      @for($i=1;$i<=5;$i++)
        @if($i <= (int) $review->rating)
          ★
        @else
          ☆
        @endif
      @endfor
      ({{ (int) $review->rating }}/5)
    </p>
    @if($review->seller_response)
      <p><strong>Seller response:</strong><br>{{ $review->seller_response }}</p>
    @endif
    <p>
      <a href="{{ $orderUrl }}" class="btn">View order</a>
    </p>
  </div>
  <p class="muted">Thanks for helping our community with your feedback.</p>
</body>
</html>

