<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\API\V1\AuthSanctumService;
use App\Contracts\API\Auth\AuthServiceInterface;
use App\Notifications\Channels\EmailNotifier;
use App\Notifications\NotifierManager;
use Illuminate\Validation\Rules\Email;

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
        //
    }
}
