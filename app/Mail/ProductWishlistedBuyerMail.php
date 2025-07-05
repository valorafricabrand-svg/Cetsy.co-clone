<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductWishlistedBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $product;
    public $buyer;
    public $seller;

    /**
     * Create a new message instance.
     */
    public function __construct($product, $buyer, $seller)
    {
        $this->product = $product;
        $this->buyer = $buyer;
        $this->seller = $seller;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product Wishlisted Buyer Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.product_wishlisted_buyer',
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

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('You added "' . $this->product->name . '" to your Favorites!')
            ->markdown('emails.product_wishlisted_buyer')
            ->with([
                'product' => $this->product,
                'buyer' => $this->buyer,
                'seller' => $this->seller,
            ]);
    }
}
