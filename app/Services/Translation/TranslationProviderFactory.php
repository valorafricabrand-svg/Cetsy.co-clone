<?php

namespace App\Services\Translation;

use App\Contracts\TranslationProvider;

class TranslationProviderFactory
{
    public function make(): TranslationProvider
    {
        return match ((string) config('translation.provider', 'deepl')) {
            'deepl' => new DeepLTranslationProvider(
                (string) config('services.deepl.base_url', 'https://api-free.deepl.com'),
                (string) config('services.deepl.key', ''),
                translation_timeout_seconds(),
                translation_retry_count(),
                (array) config('translation.providers.deepl.locale_map', [])
            ),
            default => new NullTranslationProvider(
                'Unsupported translation provider [' . config('translation.provider') . '].'
            ),
        };
    }
}
