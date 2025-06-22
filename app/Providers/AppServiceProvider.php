<?php

namespace App\Providers;

use App\Notifications\NotifierManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Services\API\V1\AuthSanctumService;
use Illuminate\Support\Facades\RateLimiter;
use App\Notifications\Channels\EmailNotifier;
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

        // Registro del sistema de notificaciones mediante interfaz
        // -----------------------------------------------------------------
        $this->app->bind(
            EmailNotifier::class,
            EmailNotifier::class,
        );
        // Establecer otros canales de notificación si es necesario del mismo modo...
        // $this->app->bind(SmsNotifier::class, SmsNotifier::class);
        // ...

        // Vincular el NotifierManager con los canales de notificación disponibles
        $this->app->bind(NotifierManager::class, function ($app) {
            return new NotifierManager([
                'mail' => $app->make(EmailNotifier::class),
                // Agregar otros canales de notificación aquí si es necesario
                // 'sms' => $app->make(SmsNotifier::class),
                // ...
            ]);
        });
        // -----------------------------------------------------------------
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Para las pruebas de control máximo de peticiones
        RateLimiter::for('test-too-many-requests', function ($request) {
            return Limit::perMinute(10)->by('test-too-many-requests-signature');
        });
    }
}
