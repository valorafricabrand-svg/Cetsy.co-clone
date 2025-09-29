<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\User;
use App\Models\Shop;

class TrackingUpdatedBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $buyer;
    public $shop;
    public $shippingData;
    public $changed;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $buyer, Shop $shop, array $shippingData, array $changed)
    {
        $this->order = $order;
        $this->buyer = $buyer;
        $this->shop = $shop;
        $this->shippingData = $shippingData;
        $this->changed = $changed;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tracking Updated for Order #'.$this->order->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tracking_updated_buyer',
        );
    }

    /**
     * Attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Tracking Updated for Order #'.$this->order->id)
            ->view('emails.tracking_updated_buyer')
            ->with([
                'order'        => $this->order,
                'buyer'        => $this->buyer,
                'shop'         => $this->shop,
                'shippingData' => $this->shippingData,
                'changed'      => $this->changed,
            ]);
    }
}

