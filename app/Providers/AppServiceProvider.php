<?php

namespace App\Providers;

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
        $this->app->bind(
            'App\Repositories\Auth\AuthRepositoryInterface',
            'App\Repositories\Auth\AuthRepository'
        );
        $this->app->bind(
            'App\Repositories\Akademik\AkademikRepositoryInterface',
            'App\Repositories\Akademik\AkademikRepository'
        );
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
