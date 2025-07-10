<?php
/* app/Http/Controllers/Admin/CategoryAttributeController.php */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\CategoryAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryAttributeController extends Controller
{
    /* -----------------------------------------------------------------
     |  POST /admin/categories/{category}/attributes
     |-----------------------------------------------------------------*/
    public function store(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'values' => 'required|string',          // comma-separated
        ]);

        DB::transaction(function () use ($category, $data) {
            $attr = $category->attributes()->create([
                'name' => $data['name'],
            ]);

            $values = collect(explode(',', $data['values']))
                        ->map(fn($v) => trim($v))
                        ->filter()
                        ->unique()
                        ->take(50)                   // safety cap
                        ->map(fn($val) => ['value' => $val]);

            $attr->values()->createMany($values);
        });

        return back()->with('success', 'Option created.');
    }

    /* -----------------------------------------------------------------
     |  PUT /admin/category-attributes/{attribute}
     |-----------------------------------------------------------------*/
    public function update(Request $request, CategoryAttribute $attribute)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'values' => 'required|string',
        ]);

        DB::transaction(function () use ($attribute, $data) {
            $attribute->update(['name' => $data['name']]);

            // Rebuild value list
            $newVals = collect(explode(',', $data['values']))
                         ->map(fn($v) => trim($v))
                         ->filter()
                         ->unique()
                         ->take(50);

            // Delete removed values
            $attribute->values()
                      ->whereNotIn('value', $newVals)
                      ->delete();

            // Add / update remaining
            foreach ($newVals as $val) {
                $attribute->values()->updateOrCreate(
                    ['value' => $val],
                    ['value' => $val]
                );
            }
        });

        return back()->with('success', 'Option updated.');
    }

    /* -----------------------------------------------------------------
     |  DELETE /admin/category-attributes/{attribute}
     |-----------------------------------------------------------------*/
    public function destroy(CategoryAttribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'Option deleted.');
    }
}
