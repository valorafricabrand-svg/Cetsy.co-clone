<?php

namespace App\Providers;

use App\Contracts\TranslationProvider;
use App\Models\Activity;
use App\Models\Message;
use App\Models\Product;
use App\Models\Shop;
use App\Observers\ActivityObserver;
use App\Observers\MessageObserver;
use App\Observers\ProductObserver;
use App\Observers\ShopObserver;
use App\Services\Translation\DeepLTranslationProvider;
use App\Services\Translation\NullTranslationProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // <-- THIS is correct
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TranslationProvider::class, function () {
            if (! config('translation.enabled', false)) {
                return new NullTranslationProvider('Auto translation is disabled.');
            }

            return match ((string) config('translation.provider', 'deepl')) {
                'deepl' => new DeepLTranslationProvider(
                    (string) config('services.deepl.base_url', 'https://api-free.deepl.com'),
                    (string) config('services.deepl.key', ''),
                    (int) config('translation.timeout', 20),
                    (int) config('translation.retries', 2),
                    (array) config('translation.providers.deepl.locale_map', [])
                ),
                default => new NullTranslationProvider(
                    'Unsupported translation provider [' . config('translation.provider') . '].'
                ),
            };
        });
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191); // <-- THIS is correct
        Activity::observe(ActivityObserver::class);
        Message::observe(MessageObserver::class);
        Product::observe(ProductObserver::class);
        Shop::observe(ShopObserver::class);

        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $relativeSignedUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
                absolute: false
            );

            return URL::to($relativeSignedUrl);
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Blade directive: @money(123.45) renders formatted converted string
        Blade::directive('money', function ($expression) {
            return "<?php echo money($expression); ?>";
        });
        Blade::directive('symbolMoney', function ($expression) {
            return "<?php echo symbol_money($expression); ?>";
        });
    }
}
