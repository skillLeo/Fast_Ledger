<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hmrc\SubmitVatReturnRequest;
use App\Services\Hmrc\VatService;
use App\Exceptions\HmrcApiException;
use App\Exceptions\InvalidVatReturnException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VatReturnController extends Controller
{
    protected VatService $vatService;

    public function __construct(VatService $vatService)
    {
        $this->vatService = $vatService;
    }

    /**
     * Show VAT return submission form
     */
    public function create(Request $request)
    {
        $periodKey = $request->query('periodKey');

        return view('hmrc.vat.submit', [
            'periodKey' => $periodKey,
        ]);
    }

    /**
     * Submit VAT return to HMRC
     */
    public function store(SubmitVatReturnRequest $request)
    {
        try {
            // Get validated data
            $data = $request->validated();

            Log::info('Submitting VAT return to HMRC', [
                'period_key' => $data['periodKey'],
                'user_id' => auth()->id(),
            ]);

            // Submit to HMRC
            $submission = $this->vatService->submitReturn($data);

            Log::info('VAT return submitted successfully', [
                'submission_id' => $submission->id,
                'period_key' => $data['periodKey'],
            ]);

            // ✅ Redirect to dashboard with success message
            return redirect()->route('hmrc.vat.dashboard')
                ->with('success', "✅ VAT return for period {$data['periodKey']} submitted successfully!");
        } catch (HmrcApiException $e) {
            Log::error('VAT return submission failed', [
                'error' => $e->getMessage(),
                'hmrc_errors' => $e->getErrors(),
            ]);

            // ✅ Redirect back with error message
            return redirect()->back()
                ->withErrors([
                    'submission' => $this->formatHmrcError($e)
                ])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Unexpected error during VAT submission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // ✅ Redirect back with generic error
            return redirect()->back()
                ->withErrors([
                    'submission' => 'An unexpected error occurred. Please try again.'
                ])
                ->withInput();
        }
    }

    /**
     * Format HMRC error message for display
     */
    protected function formatHmrcError(HmrcApiException $e): string
    {
        $errors = $e->getErrors();

        if (empty($errors)) {
            return $e->getMessage();
        }

        // Check for specific error codes
        foreach ($errors as $error) {
            $code = $error['code'] ?? '';
            $message = $error['message'] ?? '';

            switch ($code) {
                case 'DUPLICATE_SUBMISSION':
                    return '⚠️ This VAT return has already been submitted to HMRC.';

                case 'INVALID_PERIOD_KEY':
                    return '❌ Invalid period key. Please check the period and try again.';

                case 'INVALID_MONETARY_AMOUNT':
                    return '❌ Invalid amount. Please check your figures and try again.';

                default:
                    return "❌ HMRC Error: {$message}";
            }
        }

        return $e->getMessage();
    }



    /**
     * View specific VAT return
     */
    public function show(string $periodKey)
    {
        try {
            $return = $this->vatService->getReturn($periodKey);

            return view('hmrc.vat.show', [
                'return' => $return,
                'periodKey' => $periodKey,
            ]);
        } catch (HmrcApiException $e) {
            return redirect()
                ->route('hmrc.vat.dashboard')
                ->withErrors(['error' => 'Failed to retrieve VAT return: ' . $e->getMessage()]);
        }
    }
}
