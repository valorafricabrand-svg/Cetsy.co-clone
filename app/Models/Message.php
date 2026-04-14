<?php
// app/Models/Message.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'product_id',
        'body',
        'is_read',
        'attachment_path',
        'shared_listing_ids',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'shared_listing_ids' => 'array',
    ];

    public function sender()   { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
    public function product()  { return $this->belongsTo(Product::class); }

    public static function hydrateSharedProducts(iterable $messages): void
    {
        $messageCollection = $messages instanceof Collection ? $messages : collect($messages);

        $sharedIds = $messageCollection
            ->flatMap(fn ($message) => collect($message->shared_listing_ids ?? []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($sharedIds->isEmpty()) {
            $messageCollection->each(fn ($message) => $message->setRelation('sharedProducts', collect()));
            return;
        }

        $products = Product::with(['media', 'shop'])
            ->whereIn('id', $sharedIds)
            ->get()
            ->keyBy(fn ($product) => (int) $product->id);

        $messageCollection->each(function ($message) use ($products) {
            $sharedProducts = collect($message->shared_listing_ids ?? [])
                ->map(fn ($id) => $products->get((int) $id))
                ->filter()
                ->values();

            $message->setRelation('sharedProducts', $sharedProducts);
        });
    }
}
