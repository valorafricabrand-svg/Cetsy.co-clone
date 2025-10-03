<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Subscription $subscription;
    public int $daysLeft;

    public function __construct(Subscription $subscription, int $daysLeft)
    {
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Subscription expires in ' . $this->daysLeft . ' day' . ($this->daysLeft === 1 ? '' : 's'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_expiry_reminder',
        );
    }

    public function build()
    {
        return $this->subject('Reminder: Subscription expires in ' . $this->daysLeft . ' day' . ($this->daysLeft === 1 ? '' : 's'))
            ->view('emails.subscription_expiry_reminder')
            ->with([
                'subscription' => $this->subscription,
                'daysLeft'     => $this->daysLeft,
                'manageUrl'    => route('seller.subscription'),
            ]);
    }
}

