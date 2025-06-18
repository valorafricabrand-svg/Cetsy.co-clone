<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use App\Models\Product;

class MediaController extends Controller
{
    /**
     * Upload images for a product.
     */
    public function upload(Request $request, Product $product)
    {
        $request->validate([
            'media.*' => 'image|max:2048', // max 2MB per image
        ]);

        foreach ($request->file('media', []) as $image) {
            $path = $image->store('product-images', 'public');

            $product->media()->create([
                'url' => $path,
            ]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    /**
     * Delete a product media image.
     */
    public function destroy(Media $media)
    {
        // Delete file from storage
        Storage::disk('public')->delete($media->url);

        // Delete database record
        $media->delete();

        return back()->with('success', 'Image deleted successfully.');
    }
}
