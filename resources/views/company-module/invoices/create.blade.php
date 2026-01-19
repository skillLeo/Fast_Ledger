@extends('admin.layout.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/transaction-form.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/sortable-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/product-dropdown.css') }}">
@endpush

@section('content')
    @php
        // ✅ Use default values for non-edit mode - will be replaced by AJAX
        $currentPrefix = 'SIN';
        $minSuffixNum = 1;
        $suffixLen = 6;

        // ✅ Override with actual invoice number in edit mode
        if (isset($editData) && $editData) {
            preg_match('/^([A-Z]+)(\d{6})$/', $editData['invoice_no'], $m);
            if ($m) {
                $currentPrefix = $m[1];
                $minSuffixNum = (int) $m[2];
            }
        }
    @endphp

    {{-- ✅ Add context meta tag for data-loader.js --}}
    <meta name="app-context" content="company_module">

    {{-- ✅ Add company ID meta tag for code-manager.js --}}
    <meta name="company-id" content="{{ session('current_company_id') }}">

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        {{-- Header --}}
                        @include('admin.day_book._partials._header', [
                            'type' => $type,
                            'paymentType' => $paymentType,
                        ])

                        {{-- Payment Type Selection --}}
                        <div class="payment-type-selection d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2 mb-2">

                                @php
                                    // ✅ Determine which buttons to show based on payment type
                                    $isPurchaseType = in_array($paymentType, ['purchase', 'purchase_credit']);
                                @endphp

                                @if ($isPurchaseType)
                                    {{-- ✅ Show ONLY Purchase Buttons --}}
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'purchase' ? 'active' : '' }}"
                                        data-payment-type="purchase">
                                        Purchase Invoice
                                    </button>
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'purchase_credit' ? 'active' : '' }}"
                                        data-payment-type="purchase_credit">
                                        Purchase Credit
                                    </button>
                                @else
                                    {{-- ✅ Show ONLY Sales Buttons --}}
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'sales_invoice' ? 'active' : '' }}"
                                        data-payment-type="sales_invoice">
                                        Sales Invoice
                                    </button>
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'sales_credit' ? 'active' : '' }}"
                                        data-payment-type="sales_credit">
                                        Sales Credit
                                    </button>
                                @endif

                            </div>

                            {{-- Save Dropdown --}}
                            <div class="d-flex gap-2 mb-3">
                                <div class="dropdown sales-invoice-only" id="saveDropdownContainer">
                                    <button class="btn teal-custom dropdown-toggle" type="button" id="saveDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 100px;">
                                        <i class="fas fa-save me-1"></i>Save
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="saveDropdown" style="min-width: 160px;">
                                        <li>
                                            <a class="dropdown-item py-2 px-3 fs-6" href="#" id="justSave">
                                                <i class="fas fa-save me-2 text-center" style="width: 16px;"></i>Save
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 px-3 fs-6" href="#"
                                                id="saveAndEmailDropdown">
                                                <i class="fas fa-envelope me-2 text-center" style="width: 16px;"></i>Save &
                                                Email
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 px-3 fs-6" href="#"
                                                id="saveAndAddNewDropdown">
                                                <i class="fas fa-plus me-2 text-center" style="width: 16px;"></i>Save & Add
                                                New
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 px-3 fs-6" href="#" id="saveAsDraft">
                                                <i class="fas fa-file me-2 text-center" style="width: 16px;"></i>Save As
                                                Draft
                                            </a>
                                        </li>
                                    </ul>

                                    <button type="button" class="btn teal-custom" id="previewButton">
                                        <i class="fas fa-eye me-1"></i>Preview
                                    </button>
                                </div>

                                <a href="{{ route('company.invoices.index') }}" class="btn teal-custom">
                                    <i class="fas fa-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body">

                            {{-- Transaction Code Input --}}
                            @include('admin.day_book._partials._code-input', [
                                'currentPrefix' => $currentPrefix,
                                'minSuffixNum' => $minSuffixNum,
                                'suffixLen' => $suffixLen,
                                'autoCode' =>
                                    $currentPrefix . str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT),
                                'type' => $type,
                                'paymentType' => $paymentType,
                            ])

                            {{-- Invoice Form --}}
                            <div class="sales-invoice-form" id="salesInvoiceForm">
                                <form method="POST" action="{{ route('company.invoices.store') }}"
                                    id="salesInvoiceTransactionForm">
                                    @csrf

                                    {{-- Hidden Fields --}}
                                    <input type="hidden" name="current_payment_type" id="salesInvoicePaymentType"
                                        value="{{ $paymentType }}">
                                    <input type="hidden" name="account_type" value="office">
                                    <input type="hidden" name="Amount" id="hiddenMainAmount" value="0">
                                    <input type="hidden" name="invoice_documents" id="invoiceDocuments_data"
                                        value="[]">

                                    @if (isset($editData) && $editData)
                                        <input type="hidden" name="edit_invoice_id"
                                            value="{{ $editData['invoice']->id }}">
                                    @endif

                                    <div class="background-light">

                                        {{-- Form Fields (Customer, Dates, etc) --}}
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="row">

                                                    {{-- Customer Field --}}
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label fw-bold"
                                                                id="customerFieldLabel">Customer</label>
                                                            <select name="customer_id" id="customerDropdown"
                                                                class="form-select custom-border p-1 rounded-0 @error('customer_id') is-invalid @enderror">
                                                                <option value="">Select Customer</option>
                                                            </select>
                                                            @error('customer_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- Invoice Date --}}
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold"
                                                                id="invoiceDateLabel">Invoice Date</label>
                                                            <input type="date" name="Transaction_Date"
                                                                value="{{ date('Y-m-d') }}"
                                                                class="form-control custom-border rounded-0 @error('Transaction_Date') is-invalid @enderror">
                                                            @error('Transaction_Date')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- Due Date --}}
                                                    {{-- Due Date --}}
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Due Date</label>
                                                            <input type="date" name="Inv_Due_Date"
                                                                class="form-control custom-border rounded-0 @error('Inv_Due_Date') is-invalid @enderror">

                                                            {{-- ✅ ADD THIS: Server-side validation error --}}
                                                            @error('Inv_Due_Date')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                            {{-- ✅ ADD THIS: Client-side validation error --}}
                                                            <div class="invalid-feedback" id="dueDateError"
                                                                style="display: none;"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Invoice Number --}}
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold" id="invoiceNoLabel">Invoice
                                                                No</label>
                                                            <div class="input-group">
                                                                <span
                                                                    class="input-group-text bg-light fw-bold custom-border p-1 rounded-0"
                                                                    id="invoicePrefix">{{ $currentPrefix }}</span>
                                                                <input type="text" id="invoiceSuffix"
                                                                    class="form-control custom-border rounded-0 p-1"
                                                                    value="{{ str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT) }}">
                                                            </div>
                                                            <div id="invoiceCodeMsg" class="mt-1"></div>
                                                            <input type="hidden" name="Transaction_Code"
                                                                id="invoiceTransactionCode"
                                                                value="{{ $currentPrefix . str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT) }}">
                                                            <input type="hidden" name="invoice_no" id="invoiceNoHidden"
                                                                value="{{ $currentPrefix . str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT) }}">
                                                        </div>
                                                    </div>

                                                    {{-- Invoice Reference --}}
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold" id="invoiceRefLabel">Invoice
                                                                Ref</label>
                                                            <input type="text" name="invoice_ref"
                                                                value="{{ old('invoice_ref') }}"
                                                                class="form-control custom-border rounded-0 @error('invoice_ref') is-invalid @enderror"
                                                                placeholder="Invoice Reference">
                                                            @error('invoice_ref')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        {{-- Invoice Items Table --}}
                                        @include('admin.day_book._partials._invoice-items-table')

                                        {{-- Notes Editor --}}
                                        @include('admin.day_book._partials._notes-editor')

                                        {{-- Hidden Summary Fields --}}
                                        <input type="hidden" name="invoice_net_amount" id="hiddenInvoiceNetAmount"
                                            value="0">
                                        <input type="hidden" name="invoice_vat_amount" id="hiddenInvoiceVATAmount"
                                            value="0">
                                        <input type="hidden" name="invoice_total_amount" id="hiddenInvoiceTotalAmount"
                                            value="0">

                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('admin.day_book._modals._chart-of-accounts')
    @include('admin.day_book._modals._product-modal')
    @include('admin.day_book._modals._invoice-file-upload-modal')
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    {{-- JavaScript Files --}}
    <script src="{{ asset('admin/js/transactions/code-manager.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/vat-manager.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/data-loader.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/invoice-handler.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/form-manager.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/coa-modal.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/product-modal.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/file-upload-handler.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/notes-editor.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/form-submission-handler.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // ✅ DEBUG INFO
        console.log('==========================================');
        console.log('🏢 COMPANY MODULE INVOICE FORM');
        console.log('==========================================');
        console.log('📋 Payment Type:', '{{ $paymentType }}');
        console.log('📝 Edit Mode:', {{ isset($editData) && $editData ? 'true' : 'false' }});

        @if (isset($editData) && $editData)
            console.log('✅ Edit Data Present:', {
                invoice_id: {{ $editData['invoice']->id }},
                invoice_no: '{{ $editData['invoice_no'] }}',
                customer: '{{ $editData['customer'] }}',
                items_count: {{ count($editData['items']) }}
            });
        @endif
        console.log('==========================================');

        // ✅ Store company ID in sessionStorage
        @if (session('current_company_id'))
            sessionStorage.setItem('current_company_id', '{{ session('current_company_id') }}');
        @endif

        // ✅ Generate auto code via AJAX helper function
        function generateAutoCodeAjax(paymentType) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) return Promise.reject(new Error('CSRF token not found'));

            console.log('🔄 Generating auto code for payment type:', paymentType);

            return fetch('/company/invoices/generate-auto-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_type: paymentType,
                        account_type: 'office',
                        company_id: {{ session('current_company_id') ?? 'null' }}
                    })
                })
                .then(r => {
                    if (!r.ok) {
                        throw new Error(`HTTP error! status: ${r.status}`);
                    }
                    return r.json();
                })
                .then(data => {
                    if (!data?.success || !data?.auto_code) {
                        throw new Error(data?.message || 'Failed to generate');
                    }

                    console.log('✅ Auto code generated:', data.auto_code);

                    // Apply the code to the form
                    const matches = data.auto_code.match(/^([A-Z]+)(\d{6})$/);
                    if (matches) {
                        const prefix = matches[1];
                        const suffix = matches[2];

                        const prefixEl = document.getElementById('invoicePrefix');
                        const suffixEl = document.getElementById('invoiceSuffix');

                        if (prefixEl) prefixEl.textContent = prefix;
                        if (suffixEl) suffixEl.value = suffix;

                        // Update hidden fields
                        const transCodeEl = document.getElementById('invoiceTransactionCode');
                        const invNoEl = document.getElementById('invoiceNoHidden');

                        if (transCodeEl) transCodeEl.value = data.auto_code;
                        if (invNoEl) invNoEl.value = data.auto_code;

                        console.log('✅ Form updated with new code');
                    }

                    return data;
                })
                .catch(error => {
                    console.error('❌ Auto code generation failed:', error);
                    alert('Failed to generate invoice number: ' + error.message);
                    throw error;
                });
        }

        // ✅ MAIN INITIALIZATION
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🏢 Company Module Invoice Form Initialized');

            const isEditMode = {{ isset($editData) && $editData ? 'true' : 'false' }};
            const initialPaymentType = '{{ $paymentType }}';

            // ✅ ONLY fetch auto code if NOT in edit mode
            if (!isEditMode) {
                console.log('🔄 Fetching initial auto code for:', initialPaymentType);
                generateAutoCodeAjax(initialPaymentType)
                    .then(() => {
                        console.log('✅ Initial auto code loaded successfully');
                    })
                    .catch(err => {
                        console.error('❌ Failed to load initial auto code:', err);
                    });
            } else {
                console.log('📝 EDIT MODE: Skipping auto code generation');
            }

            // ✅ Initialize all handlers
            window.invoiceHandler.initializeElements();
            window.coaModal.initialize();
            window.productModal.initialize();

            if (typeof FileUploadHandler !== 'undefined') {
                window.fileUploadHandler = new FileUploadHandler();
                window.fileUploadHandler.initialize();
                console.log('✅ File upload handler initialized');
            }

            if (typeof CollapsibleNotesEditor !== 'undefined') {
                window.notesEditor = new CollapsibleNotesEditor();
                window.notesEditor.initialize();
            }

            if (typeof FormSubmissionHandler !== 'undefined') {
                window.formSubmissionHandler = new FormSubmissionHandler();
                window.formSubmissionHandler.initialize();
            }

            // ✅ Get DOM elements
            const paymentTypeButtons = document.querySelectorAll('.btn-simple');
            const addItemBtn = document.getElementById('addItemBtn');
            const addFileBtn = document.getElementById('addFileBtn');

            // ✅ FIXED: Payment type buttons with supplier/customer switching
            paymentTypeButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    const paymentType = this.dataset.paymentType;

                    paymentTypeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const salesInvoicePaymentType = document.getElementById(
                        'salesInvoicePaymentType');
                    if (salesInvoicePaymentType) {
                        salesInvoicePaymentType.value = paymentType;
                    }

                    // Update labels
                    window.formManager.updateFormLabels(paymentType);

                    // Update VAT rates
                    window.vatManager.updateFormVatRates(paymentType);

                    // ✅ Only refresh auto code if NOT in edit mode
                    if (!isEditMode) {
                        console.log('🔄 Payment type changed to:', paymentType);
                        generateAutoCodeAjax(paymentType);
                    }

                    // ✅ CRITICAL FIX: Use the new reload method
                    if (window.dataLoader) {
                        console.log('🔄 Reloading dropdown for payment type:', paymentType);
                        await window.dataLoader.reloadDropdownForPaymentType(paymentType);
                    }
                });
            });

            // Add Row button
            if (addItemBtn) {
                addItemBtn.addEventListener('click', function() {
                    console.log('➕ Add Row clicked');
                    window.invoiceHandler.addNewInvoiceRow();
                });
                console.log('✅ Add Row button listener attached');
            }

            // Add File button
            if (addFileBtn) {
                addFileBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('📎 Add File clicked');

                    if (window.fileUploadHandler) {
                        window.fileUploadHandler.openUploadModal();
                    } else {
                        console.error('❌ File upload handler not initialized');
                        alert('File upload feature is not available. Please refresh the page.');
                    }
                });
                console.log('✅ Add File button listener attached');
            }

            // Save button handlers
            const justSaveBtn = document.getElementById('justSave');
            const saveAndEmailBtn = document.getElementById('saveAndEmailDropdown');
            const saveAndAddNewBtn = document.getElementById('saveAndAddNewDropdown');
            const saveAsDraftBtn = document.getElementById('saveAsDraft');
            const previewBtn = document.getElementById('previewButton');

            if (justSaveBtn) {
                justSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.invoiceHandler.validateInvoiceForm()) return false;
                    window.formSubmissionHandler.submitSalesInvoiceForm('save');
                });
            }

            if (saveAndEmailBtn) {
                saveAndEmailBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.invoiceHandler.validateInvoiceForm()) return false;
                    window.formSubmissionHandler.submitSalesInvoiceForm('save_and_email');
                });
            }

            if (saveAndAddNewBtn) {
                saveAndAddNewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.invoiceHandler.validateInvoiceForm()) return false;
                    window.formSubmissionHandler.submitSalesInvoiceForm('save_and_add_new');
                });
            }

            if (saveAsDraftBtn) {
                saveAsDraftBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.invoiceHandler.validateInvoiceForm()) return false;
                    window.formSubmissionHandler.submitSalesInvoiceForm('save_as_draft');
                });
                console.log('✅ Save as Draft button listener attached');
            }

            if (previewBtn) {
                previewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.invoiceHandler.validateInvoiceForm()) return false;
                    window.formSubmissionHandler.submitSalesInvoiceForm('preview');
                });
            }

            // Initialize sortable
            setTimeout(() => {
                window.invoiceHandler.initializeSortable();
                console.log('✅ Sortable initialized');
            }, 300);

            window.formManager.showSalesInvoiceForm();

            // ✅ CRITICAL: Update labels and load correct dropdown data for initial payment type
            console.log('🏷️ Setting initial labels for payment type:', initialPaymentType);
            window.formManager.updateFormLabels(initialPaymentType);

            // ✅ Load correct data (customers or suppliers) based on payment type
            window.dataLoader.loadAllData(initialPaymentType).then(() => {
                console.log('✅ Initial data loaded for payment type:', initialPaymentType);

                // ✅ Populate correct dropdown (data already loaded by loadAllData)
                const isPurchaseType = ['purchase', 'purchase_credit'].includes(initialPaymentType);
                if (isPurchaseType) {
                    console.log('📦 Populating suppliers dropdown on initial load');
                    window.dataLoader.populateSuppliersDropdown(null);
                } else {
                    console.log('👥 Populating customers dropdown on initial load');
                    window.dataLoader.populateCustomerDropdown(null);
                }
            });

            @if (isset($editData) && $editData)
                console.log('📝 EDIT MODE DETECTED - Starting immediate population...');

                // ✅ FIX: Don't wait for event - check if dataLoader exists immediately
                (async function populateEditData() {
                    const editData = @json($editData);
                    console.log('📦 Edit Data Received:', {
                        invoice_id: editData.invoice?.id,
                        customer: editData.customer,
                        items_count: editData.items?.length
                    });

                    // ✅ Wait for dataLoader to exist (not event)
                    let attempts = 0;
                    while (!window.dataLoader && attempts < 100) {
                        await new Promise(resolve => setTimeout(resolve, 50));
                        attempts++;
                    }

                    if (!window.dataLoader) {
                        console.error('❌ DataLoader not found after 5 seconds');
                        return;
                    }

                    console.log('✅ DataLoader found, waiting for data to load...');

                    // ✅ Wait for data to be loaded
                    await window.dataLoader.waitForDataLoad();
                    console.log('✅ All data loaded');

                    // ✅ Wait for DOM to be stable
                    await new Promise(resolve => setTimeout(resolve, 800));

                    try {
                        // ✅ 1. Populate Supplier
                        const customerDropdown = document.getElementById('customerDropdown');
                        if (customerDropdown && editData.customer) {
                            console.log('🔍 Setting supplier to:', editData.customer);

                            // Verify option exists
                            const hasOption = Array.from(customerDropdown.options).some(
                                opt => opt.value == editData.customer
                            );

                            if (hasOption) {
                                customerDropdown.value = editData.customer;
                                customerDropdown.dispatchEvent(new Event('change'));
                                console.log('✅ Supplier set to:', editData.customer);
                            } else {
                                console.error('❌ Supplier option not found:', editData.customer);
                                console.log('📋 Available options:',
                                    Array.from(customerDropdown.options).map(opt => ({
                                        value: opt.value,
                                        text: opt.text
                                    }))
                                );
                            }
                        }

                        // ✅ 2. Populate Invoice Date
                        const invoiceDateInput = document.querySelector('input[name="Transaction_Date"]');
                        if (invoiceDateInput && editData.invoice_date) {
                            invoiceDateInput.value = editData.invoice_date;
                            console.log('✅ Invoice date set');
                        }

                        // ✅ 3. Populate Due Date
                        const dueDateInput = document.querySelector('input[name="Inv_Due_Date"]');
                        if (dueDateInput && editData.due_date) {
                            dueDateInput.value = editData.due_date;
                            console.log('✅ Due date set');
                        }

                        // ✅ 4. Populate Invoice Ref
                        const invoiceRefInput = document.querySelector('input[name="invoice_ref"]');
                        if (invoiceRefInput && editData.invoice_ref) {
                            invoiceRefInput.value = editData.invoice_ref;
                            console.log('✅ Invoice ref set');
                        }

                        // ✅ 5. Populate Notes
                        if (editData.notes) {
                            const notesHidden = document.getElementById('invoiceNotesHidden');
                            if (notesHidden) {
                                notesHidden.value = editData.notes;
                                console.log('✅ Notes set');
                            }
                        }

                        // ✅ 6. POPULATE ITEMS - COMPLETE CODE
                        if (editData.items && Array.isArray(editData.items) && editData.items.length > 0) {
                            console.log('\n📦 Starting to populate', editData.items.length, 'items...');

                            // Clear table first
                            const invoiceItemsTable = document.getElementById('invoiceItemsTable');
                            if (invoiceItemsTable) {
                                invoiceItemsTable.innerHTML = '';
                                console.log('✅ Table cleared');
                            }

                            // Load items sequentially
                            for (let i = 0; i < editData.items.length; i++) {
                                const item = editData.items[i];
                                console.log(`\n📦 [Item ${i}] Loading:`, {
                                    item_code: item.item_code,
                                    description: item.description?.substring(0, 30),
                                    ledger_id: item.ledger_id,
                                    qty: item.qty,
                                    unit_amount: item.unit_amount
                                });

                                // Create row
                                window.invoiceHandler.addNewInvoiceRow();
                                await new Promise(resolve => setTimeout(resolve, 300));

                                // Get the row we just created
                                const rows = document.querySelectorAll('#invoiceItemsTable tr');
                                const row = rows[rows.length - 1];

                                if (!row) {
                                    console.error(`❌ [Item ${i}] Row not found`);
                                    continue;
                                }

                                // Mark as auto-filling
                                row.dataset.isAutoFilling = 'true';

                                try {
                                    // Fill basic fields
                                    const itemCodeInput = row.querySelector('.item-code-input');
                                    const descInput = row.querySelector('input[name*="[description]"]');
                                    const qtyInput = row.querySelector('.qty-input');
                                    const unitAmountInput = row.querySelector('.unit-amount');

                                    if (itemCodeInput) itemCodeInput.value = item.item_code || '';
                                    if (descInput) descInput.value = item.description || '';
                                    if (qtyInput) qtyInput.value = item.qty || '1';
                                    if (unitAmountInput) unitAmountInput.value = parseFloat(item
                                        .unit_amount || 0).toFixed(2);

                                    console.log(`✅ [Item ${i}] Basic fields set`);

                                    // Set ledger dropdown
                                    const ledgerSelect = row.querySelector('.ledger-select');
                                    if (ledgerSelect && item.ledger_id) {
                                        ledgerSelect.value = item.ledger_id;
                                        ledgerSelect.dispatchEvent(new Event('change'));
                                        console.log(`✅ [Item ${i}] Ledger set:`, item.ledger_id);

                                        // Wait for account dropdown
                                        const accountSelect = row.querySelector('.account-select');
                                        if (accountSelect && item.account_ref) {
                                            let attempts = 0;
                                            while (accountSelect.options.length <= 1 && attempts < 50) {
                                                await new Promise(resolve => setTimeout(resolve, 100));
                                                attempts++;
                                            }

                                            // Find and set account
                                            for (let j = 0; j < accountSelect.options.length; j++) {
                                                const optionText = accountSelect.options[j].text.split('(')[
                                                    0].trim();
                                                if (optionText === item.account_ref.trim()) {
                                                    accountSelect.selectedIndex = j;
                                                    accountSelect.dispatchEvent(new Event('change'));
                                                    console.log(`✅ [Item ${i}] Account set:`, item
                                                        .account_ref);
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    // Set VAT
                                    const vatRateSelect = row.querySelector('.vat-rate');
                                    const vatIdField = row.querySelector('.item-vat-id');

                                    if (vatRateSelect && item.vat_form_label_id) {
                                        const currentPaymentType = '{{ $paymentType }}';

                                        // Load VAT types
                                        await new Promise((resolve) => {
                                            window.vatManager.loadVatTypesByForm(currentPaymentType,
                                                (vatTypes) => {
                                                    vatRateSelect.innerHTML = window.vatManager
                                                        .createVatDropdownOptions(vatTypes);
                                                    setTimeout(resolve, 150);
                                                });
                                        });

                                        // Poll until options render
                                        let pollAttempts = 0;
                                        while (vatRateSelect.options.length <= 1 && pollAttempts < 50) {
                                            await new Promise(resolve => setTimeout(resolve, 100));
                                            pollAttempts++;
                                        }

                                        if (vatRateSelect.options.length > 1) {
                                            // Try to match by vat_form_label_id
                                            let matchingOption = Array.from(vatRateSelect.options).find(
                                                opt =>
                                                opt.dataset.vatId == item.vat_form_label_id
                                            );

                                            // Fallback to rate matching
                                            if (!matchingOption && item.vat_rate !== undefined) {
                                                matchingOption = Array.from(vatRateSelect.options).find(
                                                    opt =>
                                                    Math.abs(parseFloat(opt.value) - parseFloat(item
                                                        .vat_rate)) < 0.01
                                                );
                                            }

                                            if (matchingOption) {
                                                vatRateSelect.value = matchingOption.value;
                                                if (vatIdField) {
                                                    vatIdField.value = matchingOption.dataset.vatId || item
                                                        .vat_form_label_id;
                                                }
                                                vatRateSelect.dispatchEvent(new Event('change'));
                                                console.log(`✅ [Item ${i}] VAT set:`, matchingOption.value +
                                                    '%');
                                            }
                                        }
                                    }

                                    // Set product image
                                    if (item.product_image) {
                                        const imagePreviewContainer = row.querySelector(
                                            '.item-image-preview');
                                        const imageUrlField = row.querySelector('.item-image-url');

                                        if (imagePreviewContainer) {
                                            imagePreviewContainer.innerHTML = `
                                    <img src="${item.product_image}" 
                                        alt="Product" 
                                        class="product-thumbnail"
                                        title="Click to view full size">
                                `;
                                        }

                                        if (imageUrlField) {
                                            imageUrlField.value = item.product_image;
                                        }
                                        console.log(`✅ [Item ${i}] Image set`);
                                    }

                                    console.log(`✅ [Item ${i}] COMPLETE`);

                                } catch (error) {
                                    console.error(`❌ [Item ${i}] Error:`, error);
                                } finally {
                                    // Mark auto-filling complete
                                    row.dataset.isAutoFilling = 'false';

                                    // Trigger calculation
                                    await new Promise(resolve => setTimeout(resolve, 200));
                                    const unitAmountInput = row.querySelector('.unit-amount');
                                    if (unitAmountInput) {
                                        unitAmountInput.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }));
                                    }
                                }

                                // Wait between items
                                await new Promise(resolve => setTimeout(resolve, 400));
                            }

                            // Final summary update
                            await new Promise(resolve => setTimeout(resolve, 500));
                            if (window.invoiceHandler) {
                                window.invoiceHandler.updateInvoiceSummary();
                                console.log('\n✅ ALL', editData.items.length, 'ITEMS LOADED SUCCESSFULLY');
                            }
                        }

                    } catch (error) {
                        console.error('❌ Error populating edit data:', error);
                    }
                })();
            @endif

            console.log('✅ Company Module Invoice Form Ready');
        });
        /**
         * ========================================================================
         * COMPANY MODULE INVOICE FORM - FORCE DATA COLLECTION
         * ========================================================================
         * This ensures ALL invoice data is collected properly in Company Module
         */
        (function() {
            console.log('🏢 Company Module Invoice Data Collector Initialized');

            /**
             * ✅ Override updateInvoiceSummary to FORCE update hidden fields
             */
            const originalUpdateSummary = window.invoiceHandler.updateInvoiceSummary.bind(window.invoiceHandler);

            window.invoiceHandler.updateInvoiceSummary = function() {
                console.log('🔄 Company Module: Updating invoice summary...');

                // Call original function
                originalUpdateSummary();

                // ✅ FORCE verify all hidden fields are populated
                setTimeout(() => {
                    const netAmount = document.getElementById('hiddenInvoiceNetAmount');
                    const vatAmount = document.getElementById('hiddenInvoiceVATAmount');
                    const totalAmount = document.getElementById('hiddenInvoiceTotalAmount');

                    console.log('📊 Company Module Summary Verification:', {
                        net: netAmount?.value || 'MISSING',
                        vat: vatAmount?.value || 'MISSING',
                        total: totalAmount?.value || 'MISSING'
                    });

                    // ✅ If values are still "0", recalculate manually
                    if (netAmount && parseFloat(netAmount.value) === 0) {
                        let totalNet = 0;
                        let totalVAT = 0;
                        let totalGross = 0;

                        document.querySelectorAll('#invoiceItemsTable tr').forEach(row => {
                            const qty = parseFloat(row.querySelector('.qty-input')?.value || 1);
                            const unitAmount = parseFloat(row.querySelector('.unit-amount')
                                ?.value || 0);
                            const vatAmt = parseFloat(row.querySelector('.vat-amount')?.value || 0);
                            const netAmt = parseFloat(row.querySelector('.net-amount')?.value || 0);

                            const lineTotal = unitAmount * qty;
                            totalNet += lineTotal;
                            totalVAT += vatAmt;
                            totalGross += netAmt;
                        });

                        console.log('🔧 Company Module: Recalculated totals:', {
                            net: totalNet.toFixed(2),
                            vat: totalVAT.toFixed(2),
                            total: totalGross.toFixed(2)
                        });

                        if (netAmount) netAmount.value = totalNet.toFixed(2);
                        if (vatAmount) vatAmount.value = totalVAT.toFixed(2);
                        if (totalAmount) totalAmount.value = totalGross.toFixed(2);
                    }
                }, 100);
            };

            /**
             * ✅ Add pre-submit validation and data collection
             */
            const originalSubmit = window.formSubmissionHandler.submitSalesInvoiceForm.bind(window
                .formSubmissionHandler);

            window.formSubmissionHandler.submitSalesInvoiceForm = function(action) {
                console.log('📤 Company Module: Pre-submit validation and data collection...');

                // ✅ FORCE update summary before submission
                window.invoiceHandler.updateInvoiceSummary();

                // ✅ Verify invoice_no is set
                const invoiceNoHidden = document.getElementById('invoiceNoHidden');
                const transactionCode = document.getElementById('invoiceTransactionCode');

                if (invoiceNoHidden && !invoiceNoHidden.value) {
                    invoiceNoHidden.value = transactionCode?.value || 'SIN000001';
                    console.log('✅ Company Module: invoice_no set to:', invoiceNoHidden.value);
                }

                // ✅ Collect and verify all item data
                const rows = document.querySelectorAll('#invoiceItemsTable tr');
                const itemsData = [];

                rows.forEach((row, index) => {
                    const itemCode = row.querySelector('.item-code-input')?.value || '';
                    const description = row.querySelector('input[name*="[description]"]')?.value || '';
                    const ledgerId = row.querySelector('.ledger-select')?.value || '';
                    const accountRef = row.querySelector('.account-select')?.value || '';
                    const qty = row.querySelector('.qty-input')?.value || '1';
                    const unitAmount = row.querySelector('.unit-amount')?.value || '0';
                    const vatRate = row.querySelector('.vat-rate')?.value || '0';
                    const vatAmount = row.querySelector('.vat-amount')?.value || '0';
                    const netAmount = row.querySelector('.net-amount')?.value || '0';
                    const vatId = row.querySelector('.item-vat-id')?.value || '';
                    const productImage = row.querySelector('.item-image-url')?.value || '';

                    if (description && parseFloat(unitAmount) > 0) {
                        itemsData.push({
                            index: index,
                            item_code: itemCode,
                            description: description,
                            ledger_id: ledgerId,
                            account_ref: accountRef,
                            qty: qty,
                            unit_amount: unitAmount,
                            vat_rate: vatRate,
                            vat_amount: vatAmount,
                            net_amount: netAmount,
                            vat_form_label_id: vatId,
                            product_image: productImage
                        });
                    }
                });

                console.log('📦 Company Module: Collected items:', itemsData.length);
                console.log('📋 Company Module: Item details:', itemsData);

                if (itemsData.length === 0) {
                    alert('Please add at least one item to the invoice');
                    return false;
                }

                // ✅ Verify all critical fields
                const criticalFields = {
                    'Transaction_Date': document.querySelector('input[name="Transaction_Date"]')?.value,
                    'customer_id': document.getElementById('customerDropdown')?.value,
                    'Transaction_Code': document.getElementById('invoiceTransactionCode')?.value,
                    'invoice_no': document.getElementById('invoiceNoHidden')?.value,
                    'invoice_ref': document.querySelector('input[name="invoice_ref"]')?.value,
                    'Inv_Due_Date': document.querySelector('input[name="Inv_Due_Date"]')?.value,
                    'invoice_net_amount': document.getElementById('hiddenInvoiceNetAmount')?.value,
                    'invoice_vat_amount': document.getElementById('hiddenInvoiceVATAmount')?.value,
                    'invoice_total_amount': document.getElementById('hiddenInvoiceTotalAmount')?.value
                };

                console.log('✅ Company Module: Critical fields:', criticalFields);

                // Check for missing critical data
                const missingFields = [];
                if (!criticalFields.Transaction_Date) missingFields.push('Transaction Date');
                if (!criticalFields.customer_id) missingFields.push('Customer');
                if (!criticalFields.invoice_net_amount || parseFloat(criticalFields.invoice_net_amount) === 0) {
                    missingFields.push('Invoice Total (calculation may have failed)');
                }

                if (missingFields.length > 0) {
                    alert('Missing required fields: ' + missingFields.join(', '));
                    return false;
                }

                // ✅ Proceed with submission
                console.log('✅ Company Module: All data verified, proceeding with submission');
                return originalSubmit.call(this, action);
            };

            console.log('✅ Company Module Invoice Data Collector Ready');
        })();
    </script>

    <style>
        .custom-border {
            border: 1px solid #000 !important;
        }
    </style>
@endsection
