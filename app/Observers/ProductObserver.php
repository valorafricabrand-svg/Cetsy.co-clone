<?php

namespace App\Observers;

use App\Jobs\TranslateLocalizedContent;
use App\Models\Product;
use App\Services\Translation\LocalizedContentTranslationService;
use Illuminate\Support\Facades\DB;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->dispatchIfNeeded($product, true);
    }

    public function updated(Product $product): void
    {
        $this->dispatchIfNeeded($product);
    }

    protected function dispatchIfNeeded(Product $product, bool $created = false): void
    {
        if (! translation_auto_translate_on_write()) {
            return;
        }

        $translations = app(LocalizedContentTranslationService::class);

        if (! $translations->canTranslateModel($product)) {
            return;
        }

        if (! $created && ! $this->translatableFieldsChanged($product)) {
            return;
        }

        if (! $translations->needsTranslation($product)) {
            return;
        }

        $dispatch = function () use ($product): void {
            TranslateLocalizedContent::dispatch(Product::class, $product->getKey())
                ->onQueue(translation_queue_name());
        };

        if (! app()->runningUnitTests() && DB::transactionLevel() > 0) {
            DB::afterCommit($dispatch);
            return;
        }

        $dispatch();
    }

    protected function translatableFieldsChanged(Product $product): bool
    {
        $changed = array_keys($product->getChanges());

        foreach ($product->translatableAttributes() as $field) {
            if (in_array($field, $changed, true) || in_array($field . '_translations', $changed, true)) {
                return true;
            }
        }

        return false;
    }
}
