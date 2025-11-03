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

class OrderCancelledShopOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $shopOwner;
    public $buyer;
    public $shop;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $shopOwner, User $buyer, Shop $shop, ?string $reason = null)
    {
        $this->order = $order;
        $this->shopOwner = $shopOwner;
        $this->buyer = $buyer;
        $this->shop = $shop;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->id . ' Cancelled by Buyer',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_cancelled_shop_owner',
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Order #' . $this->order->id . ' Cancelled by Buyer')
            ->view('emails.order_cancelled_shop_owner')
            ->with([
                'order'     => $this->order,
                'shopOwner' => $this->shopOwner,
                'buyer'     => $this->buyer,
                'shop'      => $this->shop,
                'reason'    => $this->reason,
            ]);
    }
}

