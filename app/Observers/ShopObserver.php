<?php

namespace App\Observers;

use App\Jobs\TranslateLocalizedContent;
use App\Models\Shop;
use App\Services\Translation\LocalizedContentTranslationService;
use Illuminate\Support\Facades\DB;

class ShopObserver
{
    public function created(Shop $shop): void
    {
        $this->dispatchIfNeeded($shop, true);
    }

    public function updated(Shop $shop): void
    {
        $this->dispatchIfNeeded($shop);
    }

    protected function dispatchIfNeeded(Shop $shop, bool $created = false): void
    {
        if (! config('translation.auto_translate_on_write', true)) {
            return;
        }

        $translations = app(LocalizedContentTranslationService::class);

        if (! $translations->canTranslateModel($shop)) {
            return;
        }

        if (! $created && ! $this->translatableFieldsChanged($shop)) {
            return;
        }

        if (! $translations->needsTranslation($shop)) {
            return;
        }

        $dispatch = function () use ($shop): void {
            TranslateLocalizedContent::dispatch(Shop::class, $shop->getKey())
                ->onQueue((string) config('translation.queue', 'default'));
        };

        if (! app()->runningUnitTests() && DB::transactionLevel() > 0) {
            DB::afterCommit($dispatch);
            return;
        }

        $dispatch();
    }

    protected function translatableFieldsChanged(Shop $shop): bool
    {
        $changed = array_keys($shop->getChanges());

        foreach ($shop->translatableAttributes() as $field) {
            if (in_array($field, $changed, true) || in_array($field . '_translations', $changed, true)) {
                return true;
            }
        }

        return false;
    }
}
