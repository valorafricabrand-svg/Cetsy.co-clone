<?php

namespace App\Console\Commands;

use App\Jobs\TranslateLocalizedContent;
use App\Models\Product;
use App\Models\Shop;
use App\Services\Translation\LocalizedContentTranslationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BackfillTranslations extends Command
{
    protected $signature = 'translations:backfill
        {model=all : Which records to translate (products, shops, all)}
        {--locale=* : Restrict translation to one or more target locales}
        {--force : Overwrite existing translated values}
        {--sync : Run translation inline instead of queueing jobs}
        {--chunk= : Number of records to process per chunk}';

    protected $description = 'Backfill missing translated marketplace content for products and shops';

    public function handle(LocalizedContentTranslationService $translations): int
    {
        if (! $translations->configured()) {
            $this->error('Auto translation is disabled or the provider is not configured.');

            return self::FAILURE;
        }

        $models = $this->resolveRequestedModels((string) $this->argument('model'));

        if ($models === []) {
            $this->error('Unknown model option. Use products, shops, or all.');

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) ($this->option('chunk') ?: translation_chunk_size()));
        $locales = $this->normalizedLocales();
        $force = (bool) $this->option('force');
        $sync = (bool) $this->option('sync');

        $queued = 0;
        $translated = 0;

        foreach ($models as $modelClass) {
            $label = $this->labelFor($modelClass);
            $this->line('Scanning ' . $label . '...');

            $this->queryFor($modelClass)
                ->orderBy('id')
                ->chunkById($chunkSize, function ($records) use ($translations, $locales, $force, $sync, &$queued, &$translated) {
                    foreach ($records as $record) {
                        if (! $translations->needsTranslation($record, $force, $locales)) {
                            continue;
                        }

                        if ($sync) {
                            $summary = $translations->translateModelInstance($record, $force, $locales);
                            if (($summary['updated'] ?? 0) > 0) {
                                $translated++;
                            }

                            continue;
                        }

                        TranslateLocalizedContent::dispatch($record::class, $record->getKey(), $force, $locales)
                            ->onQueue(translation_queue_name());
                        $queued++;
                    }
                });
        }

        if ($sync) {
            $this->info(sprintf('Translated %d record(s) inline.', $translated));
        } else {
            $this->info(sprintf('Queued %d translation job(s).', $queued));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, class-string<Model>>
     */
    protected function resolveRequestedModels(string $selection): array
    {
        return match (strtolower(trim($selection))) {
            'products' => [Product::class],
            'shops' => [Shop::class],
            'all' => [Product::class, Shop::class],
            default => [],
        };
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function queryFor(string $modelClass): Builder
    {
        /** @var Builder $query */
        $query = $modelClass::query();

        if ($modelClass === Product::class) {
            $query->with('shop:id,language');
        }

        return $query;
    }

    /**
     * @return array<int, string>|null
     */
    protected function normalizedLocales(): ?array
    {
        $locales = array_values(array_filter(array_map(
            static fn ($locale): ?string => normalize_locale(is_string($locale) ? $locale : null),
            (array) $this->option('locale')
        )));

        return $locales === [] ? null : array_values(array_unique($locales));
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function labelFor(string $modelClass): string
    {
        return match ($modelClass) {
            Product::class => 'products',
            Shop::class => 'shops',
            default => class_basename($modelClass),
        };
    }
}
