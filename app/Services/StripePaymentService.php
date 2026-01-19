<?php
// app/Services/StripePaymentService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    /**
     * Get publishable key based on mode
     */
    public function getPublishableKey(): string
    {
        $mode = config('services.stripe.mode', 'test');
        
        if ($mode === 'live') {
            $key = config('services.stripe.live.key');
        } else {
            $key = config('services.stripe.test.key');
        }

        // ✅ Ensure we return a string, never null
        if (empty($key)) {
            Log::error('Stripe publishable key is not set', [
                'mode' => $mode,
                'config_path' => $mode === 'live' ? 'services.stripe.live.key' : 'services.stripe.test.key'
            ]);
            
            // Return a fallback (will fail gracefully in frontend)
            return '';
        }

        return $key;
    }

    /**
     * Get secret key based on mode
     */
    public function getSecretKey(): string
    {
        $mode = config('services.stripe.mode', 'test');
        
        if ($mode === 'live') {
            $secret = config('services.stripe.live.secret');
        } else {
            $secret = config('services.stripe.test.secret');
        }

        // ✅ Ensure we return a string, never null
        if (empty($secret)) {
            Log::error('Stripe secret key is not set', [
                'mode' => $mode,
                'config_path' => $mode === 'live' ? 'services.stripe.live.secret' : 'services.stripe.test.secret'
            ]);
            
            throw new \Exception('Stripe secret key is not configured');
        }

        return $secret;
    }

    /**
     * Check if in test mode
     */
    public function isTestMode(): bool
    {
        return config('services.stripe.mode', 'test') === 'test';
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(float $amount, array $metadata = []): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->getSecretKey());

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)($amount * 100), // Convert to pence/cents
                'currency' => 'gbp',
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
            ];

        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe card error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getError()->message,
            ];

        } catch (\Stripe\Exception\RateLimitException $e) {
            Log::error('Stripe rate limit: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Too many requests. Please try again.',
            ];

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error('Stripe invalid request: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Invalid payment request.',
            ];

        } catch (\Stripe\Exception\AuthenticationException $e) {
            Log::error('Stripe authentication failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment authentication failed.',
            ];

        } catch (\Stripe\Exception\ApiConnectionException $e) {
            Log::error('Stripe connection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Network error. Please try again.',
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment processing error.',
            ];

        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An unexpected error occurred.',
            ];
        }
    }

    /**
     * Get payment intent details
     */
    public function getPaymentIntent(string $paymentIntentId): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->getSecretKey());

            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'payment_intent' => $paymentIntent,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment intent: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}