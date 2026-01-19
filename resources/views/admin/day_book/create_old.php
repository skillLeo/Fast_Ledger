@extends('admin.layout.app')

<style>
    .form-label-grey {
        background-color: #e0e0e0;
        padding: 8px 12px;
        font-weight: bold;
        text-align: right;
        display: flex;
        align-items: center;
        height: 100%;
    }
</style>

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="page-title mb-0">
                                <h4 class="mb-1">Add New Entry</h4>
                                @if ($type === 'client')
                                    <span class="badge bg-primary fs-6">
                                        <i class="fas fa-user me-1"></i>Client Account
                                    </span>
                                @elseif($type === 'office')
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-building me-1"></i>Office Account
                                    </span>
                                @endif
                            </div>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="card-body pb-0">
                                <h5 class="mb-3">Select Payment Type</h5>
                                <div class="d-flex flex-row gap-2 mb-4">
                                    <button type="button"
                                        class="payment-type-btn {{ $paymentType === 'inter_bank_client' ? 'active' : 'custom-hover' }}"
                                        data-payment-type="inter_bank_client"
                                        style="background-color: {{ $paymentType === 'inter_bank_client' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                        Inter Bank Client Client
                                    </button>
                                    <button type="button"
                                        class="payment-type-btn {{ $paymentType === 'inter_ledger' ? 'active' : 'custom-hover' }}"
                                        data-payment-type="inter_ledger"
                                        style="background-color: {{ $paymentType === 'inter_ledger' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                        Inter Ledger
                                    </button>
                                    <button type="button"
                                        class="payment-type-btn {{ $paymentType === 'payment' ? 'active' : 'custom-hover' }}"
                                        data-payment-type="payment"
                                        style="background-color: {{ $paymentType === 'payment' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                        Payment
                                    </button>
                                    <button type="button"
                                        class="payment-type-btn {{ $paymentType === 'receipt' ? 'active' : 'custom-hover' }}"
                                        data-payment-type="receipt"
                                        style="background-color: {{ $paymentType === 'receipt' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                        Receipt
                                    </button>
                                    <button type="button"
                                        class="payment-type-btn {{ $paymentType === 'cheque' ? 'active' : 'custom-hover' }}"
                                        data-payment-type="cheque"
                                        style="background-color: {{ $paymentType === 'cheque' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                        Cheque
                                    </button>
                                </div>
                                <style>
                                    .custom-hover:hover,
                                    .custom-hover:focus {
                                        background-color: #72b3dc !important;
                                        color: #fff !important;
                                    }

                                    .payment-type-btn {
                                        padding: 8px 16px;
                                        border-radius: 4px;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                    }
                                </style>
                                <hr>
                            </div>
                            <div class="row">
                                {{-- Add Entry Details and Entry Ref --}}
                                <div class="mb-4">
                                    <label class="form-label fw-bold d-block">Add Entry Details</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" id="entryRefInput"
                                            class="form-control w-auto @error('Transaction_Code') is-invalid @enderror"
                                            value="{{ old('Transaction_Code', $autoCode) }}"
                                            style="background-color: #f5f5f5; font-weight: bold;">
                                        <button type="button" id="generateNewCode" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-refresh"></i> Generate New
                                        </button>
                                        <button type="button" id="toggleManualEdit"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i> Edit Manual
                                        </button>
                                    </div>
                                    <div id="codeValidationMessage" class="mt-1"></div>
                                    <small class="text-muted">Format: PREFIX + YYMMDD + 2-digit random number (e.g.,
                                        PAYC25070547)</small>
                                    @error('Transaction_Code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <x-form method="POST" action="transactions.store">

                                        {{-- Hidden field to store Transaction_Code for form submission --}}
                                        <input type="hidden" id="hiddenTransactionCode" name="Transaction_Code"
                                            value="{{ old('Transaction_Code', $autoCode) }}">

                                        {{-- Hidden field to store current payment type --}}
                                        <input type="hidden" id="currentPaymentType" name="current_payment_type"
                                            value="{{ $paymentType }}">

                                        {{-- Paid In/Out (hidden field with default value) --}}
                                        <input type="hidden" name="Paid_In_Out" value="1">

                                        {{-- Date --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey">Date</label>
                                            <div class="col-sm-4">
                                                <input type="date" name="Transaction_Date"
                                                    value="{{ old('Transaction_Date', date('Y-m-d')) }}"
                                                    class="form-control @error('Transaction_Date') is-invalid @enderror">
                                                @error('Transaction_Date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Bank Account Fields - Dynamic based on payment type --}}
                                        <div id="singleBankAccountField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey">Bank Account</label>
                                            <div class="col-sm-4">
                                                <select name="Bank_Account_ID" id="BankAccountDropdown"
                                                    class="form-select @error('Bank_Account_ID') is-invalid @enderror">
                                                    <option value="" disabled
                                                        {{ old('Bank_Account_ID') ? '' : 'selected' }}>
                                                        Select Bank Account
                                                    </option>
                                                    @foreach ($bankAccounts as $bankAccount)
                                                        <option value="{{ $bankAccount->Bank_Account_ID }}"
                                                            data-bank-type="{{ $bankAccount->Bank_Type_ID }}"
                                                            {{ old('Bank_Account_ID') == $bankAccount->Bank_Account_ID ? 'selected' : '' }}>
                                                            {{ $bankAccount->Bank_Name }}
                                                            ({{ $bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('Bank_Account_ID')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Bank Account From Field (for inter bank transfers) --}}
                                        <div id="bankAccountFromField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey">Bank Account From</label>
                                            <div class="col-sm-4">
                                                <select name="Bank_Account_From_ID" id="BankAccountFromDropdown"
                                                    class="form-select @error('Bank_Account_From_ID') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Source Bank Account</option>
                                                    @foreach ($bankAccounts as $bankAccount)
                                                        <option value="{{ $bankAccount->Bank_Account_ID }}"
                                                            data-bank-type="{{ $bankAccount->Bank_Type_ID }}"
                                                            {{ old('Bank_Account_From_ID') == $bankAccount->Bank_Account_ID ? 'selected' : '' }}>
                                                            {{ $bankAccount->Bank_Name }}
                                                            ({{ $bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('Bank_Account_From_ID')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Bank Account To Field (for inter bank transfers) --}}
                                        <div id="bankAccountToField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey">Bank Account To</label>
                                            <div class="col-sm-4">
                                                <select name="Bank_Account_To_ID" id="BankAccountToDropdown"
                                                    class="form-select @error('Bank_Account_To_ID') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Destination Bank Account</option>
                                                    {{-- This will be populated based on authenticated client's ledger --}}
                                                </select>
                                                @error('Bank_Account_To_ID')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Client Ledger (Ledger Reference Dropdown) --}}
                                        <div class="mb-1 row position-relative">
                                            <label class="col-sm-3 col-form-label form-label-grey">Client Ledger</label>
                                            <div class="col-sm-4">
                                                <select name="Ledger_Ref" id="ledgerRefDropdown"
                                                    class="form-select @error('Ledger_Ref') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Ledger Reference
                                                    </option>
                                                    @foreach ($ledgerRefs as $ledgerRef)
                                                        <option value="{{ $ledgerRef->Ledger_Ref }}"
                                                            {{ old('Ledger_Ref') == $ledgerRef->Ledger_Ref ? 'selected' : '' }}>
                                                            {{ $ledgerRef->Ledger_Ref }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('Ledger_Ref')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Payment Type --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey">Payment Type</label>
                                            <div class="col-sm-4">
                                                <select name="Payment_Type_ID" id="PaymentTypeDropdown"
                                                    class="form-select @error('Payment_Type_ID') is-invalid @enderror">
                                                    <option value="" selected disabled>Select Payment Type</option>
                                                    @foreach ($paymentTypesRange as $paymentType)
                                                        <option value="{{ $paymentType->Payment_Type_ID }}"
                                                            {{ old('Payment_Type_ID') == $paymentType->Payment_Type_ID ? 'selected' : '' }}>
                                                            {{ $paymentType->Payment_Type_Name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('Payment_Type_ID')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Payment Ref (Text Input) --}}
                                        <div class="mb-1 row position-relative">
                                            <label class="col-sm-3 col-form-label form-label-grey">Payment Ref</label>
                                            <div class="col-sm-4">
                                                <input type="text" id="paymentRefInput" name="Payment_Ref"
                                                    value="{{ old('Payment_Ref') }}"
                                                    class="form-control @error('Payment_Ref') is-invalid @enderror"
                                                    placeholder="Payment Reference">
                                                @error('Payment_Ref')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Amount --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey">Amount</label>
                                            <div class="col-sm-4">
                                                <input type="number" name="Amount" value="{{ old('Amount') }}"
                                                    class="form-control @error('Amount') is-invalid @enderror"
                                                    placeholder="Amount">
                                                @error('Amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Description --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey">Description</label>
                                            <div class="col-sm-4">
                                                <textarea name="Description" rows="4" class="form-control @error('Description') is-invalid @enderror"
                                                    placeholder="Transaction Description">{{ old('Description') }}</textarea>
                                                @error('Description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-sm-7 offset-sm-1 d-flex justify-content-start gap-3">
                                                <button type="submit" class="btn btn-primary px-4">Save</button>
                                                <a href="{{ url()->previous() }}" class="btn btn-warning px-4">Cancel</a>
                                            </div>
                                        </div>

                                    </x-form>
                                </div>

                                <!-- Ledger Details -->
                                <div class="col-md-6">
                                    <div id="ledgerDetails" class="border p-3 bg-light" style="display: none;">
                                        <h6><strong><span id="clientName"></span></strong></h6>
                                        <p><strong>File Name:</strong> <span id="fileFullName"></span></p>
                                        <p><strong>Address:</strong> <span id="clientAddress"></span></p>
                                        <a id="clientledgerLink" href="#">
                                            <p><strong>Ledger Ref:</strong> <span id="ledgerRef"></span></p>
                                        </a>
                                        <p><strong>Matter:</strong> <span id="matter"></span></p>
                                        <p><strong>Sub Matter:</strong> <span id="subMatter"></span></p>
                                        <p><strong>Client Ledger Balance:</strong> <span
                                                id="clientLedgerBalance">0.00</span></p>
                                        <p><strong>Office Ledger Balance:</strong> <span
                                                id="officeLedgerBalance">0.00</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle payment type button clicks
            const paymentTypeButtons = document.querySelectorAll('.payment-type-btn');
            const entryRefInput = document.getElementById('entryRefInput');
            const hiddenTransactionCode = document.getElementById('hiddenTransactionCode');
            const currentPaymentTypeInput = document.getElementById('currentPaymentType');
            const ledgerRefDropdown = document.getElementById('ledgerRefDropdown');
            const generateNewCodeBtn = document.getElementById('generateNewCode');
            const toggleManualEditBtn = document.getElementById('toggleManualEdit');
            const codeValidationMessage = document.getElementById('codeValidationMessage');

            // Bank account field elements
            const singleBankAccountField = document.getElementById('singleBankAccountField');
            const bankAccountFromField = document.getElementById('bankAccountFromField');
            const bankAccountToField = document.getElementById('bankAccountToField');
            const bankAccountDropdown = document.getElementById('BankAccountDropdown');
            const bankAccountFromDropdown = document.getElementById('BankAccountFromDropdown');
            const bankAccountToDropdown = document.getElementById('BankAccountToDropdown');

            let isManualMode = false;
            let validationTimeout;

            // Function to show/hide bank account fields based on payment type
            function toggleBankAccountFields(paymentType) {
                if (paymentType === 'inter_bank_client') {
                    // Show two dropdowns for inter bank transfer
                    singleBankAccountField.style.display = 'none';
                    bankAccountFromField.style.display = '';
                    bankAccountToField.style.display = '';
                    
                    // Clear the single bank account selection
                    bankAccountDropdown.value = '';
                    bankAccountDropdown.removeAttribute('required');
                    
                    // Make both fields required for inter bank transfer
                    bankAccountFromDropdown.setAttribute('required', 'required');
                    bankAccountToDropdown.setAttribute('required', 'required');
                } else {
                    // Show single dropdown for other payment types
                    singleBankAccountField.style.display = '';
                    bankAccountFromField.style.display = 'none';
                    bankAccountToField.style.display = 'none';
                    
                    // Clear the dual bank account selections
                    bankAccountFromDropdown.value = '';
                    bankAccountToDropdown.value = '';
                    bankAccountFromDropdown.removeAttribute('required');
                    bankAccountToDropdown.removeAttribute('required');
                    
                    // Make single field required
                    bankAccountDropdown.setAttribute('required', 'required');
                    
                    // Update placeholder based on payment type
                    updateBankAccountPlaceholder(paymentType);
                }
            }

            // Function to populate Bank Account To dropdown with all ledger bank accounts
            function populateBankAccountTo(sourceBankAccountId) {
                if (!sourceBankAccountId || currentPaymentTypeInput.value !== 'inter_bank_client') {
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }

                // Show loading state
                bankAccountToDropdown.innerHTML = '<option value="" disabled selected>Loading ledger bank accounts...</option>';

                fetch('/transactions/ledger-bank-accounts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    },
                    body: JSON.stringify({
                        bank_account_id: sourceBankAccountId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear existing options
                    bankAccountToDropdown.innerHTML = '<option value="" disabled selected>Select Destination Bank Account</option>';
                    
                    if (data.success && data.ledger_bank_accounts && data.ledger_bank_accounts.length > 0) {
                        // Group accounts by client
                        data.ledger_bank_accounts.forEach(clientGroup => {
                            // Add client header
                            const headerOption = document.createElement('option');
                            headerOption.value = '';
                            headerOption.disabled = true;
                            headerOption.textContent = `--- ${clientGroup.client_name} ---`;
                            headerOption.style.fontWeight = 'bold';
                            headerOption.style.backgroundColor = '#f8f9fa';
                            bankAccountToDropdown.appendChild(headerOption);
                            
                            // Add ledger information as disabled options
                            if (clientGroup.ledgers && clientGroup.ledgers.length > 0) {
                                clientGroup.ledgers.forEach(ledger => {
                                    const ledgerOption = document.createElement('option');
                                    ledgerOption.value = '';
                                    ledgerOption.disabled = true;
                                    ledgerOption.textContent = `    Ledger: ${ledger.Ledger_Ref} (${ledger.Matter || 'No Matter'})`;
                                    ledgerOption.style.fontSize = '0.9em';
                                    ledgerOption.style.color = '#6c757d';
                                    bankAccountToDropdown.appendChild(ledgerOption);
                                });
                            }
                            
                            // Add bank accounts for this client
                            if (clientGroup.bank_accounts && clientGroup.bank_accounts.length > 0) {
                                clientGroup.bank_accounts.forEach(bankAccount => {
                                    const option = document.createElement('option');
                                    option.value = bankAccount.Bank_Account_ID;
                                    option.textContent = `    ${bankAccount.Bank_Name}${bankAccount.Account_Number ? ' - ' + bankAccount.Account_Number : ''} (${bankAccount.Bank_Type})`;
                                    option.style.paddingLeft = '20px';
                                    bankAccountToDropdown.appendChild(option);
                                });
                            }
                        });
                    } else {
                        bankAccountToDropdown.innerHTML = '<option value="" disabled selected>No ledger bank accounts found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching ledger bank accounts:', error);
                    bankAccountToDropdown.innerHTML = '<option value="" disabled selected>Error loading bank accounts</option>';
                });
            }

            // Handle Bank Account From selection for inter bank transfers
            bankAccountFromDropdown.addEventListener('change', function() {
                const selectedBankAccountId = this.value;
                if (selectedBankAccountId && currentPaymentTypeInput.value === 'inter_bank_client') {
                    populateBankAccountTo(selectedBankAccountId);
                } else {
                    // Clear Bank Account To dropdown
                    bankAccountToDropdown.innerHTML = '<option value="" disabled selected>Select Destination Bank Account</option>';
                }
            });

            // Handle payment type button clicks
            paymentTypeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const paymentType = this.dataset.paymentType;

                    // Remove active class from all buttons
                    paymentTypeButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.classList.add('custom-hover');
                        btn.style.backgroundColor = '#1b598c';
                    });

                    // Add active class to clicked button
                    this.classList.add('active');
                    this.classList.remove('custom-hover');
                    this.style.backgroundColor = '#72b3dc';

                    // Update hidden input
                    currentPaymentTypeInput.value = paymentType;

                    // Toggle bank account fields based on payment type
                    toggleBankAccountFields(paymentType);

                    // Generate new auto code via AJAX
                    generateAutoCodeAjax(paymentType);

                    // Clear validation message when switching payment types
                    clearValidationMessage();

                    // If inter bank client is selected and there's a bank account selected, populate bank account to
                    if (paymentType === 'inter_bank_client') {
                        const selectedBankAccountId = bankAccountFromDropdown.value;
                        if (selectedBankAccountId) {
                            populateBankAccountTo(selectedBankAccountId);
                        }
                    }

                    // Reload the page with new payment type to update bank accounts
                    setTimeout(() => {
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('payment_type', paymentType);
                        window.location.href = currentUrl.toString();
                    }, 1000);
                });
            });

            // Handle ledger reference selection (Client Ledger dropdown)
            ledgerRefDropdown.addEventListener('change', function() {
                const selectedLedgerRef = this.value;
                if (selectedLedgerRef) {
                    fetchLedgerDetails(selectedLedgerRef);
                } else {
                    // Hide ledger details if no selection
                    document.getElementById('ledgerDetails').style.display = 'none';
                }
            });

            // Sync visible input with hidden field
            function syncTransactionCode() {
                hiddenTransactionCode.value = entryRefInput.value;
            }

            // Handle input changes with better validation
            entryRefInput.addEventListener('input', function() {
                syncTransactionCode();

                if (isManualMode) {
                    clearTimeout(validationTimeout);

                    const code = this.value.trim();
                    if (code.length >= 3) {
                        showValidationMessage('Checking availability...', 'info');
                        validationTimeout = setTimeout(() => {
                            validateTransactionCode(code);
                        }, 500); // Debounce for 500ms
                    } else {
                        clearValidationMessage();
                    }
                }
            });

            // Handle manual editing toggle
            toggleManualEditBtn.addEventListener('click', function() {
                isManualMode = !isManualMode;

                if (isManualMode) {
                    entryRefInput.removeAttribute('readonly');
                    entryRefInput.style.backgroundColor = '#ffffff';
                    entryRefInput.focus();
                    entryRefInput.select(); // Select all text for easy editing
                    this.innerHTML = '<i class="fas fa-lock"></i> Lock Auto';
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-warning');
                    showValidationMessage(
                        'Manual editing enabled. Code will be validated when you stop typing.', 'info');
                } else {
                    entryRefInput.setAttribute('readonly', 'readonly');
                    entryRefInput.style.backgroundColor = '#f5f5f5';
                    this.innerHTML = '<i class="fas fa-edit"></i> Edit Manual';
                    this.classList.remove('btn-warning');
                    this.classList.add('btn-outline-secondary');
                    clearValidationMessage();
                }
            });

            // Handle generate new code
            generateNewCodeBtn.addEventListener('click', function() {
                const paymentType = currentPaymentTypeInput.value;

                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

                generateAutoCodeAjax(paymentType)
                    .finally(() => {
                        // Reset button state
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-refresh"></i> Generate New';
                    });

                // Reset to auto mode if in manual mode
                if (isManualMode) {
                    toggleManualEditBtn.click();
                }
            });

            function fetchLedgerDetails(ledgerRef) {
                // Show loading state
                const ledgerDetailsPanel = document.getElementById('ledgerDetails');
                ledgerDetailsPanel.style.display = 'block';
                ledgerDetailsPanel.innerHTML =
                    '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading ledger details...</div>';

                fetch(`/transactions/ledger-details/${encodeURIComponent(ledgerRef)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        // Restore original HTML structure and update with data
                        ledgerDetailsPanel.innerHTML = `
                    <h6><strong><span id="clientName">${data.Full_Name || 'N/A'}</span></strong></h6>
                    <p><strong>File Name:</strong> <span id="fileFullName">${data.Full_Name || 'N/A'}</span></p>
                    <p><strong>Address:</strong> <span id="clientAddress">${data.Full_Address || 'N/A'}</span></p>
                    <a id="clientledgerLink" href="#">
                        <p><strong>Ledger Ref:</strong> <span id="ledgerRef">${data.Ledger_Ref || 'N/A'}</span></p>
                    </a>
                    <p><strong>Matter:</strong> <span id="matter">${data.Matter || 'N/A'}</span></p>
                    <p><strong>Sub Matter:</strong> <span id="subMatter">${data.Sub_Matter || 'N/A'}</span></p>
                    <p><strong>Client Ledger Balance:</strong> <span id="clientLedgerBalance">${data.Client_Ledger_Balance || '0.00'}</span></p>
                    <p><strong>Office Ledger Balance:</strong> <span id="officeLedgerBalance">${data.Office_Ledger_Balance || '0.00'}</span></p>
                `;
                    })
                    .catch(error => {
                        console.error('Error fetching ledger details:', error);
                        ledgerDetailsPanel.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading ledger details: ${error.message}
                    </div>
                `;
                    });
            }

            function validateTransactionCode(code) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    showValidationMessage('CSRF token not found', 'error');
                    return;
                }

                fetch('/transactions/check-code-unique', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                        },
                        body: JSON.stringify({
                            transaction_code: code
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.exists) {
                                showValidationMessage('Transaction code already exists!', 'error');
                            } else {
                                showValidationMessage('Transaction code is available', 'success');
                            }
                        } else {
                            showValidationMessage(data.message || 'Validation failed', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error validating transaction code:', error);
                        showValidationMessage('Error checking transaction code', 'error');
                    });
            }

            function showValidationMessage(message, type) {
                const iconClass = type === 'error' ? 'exclamation-circle' :
                    type === 'success' ? 'check-circle' : 'info-circle';
                const textClass = type === 'error' ? 'danger' :
                    type === 'success' ? 'success' : 'info';

                codeValidationMessage.innerHTML = `
            <small class="text-${textClass}">
                <i class="fas fa-${iconClass}"></i>
                ${message}
            </small>
        `;
            }

            function clearValidationMessage() {
                codeValidationMessage.innerHTML = '';
            }

            function generateAutoCodeAjax(paymentType) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    generateAutoCodeFallback(paymentType);
                    return Promise.reject('CSRF token not found');
                }

                return fetch('/transactions/generate-auto-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                        },
                        body: JSON.stringify({
                            payment_type: paymentType
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.auto_code) {
                            entryRefInput.value = data.auto_code;
                            syncTransactionCode();
                            clearValidationMessage();
                            showValidationMessage('New code generated successfully', 'success');
                            setTimeout(clearValidationMessage, 3000);
                        } else {
                            throw new Error(data.message || 'Failed to generate auto code');
                        }
                    })
                    .catch(error => {
                        console.error('Error generating auto code:', error);
                        // Fallback to client-side generation
                        generateAutoCodeFallback(paymentType);
                    });
            }

            function generateAutoCodeFallback(paymentType) {
                // Fallback client-side auto code generation
                const today = new Date();
                const year = today.getFullYear().toString().substr(-2);
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                const dateStr = year + month + day;

                let prefix = '';
                switch (paymentType) {
                    case 'inter_bank_client':
                        prefix = 'BTBC';
                        break;
                    case 'inter_ledger':
                        prefix = 'LTLC';
                        break;
                    case 'payment':
                        prefix = 'PAYC';
                        break;
                    case 'receipt':
                        prefix = 'RECC';
                        break;
                    case 'cheque':
                        prefix = 'CHQC';
                        break;
                    default:
                        prefix = 'TXN';
                }

                // Generate 2-digit random number with better randomness
                const randomNum = Math.floor(Math.random() * 90) + 10; // 10-99
                const sequence = String(randomNum).padStart(2, '0');

                const autoCode = prefix + dateStr + sequence;
                entryRefInput.value = autoCode;
                syncTransactionCode();
                showValidationMessage('Code generated (fallback method)', 'info');
            }

            function updateBankAccountPlaceholder(paymentType) {
                const placeholder = bankAccountDropdown.querySelector('option[value=""]');
                if (placeholder) {
                    if (paymentType === 'payment') {
                        placeholder.textContent = 'Payment Bank Account';
                    } else {
                        placeholder.textContent = 'Client Bank Account';
                    }
                }
            }

            // Initialize readonly state
            entryRefInput.setAttribute('readonly', 'readonly');

            // Initialize on page load
            const currentPaymentType = currentPaymentTypeInput.value;
            toggleBankAccountFields(currentPaymentType);
            updateBankAccountPlaceholder(currentPaymentType);

            // Sync on page load
            syncTransactionCode();

            // If there's an old ledger ref selected, fetch its details
            const selectedLedgerRef = ledgerRefDropdown.value;
            if (selectedLedgerRef) {
                fetchLedgerDetails(selectedLedgerRef);
            }

            // If there's a selected bank account from and inter bank client is selected, populate bank account to
            const selectedBankAccountFromId = bankAccountFromDropdown.value;
            if (selectedBankAccountFromId && currentPaymentType === 'inter_bank_client') {
                populateBankAccountTo(selectedBankAccountFromId);
            }

            // Form submission validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const transactionCode = entryRefInput.value.trim();
                    if (!transactionCode) {
                        e.preventDefault();
                        showValidationMessage('Transaction code is required', 'error');
                        entryRefInput.focus();
                        return false;
                    }

                    // Additional validation for inter bank client transfers
                    if (currentPaymentTypeInput.value === 'inter_bank_client') {
                        const bankAccountFrom = bankAccountFromDropdown.value;
                        const bankAccountTo = bankAccountToDropdown.value;
                        
                        if (!bankAccountFrom) {
                            e.preventDefault();
                            alert('Please select a source bank account');
                            bankAccountFromDropdown.focus();
                            return false;
                        }
                        
                        if (!bankAccountTo) {
                            e.preventDefault();
                            alert('Please select a destination bank account');
                            bankAccountToDropdown.focus();
                            return false;
                        }
                        
                        if (bankAccountFrom === bankAccountTo) {
                            e.preventDefault();
                            alert('Source and destination bank accounts cannot be the same');
                            bankAccountToDropdown.focus();
                            return false;
                        }
                    }

                    // Ensure hidden field is updated
                    syncTransactionCode();
                });
            }
        });
    </script>
@endsection