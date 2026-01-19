<?php

namespace App\Providers;

use App\Services\Hmrc\HmrcClient;
use App\Services\Hmrc\VatService;
use App\Services\Hmrc\OAuthService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\HmrcTokenRepository;
use App\Services\Hmrc\VatObligationService;
use App\Repositories\VatObligationRepository;
use App\Repositories\VatSubmissionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->singleton(HmrcTokenRepository::class);
        $this->app->singleton(VatSubmissionRepository::class);
        $this->app->singleton(VatObligationRepository::class);

        // HMRC Services
        $this->app->singleton(HmrcClient::class);
        $this->app->singleton(OAuthService::class);
        $this->app->singleton(VatService::class);
        $this->app->singleton(VatObligationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
