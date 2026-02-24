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
        $status['state'] = 'running';
        $status['started_at'] = now()->toIso8601String();
        $status['updated_at'] = now()->toIso8601String();
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
            $status['state'] = 'failed';
            $status['finished_at'] = now()->toIso8601String();
            $status['updated_at'] = now()->toIso8601String();
            $status['error'] = $e->getMessage();
            Cache::put($cacheKey, $status, now()->addDays(2));
            throw $e;
        }
    }

    private function cacheKey(): string
    {
        return 'admin:settings:' . $this->settingId . ':image-optimizer';
    }
}

