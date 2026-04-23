<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CounterOfferReceivedMail extends LocalizedMailable
{
    use Queueable, SerializesModels;

    public $offer;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
        $this->usePreferredLocale($offer->product?->shop?->user, $offer->product?->shop, $offer->product);
    }

    public function envelope()
    {
        return new Envelope(
            subject: __('emails.counter_offer_received.subject'),
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
