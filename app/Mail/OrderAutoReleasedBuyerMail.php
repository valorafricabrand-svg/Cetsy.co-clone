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

class OrderAutoReleasedBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public User $buyer;
    public ?User $seller;
    public ?Shop $shop;

    public function __construct(Order $order, User $buyer, ?Shop $shop = null, ?User $seller = null)
    {
        $this->order  = $order;
        $this->buyer  = $buyer;
        $this->shop   = $shop;
        $this->seller = $seller;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Completed — Payment Released (Order #'.$this->order->id.')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_auto_released_buyer',
            with: [
                'order'  => $this->order,
                'buyer'  => $this->buyer,
                'seller' => $this->seller,
                'shop'   => $this->shop,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

