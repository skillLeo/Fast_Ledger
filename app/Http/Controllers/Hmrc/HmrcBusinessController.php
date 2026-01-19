<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Models\HmrcBusiness;
use App\Services\Hmrc\HmrcBusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HmrcBusinessController extends Controller
{
    public function __construct(private HmrcBusinessService $service) {}

    public function index(Request $request)
    {
        $userId = (int) Auth::id();
        $businesses = HmrcBusiness::where('user_id', $userId)->get();
        return view('hmrc.businesses.index', compact('businesses'));
    }

    public function sync(Request $request)
    {
        $request->validate([
            'nino' => 'required|string|min:8|max:10',
            'test_scenario' => 'nullable|string|max:100',
        ]);
        $userId = (int) Auth::id();
        $nino = (string) $request->input('nino');
        $testScenario = $request->input('test_scenario');

        try {
            $this->service->syncAllBusinesses($userId, $nino, $testScenario);
            return back()->with('success', 'Businesses synced successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to sync HMRC businesses', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to sync businesses: ' . $e->getMessage());
        }
    }

    public function show(HmrcBusiness $business)
    {
        $this->authorizeBusiness($business);
        return view('hmrc.businesses.show', compact('business'));
    }

    protected function authorizeBusiness(HmrcBusiness $business): void
    {
        $userId = (int) Auth::id();
        abort_unless($business->user_id === $userId, 403);
    }
}
