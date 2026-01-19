<?php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use App\Services\CompanyModule\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyUserController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display company users
     */
    public function index($companyId)
    {
        $result = $this->companyService->getCompanyUsers($companyId);

        if (!$result['success']) {
            return redirect()->route('company.show', $companyId)
                ->withErrors(['error' => $result['message']]);
        }

        $users = $result['users'];
        $company = $this->companyService->getCompany($companyId)['company'];

        return view('company-module.users.index', compact('company', 'users'));
    }

    /**
     * Invite a user to the company
     */
    public function invite(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|in:admin,accountant,viewer',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->companyService->inviteUser(
            $companyId,
            $request->email,
            $request->role
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', 'User invited successfully!');
    }

    /**
     * Remove a user from the company
     */
    public function remove($companyId, $userId)
    {
        $result = $this->companyService->removeUser($companyId, $userId);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, $companyId, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,accountant,viewer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $result = $this->companyService->updateUserRole(
            $companyId,
            $userId,
            $request->role
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Accept company invitation
     */
    public function acceptInvitation($token)
    {
        // This will be handled in CompanyController or a separate InvitationController
        // For now, redirect to login
        return redirect()->route('login')
            ->with('info', 'Please login to accept the invitation.');
    }
}