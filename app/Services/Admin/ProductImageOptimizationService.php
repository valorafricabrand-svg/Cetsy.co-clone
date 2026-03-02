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
            'orientation_corrected' => 0,
            'exif_guard_skipped' => 0,
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
                if (($result['skip_reason'] ?? null) === 'exif_guard') {
                    $stats['exif_guard_skipped']++;
                }
            }

            if (!empty($result['orientation_corrected'])) {
                $stats['orientation_corrected']++;
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
     * @return array{status:string,before_bytes:int,after_bytes:int,resized:bool,orientation_corrected:bool,skip_reason:?string}
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
            return ['status' => 'missing', 'before_bytes' => 0, 'after_bytes' => 0, 'resized' => false, 'orientation_corrected' => false, 'skip_reason' => null];
        }

        $absolutePath = $disk->path($publicPath);
        if (!is_file($absolutePath)) {
            return ['status' => 'missing', 'before_bytes' => 0, 'after_bytes' => 0, 'resized' => false, 'orientation_corrected' => false, 'skip_reason' => null];
        }

        $ext = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (!$this->canProcessExtension($ext)) {
            return ['status' => 'skipped', 'before_bytes' => 0, 'after_bytes' => 0, 'resized' => false, 'orientation_corrected' => false, 'skip_reason' => 'unsupported_extension'];
        }

        $beforeBytes = (int) @filesize($absolutePath);
        $isJpeg = in_array($ext, ['jpg', 'jpeg'], true);
        if ($isJpeg && !function_exists('exif_read_data')) {
            // Safety guard: without EXIF support, orientation tags cannot be trusted on rewrite.
            return [
                'status' => 'skipped',
                'before_bytes' => max(0, $beforeBytes),
                'after_bytes' => max(0, $beforeBytes),
                'resized' => false,
                'orientation_corrected' => false,
                'skip_reason' => 'exif_guard',
            ];
        }

        $orientation = $isJpeg ? $this->readExifOrientation($absolutePath) : null;
        $image = $manager->read($absolutePath);
        $orientationCorrected = false;
        if ($orientation !== null && $orientation >= 2 && $orientation <= 8) {
            $image = $this->applyExifOrientation($image, $orientation);
            $orientationCorrected = true;
        } else {
            $image->orient();
        }

        $beforeWidth = $image->width();
        $beforeHeight = $image->height();

        $image->scaleDown($maxWidth, $maxHeight);

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
                'orientation_corrected' => $orientationCorrected,
                'skip_reason' => 'not_smaller',
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
            'orientation_corrected' => $orientationCorrected,
            'skip_reason' => null,
        ];
    }

    private function readExifOrientation(string $absolutePath): ?int
    {
        if (!function_exists('exif_read_data')) {
            return null;
        }

        try {
            $exif = @exif_read_data($absolutePath, 'IFD0');
            $orientation = is_array($exif) ? ($exif['Orientation'] ?? null) : null;
            $orientation = is_numeric($orientation) ? (int) $orientation : null;
            if ($orientation !== null && $orientation >= 1 && $orientation <= 8) {
                return $orientation;
            }
        } catch (\Throwable $e) {
            // fallback to full EXIF payload parse below
        }

        try {
            $exif = @exif_read_data($absolutePath, null, true);
            if (!is_array($exif)) {
                return null;
            }

            $orientation = $exif['IFD0']['Orientation'] ?? $exif['Orientation'] ?? null;
            $orientation = is_numeric($orientation) ? (int) $orientation : null;

            return ($orientation !== null && $orientation >= 1 && $orientation <= 8)
                ? $orientation
                : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function applyExifOrientation($image, int $orientation)
    {
        return match ($orientation) {
            2 => $image->flop(),
            3 => $image->rotate(180),
            4 => $image->rotate(180)->flop(),
            5 => $image->rotate(270)->flop(),
            6 => $image->rotate(270),
            7 => $image->rotate(90)->flop(),
            8 => $image->rotate(90),
            default => $image,
        };
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
