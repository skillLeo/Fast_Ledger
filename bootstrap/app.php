<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Middleware\EnsureHmrcTokenValid;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/hmrc.php',
            __DIR__ . '/../routes/shared.php',
        ],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
            'auth' => Authenticate::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'hmrc.token' => EnsureHmrcTokenValid::class,

            // Company Module Middleware
            'module' => \App\Http\Middleware\CheckModuleAccess::class,
            'company.role' => \App\Http\Middleware\CheckCompanyRole::class,
            'current.company' => \App\Http\Middleware\EnsureCurrentCompany::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'subscription.check' => \App\Http\Middleware\CheckSubscriptionStatus::class,
            
            // ✅ ADD THIS LINE
            'onboarding' => \App\Http\Middleware\CheckOnboardingComplete::class,
        ]);

        // ✅ Add to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();