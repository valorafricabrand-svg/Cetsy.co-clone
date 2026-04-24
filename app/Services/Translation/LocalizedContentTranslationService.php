<?php

namespace App\Services\Translation;

use App\Contracts\TranslationProvider;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Model;

class LocalizedContentTranslationService
{
    public function __construct(
        protected TranslationProvider $provider
    ) {
    }

    public function configured(): bool
    {
        return translation_enabled()
            && $this->provider->configured();
    }

    public function canTranslateModel(Model $model): bool
    {
        return $this->configured()
            && method_exists($model, 'translatableAttributes')
            && is_array($model->translatableAttributes());
    }

    public function needsTranslation(
        Model $model,
        bool $force = false,
        ?array $locales = null,
        ?array $fields = null
    ): bool {
        if (! $this->canTranslateModel($model)) {
            return false;
        }

        $sourceLocale = $this->sourceLocaleFor($model);
        $targetLocales = $this->targetLocalesFor($model, $locales);
        $resolvedFields = $this->resolveFields($model, $fields);

        if ($targetLocales === [] || $resolvedFields === []) {
            return false;
        }

        foreach ($resolvedFields as $field) {
            $source = trim((string) $model->getAttribute($field));

            if ($source === '') {
                continue;
            }

            $translations = $model->translationsFor($field);

            foreach ($targetLocales as $targetLocale) {
                if ($targetLocale === $sourceLocale || ! $this->provider->supports($sourceLocale, $targetLocale)) {
                    continue;
                }

                if ($force || empty($translations[$targetLocale])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    public function translatePersistedModel(
        string $modelClass,
        int|string $modelId,
        bool $force = false,
        ?array $locales = null,
        ?array $fields = null
    ): array {
        /** @var Model|null $model */
        $model = $modelClass::query()->find($modelId);

        if (! $model) {
            return [
                'model' => $modelClass,
                'id' => $modelId,
                'updated' => 0,
                'translated' => [],
                'skipped' => ['missing_model'],
            ];
        }

        return $this->translateModelInstance($model, $force, $locales, $fields);
    }

    /**
     * @return array<string, mixed>
     */
    public function translateModelInstance(
        Model $model,
        bool $force = false,
        ?array $locales = null,
        ?array $fields = null
    ): array {
        $summary = [
            'model' => $model::class,
            'id' => $model->getKey(),
            'updated' => 0,
            'translated' => [],
            'skipped' => [],
        ];

        if (! $this->canTranslateModel($model)) {
            $summary['skipped'][] = 'provider_not_configured';

            return $summary;
        }

        $sourceLocale = $this->sourceLocaleFor($model);
        $targetLocales = $this->targetLocalesFor($model, $locales);
        $resolvedFields = $this->resolveFields($model, $fields);

        if ($targetLocales === [] || $resolvedFields === []) {
            $summary['skipped'][] = 'no_target_locales';

            return $summary;
        }

        $updates = [];

        foreach ($targetLocales as $targetLocale) {
            if ($targetLocale === $sourceLocale || ! $this->provider->supports($sourceLocale, $targetLocale)) {
                $summary['skipped'][] = 'unsupported_locale:' . $targetLocale;
                continue;
            }

            $batchTexts = [];
            $batchFields = [];

            foreach ($resolvedFields as $field) {
                $source = trim((string) $model->getAttribute($field));

                if ($source === '') {
                    continue;
                }

                $existingTranslations = $updates[$field] ?? $model->translationsFor($field);

                if (! $force && ! empty($existingTranslations[$targetLocale])) {
                    continue;
                }

                $batchTexts[] = $source;
                $batchFields[] = $field;
            }

            if ($batchTexts === []) {
                continue;
            }

            $translatedTexts = $this->provider->translate($batchTexts, $sourceLocale, $targetLocale, [
                'tag_handling' => $this->containsHtml($batchTexts) ? 'html' : null,
            ]);

            foreach ($translatedTexts as $index => $translatedText) {
                $field = $batchFields[$index] ?? null;

                if (! $field) {
                    continue;
                }

                $normalizedText = trim((string) $translatedText);

                if ($normalizedText === '') {
                    continue;
                }

                $fieldTranslations = $updates[$field] ?? $model->translationsFor($field);
                $fieldTranslations[$targetLocale] = $normalizedText;
                $updates[$field] = $fieldTranslations;
                $summary['translated'][] = $field . ':' . $targetLocale;
            }
        }

        if ($updates === []) {
            $summary['skipped'][] = 'nothing_to_translate';

            return $summary;
        }

        foreach ($updates as $field => $translations) {
            $model->setAttribute($field . '_translations', $translations ?: null);
        }

        $model->saveQuietly();
        $summary['updated'] = count($summary['translated']);

        return $summary;
    }

    /**
     * @param  array<int, string>|null  $locales
     * @return array<int, string>
     */
    public function targetLocalesFor(Model $model, ?array $locales = null): array
    {
        $context = $this->translationContextFor($model);
        $sourceLocale = $this->sourceLocaleFor($model);

        if (is_array($locales) && $locales !== []) {
            return array_values(array_unique(array_filter(array_map(
                static fn ($locale): ?string => normalize_locale(is_string($locale) ? $locale : null),
                $locales
            ))));
        }

        return array_values(array_filter(
            array_keys(content_translation_locales($context)),
            static fn (string $locale): bool => $locale !== $sourceLocale
        ));
    }

    public function sourceLocaleFor(Model $model): string
    {
        $context = $this->translationContextFor($model);

        return shop_primary_locale($context);
    }

    /**
     * @param  array<int, string>|null  $fields
     * @return array<int, string>
     */
    protected function resolveFields(Model $model, ?array $fields = null): array
    {
        $translatable = method_exists($model, 'translatableAttributes')
            ? $model->translatableAttributes()
            : [];

        if (! is_array($translatable)) {
            return [];
        }

        if (! is_array($fields) || $fields === []) {
            return array_values($translatable);
        }

        return array_values(array_intersect($translatable, array_filter($fields, 'is_string')));
    }

    protected function translationContextFor(Model $model): mixed
    {
        if ($model instanceof Product) {
            $model->loadMissing('shop');

            return $model->shop;
        }

        if ($model instanceof Shop) {
            return $model;
        }

        return null;
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
