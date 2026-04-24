<?php

namespace App\Services\Translation;

use App\Contracts\TranslationProvider;

class NullTranslationProvider implements TranslationProvider
{
    public function __construct(
        protected ?string $reason = null
    ) {
    }

    public function configured(): bool
    {
        return false;
    }

    public function translatableLocales(): array
    {
        return [];
    }

    public function supports(string $sourceLocale, string $targetLocale): bool
    {
        return false;
    }

    public function translate(array $texts, string $sourceLocale, string $targetLocale, array $options = []): array
    {
        throw new TranslationException($this->reason ?: 'Auto translation provider is not configured.');
    }
}
