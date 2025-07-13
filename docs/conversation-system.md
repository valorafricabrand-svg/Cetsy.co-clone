# Conversation System Documentation

## Overview

The conversation system has been completely redesigned to provide a better messaging experience between buyers and sellers. The new system groups messages by product and participants, creating proper conversations instead of individual message threads.

## Key Features

### 1. Conversation-Based Messaging
- Messages are grouped by product and participants (buyer-seller pair)
- Each conversation has a unique ID: `{product_id}-{other_user_id}`
- Conversations show the complete message history between participants

### 2. Improved User Experience
- **Buyer Side**: View all conversations with sellers about specific products
- **Seller Side**: View all conversations with buyers about their products
- Real-time unread message counts
- Message status tracking (read/unread)

### 3. Enhanced Security
- Sellers can only view conversations about their own products
- Buyers can only view conversations they're part of
- Proper authorization checks on all routes

## Architecture

### Database Structure
```sql
messages table:
- id (primary key)
- sender_id (foreign key to users)
- receiver_id (foreign key to users)
- product_id (foreign key to products)
- body (text)
- is_read (boolean)
- created_at, updated_at
```

### Conversation ID Format
```
{product_id}-{other_user_id}
Example: "123-456" (product 123, other user 456)
```

## Controllers

### Main MessageController (`app/Http/Controllers/MessageController.php`)

#### Methods:
- `store()` - Create new messages
- `buyerIndex()` - Show buyer conversations
- `show()` - Show specific conversation (buyer)
- `sellerIndex()` - Show seller conversations  
- `sellerShow()` - Show specific conversation (seller)

### Seller MessageController (`app/Http/Controllers/Seller/MessageController.php`)

#### Methods:
- `index()` - Show seller conversations with filtering
- `show()` - Show specific conversation with authorization
- `markAsRead()` - Mark individual message as read
- `bulkMarkAsRead()` - Mark all unread messages as read
- `reply()` - Send reply to conversation

## Routes

### Buyer Routes
```php
Route::get('messages', [MessageController::class, 'buyerIndex'])->name('messages.index');
Route::get('messages/{conversationId}', [MessageController::class, 'show'])->name('messages.show');
```

### Seller Routes
```php
Route::get('messages', [Seller\MessageController::class, 'index'])->name('messages.index');
Route::get('messages/{conversationId}', [Seller\MessageController::class, 'show'])->name('messages.show');
Route::post('messages/{conversationId}/reply', [Seller\MessageController::class, 'reply'])->name('messages.reply');
Route::post('messages/{message}/mark-read', [Seller\MessageController::class, 'markAsRead'])->name('messages.mark-read');
Route::post('messages/bulk-mark-read', [Seller\MessageController::class, 'bulkMarkAsRead'])->name('messages.bulk-mark-read');
```

## Views

### Buyer Views
- `resources/views/buyer/messages/index.blade.php` - Conversation list
- `resources/views/buyer/messages/show.blade.php` - Individual conversation

### Seller Views
- `resources/views/seller/messages/index.blade.php` - Conversation list
- `resources/views/seller/messages/show.blade.php` - Individual conversation

## Features

### 1. Conversation Grouping
Messages are automatically grouped by:
- Product ID
- Participant pair (buyer-seller)

### 2. Unread Message Tracking
- Real-time unread count per conversation
- Visual indicators for unread messages
- Bulk mark as read functionality

### 3. Filtering Options
- Filter by product
- Filter by read status (unread only)
- Sort by latest activity

### 4. Email Notifications
- Automatic email notifications when messages are sent
- Uses Laravel's mail system
- Graceful error handling

### 5. Authorization
- Sellers can only access conversations about their products
- Buyers can only access conversations they're part of
- Proper validation of conversation IDs

## Usage Examples

### Creating a Message
```php
Message::create([
    'sender_id' => $buyer->id,
    'receiver_id' => $seller->id,
    'product_id' => $product->id,
    'body' => 'Hello, I\'m interested in this product!'
]);
```

### Getting Conversations (Buyer)
```php
$conversations = Message::where(function($query) use ($user) {
    $query->where('sender_id', $user->id)
          ->orWhere('receiver_id', $user->id);
})
->with(['product', 'sender', 'receiver'])
->orderBy('created_at', 'desc')
->get()
->groupBy(function($message) use ($user) {
    $otherUserId = $message->sender_id == $user->id ? $message->receiver_id : $message->sender_id;
    return $message->product_id . '-' . $otherUserId;
});
```

### Getting Conversations (Seller)
```php
$productIds = $shop->products()->pluck('id');
$conversations = Message::whereIn('product_id', $productIds)
    ->where(function($query) use ($user) {
        $query->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
    })
    // ... rest of grouping logic
```

## Testing

### Test Command
Run the test command to verify the system:
```bash
php artisan test:conversations
```

This will:
1. Create test users and products
2. Generate sample conversations
3. Test conversation grouping
4. Display results

## Migration from Old System

The old system used individual message threads. The new system:
1. Groups messages by product and participants
2. Uses conversation IDs instead of individual message IDs
3. Provides better organization and user experience

### Breaking Changes
- Route parameters changed from `{message}` to `{conversationId}`
- View variables updated to use conversation structure
- Authorization logic enhanced

## Security Considerations

1. **Authorization**: All routes check user permissions
2. **Data Validation**: Conversation IDs are validated
3. **SQL Injection**: Uses Eloquent ORM with proper parameter binding
4. **XSS Protection**: Blade templates automatically escape output

## Performance Optimizations

1. **Eager Loading**: Uses `with()` to load relationships
2. **Indexing**: Database indexes on frequently queried columns
3. **Pagination**: Can be easily added for large conversation lists
4. **Caching**: Conversation counts can be cached

## Future Enhancements

1. **Real-time Messaging**: WebSocket integration
2. **File Attachments**: Support for images and documents
3. **Message Search**: Full-text search capabilities
4. **Push Notifications**: Mobile app integration
5. **Message Templates**: Pre-written responses for sellers

## Troubleshooting

### Common Issues

1. **Conversation Not Found**
   - Check if conversation ID format is correct
   - Verify user has permission to access conversation

2. **Messages Not Grouping**
   - Ensure product_id is set correctly
   - Check sender_id and receiver_id relationships

3. **Unread Count Issues**
   - Verify `is_read` field is being updated
   - Check authorization logic

### Debug Commands
```bash
# Test conversation system
php artisan test:conversations

# Clear cache if needed
php artisan cache:clear

# Check database connections
php artisan tinker
```

## Support

For issues or questions about the conversation system:
1. Check this documentation
2. Review the test command output
3. Check Laravel logs for errors
4. Verify database structure and relationships 