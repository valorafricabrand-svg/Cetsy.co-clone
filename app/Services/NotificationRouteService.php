<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use App\Models\Review;

class NotificationRouteService
{
    /**
     * Get the appropriate route for a notification based on type and user role
     */
    public static function getRouteForNotification(Activity $notification, User $user): ?string
    {
        // Reviews: always deep-link to the specific review context,
        // regardless of any generic link stored on the activity.
        if (($notification->related_type ?? null) === Review::class) {
            return self::getReviewRoute($notification, $user);
        }

        // For all other notifications, prefer an explicit link when present.
        if (!empty($notification->link)) {
            return $notification->link;
        }

        $type = $notification->type ?? 'general';
        
        switch ($type) {
            case Activity::TYPE_MESSAGE:
                return self::getMessageRoute($user, $notification);
            case Activity::TYPE_WISHLIST:
                return self::getWishlistRoute($notification, $user);
                
            case Activity::TYPE_OFFER:
                return self::getOfferRoute($user, $notification);
                
            case Activity::TYPE_ORDER:
                return self::getOrderRoute($user, $notification);
            
            case Activity::TYPE_DISPUTE:
                return self::getDisputeRoute($user, $notification);
                
            case Activity::TYPE_KYC:
                return self::getKycRoute($user);
                
            case Activity::TYPE_WALLET:
                return self::getWalletRoute($user);
                
            case Activity::TYPE_SUBSCRIPTION:
                return self::getSubscriptionRoute($user);
                
            case Activity::TYPE_PAYOUT:
                return self::getPayoutRoute($user);
                
            case Activity::TYPE_PRODUCT:
                return self::getProductRoute($user, $notification);
                
            default:
                return route('notifications.index');
        }
    }

    /**
     * Deep-link routing for review-related notifications.
     *
     * Sellers: jump to the specific review row on the reviews index.
     * Buyers: jump to the associated order details (where the review is shown).
     */
    private static function getReviewRoute(Activity $notification, User $user): string
    {
        $reviewId = (int) ($notification->related_id ?? 0);

        // Seller: link into their reviews dashboard, anchored to this review
        if (method_exists($user, 'isSeller') && $user->isSeller()) {
            if (\Illuminate\Support\Facades\Route::has('seller.reviews.index')) {
                $base = route('seller.reviews.index');
                return $reviewId > 0 ? $base . '#review-' . $reviewId : $base;
            }
            return route('notifications.index');
        }

        // Buyer: prefer order details page if we know the order
        if (method_exists($user, 'isBuyer') && $user->isBuyer()) {
            $orderId = 0;
            $props = $notification->properties ?? [];
            if (is_array($props) && isset($props['order_id'])) {
                $orderId = (int) $props['order_id'];
            }

            if ($orderId > 0 && \Illuminate\Support\Facades\Route::has('buyer.orders.show')) {
                $base = route('buyer.orders.show', $orderId);
                return $reviewId > 0 ? $base . '#review-' . $reviewId : $base;
            }

            // Fall back to any explicit link, then generic orders list
            if (!empty($notification->link)) {
                return $notification->link;
            }
            if (\Illuminate\Support\Facades\Route::has('account.orders')) {
                return route('account.orders');
            }
        }

        // Admin or unknown role: fall back to stored link or notifications index
        if (!empty($notification->link)) {
            return $notification->link;
        }

        return route('notifications.index');
    }

    /**
     * Get message route based on user role
     */
    private static function getMessageRoute(User $user, ?Activity $notification = null): string
    {
        try {
            if ($notification && $notification->related_id) {
                $msg = \App\Models\Message::with('product.shop')->find((int) $notification->related_id);
                if ($msg) {
                    $otherId = $user->id === $msg->sender_id ? $msg->receiver_id : $msg->sender_id;
                    $pid = (int) ($msg->product_id ?? 0);
                    $convId  = $pid . '-' . (int) $otherId;

                    if ($user->isSeller()) {
                        $isOwnShopProductMessage = $pid > 0
                            && (int) optional(optional($msg->product)->shop)->user_id === (int) $user->id;
                        $isInboundGeneralInquiry = $pid === 0 && (int) $msg->receiver_id === (int) $user->id;

                        // Seller accounts can also act as buyers. Route based on message context.
                        if ($isOwnShopProductMessage || $isInboundGeneralInquiry) {
                            return route('seller.messages.show', $convId);
                        }
                        return route('buyer.messages.show', $convId);
                    }

                    if ($user->isAdmin()) { return route('admin.messages.index'); }
                    return route('buyer.messages.show', $convId);
                }
            }
        } catch (\Throwable $e) {}
        if ($user->isSeller()) { return route('seller.messages.index'); }
        if ($user->isAdmin()) { return route('admin.messages.index'); }
        return route('buyer.messages.index');
    }

    private static function getWishlistRoute(Activity $notification, User $user): string
    {
        // For sellers: deep link to conversation with prefilled message
        if ($user->isSeller()) {
            $productId = (int) ($notification->related_id ?? 0);
            $buyerId = (int) ($notification->causer_id ?? 0);
            if ($productId > 0 && $buyerId > 0) {
                $conversationId = $productId.'-'.$buyerId;
                $prefill = '';
                try {
                    $product = \App\Models\Product::find($productId);
                    $buyer = \App\Models\User::find($buyerId);
                    if ($product && $buyer) {
                        $prefill = 'Hi '.$buyer->name.', thanks for favoriting “'.$product->name.'”. Can I answer any questions or offer help?';
                    }
                } catch (\Throwable $e) {}
                $url = route('seller.messages.show', $conversationId);
                if ($prefill) {
                    $url .= '?prefill='.urlencode($prefill);
                }
                return $url;
            }
            return route('seller.messages.index');
        }
        // Buyers -> product page; Admin -> notifications
        if ($user->isBuyer()) {
            $productId = (int) ($notification->related_id ?? 0);
            if ($productId > 0) {
                try {
                    $p = \App\Models\Product::find($productId);
                    if ($p) return route('products.show', $p->slug ?? $p->id);
                } catch (\Throwable $e) {}
            }
            return route('products.index');
        }
        return route('admin.notifications.index');
    }

    /**
     * Get offer route based on user role
     */
    private static function getOfferRoute(User $user, ?Activity $notification = null): string
    {
        $offerId = (int) ($notification->related_id ?? 0);

        if ($user->isSeller()) {
            // Deep link to a specific offer if possible
            if ($offerId > 0 && \Illuminate\Support\Facades\Route::has('seller.offers.show')) {
                return route('seller.offers.show', $offerId);
            }
            return route('seller.offers.index');
        } elseif ($user->isAdmin()) {
            return route('admin.dashboard'); // Admin doesn't have specific offer route
        } else {
            // Buyer: deep link to offer details when available
            if ($offerId > 0 && \Illuminate\Support\Facades\Route::has('buyer.offers.details')) {
                return route('buyer.offers.details', $offerId);
            }
            // Fallback: buyer offers dashboard or available products
            if (\Illuminate\Support\Facades\Route::has('buyer.offers')) {
                return route('buyer.offers');
            }
            return route('buyer.offers.available-products');
        }
    }

    /**
     * Get order route based on user role
     */
    private static function getOrderRoute(User $user, ?Activity $notification = null): string
    {
        $orderId = (int) ($notification->related_id ?? 0);
        if ($user->isSeller()) {
            if ($orderId > 0 && \Illuminate\Support\Facades\Route::has('seller.orders.show')) {
                return route('seller.orders.show', $orderId);
            }
            if (\Illuminate\Support\Facades\Route::has('seller.orders.index')) {
                return route('seller.orders.index');
            }
            return route('orders.index');
        } elseif ($user->isAdmin()) {
            return route('admin.dashboard'); // Admin doesn't have specific order route
        } else {
            if ($orderId > 0 && \Illuminate\Support\Facades\Route::has('buyer.orders.show')) {
                return route('buyer.orders.show', $orderId);
            }
            return route('account.orders');
        }
    }

    /**
     * Get dispute route based on user role and related_id
     */
    private static function getDisputeRoute(User $user, ?Activity $notification = null): string
    {
        $disputeId = (int) ($notification->related_id ?? 0);
        if ($user->isAdmin()) {
            // Unify to public dispute view even for admins
            if ($disputeId > 0 && \Illuminate\Support\Facades\Route::has('disputes.show')) {
                return route('disputes.show', $disputeId);
            }
            return route('admin.notifications.index');
        }
        if ($disputeId > 0 && \Illuminate\Support\Facades\Route::has('disputes.show')) {
            return route('disputes.show', $disputeId);
        }
        return route('notifications.index');
    }

    /**
     * Get KYC route based on user role
     */
    private static function getKycRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('seller.kyc');
        } elseif ($user->isAdmin()) {
            return route('admin.kyc.index');
        } else {
            return route('notifications.index'); // Buyers don't have KYC
        }
    }

    /**
     * Get wallet route based on user role
     */
    private static function getWalletRoute(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.wallets.index');
        } else {
            return route('wallet.index');
        }
    }

    /**
     * Get subscription route based on user role
     */
    private static function getSubscriptionRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('seller.subscription');
        } elseif ($user->isAdmin()) {
            return route('admin.dashboard'); // Admin doesn't have specific subscription route
        } else {
            return route('notifications.index'); // Buyers don't have subscriptions
        }
    }

    /**
     * Get payout route based on user role
     */
    private static function getPayoutRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('seller.payouts.index');
        } elseif ($user->isAdmin()) {
            return route('admin.payouts.index');
        } else {
            return route('notifications.index'); // Buyers don't have payouts
        }
    }

    /**
     * Get product route based on user role and product context.
     *
     * When a notification is tied to a specific product (via related_id or
     * properties.product_id), we deep-link to that listing instead of the
     * generic products index.
     */
    private static function getProductRoute(User $user, ?Activity $notification = null): string
    {
        $product = null;

        if ($notification) {
            $productId = (int) ($notification->related_id ?? 0);

            // Some activities store product_id inside the JSON properties bag.
            if ($productId <= 0 && is_array($notification->properties ?? null)) {
                $productId = (int) ($notification->properties['product_id'] ?? 0);
            }

            if ($productId > 0) {
                try {
                    $product = \App\Models\Product::find($productId);
                } catch (\Throwable $e) {
                    $product = null;
                }
            }
        }

        // Admins: go to admin product detail when possible
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            if ($product && \Illuminate\Support\Facades\Route::has('admin.products.show')) {
                return route('admin.products.show', $product->id);
            }
            if (\Illuminate\Support\Facades\Route::has('admin.products.index')) {
                return route('admin.products.index');
            }
            return route('admin.notifications.index');
        }

        // Sellers / buyers: prefer specific listing when we know the product
        if ($product) {
            // Sellers usually work with the internal product detail page
            if (method_exists($user, 'isSeller') && $user->isSeller()) {
                if (\Illuminate\Support\Facades\Route::has('products.show')) {
                    return route('products.show', $product);
                }
            }

            // Buyers and others: prefer the public listing page if available
            if (!empty($product->slug) && \Illuminate\Support\Facades\Route::has('listing.show')) {
                return route('listing.show', $product->slug);
            }

            if (\Illuminate\Support\Facades\Route::has('products.show')) {
                return route('products.show', $product);
            }
        }

        // Fallbacks when we could not resolve a specific product
        if (method_exists($user, 'isSeller') && $user->isSeller()) {
            return route('products.index');
        }

        if (method_exists($user, 'isBuyer') && $user->isBuyer()) {
            return route('wishlist');
        }

        return route('products.index');
    }

    /**
     * Get display text for the notification link
     */
    public static function getLinkText(Activity $notification, User $user): string
    {
        $type = $notification->type ?? 'general';
        // If this notification is about a Review, prefer a review-specific label
        if (($notification->related_type ?? null) === Review::class) {
            return 'View Review';
        }
        
        switch ($type) {
            case Activity::TYPE_MESSAGE:
                return 'View Messages';
            case Activity::TYPE_WISHLIST:
                return $user->isSeller() ? 'Message Buyer' : 'View Product';
            case Activity::TYPE_OFFER:
                return 'View Offers';
            case Activity::TYPE_ORDER:
                return 'View Order';
            case Activity::TYPE_DISPUTE:
                return 'View Dispute';
            case Activity::TYPE_KYC:
                return 'View KYC';
            case Activity::TYPE_WALLET:
                return 'View Wallet';
            case Activity::TYPE_SUBSCRIPTION:
                return 'View Subscription';
            case Activity::TYPE_PAYOUT:
                return 'View Payouts';
            case Activity::TYPE_PRODUCT:
                // Product-related activities: show more human wording
                if (method_exists($user, 'isSeller') && $user->isSeller()) {
                    return 'View Listing';
                }
                return 'View Product';
            default:
                return '';
        }
    }
}
