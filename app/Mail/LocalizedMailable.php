<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Mail\Mailable;

abstract class LocalizedMailable extends Mailable
{
    protected function usePreferredLocale(mixed ...$candidates): static
    {
        return $this->useLocale(preferred_locale_for(...$candidates) ?? default_locale());
    }

    protected function useLocale(?string $locale): static
    {
        $resolvedLocale = normalize_locale($locale) ?? default_locale();

        $this->locale($resolvedLocale);

        return $this;
    }

    protected function mailLocale(): string
    {
        return normalize_locale($this->locale ?? null) ?? current_locale();
    }

    public function send($mailer)
    {
        return $this->withLocalizedContext(fn () => parent::send($mailer));
    }

    public function render()
    {
        return $this->withLocalizedContext(fn () => parent::render());
    }

    protected function withLocalizedContext(callable $callback): mixed
    {
        $originalLocale = Carbon::getLocale();

        try {
            Carbon::setLocale($this->mailLocale());

            return $callback();
        } finally {
            Carbon::setLocale($originalLocale);
        }
    }
}
