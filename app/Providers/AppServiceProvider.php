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
        $this->app->singleton(GoogleRecaptcha::class, function ($params) {
            return new GoogleRecaptcha($params['token']);
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
