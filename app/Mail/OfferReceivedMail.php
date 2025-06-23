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

class OfferReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $product;
    public $buyer;
    public $shopOwner;

    /**
     * Create a new message instance.
     */
    public function __construct(Offer $offer, Product $product, User $buyer, User $shopOwner)
    {
        $this->offer = $offer;
        $this->product = $product;
        $this->buyer = $buyer;
        $this->shopOwner = $shopOwner;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Offer Received for "' . $this->product->name . '"',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.offer_received',
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

    public function build()
    {
        return $this->subject('New Offer Received for "' . $this->product->name . '"')
            ->view('emails.offer_received')
            ->with([
                'offer' => $this->offer,
                'product' => $this->product,
                'buyer' => $this->buyer,
                'shopOwner' => $this->shopOwner,
            ]);
    }
} 