<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedBuyerMail extends LocalizedMailable
{
    use Queueable, SerializesModels;

    public $order;
    public $buyer;
    public $shop;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $buyer, Shop $shop)
    {
        $this->order = $order;
        $this->buyer = $buyer;
        $this->shop = $shop;
        $this->usePreferredLocale($buyer, $shop);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.order_created_buyer.subject', ['order' => $this->order->id]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_created_buyer',
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
        return $this->subject(__('emails.order_created_buyer.subject', ['order' => $this->order->id]))
            ->view('emails.order_created_buyer')
            ->with([
                'order' => $this->order,
                'buyer' => $this->buyer,
                'shop' => $this->shop,
            ]);
    }
}
