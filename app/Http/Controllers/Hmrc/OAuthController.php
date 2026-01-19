<?php

namespace App\Http\Controllers\Hmrc;

use Illuminate\Http\Request;
use App\Services\Hmrc\OAuthService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Hmrc\VatObligationService;
use App\Exceptions\HmrcAuthenticationException;

class OAuthController extends Controller
{
    protected OAuthService $oauthService;
    protected VatObligationService $obligationService;
    public function __construct(OAuthService $oauthService, VatObligationService $obligationService)
    {
        $this->oauthService = $oauthService;
        $this->obligationService = $obligationService;
    }

    /**
     * Show connect to HMRC page
     */
    public function index()
    {
        return view('hmrc.oauth.index');
    }

    /**
     * Redirect to HMRC authorization page
     */
    public function connect()
    {
        try {
            $authUrl = $this->oauthService->getAuthorizationUrl();

            Log::info('Redirecting to HMRC authorization', [
                'user_id' => auth()->id(),
            ]);

            return redirect($authUrl);
        } catch (\Exception $e) {
            Log::error('Failed to generate authorization URL', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Failed to connect to HMRC. Please try again.',
            ]);
        }
    }

    /**
     * Handle OAuth callback from HMRC
     */
    public function callback(Request $request)
    {
        try {
            // Validate required parameters
            if (!$request->has('code')) {
                throw new HmrcAuthenticationException('Authorization code not provided');
            }

            // Verify state parameter (CSRF protection)
            if ($request->has('state') && !$this->oauthService->verifyState($request->state)) {
                throw new HmrcAuthenticationException('Invalid state parameter');
            }

            // Exchange code for token
            $token = $this->oauthService->exchangeCodeForToken(
                $request->code,
                auth()->id()
            );

            Log::info('HMRC OAuth successful', [
                'user_id' => auth()->id(),
                'token_id' => $token->id,
                'vrn' => $token->vrn,
            ]);

            // ✅ NEW: Auto-sync obligations after connecting
           try {
            $syncedCount = $this->obligationService->syncObligations($token->vrn);

            Log::info('Obligations synced after OAuth', [
                'vrn' => $token->vrn,
                'count' => $syncedCount,
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to sync obligations', [
                'error' => $e->getMessage(),
            ]);
            // Don't fail OAuth if sync fails
        }

        return redirect()->route('hmrc.vat.dashboard')
            ->with('success', 'Successfully connected to HMRC!');

    }catch (HmrcAuthenticationException $e) {
            Log::warning('OAuth authentication failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('hmrc.connect')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('hmrc.connect')
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Disconnect from HMRC (revoke token)
     */
    public function disconnect()
    {
        try {
            $vrn = config('hmrc.vat.vrn');
            $token = $this->oauthService->getValidToken($vrn, auth()->id());

            if ($token) {
                $this->oauthService->revokeToken($token);
            }

            Log::info('HMRC disconnected', [
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('hmrc.connect')
                ->with('success', 'Successfully disconnected from HMRC');
        } catch (\Exception $e) {
            Log::error('Disconnect error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to disconnect. Please try again.']);
        }
    }

    /**
     * Check connection status
     */
    public function status()
    {
        try {
            $vrn = config('hmrc.vat.vrn');
            $token = $this->oauthService->getValidToken($vrn, auth()->id());

            return response()->json([
                'connected' => true,
                'vrn' => $token->vrn,
                'expires_at' => $token->expires_at->toDateTimeString(),
                'expires_in_minutes' => $token->expires_at->diffInMinutes(now()),
            ]);
        } catch (HmrcAuthenticationException $e) {
            return response()->json([
                'connected' => false,
                'message' => 'Not connected to HMRC',
            ], 401);
        }
    }
}
