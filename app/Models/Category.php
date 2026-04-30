<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'slug', 'image', 'listing_fee','listing_type', 'description', 'listing_frequency'];

    public static function toPlainDescription(?string $description): ?string
    {
        $description = trim((string) $description);

        if ($description === '') {
            return null;
        }

        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = preg_replace('/<\s*br\s*\/?>/i', ' ', $description);
        $description = preg_replace('/<\/\s*(p|div|li|h[1-6]|tr|td|th|ul|ol|blockquote)\s*>/i', ' ', $description);
        $description = strip_tags($description);
        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = str_replace("\xC2\xA0", ' ', $description);
        $description = preg_replace('/\s+/u', ' ', $description);

        return trim($description) ?: null;
    }

    public function getPlainDescriptionAttribute(): ?string
    {
        return self::toPlainDescription($this->description);
    }

    public static function findBySlugOrNameSlug(string $slug): ?self
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $category = self::where('slug', $slug)->first();

        if ($category) {
            return $category;
        }

        return self::query()
            ->get()
            ->first(fn (self $category) => in_array($slug, self::slugCandidates($category->name), true));
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (($field ?? $this->getRouteKeyName()) === 'slug') {
            return self::findBySlugOrNameSlug((string) $value);
        }

        return parent::resolveRouteBinding($value, $field);
    }

    private static function slugCandidates(?string $name): array
    {
        $name = html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $javascriptSlug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');

        return array_values(array_unique(array_filter([
            Str::slug($name),
            $javascriptSlug,
        ])));
    }


    // Direct children (one-level down)
public function children()
{
    return $this->hasMany(self::class, 'parent_id');
}

// Optional: the inverse (upwards) if you need it elsewhere
public function parent()
{
    return $this->belongsTo(self::class, 'parent_id');
}





    public function products()
{
    return $this->hasMany(Product::class, 'category_id');
}


    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    /**
 * Recursive children relationship (all descendants, eager-loadable)
 */
public function childrenRecursive()
{
    // Call itself recursively so every level comes back in one query
    return $this->children()->with('childrenRecursive');
}


public function attributes()
{
    return $this->hasMany(CategoryAttribute::class);
}


}
