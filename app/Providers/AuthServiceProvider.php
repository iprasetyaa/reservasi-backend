<?php

namespace App\Providers;

use App\Enums\UserRoleEnum;
use App\Models\CommandCenterCloseDate;
use App\Models\Reservation;
use App\Policies\CCCloseDatePolicy;
use App\Policies\ReservationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Reservation::class => ReservationPolicy::class,
        CommandCenterCloseDate::class => CCCloseDatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /* define a admin user role */
        Gate::define('isAdmin', function () {
            return Auth::user()->hasRole(UserRoleEnum::admin_reservasi());
        });

        /* define a employee user role */
        Gate::define('isEmployee', function () {
            return Auth::user()->hasRole(UserRoleEnum::employee_reservasi());
        });
    }
}
