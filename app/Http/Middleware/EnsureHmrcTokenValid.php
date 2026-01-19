<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Hmrc\OAuthService;
use App\Exceptions\HmrcAuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureHmrcTokenValid
{
    protected OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $vrn = config('hmrc.vat.vrn');
            $this->oauthService->getValidToken($vrn);
            
            return $next($request);
            
        } catch (HmrcAuthenticationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'HMRC authentication required',
                    'redirect' => route('hmrc.connect'),
                ], 401);
            }

            return redirect()->route('hmrc.connect')
                ->withErrors(['error' => 'Please connect to HMRC first']);
        }
    }
}