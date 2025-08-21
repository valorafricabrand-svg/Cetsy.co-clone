# Notification Routing System

This document explains how the new notification routing system works in the Cetsy platform.

## Overview

The notification system now automatically routes users to the appropriate pages based on the notification type and their user role (buyer, seller, or admin).

## How It Works

### 1. Notification Types

Each notification now has a `type` field that categorizes it:

- `message` - Chat messages and communications
- `offer` - Product offers and negotiations
- `order` - Order updates and status changes
- `kyc` - KYC application status updates
- `wallet` - Wallet transactions and balance changes
- `subscription` - Subscription status updates
- `payout` - Payout request updates
- `product` - Product-related notifications
- `general` - General system notifications

### 2. Role-Based Routing

The system automatically determines the appropriate route based on user role:

#### Buyer Routes
- Messages: `route('buyer.messages.index')`
- Offers: `route('buyer.offers.available-products')`
- Orders: `route('account.orders')`
- Wallet: `route('wallet.index')`

#### Seller Routes
- Messages: `route('seller.messages.index')`
- Offers: `route('seller.offers.index')`
- Orders: `route('orders.index')`
- KYC: `route('seller.kyc')`
- Wallet: `route('wallet.index')`
- Subscriptions: `route('seller.subscription')`
- Payouts: `route('seller.payouts.index')`

#### Admin Routes
- KYC: `route('admin.kyc.index')`
- Wallets: `route('admin.wallets.index')`
- Payouts: `route('admin.payouts.index')`
- Products: `route('admin.products.index')`

### 3. Implementation

#### Activity Model
```php
protected $fillable = [
    'user_id', 
    'is_read', 
    'description', 
    'type',           // New field
    'related_id',     // New field for related entity
    'related_type'    // New field for related entity type
];
```

#### NotificationRouteService
The service class `App\Services\NotificationRouteService` handles:
- Route determination based on notification type and user role
- Link text generation for UI display
- Fallback to general notifications page

#### Usage in Controllers
```php
Activity::create([
    'user_id' => $user->id,
    'is_read' => false,
    'description' => 'You received a new message',
    'type' => \App\Models\Activity::TYPE_MESSAGE
]);
```

### 4. UI Display

#### Top Navigation Dropdown
- Shows recent notifications with clickable action buttons
- Each notification displays an appropriate action button (e.g., "View Messages", "View Offers")
- Buttons route users directly to the relevant page

#### Notifications Index Page
- Full list of all notifications
- Each notification includes an action button
- Consistent with dropdown behavior

### 5. Migration

Run the migration to add new fields:
```bash
php artisan migrate
```

This adds:
- `type` column for notification categorization
- `related_id` for linking to specific entities
- `related_type` for entity type identification
- Database indexes for performance

## Benefits

1. **Better User Experience**: Users can click directly to relevant pages
2. **Role-Aware Routing**: Different routes for different user types
3. **Consistent Interface**: Same behavior across dropdown and full page
4. **Extensible**: Easy to add new notification types and routes
5. **Performance**: Database indexes for efficient queries

## Future Enhancements

- Add notification preferences per user
- Implement push notifications
- Add notification templates
- Support for notification actions (accept/decline buttons)
- Notification history and analytics
