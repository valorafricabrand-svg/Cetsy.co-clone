<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
  <h2 style="margin: 0 0 12px;">New Contact Form Submission</h2>

  <p style="margin: 0 0 16px;">
    You received a new message from the Contact Us page.
  </p>

  <table cellpadding="8" cellspacing="0" border="0" style="border-collapse: collapse; width: 100%; max-width: 700px;">
    <tr>
      <td style="width: 180px; font-weight: 700; border-bottom: 1px solid #e5e7eb;">Name</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['name'] ?? '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700; border-bottom: 1px solid #e5e7eb;">Email</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['email'] ?? '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700; border-bottom: 1px solid #e5e7eb;">Subject</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['subject'] ?? '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700; border-bottom: 1px solid #e5e7eb;">Order Number</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['order_number'] ?: '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700; border-bottom: 1px solid #e5e7eb;">User ID</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['user_id'] ?: '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700; border-bottom: 1px solid #e5e7eb;">IP Address</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['ip'] ?? '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700; border-bottom: 1px solid #e5e7eb;">Submitted At</td>
      <td style="border-bottom: 1px solid #e5e7eb;">{{ $payload['submitted_at'] ?? '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: 700;">Message</td>
      <td>
        <div style="white-space: pre-wrap;">{{ $payload['message'] ?? '-' }}</div>
      </td>
    </tr>
  </table>
</body>
</html>
