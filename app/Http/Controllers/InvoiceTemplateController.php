<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Client;
use App\Models\BankAccount;
use Illuminate\Support\Str;
use App\Models\DraftInvoice;
use Illuminate\Http\Request;
use App\Models\InvoiceTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplateController extends Controller
{
    /**
     * ✅ FIXED: Get client ID based on context (main app or company module)
     */
    private function getClientId()
    {
        // Check if we're in company module context
        $companyId = session('current_company_id');

        if ($companyId) {
            Log::info('Using company module context', ['company_id' => $companyId]);
            return $companyId;
        }

        // Otherwise, use main app client ID
        $clientId = auth()->user()->Client_ID;

        if (!$clientId) {
            throw new \Exception('No client or company selected. Please login or select a company.');
        }

        Log::info('Using main app context', ['client_id' => $clientId]);
        return $clientId;
    }

    public function preview(Request $request)
    {

        $validated = $request->all();

        // ✅ FIXED: Get correct client/company ID
        $clientId = $this->getClientId();

        Log::info('Creating draft invoice', [
            'context' => session('current_company_id') ? 'company_module' : 'main_app',
            'client_id' => $clientId,
            'has_items' => isset($validated['items'])
        ]);

        // Create draft invoice
        $draft = DraftInvoice::createDraft($validated, $clientId);

        // ✅ FIXED: Redirect to correct route based on context
        $isCompanyModule = session('current_company_id') !== null;

        if ($isCompanyModule) {
            Log::info('Redirecting to company module preview', ['draft_key' => $draft->draft_key]);
            return redirect()->route('company.invoices.templates.preview.show', [
                'draft' => $draft->draft_key
            ]);
        } else {
            Log::info('Redirecting to main app preview', ['draft_key' => $draft->draft_key]);
            return redirect()->route('invoicetemplates.preview.show', [
                'draft' => $draft->draft_key
            ]);
        }
    }

    public function showPreview(Request $request, string $draftKey)
    {
        $draft = DraftInvoice::getByKey($draftKey);
        if (!$draft) {
            return redirect()->back()->with('error', 'Invoice draft not found or expired');
        }

        $currentClientId = $this->getClientId();

        if ($draft->client_id !== $currentClientId) {
            abort(403, 'Unauthorized access');
        }

        $validated = $draft->invoice_data;
        $clientId = $draft->client_id;

        $isCompanyModule = session('current_company_id') !== null;

        $customerData = null;
        $companyData = null;
        $fileData = null;
        if ($isCompanyModule) {
            // Company module: Fetch customer and company separately
            $companyData = $this->getCompanyDetails($clientId);

            if (isset($validated['customer_id']) && $validated['customer_id']) {
                $customerData = $this->getCustomerDetails($validated['customer_id'], $clientId);
            } elseif (isset($validated['file_id']) && $validated['file_id']) {
                $customerData = $this->getCustomerDetails($validated['file_id'], $clientId);
            }

            Log::info('Company module data loaded', [
                'company_id' => $clientId,
                'customer_found' => $customerData ? 'yes' : 'no'
            ]);

            // dd('customer',$customerData, 'company',$companyData);
        } else {
            // Main app: Use existing logic (unchanged)
            if (isset($validated['file_id']) && $validated['file_id']) {
                $fileData = File::where('File_ID', $validated['file_id'])
                    ->where('Client_ID', $clientId)
                    ->first();
            }
        }

        $client = $isCompanyModule ? $customerData : Client::where('Client_ID', $clientId)->first();


        $bankAccount = $this->getBankAccount($clientId);

        $templates = InvoiceTemplate::where('client_id', $clientId)
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $templateId = $request->get('template_id');
        $template = $this->loadTemplateForClient($clientId, $templateId);

        // ✅ NEW: Pass context information
        $isCompanyModule = session('current_company_id') !== null;
        $routePrefix = $isCompanyModule ? 'company.invoices.templates' : 'invoicetemplates';

        $invoiceNotes = $this->parseInvoiceNotes($validated['invoice_notes'] ?? null);


        return view('admin.day_book.preview', compact(
            'validated',
            'client',
            'template',
            'templates',
            'fileData',
            'draft',
            'bankAccount',
            'isCompanyModule',
            'routePrefix',
            'invoiceNotes',
            'customerData',  // ✅ Add this
            'companyData'
        ));
    }

    private function getCompanyDetails($companyId)
    {
        $company = \App\Models\CompanyModule\Company::where('id', $companyId)->first();

        if (!$company) {
            Log::warning('Company not found', ['company_id' => $companyId]);
            return null;
        }

        // ✅ Map company fields using ACTUAL column names from company_module_companies table
        return (object)[
            'Company_Name' => $company->Company_Name,           // ✅ Correct field
            'Business_Name' => $company->Company_Name,          // ✅ Alias for blade compatibility
            'Address1' => $company->Street_Address ?? '',       // ✅ Correct field
            'Address_Line_1' => $company->Street_Address ?? '', // ✅ Alias
            'Address2' => '',                                   // ✅ Not in table
            'Address_Line_2' => '',                             // ✅ Not in table
            'Town' => $company->City ?? '',                     // ✅ Correct field
            'City' => $company->City ?? '',                     // ✅ Alias
            'Post_Code' => $company->Postal_Code ?? '',         // ✅ Correct field (was Post_Code, should be Postal_Code)
            'Postal_Code' => $company->Postal_Code ?? '',       // ✅ Alias
            'State' => $company->State ?? '',                   // ✅ Add State
            'Country' => $company->Country ?? '',               // ✅ Add Country
            'Phone' => $company->Phone_Number ?? '',            // ✅ Correct field (was Contact_Phone)
            'Contact_Phone' => $company->Phone_Number ?? '',    // ✅ Alias
            'Mobile' => '',                                     // ✅ Not in table
            'Email' => $company->Email ?? '',                   // ✅ Correct field (was Contact_Email)
            'Contact_Email' => $company->Email ?? '',           // ✅ Alias
            'VAT_Registration_No' => $company->Tax_ID ?? '',    // ✅ Use Tax_ID instead
            'Tax_ID' => $company->Tax_ID ?? '',                 // ✅ Actual field
            'Company_Reg_No' => $company->SIF_Identifier ?? '', // ✅ Use SIF_Identifier
            'Website' => $company->Website ?? ''                // ✅ Add Website
        ];
    }

    /**
     * ✅ NEW: Get customer details for Company Module
     */

    private function getCustomerDetails($customerId, $companyId)
    {
        $customer = \App\Models\CompanyModule\Customer::where('id', $customerId)
            ->where('Company_ID', $companyId)
            ->first();

        if (!$customer) {
            Log::warning('Customer not found', [
                'customer_id' => $customerId,
                'company_id' => $companyId
            ]);
            return null;
        }

        // ✅ Map customer fields using ACTUAL column names from customers table
        return (object)[
            'Legal_Name_Company_Name' => $customer->Legal_Name_Company_Name ?? 'N/A',
            'Business_Name' => $customer->Legal_Name_Company_Name ?? 'N/A',  // ✅ Alias for blade
            'Contact_Name' => $customer->Contact_Person_Name ?? '',          // ✅ Correct field
            'Contact_Person_Name' => $customer->Contact_Person_Name ?? '',   // ✅ Alias
            'Street_Address' => $customer->Street_Address ?? '',
            'Address1' => $customer->Street_Address ?? '',                   // ✅ Alias
            'Address2' => '',                                                // ✅ Not in table
            'City' => $customer->City ?? '',
            'Town' => $customer->City ?? '',                                 // ✅ Alias
            'Postal_Code' => $customer->Postal_Code ?? '',
            'Post_Code' => $customer->Postal_Code ?? '',                     // ✅ Alias
            'Province' => $customer->Province ?? '',
            'Country' => $customer->Country ?? '',
            'Phone' => $customer->Phone ?? '',
            'Mobile' => '',                                                  // ✅ Not in table
            'Email' => $customer->Email ?? '',
            'Tax_ID_Number' => $customer->Tax_ID_Number ?? '',
            'VAT_Registration_No' => $customer->Tax_ID_Number ?? '',         // ✅ Alias
            'Customer_Type' => $customer->Customer_Type ?? 'Individual',
            'Tax_ID_Type' => $customer->Tax_ID_Type ?? ''
        ];
    }

    /**
     * ✅ NEW: Get Client or Company entity based on context
     */
    private function getClientOrCompany($clientId)
    {
        // Check if we're in company module context
        if (session('current_company_id')) {
            $company = \App\Models\CompanyModule\Company::where('id', $clientId)->first();

            if (!$company) {
                throw new \Exception('Company not found');
            }

            // Map company fields to match client structure
            return (object)[
                'Client_ID' => $company->Company_ID,
                'Business_Name' => $company->Company_Name,
                'Address' => $company->Address_Line_1 ?? '',
                'City' => $company->City ?? '',
                'Postcode' => $company->Post_Code ?? '',
                'Phone' => $company->Contact_Phone ?? '',
                'Email' => $company->Contact_Email ?? '',
                'Client_Ref' => $company->Company_ID,
            ];
        }

        // Main app - get client
        return Client::where('Client_ID', $clientId)->first();
    }

    /**
     * ✅ NEW: Get bank account based on context
     */
    private function getBankAccount($clientId)
    {
        return BankAccount::where('Client_ID', $clientId)
            ->where('Is_Deleted', 0)
            ->first();
    }

    public function loadTemplateForClient($clientId, $templateId = null)
    {
        $template = null;

        // Priority 1: Load specific template if ID provided (manual selection)
        if ($templateId) {
            $template = InvoiceTemplate::where('client_id', $clientId)
                ->where('id', $templateId)
                ->first();
        }

        // Priority 2: ✅ Load LATEST customized template (by updated_at)
        if (!$template) {
            $template = InvoiceTemplate::where('client_id', $clientId)
                ->where('created_by', auth()->id())  // ✅ Only templates created by this user
                ->orderBy('updated_at', 'desc')      // ✅ Latest customized first
                ->first();
        }

        // Priority 3: Load any template for this client (fallback)
        if (!$template) {
            $template = InvoiceTemplate::where('client_id', $clientId)
                ->orderBy('updated_at', 'desc')
                ->first();
        }

        // Priority 4: Create default object for first-time users
        if (!$template) {
            $template = (object)[
                'id' => null,
                'name' => 'Default Template',
                'description' => 'Base invoice template',
                'logo_path' => null,
                'template_data' => $this->getDefaultStyles(),
                'is_default' => false
            ];
        } else {
            // Ensure template_data is array
            if (is_string($template->template_data)) {
                $template->template_data = json_decode($template->template_data, true) ?? $this->getDefaultStyles();
            }
        }

        return $template;
    }

    private function getDefaultStyles()
    {
        return [
            'primaryColor' => '#1e3a8a',
            'secondaryColor' => '#16a34a',
            'titleFont' => 'Arial',
            'bodyFont' => 'Arial',
            'fontSize' => '11px',
            'titleFontSize' => '36px',
            'logoPath' => null,
            'positions' => []
        ];
    }

    /**
     * Show customize mode with template
     */
    public function showCustomizationMode(Request $request, string $draftKey)
    {
        $draft = DraftInvoice::getByKey($draftKey);
        if (!$draft) {
            return redirect()->back()->with('error', 'Invoice draft not found or expired');
        }

        $currentClientId = $this->getClientId();

        if ($draft->client_id !== $currentClientId) {
            abort(403, 'Unauthorized access');
        }

        $validated = $draft->invoice_data;
        $clientId = $draft->client_id;

        $isCompanyModule = session('current_company_id') !== null;

        $customerData = null;
        $companyData = null;
        $fileData = null;

        if ($isCompanyModule) {
            // Company module: Fetch customer and company separately
            $companyData = $this->getCompanyDetails($clientId);

            if (isset($validated['customer_id']) && $validated['customer_id']) {
                $customerData = $this->getCustomerDetails($validated['customer_id'], $clientId);
            } elseif (isset($validated['file_id']) && $validated['file_id']) {
                $customerData = $this->getCustomerDetails($validated['file_id'], $clientId);
            }

            Log::info('Company module data loaded', [
                'company_id' => $clientId,
                'customer_found' => $customerData ? 'yes' : 'no'
            ]);
        } else {
            // Main app: Use existing logic (unchanged)
            if (isset($validated['file_id']) && $validated['file_id']) {
                $fileData = File::where('File_ID', $validated['file_id'])
                    ->where('Client_ID', $clientId)
                    ->first();
            }
        }

        // For backward compatibility, set $client to customer data
        $client = $isCompanyModule ? $customerData : Client::where('Client_ID', $clientId)->first();

        $bankAccount = $this->getBankAccount($clientId);

        $templates = InvoiceTemplate::where('client_id', $clientId)
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $template = $this->loadTemplateForClient($clientId, $request->get('template_id'));

        // ✅ ADD THESE LINES - Pass context information
        $isCompanyModule = session('current_company_id') !== null;
        $routePrefix = $isCompanyModule ? 'company.invoices.templates' : 'invoicetemplates';

        $invoiceNotes = $this->parseInvoiceNotes($validated['invoice_notes'] ?? null);

        return view('admin.day_book.customize_invoice', compact(
            'validated',
            'client',
            'templates',
            'template',
            'fileData',
            'draft',
            'bankAccount',
            'isCompanyModule',  // ✅ Add this
            'routePrefix',
            'invoiceNotes',
            'customerData',  // ✅ Add this
            'companyData'      // ✅ Add this
        ));
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        try {
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');

                // Generate unique filename
                $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                // Store in public/logos directory
                $path = $file->storeAs('invoice_logos', $filename, 'public');

                Log::info('Logo uploaded', [
                    'path' => $path,
                    'user_id' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'url' => route('uploadfiles.show', ['folder' => 'invoice_logos', 'filename' => $filename]),
                    'path' => $path
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Logo upload failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save or update template
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_data' => 'required|string',
            'is_default' => 'boolean',
            'draft_key' => 'required|string'
        ]);

        try {
            $draft = DraftInvoice::getByKey($request->draft_key);
            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found'
                ], 404);
            }

            $clientId = $draft->client_id;

            // Check if template already exists
            $template = InvoiceTemplate::where('client_id', $clientId)
                ->where('name', $request->name)
                ->where('created_by', auth()->id())  // ✅ Only user's own templates
                ->first();

            if ($template) {
                // ✅ Update existing template (this automatically updates updated_at)
                $template->update([
                    'template_data' => $request->template_data,
                    'logo_path' => $request->logo_path,
                    'description' => $request->description ?? 'Custom invoice template',
                ]);

                Log::info('Template updated', [
                    'template_id' => $template->id,
                    'updated_at' => $template->updated_at  // ✅ Latest timestamp
                ]);
            } else {
                // Create new template
                $template = InvoiceTemplate::create([
                    'client_id' => $clientId,
                    'name' => $request->name,
                    'description' => $request->description ?? 'Custom invoice template',
                    'template_data' => $request->template_data,
                    'logo_path' => $request->logo_path,
                    'is_default' => 0,
                    'created_by' => auth()->id(),  // ✅ Track who created it
                ]);

                Log::info('New template created', [
                    'template_id' => $template->id,
                    'created_at' => $template->created_at
                ]);
            }

            // ✅ OPTIONAL: Set as default if requested
            if ($request->is_default) {
                $template->setAsDefault();
            }

            return response()->json([
                'success' => true,
                'template_id' => $template->id,
                'message' => 'Template saved successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Template save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load template by ID (AJAX)
     */
    public function loadTemplate($id)
    {
        try {
            // ✅ FIXED: Get correct client/company ID
            $clientId = $this->getClientId();

            $template = InvoiceTemplate::where('id', $id)
                ->where('client_id', $clientId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 404);
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        try {
            // ✅ FIXED: Get correct client/company ID
            $clientId = $this->getClientId();

            $template = InvoiceTemplate::where('id', $id)
                ->where('client_id', $clientId)
                ->firstOrFail();

            // Prevent deleting default template
            if ($template->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete default template. Set another template as default first.'
                ], 400);
            }

            // Delete logo if exists
            if ($template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template'
            ], 500);
        }
    }

    /**
     * List all templates for client
     */
    public function listTemplates()
    {
        // ✅ FIXED: Get correct client/company ID
        $clientId = $this->getClientId();

        $templates = InvoiceTemplate::where('client_id', $clientId)
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.day_book.templates_list', compact('templates'));
    }

    /**
     * Preview invoice with AJAX (for template switching)
     */
    public function previewAjax(Request $request)
    {
        try {
            $templateId = $request->template_id;
            $invoiceData = $request->invoice_data;

            // ✅ FIXED: Get correct client/company ID
            $clientId = $this->getClientId();

            // Load template
            $template = null;
            if ($templateId) {
                $template = InvoiceTemplate::where('id', $templateId)
                    ->where('client_id', $clientId)
                    ->first();

                // Decode template_data if it's JSON string
                if ($template && is_string($template->template_data)) {
                    $template->template_data = json_decode($template->template_data, true);
                }
            }

            $isCompanyModule = session('current_company_id') !== null;

            $customerData = null;
            $companyData = null;
            $fileData = null;

            if ($isCompanyModule) {
                $companyData = $this->getCompanyDetails($clientId);

                if (isset($invoiceData['customer_id']) && $invoiceData['customer_id']) {
                    $customerData = $this->getCustomerDetails($invoiceData['customer_id'], $clientId);
                } elseif (isset($invoiceData['file_id']) && $invoiceData['file_id']) {
                    $customerData = $this->getCustomerDetails($invoiceData['file_id'], $clientId);
                }
            } else {
                if (isset($invoiceData['file_id']) && $invoiceData['file_id']) {
                    $fileData = File::where('File_ID', $invoiceData['file_id'])
                        ->where('Client_ID', $clientId)
                        ->first();
                }
            }

            $client = $isCompanyModule ? $customerData : $this->getClientOrCompany($clientId);

            // ✅ Get bank account
            $bankAccount = $this->getBankAccount($clientId);

            // ✅ Ensure product images are preserved
            if (isset($invoiceData['items'])) {
                foreach ($invoiceData['items'] as $key => $item) {
                    if (isset($item['product_image']) && !empty($item['product_image'])) {
                        Log::info('Product image found in item', [
                            'item_key' => $key,
                            'image_url' => $item['product_image']
                        ]);
                    }
                }
            }

            $invoiceNotes = $this->parseInvoiceNotes($invoiceData['invoice_notes'] ?? null);


            // Render the preview content
            $html = view('admin.day_book.preview_content', [
                'validated' => $invoiceData,
                'client' => $client,
                'fileData' => $fileData,
                'template' => $template,
                'templates' => [],
                'bankAccount' => $bankAccount,
                'invoiceNotes' => $invoiceNotes,
                'customerData' => $customerData ?? null,  // ✅ Add this
                'companyData' => $companyData ?? null
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Preview AJAX failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadPdf(Request $request)
    {
        try {
            $request->validate([
                'template_id' => 'nullable|integer|exists:invoice_templates,id',
            ]);

            $validated = $request->all();
            $templateId = $validated['template_id'] ?? null;
            unset($validated['_token'], $validated['template_id']);

            $clientId = $this->getClientId();

            $isCompanyModule = session('current_company_id') !== null;

            $customerData = null;
            $companyData = null;
            $fileData = null;

            if ($isCompanyModule) {
                $companyData = $this->getCompanyDetails($clientId);

                if (isset($validated['customer_id']) && $validated['customer_id']) {
                    $customerData = $this->getCustomerDetails($validated['customer_id'], $clientId);
                } elseif (isset($validated['file_id']) && $validated['file_id']) {
                    $customerData = $this->getCustomerDetails($validated['file_id'], $clientId);
                }
            } else {
                if (isset($validated['file_id']) && $validated['file_id']) {
                    $fileData = File::where('File_ID', $validated['file_id'])
                        ->where('Client_ID', $clientId)
                        ->first();
                }
            }

            $client = $isCompanyModule ? $companyData : $this->getClientOrCompany($clientId);
            $bankAccount = $this->getBankAccount($clientId);

            // ✅ CRITICAL FIX: Use loadTemplateForClient() instead of manual logic
            // This ensures the LATEST customized template is used
            $template = $this->loadTemplateForClient($clientId, $templateId);

            Log::info('PDF Download - Template loaded', [
                'template_id' => $template->id ?? 'none',
                'template_name' => $template->name ?? 'default',
                'has_logo' => isset($template->logo_path) && $template->logo_path ? 'yes' : 'no'
            ]);

            // Extract template data
            $templateData = [];
            if ($template && isset($template->template_data)) {
                $templateData = is_string($template->template_data)
                    ? json_decode($template->template_data, true)
                    : $template->template_data;
            }

            if (empty($templateData)) {
                $templateData = $this->getDefaultStyles();
            }

            // Extract styling
            $primaryColor = $templateData['primaryColor'] ?? '#1e3a8a';
            $secondaryColor = $templateData['secondaryColor'] ?? '#16a34a';
            $titleFont = $templateData['titleFont'] ?? 'Arial';
            $bodyFont = $templateData['bodyFont'] ?? 'Arial';
            $fontSize = $templateData['fontSize'] ?? '11px';
            $tableHeaderColor = $templateData['tableHeaderColor'] ?? '#b3d9ff';
            $tableHeaderTextColor = $templateData['tableHeaderTextColor'] ?? '#000000';
            $tableBorderColor = $templateData['tableBorderColor'] ?? '#6c757d';
            $tableRowHeight = $templateData['tableRowHeight'] ?? '12px';
            $tableFontSize = $templateData['tableFontSize'] ?? '11px';

            // Handle logo
            $logoPath = null;
            $logoFullPath = null;
            if ($template && isset($template->logo_path) && $template->logo_path) {
                $logoPath = $template->logo_path;
                $logoFullPath = storage_path('app/public/' . $logoPath);

            }

            $invoiceNotes = $this->parseInvoiceNotes($validated['invoice_notes'] ?? null);


            // ✅ Pass entire templateData array for positions
            $html = view('admin.day_book.pdf_template', [
                'validated' => $validated,
                'client' => $client,
                'fileData' => $fileData,
                'bankAccount' => $bankAccount,
                'template' => $template,
                'templateData' => $templateData,
                'primaryColor' => $primaryColor,
                'secondaryColor' => $secondaryColor,
                'titleFont' => $titleFont,
                'bodyFont' => $bodyFont,
                'fontSize' => $fontSize,
                'logoPath' => $logoPath,
                'logoFullPath' => $logoFullPath,
                'tableHeaderColor' => $tableHeaderColor,
                'tableHeaderTextColor' => $tableHeaderTextColor,
                'tableBorderColor' => $tableBorderColor,
                'tableRowHeight' => $tableRowHeight,
                'tableFontSize' => $tableFontSize,
                'invoiceNotes' => $invoiceNotes,
                'customerData' => $customerData ?? null,  // ✅ Add this
                'companyData' => $companyData ?? null,
                'isCompanyModule' => $isCompanyModule
            ])->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('dpi', 96);

            $filename = 'invoice_' . ($validated['invoice_no'] ?? 'draft') . '.pdf';

            Log::info('PDF generated successfully', [
                'filename' => $filename,
                'template_used' => $template->name ?? 'default'
            ]);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
    /**
     * List all saved invoices for template customization
     */

    /**
     * ✅ Show template gallery
     */
    public function gallery()
    {
        $clientId = $this->getClientId();

        $templates = InvoiceTemplate::where('client_id', $clientId)
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $isCompanyModule = session('current_company_id') !== null;
        $viewPath = $isCompanyModule
            ? 'company-module.invoices.templates.gallery'
            : 'admin.day_book.templates_gallery';

        return view($viewPath, compact('templates'));
    }

    /**
     * ✅ Create new template with dummy data
     */
    public function createTemplate(Request $request)
    {
        $clientId = $this->getClientId();

        // Generate dummy invoice data
        $dummyData = $this->getDummyInvoiceData();

        // Create temporary draft for customization
        $draft = DraftInvoice::create([
            'draft_key' => DraftInvoice::generateKey(),
            'client_id' => $clientId,
            'status' => 'preview',
            'invoice_data' => $dummyData,
            'expires_at' => now()->addHours(24)
        ]);

        // Redirect to customize mode
        $isCompanyModule = session('current_company_id') !== null;

        if ($isCompanyModule) {
            return redirect()->route('company.invoices.templates.preview.customize', [
                'draft' => $draft->draft_key,
                'template_id' => $request->get('template_id')
            ]);
        } else {
            return redirect()->route('invoicetemplates.preview.customize', [
                'draft' => $draft->draft_key,
                'template_id' => $request->get('template_id')
            ]);
        }
    }

    /**
     * ✅ Generate dummy invoice data
     */
    private function getDummyInvoiceData()
    {
        return [
            'Transaction_Date' => now()->format('Y-m-d'),
            'Inv_Due_Date' => now()->addDays(30)->format('Y-m-d'),
            'invoice_no' => 'SIN000001',
            'invoice_ref' => 'SAMPLE-REF-001',
            'customer_id' => 1,
            'items' => [
                [
                    'item_code' => 'PROD-001',
                    'description' => 'Sample Product 1',
                    'ledger_id' => 1,
                    'account_ref' => 'Sales',
                    'unit_amount' => 100.00,
                    'vat_rate' => 20,
                    'vat_amount' => 20.00,
                    'net_amount' => 120.00,
                    'vat_form_label_id' => 1,
                    'product_image' => null
                ],
                [
                    'item_code' => 'PROD-002',
                    'description' => 'Sample Product 2',
                    'ledger_id' => 1,
                    'account_ref' => 'Sales',
                    'unit_amount' => 200.00,
                    'vat_rate' => 20,
                    'vat_amount' => 40.00,
                    'net_amount' => 240.00,
                    'vat_form_label_id' => 1,
                    'product_image' => null
                ]
            ],
            'invoice_net_amount' => 300.00,
            'invoice_vat_amount' => 60.00,
            'invoice_total_amount' => 360.00
        ];
    }


    /**
     * Parse invoice notes from JSON to structured array
     */
    private function parseInvoiceNotes($notesJson)
    {
        if (!$notesJson) return [];

        $notesData = json_decode($notesJson, true);
        if (!is_array($notesData)) return [];

        $parsed = [];
        foreach ($notesData as $note) {
            $content = $note['content'] ?? '';

            // Extract tables
            preg_match_all('/<table>(.*?)<\/table>/s', $content, $tables);
            $hasTable = !empty($tables[0]);

            // ✅ FIX: Remove tables from content BEFORE extracting text
            $textContent = $content;
            if ($hasTable) {
                // Remove all table HTML before getting plain text
                $textContent = preg_replace('/<table>.*?<\/table>/s', '', $content);
            }

            // Get plain text (remove remaining HTML tags)
            $text = strip_tags($textContent);
            $text = trim(preg_replace('/\s+/', ' ', $text));

            $parsed[] = [
                'id' => $note['id'],
                'has_table' => $hasTable,
                'table_html' => $tables[0][0] ?? null,  // ✅ FIX: Use $tables[0][0] to get full <table> tag
                'text' => $text,
                'timestamp' => $note['timestamp'] ?? null
            ];
        }

        return $parsed;
    }
}
