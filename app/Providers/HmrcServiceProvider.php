<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Hmrc\HmrcClient;
use App\Services\Hmrc\OAuthService;
use App\Services\Hmrc\VatService;
use App\Repositories\HmrcTokenRepository;
use App\Repositories\VatSubmissionRepository;

class HmrcServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->singleton(HmrcTokenRepository::class);
        $this->app->singleton(VatSubmissionRepository::class);

        // HMRC Client
        $this->app->singleton(HmrcClient::class);

        // OAuth Service
        $this->app->singleton(OAuthService::class, function ($app) {
            return new OAuthService(
                $app->make(HmrcClient::class),
                $app->make(HmrcTokenRepository::class)
            );
        });

        // VAT Service
        $this->app->singleton(VatService::class, function ($app) {
            return new VatService(
                $app->make(HmrcClient::class),
                $app->make(OAuthService::class),
                $app->make(VatSubmissionRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(base_path('routes/hmrc.php'));
    }
}