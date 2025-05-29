<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\API\V1\AuthSanctumService;
use App\Contracts\API\Auth\AuthServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthServiceInterface::class,
            AuthSanctumService::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
