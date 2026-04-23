<?php

namespace App\Mail;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CounterOfferMail extends LocalizedMailable
{
    use Queueable, SerializesModels;

    public $counterOffer;
    public $product;
    public $seller;
    public $buyer;

    /**
     * Create a new message instance.
     */
    public function __construct(Offer $counterOffer, Product $product, User $seller, User $buyer)
    {
        $this->counterOffer = $counterOffer;
        $this->product = $product;
        $this->seller = $seller;
        $this->buyer = $buyer;
        $this->usePreferredLocale($buyer, $product, $seller);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.counter_offer.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.counter_offer',
            with: [
                'counterOffer' => $this->counterOffer,
                'product' => $this->product,
                'seller' => $this->seller,
                'buyer' => $this->buyer,
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
