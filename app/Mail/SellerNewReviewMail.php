<?php

namespace App\Mail;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerNewReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public Review $review;
    public User $seller;

    public function __construct(Review $review, User $seller)
    {
        $this->review = $review->loadMissing(['orderItem.product', 'order', 'shop']);
        $this->seller = $seller;
    }

    public function build(): self
    {
        $productName = optional(optional($this->review->orderItem)->product)->name ?: 'your listing';
        $subject = 'New review received on ' . $productName;

        return $this->subject($subject)
            ->view('emails.seller_new_review')
            ->with([
                'review'   => $this->review,
                'seller'   => $this->seller,
                'product'  => optional($this->review->orderItem)->product,
                'order'    => $this->review->order,
                'shop'     => $this->review->shop,
                'reviewsUrl' => route('seller.reviews.index'),
            ]);
    }
}

