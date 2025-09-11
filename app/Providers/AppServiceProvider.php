<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // <-- THIS is correct
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191); // <-- THIS is correct

        // Blade directive: @money(123.45) renders formatted converted string
        Blade::directive('money', function ($expression) {
            return "<?php echo money($expression); ?>";
        });
        Blade::directive('symbolMoney', function ($expression) {
            return "<?php echo symbol_money($expression); ?>";
        });
    }
}
