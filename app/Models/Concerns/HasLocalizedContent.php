<?php

namespace App\Models\Concerns;

trait HasLocalizedContent
{
    public function translatableAttributes(): array
    {
        return property_exists($this, 'translatable') && is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    public function translationsFor(string $field): array
    {
        if (! in_array($field, $this->translatableAttributes(), true)) {
            return [];
        }

        $value = $this->getAttribute($this->translationColumnFor($field));

        if (is_array($value)) {
            return $this->filterTranslations($value);
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return $this->filterTranslations($decoded);
            }
        }

        return [];
    }

    public function translationFor(string $field, ?string $locale = null): ?string
    {
        $resolvedLocale = normalize_locale($locale) ?? current_locale();
        $translations = $this->translationsFor($field);

        return $this->normalizeTranslatedValue($translations[$resolvedLocale] ?? null);
    }

    public function localized(string $field, ?string $locale = null): ?string
    {
        $translated = $this->translationFor($field, $locale);

        if ($translated !== null) {
            return $translated;
        }

        $base = $this->normalizeTranslatedValue($this->getAttribute($field));

        if ($base !== null) {
            return $base;
        }

        foreach ($this->translationsFor($field) as $candidate) {
            $normalized = $this->normalizeTranslatedValue($candidate);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    public function fillTranslations(array $translations): void
    {
        foreach ($this->translatableAttributes() as $field) {
            $values = $translations[$field] ?? [];

            $this->setAttribute(
                $this->translationColumnFor($field),
                is_array($values) && $values !== [] ? $this->filterTranslations($values) : null
            );
        }
    }

    protected function translationColumnFor(string $field): string
    {
        return $field . '_translations';
    }

    protected function filterTranslations(array $translations): array
    {
        $filtered = [];

        foreach ($translations as $locale => $value) {
            $resolvedLocale = normalize_locale(is_string($locale) ? $locale : null);
            $resolvedValue = $this->normalizeTranslatedValue($value);

            if ($resolvedLocale && $resolvedValue !== null) {
                $filtered[$resolvedLocale] = $resolvedValue;
            }
        }

        return $filtered;
    }

    protected function normalizeTranslatedValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
