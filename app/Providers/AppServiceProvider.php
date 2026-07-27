<?php

namespace App\Providers;

use App\Models\StoreSetting;
use App\Services\Payments\MockPaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Swap MockPaymentGateway for a real implementation (Stripe, PayPal, a
        // regional gateway) here when one is ready — nothing else changes.
        $this->app->bind(PaymentGateway::class, MockPaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // Guard against early boot / pre-migration requests where the table doesn't exist yet.
            if (Schema::hasTable('store_settings')) {
                $view->with('storeSettings', StoreSetting::current());
            }
        });
    }
}
