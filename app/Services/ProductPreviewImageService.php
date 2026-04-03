<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ProductPreviewImageService
{
    private const CACHE_PREFIX = 'preview-cache/products';

    /**
     * @var array<string, array<string, int|string>>
     */
    private const VARIANTS = [
        'thumb' => [
            'max_width' => 720,
            'max_height' => 720,
            'label' => 'CETSY PREVIEW',
            'font_size' => 14,
            'padding_x' => 8,
            'padding_y' => 6,
            'margin' => 10,
            'quality' => 80,
        ],
        'display' => [
            'max_width' => 1600,
            'max_height' => 1600,
            'label' => 'CETSY PREVIEW',
            'font_size' => 18,
            'padding_x' => 10,
            'padding_y' => 7,
            'margin' => 16,
            'quality' => 84,
        ],
    ];

    public function __construct(
        private ?ImageManager $manager = null
    ) {
        $this->manager ??= new ImageManager(new Driver());
    }

    public function productPreviewUrl(Product $product, string $variant = 'thumb'): string
    {
        return route('preview.products.image', [
            'product' => $product,
            'variant' => $this->normalizeVariant($variant),
        ]);
    }

    public function mediaPreviewUrl(Media $media, string $variant = 'display'): string
    {
        return route('preview.media.image', [
            'media' => $media,
            'variant' => $this->normalizeVariant($variant),
        ]);
    }

    /**
     * @return array{path:string,mime:string}|null
     */
    public function buildProductPreview(Product $product, string $variant = 'thumb'): ?array
    {
        $source = $this->resolveProductSourcePath($product);
        if ($source === null) {
            return null;
        }

        return $this->buildPreviewFromPublicPath(
            $source,
            $this->normalizeVariant($variant),
            'product-' . $product->getKey()
        );
    }

    /**
     * @return array{path:string,mime:string}|null
     */
    public function buildMediaPreview(Media $media, string $variant = 'display'): ?array
    {
        $type = strtolower((string) ($media->type ?? ''));
        if ($type === 'video') {
            return null;
        }

        $source = $this->resolvePublicPath($media->url ?? null);
        if ($source === null) {
            return null;
        }

        return $this->buildPreviewFromPublicPath(
            $source,
            $this->normalizeVariant($variant),
            'media-' . $media->getKey()
        );
    }

    private function normalizeVariant(string $variant): string
    {
        return array_key_exists($variant, self::VARIANTS) ? $variant : 'display';
    }

    private function resolveProductSourcePath(Product $product): ?string
    {
        $featured = $product->featured_image ?? null;
        if (is_string($featured) && trim($featured) !== '' && !is_video_media_path($featured)) {
            $resolved = $this->resolvePublicPath($featured);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        $mediaItems = collect();
        try {
            if ($product->relationLoaded('media')) {
                $mediaItems = collect($product->media ?? []);
            } elseif (method_exists($product, 'media')) {
                $mediaItems = $product->media()->get();
            }
        } catch (\Throwable $e) {
            $mediaItems = collect();
        }

        $media = $mediaItems->first(function ($item) {
            $url = (string) ($item->url ?? '');
            if ($url === '') {
                return false;
            }

            $type = strtolower((string) ($item->type ?? ''));
            if ($type === 'video') {
                return false;
            }

            return !is_video_media_path($url);
        });

        return $media ? $this->resolvePublicPath($media->url ?? null) : null;
    }

    private function resolvePublicPath(?string $path): ?string
    {
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $candidate = trim($path);
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://') || str_starts_with($candidate, '//')) {
            return null;
        }

        $rel = storage_rel_path($candidate);
        if (!$rel) {
            return null;
        }

        $disk = Storage::disk('public');
        $candidates = [$rel];
        $basename = basename($rel);
        foreach (['product-media', 'product_media', 'product-images', 'products'] as $dir) {
            $candidates[] = $dir . '/' . $basename;
        }

        foreach ($candidates as $candidatePath) {
            try {
                if ($disk->exists($candidatePath)) {
                    return $candidatePath;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * @return array{path:string,mime:string}|null
     */
    private function buildPreviewFromPublicPath(string $sourceRelPath, string $variant, string $keyPrefix): ?array
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($sourceRelPath)) {
            return null;
        }

        $sourceAbsPath = $disk->path($sourceRelPath);
        if (!is_file($sourceAbsPath)) {
            return null;
        }

        $sourceExt = strtolower((string) pathinfo($sourceAbsPath, PATHINFO_EXTENSION));
        $targetExt = $this->targetExtension($sourceExt);
        $fingerprint = sha1($sourceRelPath . '|' . ((string) @filemtime($sourceAbsPath)) . '|' . $variant . '|v1');
        $targetRelPath = self::CACHE_PREFIX . '/' . $keyPrefix . '/' . $fingerprint . '.' . $targetExt;

        if (!$disk->exists($targetRelPath)) {
            $this->renderPreview($sourceAbsPath, $targetRelPath, $variant, $targetExt);
        }

        if (!$disk->exists($targetRelPath)) {
            return null;
        }

        return [
            'path' => $disk->path($targetRelPath),
            'mime' => $this->mimeForExtension($targetExt),
        ];
    }

    private function renderPreview(string $sourceAbsPath, string $targetRelPath, string $variant, string $targetExt): void
    {
        $config = self::VARIANTS[$variant] ?? self::VARIANTS['display'];

        $image = $this->manager
            ->read($sourceAbsPath)
            ->removeAnimation()
            ->orient()
            ->scaleDown((int) $config['max_width'], (int) $config['max_height']);

        $this->applyWatermark($image, $config);

        $encoded = match ($targetExt) {
            'png' => $image->encode(new PngEncoder()),
            'webp' => $image->encode(new WebpEncoder((int) $config['quality'], strip: true)),
            default => $image->encode(new JpegEncoder((int) $config['quality'], progressive: true, strip: true)),
        };

        Storage::disk('public')->put($targetRelPath, (string) $encoded);
    }

    /**
     * @param array<string, int|string> $config
     */
    private function applyWatermark(ImageInterface $image, array $config): void
    {
        $label = (string) ($config['label'] ?? 'CETSY PREVIEW');
        $fontPath = $this->resolveFontPath();
        $fontSize = max(12, (int) ($config['font_size'] ?? 14));
        $paddingX = max(4, (int) ($config['padding_x'] ?? 8));
        $paddingY = max(3, (int) ($config['padding_y'] ?? 6));
        $margin = max(6, (int) ($config['margin'] ?? 10));

        if ($fontPath !== null) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $label) ?: [0, 0, 0, 0, 0, 0, 0, 0];
            $textWidth = (int) abs(($bbox[2] ?? 0) - ($bbox[0] ?? 0));
            $textHeight = (int) abs(($bbox[7] ?? 0) - ($bbox[1] ?? 0));
        } else {
            $textWidth = 0;
            $textHeight = 0;
        }

        $badgeWidth = $textWidth + ($paddingX * 2);
        $badgeHeight = $textHeight + ($paddingY * 2);

        $x = max(0, $image->width() - $badgeWidth - $margin);
        $y = max(0, $image->height() - $badgeHeight - $margin);

        $image->drawRectangle($x, $y, function ($rectangle) use ($badgeWidth, $badgeHeight) {
            $rectangle->size($badgeWidth, $badgeHeight);
            $rectangle->background('rgba(15, 23, 42, 0.72)');
        });

        if ($fontPath === null) {
            return;
        }

        $image->text($label, $x + $paddingX, $y + $paddingY, function ($fontFactory) use ($fontPath, $fontSize) {
            $fontFactory->file($fontPath);
            $fontFactory->size($fontSize);
            $fontFactory->color('rgba(255, 255, 255, 1)');
            $fontFactory->stroke('rgba(15, 23, 42, 1)', 1);
            $fontFactory->align('left');
            $fontFactory->valign('top');
        });
    }

    private function resolveFontPath(): ?string
    {
        $candidates = [
            resource_path('fonts/DejaVuSans-Bold.ttf'),
            public_path('fonts/DejaVuSans-Bold.ttf'),
            storage_path('fonts/DejaVuSans-Bold.ttf'),
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function targetExtension(string $sourceExt): string
    {
        return match ($sourceExt) {
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };
    }

    private function mimeForExtension(string $extension): string
    {
        return match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
