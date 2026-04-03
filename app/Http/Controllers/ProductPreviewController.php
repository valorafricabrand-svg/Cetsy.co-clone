<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use App\Services\ProductPreviewImageService;
use Illuminate\Http\Request;

class ProductPreviewController extends Controller
{
    public function product(Request $request, Product $product, ProductPreviewImageService $previews)
    {
        $variant = (string) $request->query('variant', 'thumb');

        if (!product_is_digital($product)) {
            return redirect()->to(product_raw_thumb_url($product));
        }

        $preview = $previews->buildProductPreview($product, $variant);
        if ($preview === null) {
            return redirect()->to(product_raw_thumb_url($product));
        }

        return response()->file($preview['path'], $this->headers($preview['mime']));
    }

    public function media(Request $request, Media $media, ProductPreviewImageService $previews)
    {
        $variant = (string) $request->query('variant', 'display');
        $product = $media->relationLoaded('product') ? $media->product : $media->product()->with('category')->first();

        if (!$product) {
            abort(404);
        }

        if (!product_is_digital($product) || strtolower((string) ($media->type ?? '')) === 'video') {
            return redirect()->to(media_url($media->url));
        }

        $preview = $previews->buildMediaPreview($media, $variant);
        if ($preview === null) {
            return redirect()->to(media_url($media->url));
        }

        return response()->file($preview['path'], $this->headers($preview['mime']));
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $mime): array
    {
        return [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800',
            'X-Robots-Tag' => 'noimageindex',
        ];
    }
}
