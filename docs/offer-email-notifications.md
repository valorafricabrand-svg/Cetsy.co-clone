# Offer Email Notifications

This document explains how the offer email notification system works in the Cetsy platform.

## Overview

When sellers take actions on offers (accept, decline, counter), the system automatically sends email notifications to buyers to keep them informed about the status of their offers.

## Email Types

### 1. Offer Accepted Email
- **Trigger**: Seller accepts an offer
- **Recipient**: Buyer who made the offer
- **Template**: `resources/views/emails/offer_accepted.blade.php`
- **Class**: `App\Mail\OfferAcceptedMail`

### 2. Offer Declined Email
- **Trigger**: Seller declines an offer
- **Recipient**: Buyer who made the offer
- **Template**: `resources/views/emails/offer_declined.blade.php`
- **Class**: `App\Mail\OfferDeclinedMail`

### 3. Counter Offer Email
- **Trigger**: Seller makes a counter offer
- **Recipient**: Buyer who made the original offer
- **Template**: `resources/views/emails/counter_offer.blade.php`
- **Class**: `App\Mail\CounterOfferMail`

## How It Works

### Individual Actions
When a seller performs an action on a single offer:

1. **Validation**: Check if the offer can be acted upon
2. **Status Update**: Update the offer status in the database
3. **Email Notification**: Send email to the buyer
4. **Logging**: Log success/failure for debugging
5. **User Feedback**: Show success/error message to seller

### Bulk Actions
When a seller performs bulk actions on multiple offers:

1. **Validation**: Validate all offer IDs and action type
2. **Processing**: Process each offer individually
3. **Email Notifications**: Send emails for each processed offer
4. **Summary**: Show summary of processed offers and emails sent

## Email Content

### Offer Accepted Email
- Congratulations message
- Product details
- Offer price
- Seller information
- Next steps for the buyer

### Offer Declined Email
- Notification of decline
- Product details
- Original offer price
- Seller's reason (if provided)
- Alternative suggestions for the buyer

### Counter Offer Email
- Notification of counter offer
- Product details
- Original vs counter offer price comparison
- Seller's message (if provided)
- Next steps for the buyer

## Error Handling

The system includes comprehensive error handling:

- **Email Failures**: Logged with detailed error information
- **User Feedback**: Sellers are informed if emails fail to send
- **Graceful Degradation**: System continues to work even if emails fail
- **Retry Logic**: Failed emails can be retried manually

## Testing

### Manual Testing
1. Create an offer as a buyer
2. As a seller, go to "My Offers"
3. Perform actions (accept/decline/counter)
4. Check buyer's email for notifications

### Command Line Testing
```bash
# Test all email types
php artisan test:offer-emails

# Test specific email type
php artisan test:offer-emails --type=declined
php artisan test:offer-emails --type=accepted
php artisan test:offer-emails --type=counter
```

### Log Checking
Since the default mailer is set to 'log', emails are written to:
```
storage/logs/laravel.log
```

## Configuration

### Mail Settings
The email system uses Laravel's mail configuration in `config/mail.php`:

- **Default Mailer**: 'log' (for development)
- **SMTP**: Configure for production
- **From Address**: Set in environment variables

### Environment Variables
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your Platform Name"
```

## Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check mail configuration in `config/mail.php`
   - Verify environment variables
   - Check Laravel logs for errors

2. **Email content issues**
   - Verify email templates exist
   - Check template syntax
   - Ensure all required variables are passed

3. **Bulk action emails**
   - Verify offer relationships are loaded
   - Check that buyers have valid email addresses
   - Monitor log files for errors

### Debugging

1. **Enable detailed logging**:
   ```php
   \Log::info('Email details', [
       'offer_id' => $offer->id,
       'buyer_email' => $offer->buyer->email,
       'action' => 'declined'
   ]);
   ```

2. **Check email content**:
   - Emails are logged to `storage/logs/laravel.log`
   - Search for "Message-ID" to find specific emails

3. **Test email templates**:
   - Use the test command: `php artisan test:offer-emails`
   - Check generated email content in logs

## Security Considerations

- **Authorization**: Only sellers can act on their own product offers
- **Validation**: All inputs are validated before processing
- **Logging**: All actions are logged for audit purposes
- **Error Handling**: Sensitive information is not exposed in error messages

## Future Enhancements

1. **Email Queues**: Implement queued emails for better performance
2. **Email Templates**: Add more customization options
3. **SMS Notifications**: Add SMS notifications for urgent updates
4. **Email Preferences**: Allow users to customize notification preferences
5. **Analytics**: Track email open rates and engagement 