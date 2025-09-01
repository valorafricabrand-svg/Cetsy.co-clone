<?php

namespace App\Helpers;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity as admin notification
     */
    public static function logAdminNotification(
        string $title, 
        string $message, 
        ?string $link = null,
        $subject = null, 
        array $properties = []
    ) {
        $activity = new Activity();
        $activity->fill([
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => 'admin_notification',
            'is_read' => false,
            'properties' => $properties
        ]);
        
        // Set the user who caused this activity
        if (Auth::check()) {
            $activity->causer_type = get_class(Auth::user());
            $activity->causer_id = Auth::id();
        }
        
        // Set subject if provided
        if ($subject) {
            $activity->subject_type = get_class($subject);
            $activity->subject_id = $subject->id;
        }
        
        $activity->save();
        return $activity;
    }
    
    /**
     * Log a new product as admin notification
     */
    public static function logNewProduct($product)
    {
        return self::logAdminNotification(
            'New Product Listed',
            "A new product '{$product->name}' has been listed",
            route('admin.products.show', $product->id),
            $product,
            ['details' => "Product price: " . $product->price . " | Category: " . $product->category->name]
        );
    }
    
    /**
     * Log a new user registration as admin notification
     */
    public static function logNewUser($user)
    {
        return self::logAdminNotification(
            'New User Registered',
            "New user '{$user->name}' has registered on the platform",
            route('admin.users.show', $user->id),
            $user,
            ['details' => "Email: {$user->email} | Registration date: {$user->created_at}"]
        );
    }
}