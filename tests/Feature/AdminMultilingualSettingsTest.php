<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminMultilingualSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'translation.provider' => 'deepl',
            'services.deepl.key' => 'test-deepl-key',
            'services.deepl.base_url' => 'https://api-free.deepl.com',
        ]);
    }

    public function test_admin_can_add_supported_languages_from_settings(): void
    {
        [$admin, $setting] = $this->createAdminAndSetting();

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/v2/languages')) {
                return Http::response([
                    ['language' => 'EN'],
                    ['language' => 'FR'],
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($admin)->put(
            route('admin.settings.update', $setting),
            $this->settingsPayload([
                'locale_rows' => [
                    [
                        'code' => 'en',
                        'name' => 'English',
                        'native' => 'English',
                        'html' => 'en',
                        'og' => 'en_US',
                        'enabled' => '1',
                    ],
                    [
                        'code' => 'fr',
                        'name' => 'French',
                        'native' => 'Francais',
                        'html' => 'fr',
                        'og' => 'fr_FR',
                        'enabled' => '1',
                    ],
                ],
                'default_locale' => 'fr',
                'translation_enabled' => '1',
                'translation_auto_translate_on_write' => '1',
            ])
        );

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('fr', setting('default_locale'));
        $this->assertSame('translations', setting('translation_queue'));
        $this->assertSame(['en', 'fr'], array_keys(supported_locales()));
        $this->assertSame('French', locale_catalog()['fr']['name'] ?? null);
        $this->assertSame('Francais', locale_catalog()['fr']['native'] ?? null);
    }

    public function test_admin_cannot_enable_auto_translation_with_unsupported_catalog_language(): void
    {
        [$admin, $setting] = $this->createAdminAndSetting();

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/v2/languages')) {
                return Http::response([
                    ['language' => 'EN'],
                    ['language' => 'SW'],
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($admin)
            ->from(route('settings.edit', $setting))
            ->put(
                route('admin.settings.update', $setting),
                $this->settingsPayload([
                    'locale_rows' => [
                        [
                            'code' => 'en',
                            'name' => 'English',
                            'native' => 'English',
                            'html' => 'en',
                            'og' => 'en_US',
                            'enabled' => '1',
                        ],
                        [
                            'code' => 'zz',
                            'name' => 'Zed',
                            'native' => 'Zed',
                            'html' => 'zz',
                            'og' => 'zz_ZZ',
                            'enabled' => '1',
                        ],
                    ],
                    'default_locale' => 'en',
                    'translation_enabled' => '1',
                    'translation_auto_translate_on_write' => '1',
                ])
            );

        $response
            ->assertRedirect(route('settings.edit', $setting))
            ->assertSessionHasErrors('locale_rows');

        $this->assertArrayNotHasKey('zz', locale_catalog());
    }

    public function test_admin_can_save_custom_catalog_language_when_auto_translation_is_disabled(): void
    {
        [$admin, $setting] = $this->createAdminAndSetting();

        $response = $this->actingAs($admin)->put(
            route('admin.settings.update', $setting),
            $this->settingsPayload([
                'locale_rows' => [
                    [
                        'code' => 'en',
                        'name' => 'English',
                        'native' => 'English',
                        'html' => 'en',
                        'og' => 'en_US',
                        'enabled' => '1',
                    ],
                    [
                        'code' => 'zz',
                        'name' => 'Zed',
                        'native' => 'Zed',
                        'html' => 'zz',
                        'og' => 'zz_ZZ',
                        'enabled' => '0',
                    ],
                ],
                'default_locale' => 'en',
                'translation_enabled' => '0',
                'translation_auto_translate_on_write' => '0',
            ])
        );

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('Zed', locale_catalog()['zz']['name'] ?? null);
    }

    private function createAdminAndSetting(): array
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_ADMIN,
        ]);

        $setting = new Setting();
        $setting->forceFill([
            'option_key' => 'bootstrap',
            'option_value' => null,
            'site_name' => 'Cetsy',
            'meta_description' => 'Marketplace settings',
            'email' => 'owner@example.com',
            'timezone' => 'Africa/Nairobi',
            'default_currency' => 'USD',
            'fee_rate' => 1.5,
            'min_amount' => 1,
            'auto_release_days' => 3,
        ])->save();

        return [$admin, $setting];
    }

    private function settingsPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'site_name' => 'Cetsy',
            'meta_description' => 'Marketplace settings',
            'email' => 'owner@example.com',
            'timezone' => 'Africa/Nairobi',
            'default_currency' => 'USD',
            'release_fee_percent' => '5.5',
            'fee_rate' => '1.5',
            'min_amount' => '1',
            'auto_release_days' => '3',
            'payout_schedule' => 'manual',
            'payout_weekday' => '5',
            'payout_month_day' => '15',
            'payout_auto_approve' => '0',
            'payout_auto_disburse' => '0',
            'subscription_grace_days' => '5',
            'subscription_trial_enabled' => '0',
            'subscription_trial_days' => '30',
            'seller_signup_auto_approve' => '1',
            'seller_signup_require_logo' => '0',
            'home_listings_cache_ttl_minutes' => '10',
            'duplicate_sku_strategy' => 'append',
            'duplicate_sku_suffix' => 'DUP',
            'duplicate_sku_random_len' => '4',
            'translation_enabled' => '0',
            'translation_auto_translate_on_write' => '0',
            'translation_queue' => 'translations',
            'translation_timeout' => '25',
            'translation_retries' => '3',
            'translation_chunk_size' => '150',
            'payments_mpesa_enabled' => '0',
            'payments_paypal_enabled' => '1',
            'payments_stripe_enabled' => '0',
            'payments_paystack_enabled' => '0',
            'payments_default_gateway' => 'paypal',
        ], $overrides);
    }
}
