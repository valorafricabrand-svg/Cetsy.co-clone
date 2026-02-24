<?php

namespace App\Jobs\Admin;

use App\Services\Admin\ProductImageOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RunProductImageOptimization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CANCEL_EXCEPTION = '__IMAGE_OPTIMIZER_CANCELLED__';

    public int $tries = 1;

    /**
     * Increase timeout for large catalogs.
     */
    public int $timeout = 7200;

    public function __construct(
        public int $settingId,
        public string $runId,
        public int $maxWidth,
        public int $maxHeight,
        public int $quality,
        public ?int $requestedBy = null
    ) {
    }

    public function handle(ProductImageOptimizationService $optimizer): void
    {
        $cacheKey = $this->cacheKey();

        $status = Cache::get($cacheKey, []);
        if (!is_array($status)) {
            $status = [];
        }
        if (($status['run_id'] ?? null) !== $this->runId) {
            // Ignore stale jobs if a newer run replaced the cache payload.
            return;
        }

        if ($this->isCancellationRequested($status)) {
            $this->markCancelled(
                $cacheKey,
                $status,
                'Optimization canceled before processing started.'
            );
            return;
        }

        $status['state'] = 'running';
        $status['started_at'] = now()->toIso8601String();
        $status['updated_at'] = now()->toIso8601String();
        $status['message'] = 'Optimization is running in the background.';
        Cache::put($cacheKey, $status, now()->addHours(12));

        try {
            @set_time_limit(0);
            @ini_set('memory_limit', '1024M');

            $tick = 0;
            $summary = $optimizer->optimizeAll(
                $this->maxWidth,
                $this->maxHeight,
                $this->quality,
                function (array $stats) use ($cacheKey, &$status, &$tick): void {
                    $tick++;

                    $latest = Cache::get($cacheKey, []);
                    if (!is_array($latest)) {
                        $latest = [];
                    }
                    if ($this->isCancellationRequested($latest)) {
                        $status = array_merge($status, $latest);
                        $status['summary'] = $stats;
                        $status['updated_at'] = now()->toIso8601String();
                        Cache::put($cacheKey, $status, now()->addHours(12));

                        throw new \RuntimeException(self::CANCEL_EXCEPTION);
                    }

                    if ($tick % 20 !== 0) {
                        return;
                    }

                    $status['summary'] = $stats;
                    $status['updated_at'] = now()->toIso8601String();
                    Cache::put($cacheKey, $status, now()->addHours(12));
                }
            );

            $savedMb = round(((int) ($summary['saved_bytes'] ?? 0)) / 1048576, 2);
            $status['state'] = 'completed';
            $status['finished_at'] = now()->toIso8601String();
            $status['updated_at'] = now()->toIso8601String();
            $status['summary'] = $summary;
            $status['message'] = sprintf(
                'Image optimization finished: %d optimized (%d resized), %d skipped, %d missing, %d errors. Saved ~%s MB.',
                (int) ($summary['optimized'] ?? 0),
                (int) ($summary['resized'] ?? 0),
                (int) ($summary['skipped'] ?? 0),
                (int) ($summary['missing'] ?? 0),
                (int) ($summary['errors'] ?? 0),
                number_format($savedMb, 2)
            );

            Cache::put($cacheKey, $status, now()->addDays(2));
        } catch (\Throwable $e) {
            if ($e instanceof \RuntimeException && $e->getMessage() === self::CANCEL_EXCEPTION) {
                $status = Cache::get($cacheKey, $status);
                if (!is_array($status)) {
                    $status = [];
                }
                $this->markCancelled($cacheKey, $status, 'Optimization canceled by admin request.');
                return;
            }

            $status['state'] = 'failed';
            $status['finished_at'] = now()->toIso8601String();
            $status['updated_at'] = now()->toIso8601String();
            $status['message'] = 'Optimization failed.';
            $status['error'] = $e->getMessage();
            Cache::put($cacheKey, $status, now()->addDays(2));
            throw $e;
        }
    }

    private function isCancellationRequested(array $status): bool
    {
        return in_array(strtolower((string) ($status['state'] ?? '')), ['cancel_requested', 'cancelled'], true);
    }

    private function markCancelled(string $cacheKey, array $status, string $message): void
    {
        $status['state'] = 'cancelled';
        $status['finished_at'] = now()->toIso8601String();
        $status['updated_at'] = now()->toIso8601String();
        $status['message'] = $message;
        Cache::put($cacheKey, $status, now()->addDays(2));
    }

    private function cacheKey(): string
    {
        return 'admin:settings:' . $this->settingId . ':image-optimizer';
    }
}
