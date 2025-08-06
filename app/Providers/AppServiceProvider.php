<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ActivityService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ActivityService::class, function ($app) {
            return new ActivityService();
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