<?php

namespace App\Jobs;

use App\Services\Translation\LocalizedContentTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranslateLocalizedContent implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $timeout;

    public int $uniqueFor;

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<int, string>|null  $locales
     * @param  array<int, string>|null  $fields
     */
    public function __construct(
        public string $modelClass,
        public int|string $modelId,
        public bool $force = false,
        public ?array $locales = null,
        public ?array $fields = null
    ) {
        $this->tries = max(1, translation_retry_count());
        $this->timeout = max(120, translation_timeout_seconds() * 6);
        $this->uniqueFor = max(300, $this->timeout);
    }

    public function handle(LocalizedContentTranslationService $translations): void
    {
        $translations->translatePersistedModel(
            $this->modelClass,
            $this->modelId,
            $this->force,
            $this->locales,
            $this->fields
        );
    }

    public function uniqueId(): string
    {
        return implode(':', [
            $this->modelClass,
            (string) $this->modelId,
            $this->force ? 'force' : 'fill',
            $this->normalizedUniqueSegment($this->locales),
            $this->normalizedUniqueSegment($this->fields),
        ]);
    }

    /**
     * @param  array<int, string>|null  $values
     */
    protected function normalizedUniqueSegment(?array $values): string
    {
        if (! is_array($values) || $values === []) {
            return '*';
        }

        $normalized = array_values(array_unique(array_map(
            static fn ($value): string => strtolower(trim((string) $value)),
            $values
        )));
        sort($normalized);

        return implode(',', $normalized);
    }
}
