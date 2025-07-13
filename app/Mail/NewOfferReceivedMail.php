<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOfferReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;

    /**
     * Create a new message instance.
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Offer Received for ' . $this->offer->product->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_offer_received',
            with: [
                'offer' => $this->offer,
                'product' => $this->offer->product,
                'buyer' => $this->offer->buyer,
                'seller' => $this->offer->product->shop->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 