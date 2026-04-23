<?php

namespace App\Services\Translation;

use App\Contracts\TranslationProvider;
use Illuminate\Support\Facades\Http;

class DeepLTranslationProvider implements TranslationProvider
{
    /**
     * @param  array<string, string>  $localeMap
     */
    public function __construct(
        protected string $baseUrl,
        protected string $authKey,
        protected int $timeout = 20,
        protected int $retries = 2,
        protected array $localeMap = []
    ) {
    }

    public function configured(): bool
    {
        return trim($this->authKey) !== '' && trim($this->baseUrl) !== '';
    }

    public function supports(string $sourceLocale, string $targetLocale): bool
    {
        return $this->mapLocale($sourceLocale) !== null
            && $this->mapLocale($targetLocale) !== null
            && normalize_locale($sourceLocale) !== normalize_locale($targetLocale);
    }

    public function translate(array $texts, string $sourceLocale, string $targetLocale, array $options = []): array
    {
        $texts = array_values(array_map(
            static fn (string $text): string => trim($text),
            array_filter($texts, static fn ($text): bool => is_string($text) && trim($text) !== '')
        ));

        if ($texts === []) {
            return [];
        }

        if (! $this->configured()) {
            throw new TranslationException('DeepL auto translation is not configured.');
        }

        $resolvedSource = $this->mapLocale($sourceLocale);
        $resolvedTarget = $this->mapLocale($targetLocale);

        if (! $resolvedSource || ! $resolvedTarget) {
            throw new TranslationException(sprintf(
                'DeepL does not support translating from [%s] to [%s].',
                $sourceLocale,
                $targetLocale
            ));
        }

        $payload = [
            'text' => $texts,
            'source_lang' => $resolvedSource,
            'target_lang' => $resolvedTarget,
        ];

        if (($options['tag_handling'] ?? null) === 'html' || $this->containsHtml($texts)) {
            $payload['tag_handling'] = 'html';
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->contentType('application/json')
            ->withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . $this->authKey,
            ])
            ->timeout(max(1, $this->timeout))
            ->retry(max(1, $this->retries), 250)
            ->post('/v2/translate', $payload)
            ->throw();

        $translations = $response->json('translations');

        if (! is_array($translations) || count($translations) !== count($texts)) {
            throw new TranslationException('DeepL returned an unexpected translation payload.');
        }

        return array_map(
            static fn ($translation): string => trim((string) data_get($translation, 'text', '')),
            $translations
        );
    }

    protected function mapLocale(string $locale): ?string
    {
        $resolved = normalize_locale($locale);

        if (! $resolved) {
            return null;
        }

        if (array_key_exists($resolved, $this->localeMap)) {
            return (string) $this->localeMap[$resolved];
        }

        return strtoupper(str_replace('_', '-', $resolved));
    }

    /**
     * @param  array<int, string>  $texts
     */
    protected function containsHtml(array $texts): bool
    {
        foreach ($texts as $text) {
            if (preg_match('/<[^>]+>/', $text) === 1) {
                return true;
            }
        }

        return false;
    }
}
