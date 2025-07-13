<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;

class OfferAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $product;
    public $seller;
    public $buyer;

    /**
     * Create a new message instance.
     */
    public function __construct(Offer $offer, Product $product, User $seller, User $buyer)
    {
        $this->offer = $offer;
        $this->product = $product;
        $this->seller = $seller;
        $this->buyer = $buyer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Offer Has Been Accepted!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.offer_accepted',
            with: [
                'offer' => $this->offer,
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