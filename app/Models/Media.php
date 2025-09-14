<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'type', 'url', 'position'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL for the media file
     *
     * @return string
     */
    public function getUrl()
    {
        return asset('storage/' . $this->url);
    }
}
