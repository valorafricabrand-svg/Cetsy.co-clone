<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;

class DigitalOrderCompletedSellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public User $seller;
    public ?User $buyer;
    public ?Shop $shop;

    public function __construct(Order $order, User $seller, ?User $buyer = null, ?Shop $shop = null)
    {
        $this->order  = $order;
        $this->seller = $seller;
        $this->buyer  = $buyer;
        $this->shop   = $shop;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Released for Digital Order #'.$this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_digital_completed_seller',
            with: [
                'order'  => $this->order,
                'seller' => $this->seller,
                'buyer'  => $this->buyer,
                'shop'   => $this->shop,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

