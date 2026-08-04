<?php

namespace Ecom\Payments;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

class EcomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ecom.php', 'ecom');
        $this->app->singleton(EcomClient::class, fn ($app) => new EcomClient(
            $app->make(Factory::class),
            $app['config']->get('ecom'),
        ));
        $this->app->alias(EcomClient::class, 'ecom');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/ecom.php' => config_path('ecom.php'),
        ], 'ecom-config');
    }
}
