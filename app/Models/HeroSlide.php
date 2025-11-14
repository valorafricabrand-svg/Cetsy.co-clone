<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class HeroSlide extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'tag',
        'button_label',
        'button_url',
        'image_path',
        'is_active',
        'sort_order',
        'deal_id',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'deal_id'    => 'integer',
        'category_id'=> 'integer',
    ];

    /**
     * Scope active slides (for homepage carousel).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Resolve the primary button URL, preferring deal/category links.
     */
    public function getResolvedButtonUrlAttribute(): string
    {
        if ($this->deal && Route::has('listings')) {
            return route('listings', ['deal' => $this->deal->id]);
        }

        if ($this->category && Route::has('category.show')) {
            return route('category.show', $this->category->slug);
        }

        if ($this->button_url) {
            return $this->button_url;
        }

        return Route::has('listings') ? route('listings') : url('/listings');
    }
}
