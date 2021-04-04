<?php

namespace App\Providers;

use App\Recaptchas\GoogleRecaptcha;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GoogleRecaptcha::class, function ($app, $params) {
            return new GoogleRecaptcha($params['request']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
