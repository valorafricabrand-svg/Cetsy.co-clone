<?php

namespace App\Mail;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuyerReviewResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public Review $review;
    public User $buyer;

    public function __construct(Review $review, User $buyer)
    {
        $this->review = $review->loadMissing(['orderItem.product', 'order', 'shop']);
        $this->buyer = $buyer;
    }

    public function build(): self
    {
        $productName = optional(optional($this->review->orderItem)->product)->name ?: 'your order';
        $subject = 'Seller responded to your review on ' . $productName;

        // Prefer buyer order details page if exists
        $order = $this->review->order;
        $orderUrl = $order && \Illuminate\Support\Facades\Route::has('buyer.orders.show')
            ? route('buyer.orders.show', $order->id)
            : route('notifications.index');

        return $this->subject($subject)
            ->view('emails.buyer_review_response')
            ->with([
                'review'   => $this->review,
                'buyer'    => $this->buyer,
                'product'  => optional($this->review->orderItem)->product,
                'order'    => $this->review->order,
                'shop'     => $this->review->shop,
                'orderUrl' => $orderUrl,
            ]);
    }
}

