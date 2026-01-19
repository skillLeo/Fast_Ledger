<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Models\HmrcBusiness;
use App\Models\HmrcCalculation;
use App\Models\HmrcFinalDeclaration;
use App\Services\Hmrc\HmrcFinalDeclarationService;
use App\Services\Hmrc\HmrcCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HmrcFinalDeclarationController extends Controller
{
    protected HmrcFinalDeclarationService $declarationService;
    protected HmrcCalculationService $calculationService;

    public function __construct(
        HmrcFinalDeclarationService $declarationService,
        HmrcCalculationService $calculationService
    ) {
        $this->declarationService = $declarationService;
        $this->calculationService = $calculationService;
    }

    /**
     * Show final declaration wizard for a tax year
     */
    public function index(string $taxYear)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'No business found. Please sync your businesses first.');
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        // Redirect to current step
        return redirect()->route('hmrc.final-declaration.' . str_replace('_', '-', $declaration->wizard_step), $taxYear);
    }

    /**
     * Step 1: Prerequisites check
     */
    public function checkPrerequisites(string $taxYear)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'No business found. Please sync your businesses first.');
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        $validation = $this->declarationService->validatePrerequisites($declaration);

        return view('hmrc.final-declaration.steps.prerequisites', compact(
            'declaration',
            'validation',
            'taxYear'
        ));
    }

    /**
     * Step 2: Review submissions
     */
    public function reviewSubmissions(string $taxYear)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'No business found.');
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        if (!$declaration->prerequisites_passed) {
            return redirect()->route('hmrc.final-declaration.prerequisites', $taxYear)
                ->with('error', 'Please complete prerequisites check first.');
        }

        // Fetch submissions summary
        $summary = $this->declarationService->getSubmissionsSummary($user->User_ID, $taxYear);

        return view('hmrc.final-declaration.steps.review-submissions', compact(
            'declaration',
            'summary',
            'taxYear'
        ));
    }

    /**
     * Step 3: Review calculation
     */
    public function reviewCalculation(string $taxYear)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'No business found.');
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        if (!$declaration->submissions_reviewed) {
            return redirect()->route('hmrc.final-declaration.review-submissions', $taxYear)
                ->with('error', 'Please review submissions first.');
        }

        // Get latest calculation
        $calculation = $declaration->calculation
            ?? HmrcCalculation::where('user_id', $user->User_ID)
            ->where('tax_year', $taxYear)
            ->where('status', 'completed')
            ->latest('calculation_timestamp')
            ->first();

        if (!$calculation) {
            return redirect()->route('hmrc.calculations.index')
                ->with('warning', 'No calculation found. Please trigger a calculation first.');
        }

        // Get calculation breakdown
        $breakdown = $this->calculationService->getCalculationBreakdown($calculation->id);

        return view('hmrc.final-declaration.steps.review-calculation', compact(
            'declaration',
            'calculation',
            'breakdown',
            'taxYear'
        ));
    }

    /**
     * Step 4: Review income sources
     */
    public function reviewIncome(string $taxYear)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'No business found.');
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        if (!$declaration->calculation_reviewed) {
            return redirect()->route('hmrc.final-declaration.review-calculation', $taxYear)
                ->with('error', 'Please review calculation first.');
        }

        // Fetch all income sources
        $businesses = HmrcBusiness::where('user_id', $user->User_ID)->get();
        $summary = $this->declarationService->getSubmissionsSummary($user->User_ID, $taxYear);

        return view('hmrc.final-declaration.steps.review-income', compact(
            'declaration',
            'businesses',
            'summary',
            'taxYear'
        ));
    }

    /**
     * Step 5: Declaration page
     */
    public function declaration(string $taxYear)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'No business found.');
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        if (!$declaration->income_reviewed) {
            return redirect()->route('hmrc.final-declaration.review-income', $taxYear)
                ->with('error', 'Please review income sources first.');
        }

        return view('hmrc.final-declaration.steps.declaration', compact(
            'declaration',
            'taxYear'
        ));
    }

    /**
     * Complete a wizard step (AJAX)
     */
    public function completeStep(Request $request, string $taxYear, string $step)
    {
        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return response()->json(['success' => false, 'error' => 'No business found'], 400);
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        $this->declarationService->completeWizardStep($declaration, $step, $request->all());

        return response()->json([
            'success' => true,
            'next_step' => $declaration->wizard_step,
            'progress' => $declaration->progress_percentage,
        ]);
    }

    /**
     * Confirm declaration (legal statement) (AJAX)
     */
    public function confirmDeclaration(Request $request, string $taxYear)
    {
        $request->validate([
            'declaration_confirmation' => 'required|accepted',
            'accuracy_confirmation' => 'required|accepted',
        ]);

        $user = Auth::user();
        $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

        if (!$business) {
            return response()->json(['success' => false, 'error' => 'No business found'], 400);
        }

        $declaration = $this->declarationService->getOrCreateDeclaration(
            $user->User_ID,
            $business->nino,
            $taxYear
        );

        $this->declarationService->confirmDeclaration(
            $declaration,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Declaration confirmed. You can now submit.',
        ]);
    }

    /**
     * Submit final declaration to HMRC
     */
    public function submit(string $taxYear)
    {
        try {
            $user = Auth::user();
            $business = HmrcBusiness::where('user_id', $user->User_ID)->first();

            if (!$business) {
                return back()->with('error', 'No business found.');
            }

            $declaration = $this->declarationService->getOrCreateDeclaration(
                $user->User_ID,
                $business->nino,
                $taxYear
            );

            $result = $this->declarationService->submitFinalDeclaration($declaration);

            return redirect()->route('hmrc.final-declaration.confirmation', [
                'taxYear' => $taxYear,
                'declaration' => $declaration->id,
            ])->with('success', 'Final declaration submitted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Submission failed: ' . $e->getMessage());
        }
    }

    /**
     * Show confirmation page
     */
    public function confirmation(string $taxYear, int $declaration)
    {
        $declaration = HmrcFinalDeclaration::findOrFail($declaration);

        // Ensure the user owns this declaration
        if ($declaration->user_id !== Auth::user()->User_ID) {
            abort(403, 'Unauthorized access to this declaration.');
        }

        return view('hmrc.final-declaration.confirmation', compact('declaration', 'taxYear'));
    }
}
