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
use App\Models\Payment;

class PaymentSuccessShopOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $shopOwner;
    public $buyer;
    public $shop;
    public $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $shopOwner, User $buyer, Shop $shop, Payment $payment)
    {
        $this->order = $order;
        $this->shopOwner = $shopOwner;
        $this->buyer = $buyer;
        $this->shop = $shop;
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received for Order #' . $this->order->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_success_shop_owner',
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
        return $this->subject('Payment Received for Order #' . $this->order->id)
            ->view('emails.payment_success_shop_owner')
            ->with([
                'order' => $this->order,
                'shopOwner' => $this->shopOwner,
                'buyer' => $this->buyer,
                'shop' => $this->shop,
                'payment' => $this->payment,
            ]);
    }
} 