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

// Company Module Services
use App\Services\CompanyModule\CompanyService;
use App\Services\CompanyModule\CompanySetupService;
use App\Services\CompanyModule\ModuleService;
use App\Services\CompanyModule\ActivityLogService;

// Company Module Repositories
use App\Repositories\CompanyModule\Interfaces\CompanyRepositoryInterface;
use App\Repositories\CompanyModule\CompanyRepository;
use App\Repositories\CompanyModule\Interfaces\ModuleRepositoryInterface;
use App\Repositories\CompanyModule\ModuleRepository;
use App\Repositories\CompanyModule\Interfaces\CompanyUserRepositoryInterface;
use App\Repositories\CompanyModule\CompanyUserRepository;
use App\Repositories\CompanyModule\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\CompanyModule\ActivityLogRepository;

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

        // ============================================
        // Company Module Services (NEW)
        // ============================================
        $this->app->singleton(CompanyService::class);
        $this->app->singleton(CompanySetupService::class);
        $this->app->singleton(ModuleService::class);
        $this->app->singleton(ActivityLogService::class);

        // ============================================
        // Company Module Repositories (NEW)
        // ============================================
        $this->app->bind(
            CompanyRepositoryInterface::class,
            CompanyRepository::class
        );

        $this->app->bind(
            ModuleRepositoryInterface::class,
            ModuleRepository::class
        );

        $this->app->bind(
            CompanyUserRepositoryInterface::class,
            CompanyUserRepository::class
        );

        $this->app->bind(
            ActivityLogRepositoryInterface::class,
            ActivityLogRepository::class
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
