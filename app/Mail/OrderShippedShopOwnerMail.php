<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\User;
use App\Models\Shop;

class OrderShippedShopOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $shopOwner;
    public $buyer;
    public $shop;
    public $shippingData;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $shopOwner, User $buyer, Shop $shop, array $shippingData)
    {
        $this->order = $order;
        $this->shopOwner = $shopOwner;
        $this->buyer = $buyer;
        $this->shop = $shop;
        $this->shippingData = $shippingData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->id . ' Shipped Successfully',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_shipped_shop_owner',
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
        return $this->subject('Order #' . $this->order->id . ' Shipped Successfully')
            ->view('emails.order_shipped_shop_owner')
            ->with([
                'order' => $this->order,
                'shopOwner' => $this->shopOwner,
                'buyer' => $this->buyer,
                'shop' => $this->shop,
                'shippingData' => $this->shippingData,
            ]);
    }
} 