<?php

namespace App\Services\Admin;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductImageOptimizationService
{
    /**
     * Optimize product images stored on the public disk.
     *
     * @return array<string,int|string>
     */
    public function optimizeAll(
        int $maxWidth = 1600,
        int $maxHeight = 1600,
        int $quality = 82,
        ?callable $onProgress = null
    ): array
    {
        $maxWidth = max(320, min(4096, $maxWidth));
        $maxHeight = max(320, min(4096, $maxHeight));
        $quality = max(40, min(95, $quality));

        $stats = [
            'started_at' => now()->toDateTimeString(),
            'max_width' => $maxWidth,
            'max_height' => $maxHeight,
            'quality' => $quality,
            'scanned' => 0,
            'unique_paths' => 0,
            'optimized' => 0,
            'resized' => 0,
            'skipped' => 0,
            'missing' => 0,
            'errors' => 0,
            'before_bytes' => 0,
            'after_bytes' => 0,
            'saved_bytes' => 0,
        ];

        $seen = [];
        $manager = new ImageManager(new Driver());

        $consumePath = function (?string $rawPath, string $source) use (&$seen, &$stats, $manager, $maxWidth, $maxHeight, $quality, $onProgress): void {
            if (!is_string($rawPath) || trim($rawPath) === '') {
                return;
            }

            $stats['scanned']++;

            $normalized = $this->normalizePath($rawPath);
            if (!$normalized) {
                $stats['skipped']++;
                return;
            }

            $publicPath = $this->resolvePublicDiskPath($normalized);
            if (!$publicPath) {
                $stats['missing']++;
                return;
            }

            if (isset($seen[$publicPath])) {
                $stats['skipped']++;
                return;
            }

            $seen[$publicPath] = $source;
            $stats['unique_paths']++;

            try {
                $result = $this->optimizeSinglePath($publicPath, $manager, $maxWidth, $maxHeight, $quality);
            } catch (\Throwable $e) {
                $stats['errors']++;
                return;
            }

            $status = $result['status'] ?? 'skipped';
            if ($status === 'missing') {
                $stats['missing']++;
            } elseif ($status === 'optimized') {
                $stats['optimized']++;
                if (!empty($result['resized'])) {
                    $stats['resized']++;
                }
            } else {
                $stats['skipped']++;
            }

            $before = (int) ($result['before_bytes'] ?? 0);
            $after = (int) ($result['after_bytes'] ?? 0);
            $stats['before_bytes'] += $before;
            $stats['after_bytes'] += $after;
            $stats['saved_bytes'] += max(0, $before - $after);

            if (is_callable($onProgress)) {
                $onProgress($stats);
            }
        };

        // Primary source: media table images.
        Media::query()
            ->select(['id', 'url', 'type'])
            ->where(function ($q) {
                $q->where('type', 'image')->orWhereNull('type');
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($consumePath): void {
                foreach ($rows as $row) {
                    $consumePath($row->url, 'media:' . $row->id);
                }
            });

        // Secondary source: product featured_image and legacy image column.
        Product::query()
            ->select(['id', 'featured_image', 'image'])
            ->where(function ($q) {
                $q->whereNotNull('featured_image')->where('featured_image', '<>', '')
                    ->orWhereNotNull('image')->where('image', '<>', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($consumePath): void {
                foreach ($rows as $row) {
                    $consumePath($row->featured_image, 'product_featured:' . $row->id);
                    $consumePath($row->image, 'product_image:' . $row->id);
                }
            });

        $stats['ended_at'] = now()->toDateTimeString();

        return $stats;
    }

    /**
     * @return array{status:string,before_bytes:int,after_bytes:int,resized:bool}
     */
    private function optimizeSinglePath(
        string $publicPath,
        ImageManager $manager,
        int $maxWidth,
        int $maxHeight,
        int $quality
    ): array {
        $disk = Storage::disk('public');
        if (!$disk->exists($publicPath)) {
            return ['status' => 'missing', 'before_bytes' => 0, 'after_bytes' => 0, 'resized' => false];
        }

        $absolutePath = $disk->path($publicPath);
        if (!is_file($absolutePath)) {
            return ['status' => 'missing', 'before_bytes' => 0, 'after_bytes' => 0, 'resized' => false];
        }

        $ext = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (!$this->canProcessExtension($ext)) {
            return ['status' => 'skipped', 'before_bytes' => 0, 'after_bytes' => 0, 'resized' => false];
        }

        $beforeBytes = (int) @filesize($absolutePath);
        $image = $manager->read($absolutePath);

        $beforeWidth = $image->width();
        $beforeHeight = $image->height();

        $image->orient()->scaleDown($maxWidth, $maxHeight);

        $afterWidth = $image->width();
        $afterHeight = $image->height();
        $resized = ($afterWidth !== $beforeWidth) || ($afterHeight !== $beforeHeight);

        $encoded = $this->encodeByExtension($image, $ext, $quality);
        $encodedBytes = (string) $encoded;
        $afterBytes = strlen($encodedBytes);

        // Skip rewrite if nothing resized and file is not smaller.
        if (!$resized && $beforeBytes > 0 && $afterBytes >= $beforeBytes) {
            return [
                'status' => 'skipped',
                'before_bytes' => $beforeBytes,
                'after_bytes' => $beforeBytes,
                'resized' => false,
            ];
        }

        $disk->put($publicPath, $encodedBytes);
        clearstatcache(true, $absolutePath);
        $storedBytes = (int) @filesize($absolutePath);

        return [
            'status' => 'optimized',
            'before_bytes' => max(0, $beforeBytes),
            'after_bytes' => $storedBytes > 0 ? $storedBytes : max(0, $afterBytes),
            'resized' => $resized,
        ];
    }

    private function canProcessExtension(string $ext): bool
    {
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp'], true);
    }

    private function encodeByExtension($image, string $ext, int $quality)
    {
        return match ($ext) {
            'jpg', 'jpeg' => $image->toJpeg($quality),
            'webp' => $image->toWebp($quality),
            'png' => $image->toPng(),
            'bmp' => $image->toBmp(),
            default => $image->toJpeg($quality),
        };
    }

    private function normalizePath(string $rawPath): ?string
    {
        $path = trim($rawPath);
        if ($path === '' || Str::startsWith($path, 'data:')) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            $parts = parse_url($path);
            if (!$parts || empty($parts['path'])) {
                return null;
            }

            $host = $this->normalizeHost((string) ($parts['host'] ?? ''));
            $appHost = $this->normalizeHost((string) parse_url((string) config('app.url', ''), PHP_URL_HOST));

            // Skip external images; only local storage assets are supported here.
            if ($host !== '' && $appHost !== '' && $host !== $appHost) {
                return null;
            }

            $path = (string) $parts['path'];
        }

        $path = ltrim($path, '/');
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }
        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return ltrim($path, '/');
    }

    private function resolvePublicDiskPath(string $path): ?string
    {
        $disk = Storage::disk('public');
        $path = ltrim($path, '/');
        if ($path === '') {
            return null;
        }

        $candidates = [$path];
        $basename = basename($path);
        foreach (['product-media', 'product_media', 'products', 'products/images', 'product-images'] as $dir) {
            $candidates[] = $dir . '/' . $basename;
        }

        foreach (array_unique($candidates) as $candidate) {
            if ($disk->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        if (Str::startsWith($host, 'www.')) {
            return substr($host, 4);
        }
        return $host;
    }
}
