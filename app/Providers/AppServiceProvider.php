<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Message;
use App\Observers\ActivityObserver;
use App\Observers\MessageObserver;
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
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191); // <-- THIS is correct
        Activity::observe(ActivityObserver::class);
        Message::observe(MessageObserver::class);

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
