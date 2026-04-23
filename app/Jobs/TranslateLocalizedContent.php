<?php

namespace App\Jobs;

use App\Services\Translation\LocalizedContentTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranslateLocalizedContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

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
}
