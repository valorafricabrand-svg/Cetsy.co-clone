<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\User;

class ProductWishlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $product;
    public $wishlister;
    public $shopOwner;

    /**
     * Create a new message instance.
     */
    public function __construct(Product $product, User $wishlister, User $shopOwner)
    {
        $this->product = $product;
        $this->wishlister = $wishlister;
        $this->shopOwner = $shopOwner;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Product "' . $this->product->name . '" was Added to a Wishlist!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.product_wishlisted',
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
        return $this->subject('Your Product "' . $this->product->name . '" was Added to a Wishlist!')
            ->view('emails.product_wishlisted')
            ->with([
                'product' => $this->product,
                'wishlister' => $this->wishlister,
                'shopOwner' => $this->shopOwner,
            ]);
    }
} 