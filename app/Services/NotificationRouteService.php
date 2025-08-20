<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;

class NotificationRouteService
{
    /**
     * Get the appropriate route for a notification based on type and user role
     */
    public static function getRouteForNotification(Activity $notification, User $user): ?string
    {
        $type = $notification->type ?? 'general';
        
        switch ($type) {
            case Activity::TYPE_MESSAGE:
                return self::getMessageRoute($user);
                
            case Activity::TYPE_OFFER:
                return self::getOfferRoute($user);
                
            case Activity::TYPE_ORDER:
                return self::getOrderRoute($user);
                
            case Activity::TYPE_KYC:
                return self::getKycRoute($user);
                
            case Activity::TYPE_WALLET:
                return self::getWalletRoute($user);
                
            case Activity::TYPE_SUBSCRIPTION:
                return self::getSubscriptionRoute($user);
                
            case Activity::TYPE_PAYOUT:
                return self::getPayoutRoute($user);
                
            case Activity::TYPE_PRODUCT:
                return self::getProductRoute($user);
                
            default:
                return route('notifications.index');
        }
    }

    /**
     * Get message route based on user role
     */
    private static function getMessageRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('seller.messages.index');
        } elseif ($user->isAdmin()) {
            return route('admin.dashboard'); // Admin doesn't have specific message route
        } else {
            return route('buyer.messages.index');
        }
    }

    /**
     * Get offer route based on user role
     */
    private static function getOfferRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('seller.offers.index');
        } elseif ($user->isAdmin()) {
            return route('admin.dashboard'); // Admin doesn't have specific offer route
        } else {
            return route('buyer.offers.available-products');
        }
    }

    /**
     * Get order route based on user role
     */
    private static function getOrderRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('orders.index');
        } elseif ($user->isAdmin()) {
            return route('admin.dashboard'); // Admin doesn't have specific order route
        } else {
            return route('account.orders');
        }
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
     * Get product route based on user role
     */
    private static function getProductRoute(User $user): string
    {
        if ($user->isSeller()) {
            return route('products.index');
        } elseif ($user->isAdmin()) {
            return route('admin.products.index');
        } else {
            return route('products.index');
        }
    }

    /**
     * Get display text for the notification link
     */
    public static function getLinkText(Activity $notification, User $user): string
    {
        $type = $notification->type ?? 'general';
        
        switch ($type) {
            case Activity::TYPE_MESSAGE:
                return 'View Messages';
            case Activity::TYPE_OFFER:
                return 'View Offers';
            case Activity::TYPE_ORDER:
                return 'View Orders';
            case Activity::TYPE_KYC:
                return 'View KYC';
            case Activity::TYPE_WALLET:
                return 'View Wallet';
            case Activity::TYPE_SUBSCRIPTION:
                return 'View Subscription';
            case Activity::TYPE_PAYOUT:
                return 'View Payouts';
            case Activity::TYPE_PRODUCT:
                return 'View Products';
            default:
                return 'View Details';
        }
    }
}
