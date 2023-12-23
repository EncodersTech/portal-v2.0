<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\StripeService;
use App\Services\PayPalService;
use App\Services\DwollaService;

class PaymentGatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        StripeService::init((string)config('services.stripe.secret'));

        app()->singleton(PayPalService::class, function() {
            return new PayPalService(
                (string) config('services.paypal.client_id'),
                (string) config('services.paypal.secret'),
                (string) config('services.paypal.is_live')
            );
        });

        app()->singleton(DwollaService::class, function() {
            return new DwollaService(
                (string) config('services.dwolla.client_id'),
                (string) config('services.dwolla.secret'),
                (string) config('services.dwolla.env')
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}