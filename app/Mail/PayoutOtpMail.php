<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PayoutRequest;
use App\Models\User;

class PayoutOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public PayoutRequest $payout;
    public User $user;
    public string $code;

    public function __construct(PayoutRequest $payout, User $user, string $code)
    {
        $this->payout = $payout;
        $this->user   = $user;
        $this->code   = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your payout request');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payout_otp');
    }
}

