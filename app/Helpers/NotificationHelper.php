<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    /**
     * Create a new notification from site activity
     */
    public static function createNotification(string $title, string $message, ?string $link = null): Notification
    {
        return Notification::create([
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false
        ]);
    }
    
    /**
     * Create notification for new product listing
     */
    public static function newProductListed($product)
    {
        return self::createNotification(
            'New Product Listed',
            "A new product '{$product->name}' has been listed by seller {$product->seller->name}",
            route('admin.products.show', $product->id)
        );
    }
    
    /**
     * Create notification for new seller registration
     */
    public static function newSellerRegistered($seller)
    {
        return self::createNotification(
            'New Seller Registered',
            "A new seller '{$seller->name}' has registered on the platform",
            route('admin.sellers.show', $seller->id)
        );
    }
    
    /**
     * Create notification for reported product
     */
    public static function productReported($report)
    {
        return self::createNotification(
            'Product Reported',
            "The product '{$report->product->name}' has been reported by a user",
            route('admin.product-reports.show', $report->id)
        );
    }
}