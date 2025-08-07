<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use App\Models\Media;
use App\Models\Product;



use Illuminate\Support\Facades\Log;

use Intervention\Image\Drivers\Gd\Driver; // or Imagick


class MediaController extends Controller
{
    /**
     * Upload images for a product.
     */
    public function upload(Request $request, Product $product)
    {
        $request->validate([
            'media.*' => 'required|image|max:5120', // up to 5MB each
        ]);

        foreach ($request->file('media', []) as $image) {
            $path = $image->store('product-images', 'public');
            $product->media()->create(['url' => $path]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    /**
     * Delete a product media image.
     */
    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->url);
        $media->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Crop & overwrite an existing media item.
     */


public function crop(Media $media, Request $request)
{
    $diskPath = storage_path('app/public/'.$media->url);
    if (! file_exists($diskPath)) {
        return back()->withErrors('Original file not found.');
    }

    $quality = (int) $request->input('quality', 90);

    try {
        $manager = new ImageManager(new Driver()); // v3 style

        // 1) Prefer a regular uploaded file if present
        if ($request->hasFile('cropped_image') && $request->file('cropped_image')->isValid()) {
            $image = $manager->read($request->file('cropped_image')->getRealPath());
        }
        // 2) Otherwise accept base64 data URL (from the modal)
        elseif ($request->filled('cropped_image_b64')) {
            $b64 = $request->input('cropped_image_b64');
            // Strip "data:image/*;base64," header if present
            if (preg_match('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', $b64)) {
                $b64 = substr($b64, strpos($b64, ',') + 1);
            }
            $binary = base64_decode($b64, true);
            if ($binary === false) {
                return back()->withErrors('Invalid base64 image payload.');
            }
            $image = $manager->read($binary);
        }
        else {
            // Neither file nor base64 was provided
            return back()->withErrors('No valid image provided');
        }

        // Save as JPEG with requested quality (v3)
        $image->toJpeg($quality)->save($diskPath);

        return back()->with('success', 'Image cropped successfully.');
    } catch (\Throwable $e) {
        Log::error('Media crop failed: '.$e->getMessage());
        return back()->withErrors('Server error during cropping.');
    }
}

}
