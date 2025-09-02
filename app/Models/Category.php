<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'slug', 'image', 'listing_fee','listing_type', 'description'];

  

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
