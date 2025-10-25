<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use App\Models\Media;
use App\Models\Product;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;



use Illuminate\Support\Facades\Log;

use Intervention\Image\Drivers\Gd\Driver; // or Imagick


class MediaController extends Controller
{
    /**
     * Upload media (images or videos) for a product.
     */
    public function upload(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'media'            => ['array'],
            'media.*'          => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,wmv,webm', 'max:51200'], // up to ~50MB
            'media_b64'        => ['array'],
            'media_b64.*'      => ['string'],
            'media_b64_names'  => ['array'],
            'media_b64_names.*'=> ['string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $hasFiles = $request->hasFile('media');
            $b64Items = collect($request->input('media_b64', []))
                ->filter(fn ($value) => is_string($value) && trim($value) !== '');

            if (! $hasFiles && $b64Items->isEmpty()) {
                $validator->errors()->add('media', 'Please select at least one media file.');
            }

            $names = collect($request->input('media_b64_names', []))
                ->filter(fn ($value) => is_string($value) && trim($value) !== '');

            if ($b64Items->isNotEmpty() && $names->count() && $names->count() < $b64Items->count()) {
                $validator->errors()->add('media_b64_names', 'Some cropped images are missing names.');
            }
        });

        $validator->validate();

        $before = $product->media()->count();
        $paths = [];

        // Handle uploaded files from <input type="file">
        foreach ($request->file('media', []) as $file) {
            $path = $file->store('product-media', 'public');
            $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
            $product->media()->create(['url' => $path, 'type' => $type]);
            $paths[] = $path;
        }

        // Handle cropped images provided as base64 payloads
        $base64Items = collect($request->input('media_b64', []))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '');
        $base64Names = $request->input('media_b64_names', []);
        $maxImageBytes = 5 * 1024 * 1024; // 5MB limit for images

        foreach ($base64Items as $index => $dataUrl) {
            $originalName = $base64Names[$index] ?? null;
            $paths[] = $this->storeBase64Image($product, $dataUrl, $originalName, $index, $maxImageBytes);
        }

        try {
            $after = $product->media()->count();
            Activity::create([
                'user_id'      => Auth::id(),
                'is_read'      => false,
                'type'         => Activity::TYPE_PRODUCT,
                'description'  => 'Updated product media',
                'related_id'   => $product->id,
                'related_type' => 'product',
                'properties'   => [
                    'section' => 'media',
                    'action'  => 'upload',
                    'files'   => $paths,
                    'counters'=> ['media' => ['from' => $before, 'to' => $after]],
                ],
            ]);
        } catch (\Throwable $e) { Log::error('media.activity.upload_failed', ['product_id' => $product->id, 'error' => $e->getMessage()]); }

        return back()->with('success', 'Media uploaded successfully.');
    }

    /**
     * Delete a product media image.
     */
    public function destroy(Media $media)
    {
        $product = $media->product;
        $before  = $product ? $product->media()->count() : null;
        Storage::disk('public')->delete($media->url);
        $media->delete();
        try {
            if ($product) {
                $after = $product->media()->count();
                Activity::create([
                    'user_id'      => Auth::id(),
                    'is_read'      => false,
                    'type'         => Activity::TYPE_PRODUCT,
                    'description'  => 'Removed product media',
                    'related_id'   => $product->id,
                    'related_type' => 'product',
                    'properties'   => [
                        'section' => 'media',
                        'action'  => 'delete',
                        'file'    => $media->url,
                        'counters'=> ['media' => ['from' => $before, 'to' => $after]],
                    ],
                ]);
            }
        } catch (\Throwable $e) { Log::error('media.activity.delete_failed', ['media_id' => $media->id, 'error' => $e->getMessage()]); }

        return back()->with('success', 'Media deleted successfully.');
    }

    /**
     * Bulk delete media belonging to a product.
     */
    public function bulkDestroy(Request $request, Product $product)
    {
        $ids = $request->input('media_ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        if (! is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return back()->withErrors('Select at least one media item to delete.');
        }

        $mediaItems = $product->media()->whereIn('id', $ids)->get();
        if ($mediaItems->isEmpty()) {
            return back()->withErrors('No matching media found for bulk delete.');
        }

        $before = $product->media()->count();
        $deletedFiles = [];

        foreach ($mediaItems as $media) {
            Storage::disk('public')->delete($media->url);
            $deletedFiles[] = $media->url;
            $media->delete();
        }

        try {
            $after = $product->media()->count();
            Activity::create([
                'user_id'      => Auth::id(),
                'is_read'      => false,
                'type'         => Activity::TYPE_PRODUCT,
                'description'  => 'Bulk removed product media',
                'related_id'   => $product->id,
                'related_type' => 'product',
                'properties'   => [
                    'section' => 'media',
                    'action'  => 'bulk_delete',
                    'files'   => $deletedFiles,
                    'counters'=> ['media' => ['from' => $before, 'to' => $after]],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('media.activity.bulk_delete_failed', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);
        }

        $count = count($deletedFiles);
        $message = $count === 1 ? '1 media item deleted.' : "{$count} media items deleted.";

        return back()->with('success', $message);
    }

    private function storeBase64Image(Product $product, string $dataUrl, ?string $originalName, int $index, int $maxBytes): string
    {
        $data = trim($dataUrl);
        $mime = null;
        if (preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $data, $matches)) {
            $mime = strtolower($matches[1]);
            $data = substr($data, strpos($data, ',') + 1);
        }

        $binary = base64_decode($data, true);
        if ($binary === false) {
            throw ValidationException::withMessages([
                'media_b64.' . $index => 'Invalid cropped image payload.',
            ]);
        }

        if (strlen($binary) > $maxBytes) {
            throw ValidationException::withMessages([
                'media_b64.' . $index => 'Cropped images must be 5MB or smaller.',
            ]);
        }

        if (@getimagesizefromstring($binary) === false) {
            throw ValidationException::withMessages([
                'media_b64.' . $index => 'The cropped image appears to be corrupt.',
            ]);
        }

        $extension = $this->determineImageExtension($mime, $originalName);
        $baseName = Str::slug((string) pathinfo((string) $originalName, PATHINFO_FILENAME));
        if (! $baseName) {
            $baseName = 'cropped-' . Str::lower(Str::random(6));
        }

        $filename = $baseName . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $path = 'product-media/' . $filename;

        Storage::disk('public')->put($path, $binary);
        $product->media()->create(['url' => $path, 'type' => 'image']);

        return $path;
    }

    private function determineImageExtension(?string $mime, ?string $originalName): string
    {
        $map = [
            'jpeg' => 'jpg',
            'pjpeg'=> 'jpg',
            'jpg'  => 'jpg',
            'png'  => 'png',
            'gif'  => 'gif',
            'bmp'  => 'bmp',
            'webp' => 'webp',
        ];

        if ($mime && isset($map[$mime])) {
            return $map[$mime];
        }

        $ext = strtolower((string) pathinfo((string) $originalName, PATHINFO_EXTENSION));
        if ($ext && in_array($ext, $map, true)) {
            return $ext;
        }

        return 'jpg';
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

        try {
            $product = $media->product;
            if ($product) {
                Activity::create([
                    'user_id'      => Auth::id(),
                    'is_read'      => false,
                    'type'         => Activity::TYPE_PRODUCT,
                    'description'  => 'Cropped product image',
                    'related_id'   => $product->id,
                    'related_type' => 'product',
                    'properties'   => [
                        'section' => 'media',
                        'action'  => 'crop',
                        'file'    => $media->url,
                        'quality' => $quality,
                    ],
                ]);
            }
        } catch (\Throwable $e) { Log::error('media.activity.crop_failed', ['media_id' => $media->id, 'error' => $e->getMessage()]); }

        return back()->with('success', 'Image cropped successfully.');
    } catch (\Throwable $e) {
        Log::error('Media crop failed: '.$e->getMessage());
        return back()->withErrors('Server error during cropping.');
    }
}

}
