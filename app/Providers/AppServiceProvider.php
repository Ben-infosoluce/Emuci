<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        //$this->registerPolicies();
        //********************Bureau*************************
        Gate::define("SuperAdmin", function (User $user) {
            return $user->hasRole("SuperAdmin");
        });
        Gate::define("Admin", function (User $user) {
            return $user->hasRole("Admin");
        });
        Gate::define("Caisse", function (User $user) {
            return $user->hasRole("Caisse");
        });
        Gate::define("CaisseController", function (User $user) {
            return $user->hasRole("CaisseController");
        });
        Gate::define("CaisseFinancier", function (User $user) {
            return $user->hasRole("CaisseFinancier");
        });
        Gate::define("Numerisation", function (User $user) {
            return $user->hasRole("Numerisation");
        });
        Gate::define("PoolControle", function (User $user) {
            // dd($user);
            // dd($user->hasRole("PoolControle"));
            return $user->hasRole("PoolControle");
        });
        Gate::define("MT1", function (User $user) {
            return $user->hasRole("MT1");
        });
        Gate::define("MT2", function (User $user) {
            return $user->hasRole("MT2");
        });
        Gate::define("Boss", function (User $user) {
            return $user->hasRole("Boss");
        });
        Gate::define("Raf", function (User $user) {
            return $user->hasRole("Raf");
        });

        //********************Client*************************
        //gestion de role
        Gate::define("Client", function (Client $user) {
            return $user->hasRole("Client");
        });

        // ensure unauthenticated users are redirected to the named login route
        \Illuminate\Auth\Middleware\Authenticate::redirectUsing(function ($request) {
            // keep the default "login" name used in web.php
            return route('login');
        });
    }
}
