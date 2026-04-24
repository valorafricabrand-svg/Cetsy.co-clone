<?php

namespace App\Contracts;

interface TranslationProvider
{
    public function configured(): bool;

    /**
     * Return locale codes that are safe to use as both source and target
     * languages with this provider.
     *
     * @return array<int, string>
     */
    public function translatableLocales(): array;

    public function supports(string $sourceLocale, string $targetLocale): bool;

    /**
     * Translate the provided texts from the source locale into the target locale.
     *
     * @param  array<int, string>  $texts
     * @param  array<string, mixed>  $options
     * @return array<int, string>
     */
    public function translate(array $texts, string $sourceLocale, string $targetLocale, array $options = []): array;
}
