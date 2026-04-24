<?php

namespace App\Services\Translation;

use App\Contracts\TranslationProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DeepLTranslationProvider implements TranslationProvider
{
    /**
     * @var array<int, string>|null
     */
    protected ?array $sourceLocaleCodes = null;

    /**
     * @var array<int, string>|null
     */
    protected ?array $targetLocaleCodes = null;

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
        $this->localeMap = $this->normalizeLocaleMap($this->localeMap);
    }

    public function configured(): bool
    {
        return trim($this->authKey) !== '' && trim($this->baseUrl) !== '';
    }

    public function translatableLocales(): array
    {
        $locales = array_values(array_unique(array_intersect(
            $this->allSourceLocaleCodes(),
            $this->allTargetLocaleCodes()
        )));

        sort($locales);

        return $locales;
    }

    public function supports(string $sourceLocale, string $targetLocale): bool
    {
        $resolvedSource = $this->normalizeLocaleCode($sourceLocale);
        $resolvedTarget = $this->normalizeLocaleCode($targetLocale);

        if (! $resolvedSource || ! $resolvedTarget || $resolvedSource === $resolvedTarget) {
            return false;
        }

        if (in_array($resolvedSource, $this->fallbackSourceLocaleCodes(), true)
            && in_array($resolvedTarget, $this->fallbackTargetLocaleCodes(), true)) {
            return true;
        }

        if (! $this->configured()) {
            return false;
        }

        return in_array($resolvedSource, $this->allSourceLocaleCodes(), true)
            && in_array($resolvedTarget, $this->allTargetLocaleCodes(), true);
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
        $resolved = $this->normalizeLocaleCode($locale);

        if (! $resolved) {
            return null;
        }

        if (array_key_exists($resolved, $this->localeMap)) {
            return (string) $this->localeMap[$resolved];
        }

        return strtoupper(str_replace('_', '-', $resolved));
    }

    /**
     * @return array<int, string>
     */
    protected function allSourceLocaleCodes(): array
    {
        if (is_array($this->sourceLocaleCodes)) {
            return $this->sourceLocaleCodes;
        }

        $this->sourceLocaleCodes = $this->mergeLocaleCodes(
            $this->fallbackSourceLocaleCodes(),
            $this->fetchLocaleCodes('source')
        );

        return $this->sourceLocaleCodes;
    }

    /**
     * @return array<int, string>
     */
    protected function allTargetLocaleCodes(): array
    {
        if (is_array($this->targetLocaleCodes)) {
            return $this->targetLocaleCodes;
        }

        $this->targetLocaleCodes = $this->mergeLocaleCodes(
            $this->fallbackTargetLocaleCodes(),
            $this->fetchLocaleCodes('target')
        );

        return $this->targetLocaleCodes;
    }

    /**
     * @return array<int, string>
     */
    protected function fallbackSourceLocaleCodes(): array
    {
        return array_keys($this->localeMap);
    }

    /**
     * @return array<int, string>
     */
    protected function fallbackTargetLocaleCodes(): array
    {
        return array_keys($this->localeMap);
    }

    /**
     * @return array<int, string>
     */
    protected function fetchLocaleCodes(string $type): array
    {
        if (! $this->configured()) {
            return [];
        }

        $cacheKey = sprintf(
            'translation:deepl:languages:%s:%s',
            $type,
            sha1(rtrim($this->baseUrl, '/'))
        );

        try {
            $cached = Cache::remember($cacheKey, now()->addDay(), function () use ($type): array {
                $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
                    ->acceptJson()
                    ->withHeaders([
                        'Authorization' => 'DeepL-Auth-Key ' . $this->authKey,
                    ])
                    ->timeout(max(1, $this->timeout))
                    ->retry(max(1, $this->retries), 250)
                    ->get('/v2/languages', ['type' => $type])
                    ->throw();

                return $this->parseLocaleCodes($response->json());
            });
        } catch (\Throwable) {
            return [];
        }

        return is_array($cached) ? $this->mergeLocaleCodes($cached) : [];
    }

    /**
     * @param  mixed  $payload
     * @return array<int, string>
     */
    protected function parseLocaleCodes(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $codes = [];

        foreach ($payload as $row) {
            if (! is_array($row)) {
                continue;
            }

            $locale = $this->normalizeLocaleCode((string) ($row['language'] ?? ''));

            if ($locale) {
                $codes[] = $locale;
            }
        }

        return $this->mergeLocaleCodes($codes);
    }

    /**
     * @param  array<string, string>  $localeMap
     * @return array<string, string>
     */
    protected function normalizeLocaleMap(array $localeMap): array
    {
        $normalized = [];

        foreach ($localeMap as $locale => $providerLocale) {
            $resolvedLocale = $this->normalizeLocaleCode((string) $locale);
            $resolvedProviderLocale = strtoupper(trim((string) $providerLocale));

            if (! $resolvedLocale || $resolvedProviderLocale === '') {
                continue;
            }

            $normalized[$resolvedLocale] = $resolvedProviderLocale;
        }

        return $normalized;
    }

    protected function normalizeLocaleCode(?string $locale): ?string
    {
        $resolved = sanitize_locale_code($locale);

        return $resolved ? str_replace('_', '-', $resolved) : null;
    }

    /**
     * @param  array<int, string>  ...$localeGroups
     * @return array<int, string>
     */
    protected function mergeLocaleCodes(array ...$localeGroups): array
    {
        $merged = [];

        foreach ($localeGroups as $localeGroup) {
            foreach ($localeGroup as $locale) {
                $resolved = $this->normalizeLocaleCode((string) $locale);

                if ($resolved) {
                    $merged[] = $resolved;
                }
            }
        }

        $merged = array_values(array_unique($merged));
        sort($merged);

        return $merged;
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
