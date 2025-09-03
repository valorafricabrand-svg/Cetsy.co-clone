<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PayoutRequest;
use App\Models\User;

class PayoutPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public PayoutRequest $payout;
    public User $user;

    public function __construct(PayoutRequest $payout, User $user)
    {
        $this->payout = $payout;
        $this->user   = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your payout was sent');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payout_paid');
    }
}

