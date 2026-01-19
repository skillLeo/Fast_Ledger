<?php

namespace App\Http\Controllers\CompanyModule\Verifactu;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\CompanyModule\VerifactuConnection;

class ConnectionController extends Controller
{
    /**
     * Show AEAT connection page
     */
    public function index(): View
    {
        $connections = VerifactuConnection::latest()->get();
        
        return view('verifactu.connection.index', compact('connections'));
    }

    /**
     * Test AEAT connection (placeholder for now)
     */
    public function testConnection(Request $request, VerifactuConnection $connection): RedirectResponse
    {
        // Validate for demo
        $validated = $request->validate([
            'test_field' => 'required', // This will always fail for demo
        ]);

        return redirect()
            ->back()
            ->with('success', 'Connection tested successfully!');
    }

    /**
     * Store new connection
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nif' => 'required|string|max:20|regex:/^[A-Z][0-9]{8}$/',
            'company_name' => 'required|string|max:255',
            'environment' => 'required|in:sandbox,production',
            'certificate' => 'required|file|mimes:pfx,p12|max:2048',
            'certificate_password' => 'nullable|string',
        ], [
            'nif.regex' => 'NIF format is invalid. Example: B12345678',
            'certificate.mimes' => 'Certificate must be a .pfx or .p12 file',
        ]);

        // For now, just create without actual connection
        $connection = VerifactuConnection::create([
            'name' => $validated['name'],
            'nif' => $validated['nif'],
            'company_name' => $validated['company_name'],
            'environment' => $validated['environment'],
            'certificate_path' => $request->file('certificate')->store('verifactu/certificates', 'local'),
            'certificate_password' => encrypt($validated['certificate_password'] ?? ''),
            'sif_id' => 'SIF-' . strtoupper(uniqid()),
            'status' => 'disconnected',
        ]);

        return redirect()
            ->route('verifactu.connection.index')
            ->with('success', 'Connection created successfully. Click "Connect AEAT" to test the connection.');
    }
}