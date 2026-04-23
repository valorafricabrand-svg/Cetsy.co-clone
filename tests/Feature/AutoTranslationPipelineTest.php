<?php

namespace Tests\Feature;

use App\Jobs\TranslateLocalizedContent;
use App\Models\Country;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\Translation\LocalizedContentTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AutoTranslationPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'translation.enabled' => true,
            'translation.provider' => 'deepl',
            'translation.auto_translate_on_write' => true,
            'services.deepl.key' => 'test-deepl-key',
            'services.deepl.base_url' => 'https://api-free.deepl.com',
        ]);
    }

    public function test_product_observer_queues_auto_translation_for_missing_marketplace_content(): void
    {
        [, $country, $shop] = $this->createSellerShop();

        Queue::fake();

        $product = Product::create([
            'shop_id' => $shop->id,
            'country_id' => $country->id,
            'name' => 'English Mug',
            'slug' => 'english-mug-' . $shop->id,
            'description' => 'English description',
            'type' => Product::TYPE_PHYSICAL,
            'price' => 25,
            'stock' => 3,
            'is_active' => 1,
        ]);

        Queue::assertPushed(TranslateLocalizedContent::class, function (TranslateLocalizedContent $job) use ($product) {
            return $job->modelClass === Product::class
                && (int) $job->modelId === (int) $product->id;
        });
    }

    public function test_translation_job_fills_missing_product_translations_without_overwriting_existing_values(): void
    {
        [, $country, $shop] = $this->createSellerShop();

        $product = Product::withoutEvents(function () use ($shop, $country) {
            return Product::create([
                'shop_id' => $shop->id,
                'country_id' => $country->id,
                'name' => 'English Mug',
                'name_translations' => ['sw' => 'Manual Swahili Name'],
                'slug' => 'english-mug-no-events',
                'description' => 'English description',
                'type' => Product::TYPE_PHYSICAL,
                'price' => 25,
                'stock' => 3,
                'is_active' => 1,
            ]);
        });

        Http::fake([
            'https://api-free.deepl.com/v2/translate' => Http::response([
                'translations' => [
                    ['text' => 'Maelezo ya Kiswahili'],
                ],
            ], 200),
        ]);

        app(LocalizedContentTranslationService::class)->translatePersistedModel(Product::class, $product->id);

        $product->refresh();

        $this->assertSame('Manual Swahili Name', $product->name_translations['sw'] ?? null);
        $this->assertSame('Maelezo ya Kiswahili', $product->description_translations['sw'] ?? null);
    }

    public function test_backfill_command_translates_existing_shop_content_inline(): void
    {
        [, $country, $shop] = $this->createSellerShop();

        Shop::withoutEvents(function () use ($shop, $country) {
            $shop->update([
                'country' => (string) $country->id,
                'currency' => 'USD',
                'name_translations' => null,
                'bio_translations' => null,
                'announcement_translations' => null,
                'policies_translations' => null,
            ]);
        });

        Http::fake([
            'https://api-free.deepl.com/v2/translate' => Http::response([
                'translations' => [
                    ['text' => 'Duka la Kiswahili'],
                    ['text' => 'Maelezo ya duka kwa Kiswahili.'],
                    ['text' => 'Tangazo la duka kwa Kiswahili.'],
                    ['text' => 'Sera za duka kwa Kiswahili.'],
                ],
            ], 200),
        ]);

        $this->artisan('translations:backfill', [
            'model' => 'shops',
            '--sync' => true,
            '--locale' => ['sw'],
        ])->assertSuccessful();

        $shop->refresh();

        $this->assertSame('Duka la Kiswahili', $shop->name_translations['sw'] ?? null);
        $this->assertSame('Maelezo ya duka kwa Kiswahili.', $shop->bio_translations['sw'] ?? null);
        $this->assertSame('Tangazo la duka kwa Kiswahili.', $shop->announcement_translations['sw'] ?? null);
        $this->assertSame('Sera za duka kwa Kiswahili.', $shop->policies_translations['sw'] ?? null);
    }

    private function createSellerShop(): array
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'is_active' => true,
        ]);

        $country = Country::create([
            'name' => 'Kenya',
            'country_code' => 'KE',
            'currency' => 'KES',
            'status' => 1,
        ]);

        $shop = Shop::withoutEvents(function () use ($seller, $country) {
            return Shop::create([
                'user_id' => $seller->id,
                'language' => 'English',
                'country' => (string) $country->id,
                'currency' => 'USD',
                'name' => 'English Shop',
                'slug' => 'english-shop-' . $seller->id,
                'bio' => 'English shop bio.',
                'announcement' => 'English shop announcement.',
                'policies' => 'English shop policies.',
                'address' => '123 Main Street',
                'city' => 'Nairobi',
                'postal' => '00100',
                'is_active' => true,
            ]);
        });

        return [$seller, $country, $shop];
    }
}
