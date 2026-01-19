@extends('admin.layout.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/transaction-form.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/sortable-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/product-dropdown.css') }}">
@endpush

@section('content')
    @php
        // figure out prefix and starting numeric min from the current autoCode
        preg_match('/^([A-Z]+)(\d{6})$/', $autoCode, $m);
        $currentPrefix = $m[1] ?? 'PAY';
        $minSuffixNum = isset($m[2]) ? intval($m[2]) : 1;
        $suffixLen = 6;
    @endphp

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        {{-- Header Section --}}
                        @include('admin.day_book._partials._header', [
                            'type' => $type,
                            'paymentType' => $paymentType,
                        ])

                        <!-- Payment Type Selection -->
                        <div class="payment-type-selection d-flex justify-content-between align-items-center">


                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @if ($paymentType === 'journal')
                                    <button type="button" class="btn-simple active" data-payment-type="journal">
                                        {{-- Journal --}}
                                    </button>
                                @elseif($paymentType === 'payment')
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'payment' ? 'active' : '' }}"
                                        data-payment-type="payment">
                                        Payment
                                    </button>
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'receipt' ? 'active' : '' }}"
                                        data-payment-type="receipt">
                                        Receipt
                                    </button>
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'cheque' ? 'active' : '' }}"
                                        data-payment-type="cheque">
                                        Cheque
                                    </button>

                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'inter_bank_office' ? 'active' : '' }}"
                                        data-payment-type="inter_bank_office">
                                        Inter Bank Office
                                    </button>
                                @elseif(in_array($paymentType, ['sales_invoice', 'sales_credit']))
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
                                @else
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'purchase' ? 'active' : '' }}"
                                        data-payment-type="purchase">
                                        Purchase
                                    </button>
                                    <button type="button"
                                        class="btn-simple {{ $paymentType === 'purchase_credit' ? 'active' : '' }}"
                                        data-payment-type="purchase_credit">
                                        Purchase Credit
                                    </button>
                                @endif
                            </div>

                            <div class="d-flex gap-2 mb-3">

                                {{-- Save Dropdown (Sales Invoice Only) --}}
                                <div class="dropdown sales-invoice-only" id="saveDropdownContainer" style="display: none;">
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
                                                <i class="fas fa-plus me-2 text-center" style="width: 16px;"></i>Save As
                                                Draft
                                            </a>
                                        </li>
                                    </ul>

                                    {{-- Preview Button --}}
                                    <button type="button" class="btn teal-custom" id="previewButton">
                                        <i class="fas fa-eye me-1"></i>Preview
                                    </button>
                                </div>

                                {{-- Back Button --}}
                                <a href="{{ url()->previous() }}" class="btn teal-custom">
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
                                'autoCode' => $autoCode,
                                'type' => $type,
                                'paymentType' => $paymentType,
                            ])




                            {{-- Regular Office Form (Payment/Receipt/Cheque) --}}
                            @include('admin.day_book._partials._regular-office-form', [
                                'bankAccounts' => $bankAccounts,
                                'vatTypes' => $vatTypes,
                                'autoCode' => $autoCode,
                            ])

                            {{-- Sales Invoice Form --}}
                            @include('admin.day_book._partials._invoice-form', [
                                'currentPrefix' => $currentPrefix,
                                'minSuffixNum' => $minSuffixNum,
                                'suffixLen' => $suffixLen,
                                'autoCode' => $autoCode,
                            ])

                            {{-- Include Activity Log Partial --}}
                            @include('admin.day_book._partials._activity_log')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart of Accounts Modal -->
    {{-- Modals --}}
    @include('admin.day_book._modals._chart-of-accounts')
    @include('admin.day_book._modals._custom-table')
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    {{-- Transaction Code Manager --}}
    <script src="{{ asset('admin/js/transactions/code-manager.js') }}"></script>

    {{-- VAT Manager --}}
    <script src="{{ asset('admin/js/transactions/vat-manager.js') }}"></script>

    {{-- Data Loader --}}
    <script src="{{ asset('admin/js/transactions/data-loader.js') }}"></script>

    {{-- Journal Handler --}}
    <script src="{{ asset('admin/js/transactions/journal-handler.js') }}"></script>

    {{-- Invoice Handler --}}
    <script src="{{ asset('admin/js/transactions/invoice-handler.js') }}"></script>

    {{-- Form Manager --}}
    <script src="{{ asset('admin/js/transactions/form-manager.js') }}"></script>

    {{-- Bank Manager --}}
    <script src="{{ asset('admin/js/transactions/bank-manager.js') }}"></script>

    {{-- COA Modal Manager --}}
    <script src="{{ asset('admin/js/transactions/coa-modal.js') }}"></script>

    <script src="{{ asset('admin/js/transactions/product-modal.js') }}"></script>
    {{-- File Upload Handler --}}
    <script src="{{ asset('admin/js/transactions/file-upload-handler.js') }}"></script>

    {{-- Notes Editor --}}
    <script src="{{ asset('admin/js/transactions/notes-editor.js') }}"></script>

    {{-- Table Tooltip Handler --}}
    <script src="{{ asset('admin/js/transactions/table-tooltip-handler.js') }}"></script>

    {{-- Form Submission Handler --}}
    <script src="{{ asset('admin/js/transactions/form-submission-handler.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // FIXED: Single declaration of sortable variables (removed duplicate)
        let journalSortable = null;



        // ===========================
        // SORTABLE FUNCTIONALITY
        // ===========================

        // Initialize sortable functionality for journal entries
        function initializeJournalSortable() {
            const journalTableBody = document.getElementById('journalRows');
            if (journalTableBody && !journalSortable) {
                journalSortable = Sortable.create(journalTableBody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    filter: '[data-template-row]',
                    onStart: function(evt) {
                        evt.item.style.opacity = '0.5';
                    },
                    onEnd: function(evt) {
                        evt.item.style.opacity = '1';
                        updateJournalTotals();
                        updateJournalIndices();
                        console.log('Journal entry moved from index', evt.oldIndex, 'to', evt.newIndex);
                    }
                });
                console.log('Journal sortable initialized');
            }
        }


        // Update journal indices in form field names after reordering
        function updateJournalIndices() {
            const rows = document.querySelectorAll('#journalRows tr:not([data-template-row])');
            rows.forEach((row, index) => {
                const actualIndex = index + 1;

                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.name && input.name.includes('journal_items[')) {
                        const newName = input.name.replace(/journal_items\[\d+\]/,
                            `journal_items[${actualIndex}]`);
                        input.name = newName;
                    }
                });

                row.dataset.journalRow = actualIndex;

                const selects = row.querySelectorAll('select[data-row]');
                selects.forEach(select => {
                    select.dataset.row = actualIndex;
                });
            });
        }

        // Destroy sortable instances (useful when switching between forms)
        function destroySortableInstances() {
            if (window.invoiceHandler) {
                window.invoiceHandler.destroySortable();
            }
            if (journalSortable) {
                journalSortable.destroy();
                journalSortable = null;
                console.log('Journal sortable destroyed');
            }
        }

        // ===========================
        // MAIN INITIALIZATION
        // ===========================

        // DOMContentLoaded - only initialization and event binding here
        document.addEventListener('DOMContentLoaded', function() {

            console.log('==========================================');

            // Initialize journal elements first
            window.journalHandler.initializeElements();

            // Initialize invoice elements
            window.invoiceHandler.initializeElements();
            // Initialize bank manager
            window.bankManager.initializeElements();
            window.coaModal.initialize();

            // Initialize product modal
            window.productModal.initialize();

            // Initialize file upload handler
            if (typeof FileUploadHandler !== 'undefined') {
                window.fileUploadHandler = new FileUploadHandler();
                window.fileUploadHandler.initialize();
            }
            // FIXED: Add null checks for all DOM element queries
            const paymentTypeButtons = document.querySelectorAll('.btn-simple');

            // >>> PATCH 2: Inter Bank Office DOM refs

            const currentPaymentTypeInput = document.getElementById('currentPaymentType');
            // <<< PATCH 2


            const entryRefInput = document.getElementById('entryRefInput');
            const hiddenTransactionCode = document.getElementById('hiddenTransactionCode');
            const invoiceTransactionCode = document.getElementById('invoiceTransactionCode');

            let salesInvoicePaymentTypeInput = document.getElementById('salesInvoicePaymentType');
            const generateNewCodeBtn = document.getElementById('generateNewCode');
            const codeValidationMessage = document.getElementById('codeValidationMessage');

            // Form containers
            const regularOfficeForm = document.getElementById('regularOfficeForm');
            const salesInvoiceForm = document.getElementById('salesInvoiceForm');
            const saveDropdownContainer = document.getElementById('saveDropdownContainer');

            // Journal specific elements
            const addJournalLineBtn = document.getElementById('addJournalLineBtn');

            // Invoice elements
            const addItemBtn = document.getElementById('addItemBtn');

            // Save buttons
            const justSaveBtn = document.getElementById('justSave');
            const saveAndEmailDropdownBtn = document.getElementById('saveAndEmailDropdown');
            const saveAndAddNewDropdownBtn = document.getElementById('saveAndAddNewDropdown');

            // <<< OPTION B HELPERS


            // Ensure sales invoice payment type input exists
            if (!salesInvoicePaymentTypeInput) {
                salesInvoicePaymentTypeInput = document.createElement('input');
                salesInvoicePaymentTypeInput.type = 'hidden';
                salesInvoicePaymentTypeInput.name = 'current_payment_type';
                salesInvoicePaymentTypeInput.id = 'salesInvoicePaymentType';
                salesInvoicePaymentTypeInput.value = document.querySelector('.btn-simple.active')?.dataset
                    .paymentType || 'payment';

                const salesForm = document.getElementById('salesInvoiceTransactionForm') ||
                    document.querySelector('#salesInvoiceForm form');
                if (salesForm) {
                    salesForm.appendChild(salesInvoicePaymentTypeInput);
                }
            }


            function applyAutoCode(autoCode) {
                try {
                    const m = String(autoCode).match(/^([A-Z]+)(\d{6})$/);
                    if (!m) {
                        console.error('Bad auto code format:', autoCode);
                        return;
                    }

                    const prefix = m[1];
                    const suffix = m[2];

                    // ✅ Safely update elements with null checks
                    const codePrefixEl = document.getElementById('codePrefix');
                    const codeSuffixEl = document.getElementById('codeSuffix');

                    if (codePrefixEl) {
                        codePrefixEl.textContent = prefix;
                    } else {
                        console.warn('⚠️ codePrefix element not found');
                    }

                    if (codeSuffixEl) {
                        codeSuffixEl.value = suffix;
                    } else {
                        console.warn('⚠️ codeSuffix element not found');
                    }

                    // ✅ FIXED: Use window.codeManager methods
                    if (window.codeManager) {
                        window.codeManager.setMinSuffix(parseInt(suffix, 10));
                        window.codeManager.normalizeSuffix();
                        window.codeManager.syncTransactionCode();
                        window.codeManager.checkCodeUnique();
                    }

                } catch (error) {
                    console.error('❌ Error in applyAutoCode:', error);
                    // Don't throw - just log and continue
                }
            }

            // keep your fetch; just call applyAutoCode on success
            function generateAutoCodeAjax(paymentType) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) return Promise.reject(new Error('CSRF token not found'));

                return fetch('/transactions/generate-auto-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_type: paymentType,
                            account_type: 'office'
                        })
                    })
                    .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP error')))
                    .then(data => {
                        if (!data?.success || !data?.auto_code) throw new Error(data?.message ||
                            'Failed to generate');
                        applyAutoCode(data.auto_code);
                        if (typeof showValidationMessage === 'function') {
                            showValidationMessage('New code generated successfully', 'success');
                            setTimeout(() => (typeof clearValidationMessage === 'function' &&
                                clearValidationMessage()), 3000);
                        }
                    });
            }

            // click handling for the 9–10 buttons
            document.querySelectorAll('.btn-simple').forEach(btn => {
                btn.addEventListener('click', async () => {

                    document.querySelectorAll('.btn-simple').forEach(b => b.classList
                        .remove('active'));
                    btn.classList.add('active');

                    const pt = btn.getAttribute('data-payment-type');

                    if (window.dataLoader) {
                        await window.dataLoader.switchDropdownByPaymentType(pt);
                    }

                    // reflect payment type into hidden fields used by both forms
                    const ptHidden = document.getElementById('salesInvoicePaymentType') ||
                        document.getElementById('currentPaymentType');
                    if (ptHidden) ptHidden.value = pt;
                    //  await toggleOfficeBankFields(pt);
                    // show/hide forms
                    const showInvoice = window.formManager.shouldUseInvoiceForm(pt);
                    document.getElementById('salesInvoiceForm')?.style.setProperty('display',
                        showInvoice ? 'block' : 'none');
                    document.getElementById('regularOfficeForm')?.style.setProperty('display',
                        showInvoice ? 'none' : 'block');

                    // update labels/titles for invoice-style forms
                    window.formManager.updateFormLabels(pt);

                    // get new auto code from server (authoritative) and sync
                    try {
                        await generateAutoCodeAjax(pt);
                    } catch (e) {
                        console.warn('Could not refresh code for payment type', pt, e);
                        // keep current UI code as fallback
                        normalizeSuffix();
                        syncTransactionCode();
                        checkCodeUnique(); // optional

                    }
                });
            });

            function showValidationMessage(message, type) {
                if (!codeValidationMessage) return;
                const iconClass = type === 'error' ? 'exclamation-circle' : 'check-circle';
                const textClass = type === 'error' ? 'danger' : 'success';
                codeValidationMessage.innerHTML = `
                    <small class="text-${textClass}">
                        <i class="fas fa-${iconClass}"></i> ${message}
                    </small>`;
            }

            function clearValidationMessage() {
                if (codeValidationMessage) codeValidationMessage.innerHTML = '';
            }



            /**
             * ✅ FIXED: Load draft data with proper async handling
             */
            async function loadDraftDataIntoForm(editData) {
                console.log('📝 Loading draft data:', editData);

                try {
                    // ✅ STEP 1: Ensure data loader exists and is ready
                    if (!window.dataLoader) {
                        console.log('⏳ Waiting for data loader...');
                        await new Promise(resolve => {
                            window.addEventListener('dataLoaderReady', () => {
                                console.log('✅ Data loader ready event received');
                                resolve();
                            }, {
                                once: true
                            });
                        });
                    }

                    // ✅ STEP 2: Wait for all data to be loaded
                    await window.dataLoader.waitForDataLoad();
                    console.log('✅ All data loaded');

                    // ✅ STEP 3: Populate customer dropdown if not already done
                    if (!document.getElementById('customerDropdown')?.options?.length ||
                        document.getElementById('customerDropdown').options.length <= 1) {
                        console.log('🔄 Populating customer dropdown...');
                        window.dataLoader.populateCustomerDropdown();
                        await new Promise(resolve => setTimeout(resolve, 300));
                    }

                    // ✅ STEP 4: Detect payment type from invoice number
                    const invoiceNo = editData.invoice_no;
                    let detectedPaymentType = 'sales_invoice';

                    if (invoiceNo) {
                        const prefix = invoiceNo.substring(0, 3).toUpperCase();
                        const paymentTypeMap = {
                            'SIN': 'sales_invoice',
                            'SCN': 'sales_credit',
                            'PUR': 'purchase',
                            'PUC': 'purchase_credit',
                            'JOU': 'journal'
                        };

                        detectedPaymentType = paymentTypeMap[prefix] || 'sales_invoice';
                        console.log('✅ Payment type detected:', detectedPaymentType);
                    }

                    // ✅ STEP 5: Store customer value BEFORE clicking payment type button
                    const customerFileId = editData.customer;
                    console.log('💾 Storing customer ID before payment type change:', customerFileId);

                    // ✅ STEP 6: Click payment type button (this might clear dropdowns)
                    const paymentTypeButton = document.querySelector(
                        `button[data-payment-type="${detectedPaymentType}"]`
                    );
                    if (paymentTypeButton && !paymentTypeButton.classList.contains('active')) {
                        console.log('🔘 Clicking payment type button:', detectedPaymentType);
                        paymentTypeButton.click();

                        // Wait for form to stabilize after payment type change
                        await new Promise(resolve => setTimeout(resolve, 800));

                        // ✅ CRITICAL: Refresh customer dropdown after payment type change
                        console.log('🔄 Refreshing customer dropdown after payment type change...');
                        window.dataLoader.refreshCustomerDropdown(customerFileId);
                        await new Promise(resolve => setTimeout(resolve, 300));
                    }

                    // ✅ STEP 7: Now populate all fields
                    await populateDraftFields(editData, detectedPaymentType);

                } catch (error) {
                    console.error('❌ Error loading draft:', error);
                }
            }

            /**
             * ✅ FIXED: Wait for dropdowns with better timing
             */
            function waitForDropdownsAndPopulate(editData, paymentType) {
                console.log('⏳ Waiting for dropdowns to load...', {
                    paymentType: paymentType,
                    customerFileId: editData.customer
                });

                let attempts = 0;
                const maxAttempts = 100; // Increased to 20 seconds

                const checkInterval = setInterval(() => {
                    attempts++;

                    const hasDataLoader = window.dataLoader && window.dataLoader.isAllDataLoaded();
                    const customerDropdown = document.getElementById('customerDropdown');
                    const hasCustomerOptions = customerDropdown && customerDropdown.options.length > 1;
                    const hasChartData = hasDataLoader && window.dataLoader.getChartOfAccountsData()
                        .length > 0;
                    const hasLedgerData = hasDataLoader && window.dataLoader.getLedgerRefsData().length > 0;

                    // ✅ Log every 10 attempts
                    if (attempts % 10 === 0) {
                        console.log(`🔍 Check ${attempts}/100:`, {
                            dataLoader: !!hasDataLoader,
                            customerOptions: customerDropdown ? customerDropdown.options.length : 0,
                            chartData: hasChartData ? window.dataLoader.getChartOfAccountsData()
                                .length : 0,
                            ledgerData: hasLedgerData ? window.dataLoader.getLedgerRefsData()
                                .length : 0
                        });
                    }

                    // ✅ ALL conditions must be met
                    if (hasDataLoader && hasCustomerOptions && hasChartData && hasLedgerData) {
                        clearInterval(checkInterval);
                        console.log('✅ All dropdowns ready! Starting population...');

                        // ✅ Longer delay to ensure DOM stability
                        setTimeout(() => {
                            populateDraftFields(editData, paymentType);
                        }, 1000);
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                        console.error('❌ Timeout after', maxAttempts, 'attempts. Current state:', {
                            hasDataLoader,
                            hasCustomerOptions,
                            hasChartData,
                            hasLedgerData
                        });

                        // Try anyway
                        populateDraftFields(editData, paymentType);
                    }
                }, 200);
            }

            /**
             * ✅ FIXED: Populate fields with proper supplier handling
             */
            async function populateDraftFields(editData, paymentType) {
                console.log('📝 Populating draft fields:', {
                    customer: editData.customer,
                    items: editData.items?.length || 0,
                    paymentType: paymentType
                });

                // ✅ 1. Customer/Supplier dropdown - DIRECT set with proper field detection
                const customerDropdown = document.getElementById('customerDropdown');
                if (customerDropdown && editData.customer) {
                    console.log('🔍 Setting dropdown value:', editData.customer);

                    // Wait a bit for dropdown to be fully populated
                    await new Promise(resolve => setTimeout(resolve, 500));

                    // Verify option exists
                    const hasOption = Array.from(customerDropdown.options).some(
                        opt => opt.value == editData.customer
                    );

                    if (hasOption) {
                        customerDropdown.value = editData.customer;
                        customerDropdown.dispatchEvent(new Event('change'));
                        console.log('✅ Dropdown set successfully to:', editData.customer);
                    } else {
                        console.error('❌ Dropdown option not found:', editData.customer);
                        console.log('📋 Available options:', Array.from(customerDropdown.options).map(opt => ({
                            value: opt.value,
                            text: opt.text
                        })));
                    }
                }

                // ✅ 2. Invoice Date
                const invoiceDateField = document.querySelector('input[name="Transaction_Date"]');
                if (invoiceDateField && editData.invoice_date) {
                    invoiceDateField.value = editData.invoice_date;
                    console.log('✅ Invoice date:', editData.invoice_date);
                }

                // ✅ 3. Due Date
                const dueDateField = document.querySelector('input[name="Inv_Due_Date"]');
                if (dueDateField && editData.due_date) {
                    dueDateField.value = editData.due_date;
                    console.log('✅ Due date:', editData.due_date);
                }

                // ✅ 4. Invoice Number Suffix
                const invoiceSuffix = document.getElementById('invoiceSuffix');
                if (invoiceSuffix && editData.invoice_no) {
                    const matches = editData.invoice_no.match(/\d{6}$/);
                    if (matches) {
                        invoiceSuffix.value = matches[0];
                        if (window.codeManager) {
                            window.codeManager.syncTransactionCode();
                        }
                        console.log('✅ Invoice suffix:', matches[0]);
                    }
                }

                // ✅ 5. Invoice Reference
                const invoiceRefField = document.querySelector('input[name="invoice_ref"]');
                if (invoiceRefField && editData.invoice_ref) {
                    invoiceRefField.value = editData.invoice_ref;
                    console.log('✅ Invoice ref:', editData.invoice_ref);
                }

                // ✅ 6. Notes
                const notesField = document.getElementById('invoiceNotesHidden');
                if (notesField && editData.notes) {
                    notesField.value = editData.notes;
                    console.log('✅ Notes set');
                }

                // ✅ 7. Load Items - FIXED to prevent empty rows
                if (editData.items && editData.items.length > 0) {
                    console.log('📦 Starting to load', editData.items.length, 'items');

                    const invoiceItemsTable = document.getElementById('invoiceItemsTable');
                    if (invoiceItemsTable) {
                        invoiceItemsTable.innerHTML = ''; // Clear ALL existing rows
                        console.log('✅ Table cleared');
                    }

                    // Load items sequentially
                    for (let i = 0; i < editData.items.length; i++) {
                        console.log(`\n🔄 Loading item ${i + 1} of ${editData.items.length}`);

                        try {
                            await addInvoiceItemFromDraft(editData.items[i], i);
                            console.log(`✅ Item ${i + 1} loaded successfully`);
                        } catch (error) {
                            console.error(`❌ Failed to load item ${i + 1}:`, error);
                        }

                        // Wait between items to ensure DOM stability
                        await new Promise(resolve => setTimeout(resolve, 400));
                    }

                    // Final summary update after ALL items loaded
                    await new Promise(resolve => setTimeout(resolve, 500));

                    if (window.invoiceHandler) {
                        window.invoiceHandler.updateInvoiceSummary();
                        console.log('✅ Final summary updated');
                    }

                    console.log(`\n✅ ALL ${editData.items.length} ITEMS LOADED SUCCESSFULLY`);
                }
            }

            /**
             * ✅ FIXED: Add invoice item WITHOUT creating empty rows
             */
            async function addInvoiceItemFromDraft(item, index) {
                console.log(`\n📦 [Item ${index}] Starting load...`);
                console.log(`📋 [Item ${index}] Data:`, {
                    item_code: item.item_code,
                    description: item.description?.substring(0, 30) + '...',
                    qty: item.qty,
                    unit_amount: item.unit_amount
                });

                // ✅ STEP 1: Create new row
                console.log(`➕ [Item ${index}] Creating row...`);
                window.invoiceHandler.addNewInvoiceRow();

                // ✅ STEP 2: Wait for DOM to update
                await new Promise(resolve => setTimeout(resolve, 300));

                // ✅ STEP 3: Get the LAST row (the one we just created)
                const rows = document.querySelectorAll('#invoiceItemsTable tr');
                const row = rows[rows.length - 1]; // ✅ Always get the LAST row we just created

                if (!row) {
                    console.error(`❌ [Item ${index}] Row not found!`);
                    return;
                }

                console.log(`✅ [Item ${index}] Row created, found ${rows.length} total rows`);

                // ✅ STEP 4: Mark as auto-filling FIRST
                row.dataset.isAutoFilling = 'true';

                try {
                    // ✅ STEP 5: Fill basic fields
                    console.log(`📝 [Item ${index}] Filling basic fields...`);

                    const itemCodeInput = row.querySelector('.item-code-input');
                    const descInput = row.querySelector('input[name*="[description]"]');
                    const qtyInput = row.querySelector('.qty-input');
                    const unitAmountInput = row.querySelector('.unit-amount');

                    if (itemCodeInput && item.item_code) {
                        itemCodeInput.value = item.item_code;
                    }

                    if (descInput && item.description) {
                        descInput.value = item.description;
                    }

                    if (qtyInput && item.qty) {
                        qtyInput.value = item.qty;
                    }

                    if (unitAmountInput && item.unit_amount) {
                        unitAmountInput.value = parseFloat(item.unit_amount).toFixed(2);
                    }

                    console.log(`✅ [Item ${index}] Basic fields filled`);

                    // ✅ STEP 6: Load ledger and account
                    console.log(`🔄 [Item ${index}] Loading ledger/account...`);
                    const chartAccount = window.dataLoader.getChartAccountById(item.ledger_id);

                    if (chartAccount) {
                        const ledgerSelect = row.querySelector('.ledger-select');
                        if (ledgerSelect && chartAccount.ledger_ref) {
                            for (let i = 0; i < ledgerSelect.options.length; i++) {
                                if (ledgerSelect.options[i].dataset.ledgerRef === chartAccount.ledger_ref) {
                                    ledgerSelect.selectedIndex = i;
                                    ledgerSelect.dispatchEvent(new Event('change'));
                                    break;
                                }
                            }

                            // Wait for account dropdown
                            const accountSelect = row.querySelector('.account-select');
                            if (accountSelect && item.account_ref) {
                                let attempts = 0;
                                while (accountSelect.options.length <= 1 && attempts < 50) {
                                    await new Promise(resolve => setTimeout(resolve, 100));
                                    attempts++;
                                }

                                if (accountSelect.options.length > 1) {
                                    for (let i = 0; i < accountSelect.options.length; i++) {
                                        const optionText = accountSelect.options[i].text.split('(')[0].trim();
                                        if (optionText === item.account_ref.trim()) {
                                            accountSelect.selectedIndex = i;
                                            accountSelect.dispatchEvent(new Event('change'));
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        console.log(`✅ [Item ${index}] Ledger/account set`);
                    }

                    // ✅ STEP 7: Load VAT
                    console.log(`🔄 [Item ${index}] Loading VAT...`);
                    const vatRateSelect = row.querySelector('.vat-rate');
                    const vatIdField = row.querySelector('.item-vat-id');

                    if (vatRateSelect && item.vat_form_label_id) {
                        const currentPaymentType = window.formManager.getCurrentPaymentType();

                        await new Promise((resolve) => {
                            window.vatManager.loadVatTypesByForm(currentPaymentType, (vatTypes) => {
                                vatRateSelect.innerHTML = window.vatManager
                                    .createVatDropdownOptions(vatTypes);
                                setTimeout(resolve, 150);
                            });
                        });

                        let pollAttempts = 0;
                        while (vatRateSelect.options.length <= 1 && pollAttempts < 50) {
                            await new Promise(resolve => setTimeout(resolve, 100));
                            pollAttempts++;
                        }

                        if (vatRateSelect.options.length > 1) {
                            let matchingOption = Array.from(vatRateSelect.options).find(opt =>
                                opt.dataset.vatId == item.vat_form_label_id
                            );

                            if (!matchingOption && item.vat_rate !== undefined) {
                                matchingOption = Array.from(vatRateSelect.options).find(opt =>
                                    Math.abs(parseFloat(opt.value) - parseFloat(item.vat_rate)) < 0.01
                                );
                            }

                            if (matchingOption) {
                                vatRateSelect.value = matchingOption.value;
                                if (vatIdField) {
                                    vatIdField.value = matchingOption.dataset.vatId || item.vat_form_label_id;
                                }
                                vatRateSelect.dispatchEvent(new Event('change'));
                                console.log(`✅ [Item ${index}] VAT set to ${matchingOption.value}%`);
                            }
                        }
                    }

                    // ✅ STEP 8: Product image
                    if (item.product_image) {
                        const imagePreviewContainer = row.querySelector('.item-image-preview');
                        const imageUrlField = row.querySelector('.item-image-url');

                        if (imagePreviewContainer) {
                            imagePreviewContainer.innerHTML = `
                    <img src="${item.product_image}" 
                        alt="Product" 
                        class="product-thumbnail"
                        onclick="showFullImageModal('${item.product_image}')"
                        title="Click to view full size">
                `;
                        }

                        if (imageUrlField) {
                            imageUrlField.value = item.product_image;
                        }
                        console.log(`✅ [Item ${index}] Image set`);
                    }

                    console.log(`✅ [Item ${index}] ALL FIELDS POPULATED SUCCESSFULLY`);

                } catch (error) {
                    console.error(`❌ [Item ${index}] Error:`, error);
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
                        console.log(`🧮 [Item ${index}] Calculation triggered`);
                    }
                }
            }
            /**
             * ✅ FALLBACK: Populate row using direct ledger_ref and account_ref from draft
             */
            async function populateRowUsingDirectRefs(row, item) {
                console.log('🔄 Using fallback method with direct refs');

                const ledgerSelect = row.querySelector('.ledger-select');
                const accountSelect = row.querySelector('.account-select');

                // Step 1: Find and set ledger by text matching
                if (ledgerSelect && item.ledger_ref) {
                    for (let i = 0; i < ledgerSelect.options.length; i++) {
                        const option = ledgerSelect.options[i];
                        if (option.text.trim() === item.ledger_ref.trim() ||
                            option.dataset.ledgerRef === item.ledger_ref) {
                            ledgerSelect.selectedIndex = i;
                            ledgerSelect.dispatchEvent(new Event('change'));
                            console.log('✅ Fallback: Ledger set by text:', item.ledger_ref);
                            break;
                        }
                    }

                    // Wait for account dropdown
                    let attempts = 0;
                    while (accountSelect.options.length <= 1 && attempts < 50) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                        attempts++;
                    }
                }

                // Step 2: Set account by text matching
                if (accountSelect && item.account_ref) {
                    for (let i = 0; i < accountSelect.options.length; i++) {
                        const option = accountSelect.options[i];
                        const optionText = option.text.split('(')[0].trim();

                        if (optionText === item.account_ref.trim()) {
                            accountSelect.selectedIndex = i;
                            accountSelect.dispatchEvent(new Event('change'));
                            console.log('✅ Fallback: Account set by text:', item.account_ref);
                            break;
                        }
                    }
                }

                // ✅ Step 3: CRITICAL DEBUG - Check VAT elements BEFORE trying to use them
                console.log('🔍 DEBUG: Checking VAT elements...');
                const vatRateSelect = row.querySelector('.vat-rate');
                const vatIdField = row.querySelector('.item-vat-id');


                if (!vatRateSelect) {
                    console.error('❌ CRITICAL: vatRateSelect (.vat-rate) element NOT FOUND in row!');
                    console.log('🔍 Row HTML:', row.innerHTML.substring(0, 500));
                    return; // Stop here if VAT select doesn't exist
                }

                if (!item.vat_form_label_id) {
                    console.error('❌ CRITICAL: item.vat_form_label_id is missing!');
                    return;
                }

                // If we get here, both elements exist
                console.log('✅ Both VAT elements found, proceeding...');

                try {
                    console.log('🔄 Fallback: Loading VAT types...');

                    const currentPaymentType = window.formManager.getCurrentPaymentType();

                    // ✅ Load VAT types
                    await new Promise((resolve) => {
                        window.vatManager.loadVatTypesByForm(currentPaymentType, (vatTypes) => {
                            vatRateSelect.innerHTML = window.vatManager
                                .createVatDropdownOptions(vatTypes);
                            setTimeout(resolve, 150); // Wait for DOM
                        });
                    });

                    // ✅ Poll until options exist
                    let attempts = 0;
                    while (vatRateSelect.options.length <= 1 && attempts < 50) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                        attempts++;
                    }

                    if (vatRateSelect.options.length <= 1) {
                        console.error('❌ Fallback: VAT dropdown never populated!');
                        return;
                    }

                    console.log('✅ Fallback: VAT dropdown ready with', vatRateSelect.options.length, 'options');

                    // ✅ Try to match by ID
                    let vatOption = Array.from(vatRateSelect.options).find(opt =>
                        opt.dataset.vatId == item.vat_form_label_id
                    );

                    // ✅ Fallback to rate matching
                    if (!vatOption && item.vat_rate) {
                        vatOption = Array.from(vatRateSelect.options).find(opt =>
                            Math.abs(parseFloat(opt.value) - parseFloat(item.vat_rate)) < 0.01
                        );
                    }

                    if (vatOption) {
                        vatRateSelect.value = vatOption.value;
                        if (vatIdField) {
                            vatIdField.value = item.vat_form_label_id;
                        }
                        console.log('✅ Fallback: VAT set to:', vatOption.value + '%');
                    } else {
                        console.error('❌ Fallback: No matching VAT option');
                    }
                } catch (error) {
                    console.error('❌ Error in fallback VAT loading:', error);
                }

                // Step 4: Product image
                console.log('🔍 Checking product image:', item.product_image);
                if (item.product_image) {
                    const imagePreviewContainer = row.querySelector('.item-image-preview');
                    const imageUrlField = row.querySelector('.item-image-url');

                    if (imagePreviewContainer) {
                        imagePreviewContainer.innerHTML = `
                        <img src="${item.product_image}" 
                            alt="Product" 
                            class="product-thumbnail"
                            onclick="showFullImageModal('${item.product_image}')"
                            title="Click to view full size">
                    `;
                        console.log('✅ Fallback: Product image loaded');
                    }

                    if (imageUrlField) {
                        imageUrlField.value = item.product_image;
                    }
                }

                // ✅ Done auto-filling FIRST (before any events)
                row.dataset.isAutoFilling = 'false';
                console.log('🔓 Row auto-filling complete');

                // ✅ Now trigger ONE calculation to update totals
                setTimeout(() => {
                    const unitAmountInput = row.querySelector('.unit-amount');
                    if (unitAmountInput) {
                        unitAmountInput.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        console.log('✅ Fallback: Final calculation triggered');
                    }
                }, 300);
                console.log('✅ Fallback function completed');
            }

            // Event Listeners
            // FIXED: Add null check for addJournalLineBtn
            if (addJournalLineBtn) {
                addJournalLineBtn.addEventListener('click', function() {
                    console.log('Add journal line clicked');
                    window.journalHandler.addNewJournalRow();
                });
            }


            /**
             * ✅ Helper: Manually trigger account refs loading for a ledger
             */
            function triggerAccountRefsLoad(ledgerSelect, accountSelect) {
                return new Promise((resolve) => {
                    const ledgerRef = ledgerSelect.options[ledgerSelect.selectedIndex]?.dataset?.ledgerRef;

                    if (!ledgerRef) {
                        resolve(false);
                        return;
                    }

                    console.log('🔄 Manually loading account refs for ledger:', ledgerRef);

                    // Call the API directly
                    window.dataLoader.getAccountRefsByLedger(ledgerRef, (accountRefs) => {
                        if (accountRefs && accountRefs.length > 0) {
                            accountSelect.innerHTML = '<option value="">Select Account</option>';

                            accountRefs.forEach(acc => {
                                const option = document.createElement('option');
                                option.value = acc.id;
                                option.textContent = acc.account_ref;
                                option.dataset.accountRef = acc.account_ref;
                                accountSelect.appendChild(option);
                            });

                            console.log('✅ Account refs loaded manually:', accountRefs.length);
                            resolve(true);
                        } else {
                            console.warn('⚠️ No account refs returned');
                            resolve(false);
                        }
                    });
                });
            }

            // Global remove button handler for both tables
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    e.preventDefault();
                    e.stopPropagation();

                    const row = e.target.closest('tr');
                    if (row) {
                        const invoiceItemsTable = document.getElementById('invoiceItemsTable');
                        if (invoiceItemsTable && invoiceItemsTable.contains(row)) {
                            row.remove();
                            window.invoiceHandler.updateInvoiceSummary();
                            console.log('Invoice row removed');
                        } else if (window.journalHandler.elements.journalRows && window.journalHandler
                            .elements.journalRows.contains(row)) {
                            if (!row.hasAttribute('data-template-row')) {
                                row.remove();
                                window.journalHandler.updateJournalTotals();
                                console.log('Journal row removed');
                            }
                        }
                    }
                }
            });

            // Payment Type Button Events
            paymentTypeButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    const paymentType = this.dataset.paymentType;

                    // ✅ STORE customer value before changing payment type
                    const customerDropdown = document.getElementById('customerDropdown');
                    const currentCustomerValue = customerDropdown ? customerDropdown.value :
                        null;
                    console.log('💾 Current customer before payment type change:',
                        currentCustomerValue);

                    await window.bankManager.toggleOfficeBankFields(paymentType);

                    paymentTypeButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.classList.add('custom-hover');
                    });

                    this.classList.add('active');
                    this.classList.remove('custom-hover');

                    if (currentPaymentTypeInput) {
                        currentPaymentTypeInput.value = paymentType;
                    }

                    let salesField = document.getElementById('salesInvoicePaymentType');
                    if (!salesField) {
                        const salesForm = document.getElementById(
                            'salesInvoiceTransactionForm');
                        if (salesForm) {
                            salesField = document.createElement('input');
                            salesField.type = 'hidden';
                            salesField.name = 'current_payment_type';
                            salesField.id = 'salesInvoicePaymentType';
                            salesForm.appendChild(salesField);
                        }
                    }

                    if (salesField) {
                        salesField.value = paymentType;
                    }

                    if (window.formManager.shouldUseInvoiceForm(paymentType)) {
                        window.formManager.showSalesInvoiceForm();
                        window.formManager.updateFormLabels(paymentType);
                    } else {
                        window.formManager.showRegularOfficeForm();
                    }

                    // ✅ RESTORE customer value after form changes
                    if (currentCustomerValue && window.dataLoader) {
                        setTimeout(() => {
                            window.dataLoader.refreshCustomerDropdown(
                                currentCustomerValue);
                            console.log(
                                '✅ Customer value restored after payment type change'
                            );
                        }, 300);
                    }

                    // Update VAT rates for the new payment type
                    window.vatManager.updateFormVatRates(paymentType);

                    // Load VAT types for this payment type
                    window.vatManager.loadVatTypesByForm(paymentType, function(vatTypes) {
                        console.log('VAT types loaded for', paymentType, ':', vatTypes);
                    });

                    destroySortableInstances();

                    generateAutoCodeAjax(paymentType).catch(() => {});
                    clearValidationMessage();

                    setTimeout(() => {
                        if (window.formManager.shouldUseInvoiceForm(paymentType)) {
                            if (paymentType === 'journal') {
                                initializeJournalSortable();
                            } else {
                                window.invoiceHandler.initializeSortable();
                            }
                        }
                    }, 200);
                });
            });

            // Additional event listeners for invoice functionality
            if (addItemBtn) addItemBtn.addEventListener('click', () => window.invoiceHandler.addNewInvoiceRow());
            // FIXED: Add null check for generateNewCodeBtn
            if (generateNewCodeBtn) {
                generateNewCodeBtn.addEventListener('click', function() {
                    const currentType = currentPaymentTypeInput ? currentPaymentTypeInput.value : 'payment';
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

                    generateAutoCodeAjax(currentType)
                        .finally(() => {
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-refresh"></i> Generate New';
                        });
                });
            }

            // Form submission handlers
            // Form submission handlers
            const submitButtons = [{
                    btn: justSaveBtn,
                    action: 'save'
                },
                {
                    btn: saveAndEmailDropdownBtn,
                    action: 'save_and_email'
                },
                {
                    btn: saveAndAddNewDropdownBtn,
                    action: 'save_and_add_new'
                }
            ];

            submitButtons.forEach(({
                btn,
                action
            }) => {
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (!window.invoiceHandler.validateInvoiceForm()) return false;
                        window.formSubmissionHandler.submitSalesInvoiceForm(action);
                    });
                }
            });

            const saveAsDraftBtn = document.getElementById('saveAsDraft');
            if (saveAsDraftBtn) {
                saveAsDraftBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!window.invoiceHandler.validateInvoiceForm()) return false;
                    window.formSubmissionHandler.submitSalesInvoiceForm('save_as_draft');
                });
            }

            const previewBtn = document.getElementById('previewButton');
            if (previewBtn) {
                previewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Preview button clicked ✅');
                    const paymentType = window.formManager.getCurrentPaymentType();
                    if (!window.invoiceHandler.validateInvoiceForm()) return;

                    // Set action as 'preview'
                    window.formSubmissionHandler.submitSalesInvoiceForm('preview');
                });
            }

            // Form validation for regular office form
            const regularForm = document.getElementById('regularOfficeTransactionForm');
            if (regularForm) {
                regularForm.addEventListener('submit', function(e) {



                    if (!entryRefInput || !entryRefInput.value.trim()) {
                        e.preventDefault();
                        showValidationMessage('Transaction code is required', 'error');
                        if (entryRefInput) entryRefInput.focus();
                        return false;
                    }

                    // Inter-bank office validation
                    const pt = currentPaymentTypeInput?.value || 'payment';
                    if (pt === 'inter_bank_office') {
                        const validationResult = window.bankManager.validateInterBankTransfer();

                        if (validationResult !== true) {
                            e.preventDefault();
                            alert(validationResult);
                            return false;
                        }

                        // Ensure Analysis Acc (COA + Client Ledger) chosen
                        const ledgerRefHidden = document.getElementById('ledgerRefHidden');
                        const hasLedgerRef = (ledgerRefHidden && ledgerRefHidden.value && ledgerRefHidden
                            .value.trim() !== '');
                        if (!hasLedgerRef) {
                            e.preventDefault();
                            alert('Please pick an Analysis Acc (COA + Client Ledger).');
                            return false;
                        }
                    }


                    const chartOfAccountsId = document.getElementById('chartOfAccountsId');
                    if (!chartOfAccountsId || !chartOfAccountsId.value) {
                        e.preventDefault();
                        alert('Please select a Chart of Account');
                        return false;
                    }

                    if (window.codeManager) window.codeManager.syncTransactionCode();
                    return true;
                });
            }

            window.formManager.initializePaymentType();
            if (window.codeManager) window.codeManager.syncTransactionCode();
            // syncTransactionCode();


            // WHY: When editing, pre-fill all fields with existing data
            @if (isset($editData) && $editData)
                console.log('✅ EDIT MODE DETECTED - Loading draft data');
                loadDraftDataIntoForm(@json($editData));
            @endif

            // >>> PATCH 5: initial toggle on page load
            (async () => {
                const initialType = currentPaymentTypeInput?.value ||
                    document.querySelector('.btn-simple.active')?.dataset.paymentType || 'payment';

                // ✅ NEW: Switch dropdown based on initial payment type
                if (window.dataLoader) {
                    await window.dataLoader.switchDropdownByPaymentType(initialType);
                    console.log('✅ Initial dropdown switched for payment type:', initialType);
                }

                await window.bankManager.toggleOfficeBankFields(initialType);

                window.vatManager.updateFormVatRates(initialType);
                window.vatManager.loadVatTypesByForm(initialType, function(vatTypes) {
                    console.log('✅ Initial VAT types loaded for', initialType, ':', vatTypes);
                });
            })();

            // Initialize Notes Editor
            if (typeof CollapsibleNotesEditor !== 'undefined') {
                try {
                    window.notesEditor = new CollapsibleNotesEditor();
                    window.notesEditor.initialize();
                } catch (error) {
                    console.error('CollapsibleNotesEditor initialization failed:', error);
                }
            }

            // Initialize Table Tooltip Handler
            if (typeof TableTooltipHandler !== 'undefined') {
                window.tableTooltipHandler = new TableTooltipHandler();
                window.tableTooltipHandler.initialize();
            }

            // Initialize Form Submission Handler
            if (typeof FormSubmissionHandler !== 'undefined') {
                window.formSubmissionHandler = new FormSubmissionHandler();
                window.formSubmissionHandler.initialize();
            }

            // Initialize sortable functionality for the initial form
            setTimeout(() => {
                const initialPaymentType = document.querySelector('.btn-simple.active')?.dataset
                    .paymentType || 'payment';

                if (window.formManager.shouldUseInvoiceForm(initialPaymentType)) {
                    if (initialPaymentType === 'journal') {
                        initializeJournalSortable();
                    } else {
                        window.invoiceHandler.initializeSortable();
                    }
                }
            }, 300);


        });
    </script>
@endsection
