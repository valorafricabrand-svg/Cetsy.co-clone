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

class OrderCancelledBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $buyer;
    public $shop;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $buyer, Shop $shop, ?string $reason = null)
    {
        $this->order = $order;
        $this->buyer = $buyer;
        $this->shop  = $shop;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->id . ' Cancelled',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_cancelled_buyer',
        );
    }

    /**
     * Attachments.
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Order #' . $this->order->id . ' Cancelled')
            ->view('emails.order_cancelled_buyer')
            ->with([
                'order'  => $this->order,
                'buyer'  => $this->buyer,
                'shop'   => $this->shop,
                'reason' => $this->reason,
            ]);
    }
}

