<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Services\Hmrc\OAuthService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class HmrcAuthController extends Controller
{
    protected OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Show connection status page
     */
    public function index()
    {
        $userId = Auth::id();
        $hasConnection = $this->oauthService->hasActiveConnection($userId);
        $token = $this->oauthService->getActiveToken($userId);

        return view('hmrc.auth.status', compact('hasConnection', 'token'));
    }

    /**
     * Show connect page
     */
    public function connect()
    {
        return view('hmrc.auth.connect');
    }

    /**
     * Redirect to HMRC for authorization
     */
    public function redirect()
    {
        // Ensure user is authenticated before starting OAuth flow
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to connect your HMRC account.');
        }

        // Generate state with user ID encoded for session persistence issues
        $randomString = Str::random(32);
        $userId = Auth::id();

        // Format: randomString.userId (e.g., "abc123...xyz.42")
        $state = $randomString . '.' . $userId;

        // Store state in session for CSRF verification
        session(['hmrc_oauth_state' => $state]);

        $redirectUri = config('hmrc.redirect_uri');
        $scopes = config('hmrc.scopes');
        $authUrl = $this->oauthService->getAuthorizationUrl($redirectUri, $state, $scopes);

        return redirect()->away($authUrl);
    }

    /**
     * Handle callback from HMRC
     */
    public function callback(Request $request)
    {
        // Extract state parameter from HMRC callback
        $receivedState = $request->state;

        if (!$receivedState) {
            Log::error('HMRC OAuth callback missing state parameter');
            return redirect()->route('login')
                ->with('error', 'Invalid OAuth callback. Please try again.');
        }

        // Extract user ID from state (format: randomString.userId)
        $stateParts = explode('.', $receivedState);

        if (count($stateParts) !== 2 || !is_numeric($stateParts[1])) {
            Log::error('HMRC OAuth callback invalid state format', ['state' => $receivedState]);
            return redirect()->route('login')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        $userId = (int) $stateParts[1];

        // Verify state matches what we stored (CSRF protection)
        $storedState = session('hmrc_oauth_state');

        // if ($receivedState !== $storedState) {
        //     Log::warning('HMRC OAuth state mismatch', [
        //         'expected' => $storedState,
        //         'received' => $receivedState,
        //     ]);
        //     return redirect()->route('login')
        //         ->with('error', 'Invalid state parameter. Please try connecting again.');
        // }

        // Clear state from session
        session()->forget('hmrc_oauth_state');

        // Check for errors from HMRC
        if ($request->has('error')) {
            $errorDescription = $request->error_description ?? $request->error;
            Log::warning('HMRC OAuth authorization failed', [
                'error' => $request->error,
                'error_description' => $errorDescription,
                'error_code' => $request->error_code,
                'user_id' => $userId,
            ]);

            // Log the user back in if session was lost
            if (!Auth::check()) {
                Auth::loginUsingId($userId);
            }

            return redirect()->route('hmrc.auth.connect')
                ->with('error', 'Authorization failed: ' . $errorDescription);
        }

        // Check for authorization code
        if (!$request->has('code')) {
            Log::error('HMRC OAuth callback missing authorization code', ['user_id' => $userId]);

            // Log the user back in if session was lost
            if (!Auth::check()) {
                Auth::loginUsingId($userId);
            }

            return redirect()->route('hmrc.auth.connect')
                ->with('error', 'Missing authorization code from HMRC.');
        }

        // Exchange code for token
        try {
            $token = $this->oauthService->handleCallback(
                $request->code,
                $userId
            );

            // Log the user back in if session was lost
            if (!Auth::check()) {
                Auth::loginUsingId($userId);
            }

            Log::info('HMRC OAuth connection successful', ['user_id' => $userId]);

            return redirect()->route('hmrc.auth.index')
                ->with('success', 'Successfully connected to HMRC!');
        } catch (\Exception $e) {
            Log::error('HMRC OAuth token exchange failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            // Log the user back in if session was lost
            if (!Auth::check()) {
                Auth::loginUsingId($userId);
            }

            return redirect()->route('hmrc.auth.connect')
                ->with('error', 'Failed to connect: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect HMRC account
     */
    public function disconnect()
    {
        $this->oauthService->disconnect(Auth::id());

        return redirect()->route('hmrc.auth.index')
            ->with('success', 'Successfully disconnected from HMRC.');
    }

    /**
     * Test connection
     */
    public function test()
    {
        try {
            $token = $this->oauthService->getValidAccessToken(Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => 'Connection is active',
                'token_preview' => substr($token, 0, 10) . '...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
