<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CounterOfferReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'New Counter Offer Received',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.counter_offer_received',
        );
    }

    public function attachments()
    {
        return [];
    }
} 