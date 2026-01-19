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

    .custom-hover:hover,
    .custom-hover:focus {
        background-color: #72b3dc !important;
        color: #fff !important;
    }

    .invoice-number-highlight {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        font-weight: bold;
    }

    .sales-invoice-section {
        animation: slideDown 0.4s ease-in-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            max-height: none;
            overflow: visible;
            transform: translateY(0);
        }
    }

    .card-header.bg-success {
        background-color: #198754 !important;
    }

    .card-header.bg-primary {
        background-color: #0d6efd !important;
    }

    .remove-item-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #generateBillQuestion:hover {
        color: #0d6efd !important;
        text-decoration: none !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .fade-out {
        animation: fadeOut 0.3s ease-in-out;
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    /* Enhanced table styling for exact design match */
    .invoice-table {
        margin-bottom: 0;
        border: 1px solid #dee2e6 !important;
    }

    .invoice-table th {
        background-color: #4682B4 !important;
        color: white !important;
        border-color: #4682B4 !important;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px 8px;
        vertical-align: middle;
    }

    .invoice-table td {
        padding: 8px;
        vertical-align: middle;
        border-color: #dee2e6;
    }

    .invoice-table .border-0 {
        border: none !important;
        background-color: transparent;
        box-shadow: none !important;
    }

    .invoice-table .border-0:focus {
        border: none !important;
        box-shadow: none !important;
        background-color: #f8f9fa;
    }

    /* Better input alignment */
    .text-end {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    /* Summary card enhancements */
    .card.bg-light.border-primary {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {

        .invoice-items-section .table th,
        .invoice-items-section .table td {
            padding: 6px 4px;
            font-size: 0.85rem;
        }

        .invoice-items-section .form-control-sm {
            font-size: 0.8rem;
        }
    }

    /* CHANGE 1: Added CSS class for bill generation container visibility control */
    .bill-generation-container {
        display: none;
        /* Initially hidden */
    }

    /* Additional CSS for Notes Functionality */
    .notes-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .note-item {
        animation: slideInFromRight 0.3s ease-in-out;
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .note-item:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transition: box-shadow 0.3s ease;
    }

    .note-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .note-display {
        border-left: 4px solid #28a745;
        position: relative;
    }

    .note-display:before {
        content: '';
        position: absolute;
        left: -8px;
        top: 10px;
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
        border-right: 6px solid #28a745;
    }

    .note-meta {
        font-size: 0.85rem;
    }

    .remove-note-btn:hover {
        background-color: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
    }

    .save-note-btn {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .save-note-btn:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .cancel-note-btn:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }

    @keyframes slideInFromRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Enhanced item row animations */
    .invoice-item-row {
        transition: background-color 0.3s ease;
    }

    .invoice-item-row:hover {
        background-color: #f8f9fa;
    }

    /* Notification styles */
    .alert.position-fixed {
        animation: slideInFromTop 0.3s ease-in-out;
    }

    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Button enhancements */
    #addInvoiceItemBtn,
    #submitItemBtn,
    #addNoteBtn {
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    #addInvoiceItemBtn:hover,
    #submitItemBtn:hover,
    #addNoteBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Keyboard shortcuts tooltip */
    .keyboard-shortcuts {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.8rem;
        display: none;
    }

    .show-shortcuts .keyboard-shortcuts {
        display: block;
    }

    /* Enhanced Rich Text Editor Styles */
    .rich-text-toolbar {
        border: 1px solid #dee2e6;
        border-bottom: none;
        background-color: #f8f9fa;
        padding: 8px;
        border-radius: 6px 6px 0 0;
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .rich-text-editor {
        border: 1px solid #dee2e6;
        border-radius: 0 0 6px 6px;
        min-height: 120px;
        max-height: 300px;
        overflow-y: auto;
        padding: 12px;
        background-color: white;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.5;
    }

    .rich-text-editor:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .rich-text-editor:empty:before {
        content: attr(data-placeholder);
        color: #6c757d;
        font-style: italic;
        pointer-events: none;
    }

    .format-btn {
        border: 1px solid #dee2e6;
        background-color: white;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        color: #495057;
        transition: all 0.2s ease;
    }

    .format-btn:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }

    .format-btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    .table-insert-container {
        position: relative;
        display: inline-block;
    }

    .grid-selector {
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: none;
        z-index: 1000;
        min-width: 280px;
    }

    .grid-selector.show {
        display: block;
    }

    .grid-container {
        display: grid;
        grid-template-columns: repeat(10, 20px);
        gap: 2px;
        margin: 10px 0;
    }

    .grid-cell {
        width: 20px;
        height: 20px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: background-color 0.1s ease;
    }

    .grid-cell:hover,
    .grid-cell.highlight {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .custom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .custom-modal.show {
        display: flex;
    }

    .custom-modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
    }

    /* Table Styles in Rich Text Editor */
    .rich-text-editor table {
        border-collapse: collapse;
        width: 100%;
        margin: 10px 0;
        border: 1px solid #dee2e6;
    }

    .rich-text-editor table th,
    .rich-text-editor table td {
        border: 1px solid #dee2e6;
        padding: 8px 12px;
        text-align: left;
        min-width: 50px;
    }

    .rich-text-editor table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .rich-text-editor table td:focus,
    .rich-text-editor table th:focus {
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
    }

    /* Enhanced Success Message */
    .success-message-enhanced {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 6px;
        padding: 10px 15px;
        z-index: 3000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }

    .success-message-enhanced.show {
        opacity: 1;
        transform: translateX(0);
    }
</style>

@section('content')
    @php
        // Extract prefix and starting numeric min from the current autoCode
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="page-title mb-0">
                                <h4 class="page-title">Banking</h4>
                                <div class="page-title mb-3">
                                    <span
                                        onclick="window.location='{{ route('transactions.index', ['view' => 'day_book']) }}'"
                                        class="page-title me-2" style="cursor: pointer;">
                                        Banking
                                    </span>
                                    <span class="page-title" style="text-decoration: underline;">
                                        Client Account
                                    </span>

                                </div>
                            </div>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                        </div>

                        <div class="card-body">

                            <div class="col-12 mb-2">

                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group" style="max-width: 200px;">
                                        <span class="input-group-text bg-light fw-bold"
                                            id="codePrefix">{{ $currentPrefix }}</span>
                                        <input type="text" id="codeSuffix"
                                            class="form-control @error('Transaction_Code') is-invalid @enderror"
                                            value="{{ str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT) }}"
                                            inputmode="numeric" pattern="\d{{ $suffixLen }}" autocomplete="off"
                                            aria-describedby="codeHelp codeValidationMessage">
                                    </div>
                                </div>
                                <div id="codeValidationMessage" class="mt-1"></div>

                                @error('Transaction_Code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Payment Type Selection -->
                            <div class="payment-type-selection card-body pb-0">

                                @if ($type === 'client')
                                    <!-- Client Account Payment Types -->
                                    <div class="d-flex flex-wrap gap-2 mb-1">
                                        <button type="button"
                                            class="btn-simple {{ $paymentType === 'inter_bank_client' ? 'active' : 'custom-hover' }}"
                                            data-payment-type="inter_bank_client"
                                            style="background-color: {{ $paymentType === 'inter_bank_client' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                            Inter Bank Client
                                        </button>
                                        <button type="button"
                                            class="btn-simple {{ $paymentType === 'inter_ledger' ? 'active' : 'custom-hover' }}"
                                            data-payment-type="inter_ledger"
                                            style="background-color: {{ $paymentType === 'inter_ledger' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                            Inter Ledger
                                        </button>
                                        <button type="button"
                                            class="btn-simple {{ $paymentType === 'payment' ? 'active' : 'custom-hover' }}"
                                            data-payment-type="payment"
                                            style="background-color: {{ $paymentType === 'payment' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                            Payment
                                        </button>
                                        <button type="button"
                                            class="btn-simple {{ $paymentType === 'receipt' ? 'active' : 'custom-hover' }}"
                                            data-payment-type="receipt"
                                            style="background-color: {{ $paymentType === 'receipt' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                            Receipt
                                        </button>
                                        <button type="button"
                                            class="btn-simple {{ $paymentType === 'cheque' ? 'active' : 'custom-hover' }}"
                                            data-payment-type="cheque"
                                            style="background-color: {{ $paymentType === 'cheque' ? '#72b3dc' : '#1b598c' }}; color: #fff; border:none;">
                                            Cheque
                                        </button>
                                    </div>
                                @endif

                            </div>

                            {{-- Form Row: Transaction Form + Ledger Details --}}
                            <div class="row p-2">
                                {{-- Left Column: Transaction Form --}}
                                <div class="col-md-4">
                                    <x-form method="POST" action="transactions.store">
                                        {{-- Hidden Fields --}}
                                        <input type="hidden" id="hiddenTransactionCode" name="Transaction_Code"
                                            value="{{ old('Transaction_Code', $autoCode) }}">
                                        <input type="hidden" id="currentPaymentType" name="current_payment_type"
                                            value="{{ $paymentType }}">
                                        <input type="hidden" name="account_type" value="{{ $type }}">
                                        <input type="hidden" name="Paid_In_Out" value="1">

                                        {{-- Date --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Date</label>
                                            <div class="col-sm-4">
                                                <input type="date" name="Transaction_Date" style="width: 290px;"
                                                    value="{{ old('Transaction_Date', date('Y-m-d')) }}"
                                                    class=" @error('Transaction_Date') is-invalid @enderror">
                                                @error('Transaction_Date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Single Bank Account Field --}}
                                        <div id="singleBankAccountField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Bank Account</label>
                                            <div class="col-sm-4">
                                                <select name="Bank_Account_ID" id="BankAccountDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Bank_Account_ID') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Bank Account</option>
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

                                        {{-- Bank Account From Field --}}
                                        <div id="bankAccountFromField" class="mb-1 row" style="display: none;">

                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Bank Acc
                                                From</label>

                                            <div class="col-sm-4">
                                                <select name="Bank_Account_From_ID" id="BankAccountFromDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Bank_Account_From_ID') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Source Bank Account
                                                    </option>
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

                                        {{-- Bank Account To Field --}}
                                        <div id="bankAccountToField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Bank Acc To</label>
                                            <div class="col-sm-4">
                                                <select name="Bank_Account_To_ID" id="BankAccountToDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Bank_Account_To_ID') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Destination Bank
                                                        Account</option>
                                                </select>
                                                @error('Bank_Account_To_ID')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Client Ledger From Field --}}
                                        <div id="clientLedgerFromField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Client Led
                                                From</label>
                                            <div class="col-sm-4">
                                                <select name="Ledger_Ref_From" id="LedgerRefFromDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Ledger_Ref_From') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Source Ledger Reference
                                                    </option>
                                                    @foreach ($ledgerRefs as $ledgerRef)
                                                        <option value="{{ $ledgerRef->Ledger_Ref }}"
                                                            {{ old('Ledger_Ref_From') == $ledgerRef->Ledger_Ref ? 'selected' : '' }}>
                                                            {{ $ledgerRef->Ledger_Ref }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('Ledger_Ref_From')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Client Ledger To Field --}}
                                        <div id="clientLedgerToField" class="mb-1 row" style="display: none;">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Client Led To</label>
                                            <div class="col-sm-4">
                                                <select name="Ledger_Ref_To" id="LedgerRefToDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Ledger_Ref_To') is-invalid @enderror">
                                                    <option value="" disabled selected>Select Destination Ledger
                                                        Reference</option>
                                                </select>
                                                @error('Ledger_Ref_To')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Single Client Ledger Field --}}
                                        <div id="singleClientLedgerField" class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Client Ledger</label>
                                            <div class="col-sm-4">
                                                <select name="Ledger_Ref" id="ledgerRefDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Ledger_Ref') is-invalid @enderror">
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
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Payment Type</label>
                                            <div class="col-sm-4">
                                                <select name="Payment_Type_ID" id="PaymentTypeDropdown"
                                                    style="width: 290px; height: 25px;"
                                                    class=" @error('Payment_Type_ID') is-invalid @enderror">
                                                    <option value="" selected disabled>Select Payment Type</option>
                                                    <!-- Options will be populated by JavaScript based on selected transaction type -->
                                                </select>
                                                @error('Payment_Type_ID')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Payment Ref --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Payment Ref</label>
                                            <div class="col-sm-4">
                                                <input type="text" style="width: 290px;" name="Payment_Ref"
                                                    value="{{ old('Payment_Ref') }}"
                                                    class=" @error('Payment_Ref') is-invalid @enderror"
                                                    placeholder="Payment Reference">
                                                @error('Payment_Ref')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Amount --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Amount</label>
                                            <div class="col-sm-4">
                                                <input type="number" name="Amount" style="width: 290px;"
                                                    value="{{ old('Amount') }}"
                                                    class=" @error('Amount') is-invalid @enderror" placeholder="Amount"
                                                    step="0.01" min="0.01">
                                                @error('Amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Description --}}
                                        <div class="mb-1 row">
                                            <label class="col-sm-3 col-form-label form-label-grey"
                                                style="width: 115px;">Description</label>
                                            <div class="col-sm-4">
                                                <textarea name="Description" rows="1" style="width: 290px;"
                                                    class=" @error('Description') is-invalid @enderror" placeholder="Transaction Description">{{ old('Description') }}</textarea>
                                                @error('Description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Submit Buttons --}}
                                        <div class="row mt-3">
                                            <div class="col-sm-7 offset-sm-1 d-flex justify-content-start gap-3">
                                                <button type="submit" class="btn teal-custom px-4">Save</button>
                                                <a href="{{ url()->previous() }}" class="btn btn-warning px-4">Cancel</a>
                                            </div>
                                        </div>
                                    </x-form>
                                </div>

                                {{-- Right Column: Ledger Details Panel --}}
                                <div class="col-md-8">
                                    <div id="ledgerDetails" class="border p-3 bg-light" style="display: none;">
                                        <h6><strong><span id="clientName"></span></strong></h6>
                                        <p><strong>File Name:</strong> <span id="fileFullName"></span></p>
                                        <p><strong>Address:</strong> <span id="clientAddress"></span></p>
                                        <a id="clientledgerLink" href="#">
                                            <p><strong>Ledger Ref:</strong> <span id="ledgerRef"></span></p>
                                        </a>
                                        <p><strong>Matter:</strong> <span id="matter"></span></p>
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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CRITICAL: Payment Types Data MUST be defined BEFORE the main JavaScript --}}
    <script type="text/javascript">
        // Pass payment types data to JavaScript - MUST BE DEFINED FIRST
        window.paymentTypesData = {
            receipt: @json($receiptPaymentTypes ?? []),
            payment: @json($paymentPaymentTypes ?? []),
            all: @json($allPaymentTypes ?? [])
        };

        // Store the old selected value for form validation
        window.oldPaymentTypeId = "{{ old('Payment_Type_ID') }}";

        // Debug: Log the data to console
        console.log('Payment Types Data:', window.paymentTypesData);
    </script>

   <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get account type from backend
        const accountType = '{{ $type }}';

        // DOM Elements
        const paymentTypeButtons = document.querySelectorAll('.btn-simple');
        const entryRefInput = document.getElementById('entryRefInput');
        const hiddenTransactionCode = document.getElementById('hiddenTransactionCode');
        const currentPaymentTypeInput = document.getElementById('currentPaymentType');
        const generateNewCodeBtn = document.getElementById('generateNewCode');
        const codeValidationMessage = document.getElementById('codeValidationMessage');

        // CHANGE 3: Added elements for bill generation visibility control
        const generateBillQuestion = document.getElementById('generateBillQuestion');
        const generateBillContainer = document.querySelector('.bill-generation-container');
        const paymentTypeDropdown = document.getElementById('PaymentTypeDropdown');

        // Bank Account Elements
        const singleBankAccountField = document.getElementById('singleBankAccountField');
        const bankAccountFromField = document.getElementById('bankAccountFromField');
        const bankAccountToField = document.getElementById('bankAccountToField');
        const bankAccountDropdown = document.getElementById('BankAccountDropdown');
        const bankAccountFromDropdown = document.getElementById('BankAccountFromDropdown');
        const bankAccountToDropdown = document.getElementById('BankAccountToDropdown');

        // Ledger Elements
        const singleClientLedgerField = document.getElementById('singleClientLedgerField');
        const clientLedgerFromField = document.getElementById('clientLedgerFromField');
        const clientLedgerToField = document.getElementById('clientLedgerToField');
        const ledgerRefDropdown = document.getElementById('ledgerRefDropdown');
        const ledgerRefFromDropdown = document.getElementById('LedgerRefFromDropdown');
        const ledgerRefToDropdown = document.getElementById('LedgerRefToDropdown');

        // Client Account Logic: Set default payment type based on account type
        if (accountType === 'client') {
            if (!currentPaymentTypeInput.value) {
                currentPaymentTypeInput.value = 'payment';
            }
        } else if (accountType === 'office') {
            if (!currentPaymentTypeInput.value) {
                currentPaymentTypeInput.value = 'payment';
            }
        }

        // Handle ledger reference selection
        if (ledgerRefDropdown) {
            ledgerRefDropdown.addEventListener('change', function() {
                const selectedLedgerRef = this.value;
                if (selectedLedgerRef) {
                    fetchLedgerDetails(selectedLedgerRef);
                }
            });
        }

        // CHANGE 4: Added function to check bill generation visibility
        function checkBillGenerationVisibility() {
            const currentPaymentType = currentPaymentTypeInput.value;
            const selectedPaymentTypeId = paymentTypeDropdown ? paymentTypeDropdown.value : '';

            // Show only if payment type is 'payment' or 'receipt' AND a payment type is selected from dropdown
            const shouldShow = (currentPaymentType === 'payment' || currentPaymentType === 'receipt') &&
                selectedPaymentTypeId;

            if (generateBillContainer) {
                if (shouldShow) {
                    generateBillContainer.style.display = 'block';
                } else {
                    generateBillContainer.style.display = 'none';
                    // Also hide the invoice section if it's open
                    const salesInvoiceSection = document.getElementById('salesInvoiceSection');
                    if (salesInvoiceSection && salesInvoiceSection.style.display !== 'none') {
                        const hasInvoiceField = document.getElementById('hasInvoice');
                        salesInvoiceSection.style.display = 'none';
                        if (hasInvoiceField) hasInvoiceField.value = '0';
                        if (generateBillQuestion) generateBillQuestion.style.display = 'inline';
                    }
                }
            }
        }

        // Utility Functions
        function syncTransactionCode() {
            if (hiddenTransactionCode && entryRefInput) {
                hiddenTransactionCode.value = entryRefInput.value;
            }
        }

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

        // Payment Type Filtering Functions
        function updatePaymentTypeDropdown(transactionType) {
            const paymentTypeDropdown = document.getElementById('PaymentTypeDropdown');
            if (!paymentTypeDropdown) return;

            // Clear existing options except the first one
            paymentTypeDropdown.innerHTML = '<option value="" selected disabled>Select Payment Type</option>';

            // Check if paymentTypesData exists
            if (!window.paymentTypesData) {
                console.error('Payment types data not available');
                return;
            }

            // Determine which payment types to show based on transaction type
            let paymentTypesToShow = [];

            switch (transactionType) {
                case 'receipt':
                    paymentTypesToShow = window.paymentTypesData.receipt || [];
                    break;

                case 'payment':
                    paymentTypesToShow = window.paymentTypesData.payment || [];
                    break;

                case 'inter_bank_client':
                case 'inter_ledger':
                case 'cheque':
                default:
                    // For other transaction types, show all payment types
                    paymentTypesToShow = window.paymentTypesData.all || [];
                    break;
            }

            // Populate the dropdown with filtered payment types
            paymentTypesToShow.forEach(paymentType => {
                const option = document.createElement('option');
                option.value = paymentType.Payment_Type_ID;
                option.textContent = paymentType.Payment_Type_Name;

                // Restore old selected value if it exists and matches
                if (window.oldPaymentTypeId && window.oldPaymentTypeId == paymentType.Payment_Type_ID) {
                    option.selected = true;
                }

                paymentTypeDropdown.appendChild(option);
            });

            // Update dropdown label based on transaction type
            updatePaymentTypeLabel(transactionType);

            // CHANGE 5: Added visibility check after dropdown update
            setTimeout(() => {
                checkBillGenerationVisibility();
            }, 50);
        }

        function updatePaymentTypeLabel(transactionType) {
            const paymentTypeDropdown = document.getElementById('PaymentTypeDropdown');
            const placeholderOption = paymentTypeDropdown?.querySelector('option[value=""]');

            if (placeholderOption) {
                let labelText = 'Select Payment Type';

                switch (transactionType) {
                    case 'receipt':
                        labelText = 'Select Receipt Payment Type';
                        break;
                    case 'payment':
                        labelText = 'Select Payment Payment Type';
                        break;
                    default:
                        labelText = 'Select Payment Type';
                        break;
                }

                placeholderOption.textContent = labelText;
            }
        }

        // Generate Auto Code via AJAX
        function generateAutoCodeAjax(paymentType) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found');
                return Promise.reject('CSRF token not found');
            }

            return fetch('/transactions/generate-auto-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_type: paymentType,
                        account_type: accountType
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.auto_code) {
                        // Apply new code to prefix/suffix system
                        const m = /^([A-Z]+)(\d{6})$/.exec(data.auto_code || '');
                        const prefix = m ? m[1] : '';
                        const suffix = m ? m[2] : '';

                        const prefixEl = document.getElementById('codePrefix');
                        const suffixEl = document.getElementById('codeSuffix');
                        const fmtPrefixEl = document.getElementById('fmtPrefix');

                        if (prefixEl) prefixEl.textContent = prefix;
                        if (suffixEl) suffixEl.value = suffix;
                        if (fmtPrefixEl) fmtPrefixEl.textContent = prefix;

                        if (typeof window.setMinSuffix === 'function') {
                            window.setMinSuffix(Number(suffix));
                        }

                        if (typeof window.syncTransactionCode === 'function') {
                            window.syncTransactionCode();
                        }

                        showValidationMessage('New code generated successfully', 'success');
                        setTimeout(clearValidationMessage, 3000);

                        return data.auto_code;
                    } else {
                        throw new Error(data.message || 'Failed to generate auto code');
                    }
                })
                .catch(error => {
                    console.error('Error generating auto code:', error);
                    showValidationMessage('Error: ' + error.message, 'error');
                    throw error;
                });
        }

        // Field Management Functions
        function resetAllFields() {
            const fields = [singleBankAccountField, bankAccountFromField, bankAccountToField,
                singleClientLedgerField, clientLedgerFromField, clientLedgerToField
            ];
            fields.forEach(field => {
                if (field) field.style.display = 'none';
            });

            const dropdowns = [bankAccountDropdown, bankAccountFromDropdown, bankAccountToDropdown,
                ledgerRefDropdown, ledgerRefFromDropdown, ledgerRefToDropdown
            ];
            dropdowns.forEach(dropdown => {
                if (dropdown) {
                    dropdown.value = '';
                    dropdown.removeAttribute('name');
                    dropdown.removeAttribute('required');
                }
            });
        }

        function populateBankAccountTo() {
            if (!bankAccountToDropdown || !bankAccountFromDropdown) return;
            bankAccountToDropdown.innerHTML =
                '<option value="" disabled selected>Select Destination Bank Account</option>';

            const fromOptions = bankAccountFromDropdown.querySelectorAll('option:not([value=""])');
            fromOptions.forEach(option => {
                const newOption = option.cloneNode(true);
                bankAccountToDropdown.appendChild(newOption);
            });

            filterBankAccountToOptions();
        }

        function populateClientLedgerTo() {
            if (!ledgerRefToDropdown || !ledgerRefFromDropdown) return;
            ledgerRefToDropdown.innerHTML =
                '<option value="" disabled selected>Select Destination Ledger Reference</option>';

            const fromOptions = ledgerRefFromDropdown.querySelectorAll('option:not([value=""])');
            fromOptions.forEach(option => {
                const newOption = option.cloneNode(true);
                ledgerRefToDropdown.appendChild(newOption);
            });

            filterClientLedgerToOptions();
        }

        function filterBankAccountToOptions() {
            if (!bankAccountFromDropdown || !bankAccountToDropdown) return;
            const fromValue = bankAccountFromDropdown.value;
            const toOptions = bankAccountToDropdown.querySelectorAll('option');

            toOptions.forEach(option => {
                if (option.value === '' || option.value !== fromValue) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    if (option.selected) bankAccountToDropdown.value = '';
                }
            });
        }

        function filterClientLedgerToOptions() {
            if (!ledgerRefFromDropdown || !ledgerRefToDropdown) return;
            const fromValue = ledgerRefFromDropdown.value;
            const toOptions = ledgerRefToDropdown.querySelectorAll('option');

            toOptions.forEach(option => {
                if (option.value === '' || option.value !== fromValue) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    if (option.selected) ledgerRefToDropdown.value = '';
                }
            });
        }

        function filterSingleBankAccountOptions(paymentType) {
            if (!bankAccountDropdown) return;
            const options = bankAccountDropdown.querySelectorAll('option');

            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = '';
                    return;
                }

                const bankTypeId = option.getAttribute('data-bank-type');
                if (['payment', 'receipt', 'cheque'].includes(paymentType)) {
                    if (bankTypeId === '1') {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                        if (option.selected) bankAccountDropdown.value = '';
                    }
                } else {
                    option.style.display = '';
                }
            });

            updateBankAccountPlaceholder(paymentType);
        }

        function updateBankAccountPlaceholder(paymentType) {
            const placeholder = bankAccountDropdown?.querySelector('option[value=""]');
            if (placeholder) {
                const text = ['payment', 'receipt', 'cheque'].includes(paymentType) ?
                    'Select Client Bank Account' :
                    'Select Bank Account';
                placeholder.textContent = text;
            }
        }

        // Setup Functions for Different Payment Types
        function setupInterBankClientFields() {
            if (bankAccountFromField) bankAccountFromField.style.display = '';
            if (bankAccountToField) bankAccountToField.style.display = '';
            if (singleClientLedgerField) singleClientLedgerField.style.display = '';

            if (bankAccountFromDropdown) {
                bankAccountFromDropdown.setAttribute('name', 'Bank_Account_From_ID');
                bankAccountFromDropdown.setAttribute('required', 'required');
            }
            if (bankAccountToDropdown) {
                bankAccountToDropdown.setAttribute('name', 'Bank_Account_To_ID');
                bankAccountToDropdown.setAttribute('required', 'required');
            }
            if (ledgerRefDropdown) {
                ledgerRefDropdown.setAttribute('name', 'Ledger_Ref');
                ledgerRefDropdown.setAttribute('required', 'required');
            }

            populateBankAccountTo();
        }

        function setupInterLedgerFields() {
            if (clientLedgerFromField) clientLedgerFromField.style.display = '';
            if (clientLedgerToField) clientLedgerToField.style.display = '';

            if (ledgerRefFromDropdown) {
                ledgerRefFromDropdown.setAttribute('name', 'Ledger_Ref_From');
                ledgerRefFromDropdown.setAttribute('required', 'required');
            }
            if (ledgerRefToDropdown) {
                ledgerRefToDropdown.setAttribute('name', 'Ledger_Ref_To');
                ledgerRefToDropdown.setAttribute('required', 'required');
            }

            populateClientLedgerTo();
        }

        function setupSingleFields(paymentType) {
            if (singleBankAccountField) singleBankAccountField.style.display = '';
            if (singleClientLedgerField) singleClientLedgerField.style.display = '';

            if (bankAccountDropdown) {
                bankAccountDropdown.setAttribute('name', 'Bank_Account_ID');
                bankAccountDropdown.setAttribute('required', 'required');
            }
            if (ledgerRefDropdown) {
                ledgerRefDropdown.setAttribute('name', 'Ledger_Ref');
                ledgerRefDropdown.setAttribute('required', 'required');
            }

            filterSingleBankAccountOptions(paymentType);
        }

        function toggleFieldsBasedOnPaymentType(paymentType) {
            resetAllFields();

            switch (paymentType) {
                case 'inter_bank_client':
                    setupInterBankClientFields();
                    break;
                case 'inter_ledger':
                    setupInterLedgerFields();
                    break;
                default:
                    setupSingleFields(paymentType);
                    break;
            }
        }

        // Event Listeners
        paymentTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const paymentType = this.dataset.paymentType;

                // Update button states
                paymentTypeButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.classList.add('custom-hover');
                    btn.style.backgroundColor = '#1b598c';
                });

                this.classList.add('active');
                this.classList.remove('custom-hover');
                this.style.backgroundColor = '#72b3dc';

                // Update payment type input
                currentPaymentTypeInput.value = paymentType;

                // Toggle fields based on payment type
                toggleFieldsBasedOnPaymentType(paymentType);
                updatePaymentTypeDropdown(paymentType);

                clearValidationMessage();

                // Generate new code and wait for it
                generateAutoCodeAjax(paymentType).catch(error => {
                    console.error('Error generating code:', error);
                });
            });
        });

        // CHANGE 7: Added event listener for Payment Type dropdown
        if (paymentTypeDropdown) {
            paymentTypeDropdown.addEventListener('change', function() {
                checkBillGenerationVisibility();
            });
        }

        // Add change event listeners
        if (bankAccountFromDropdown) {
            bankAccountFromDropdown.addEventListener('change', function() {
                if (currentPaymentTypeInput.value === 'inter_bank_client') {
                    filterBankAccountToOptions();
                }
            });
        }

        if (ledgerRefFromDropdown) {
            ledgerRefFromDropdown.addEventListener('change', function() {
                if (currentPaymentTypeInput.value === 'inter_ledger') {
                    filterClientLedgerToOptions();
                }
            });
        }

        // Generate New Code Button
        if (generateNewCodeBtn) {
            generateNewCodeBtn.addEventListener('click', function() {
                const currentType = currentPaymentTypeInput.value;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

                generateAutoCodeAjax(currentType)
                    .finally(() => {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-refresh"></i> Generate New';
                    });
            });
        }

        // Form Validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const formData = new FormData(this);
                const formObject = {};
                for (let [key, value] of formData.entries()) {
                    formObject[key] = value;
                }

                const currentType = currentPaymentTypeInput.value;
                const transactionCode = entryRefInput.value.trim();

                if (!transactionCode) {
                    e.preventDefault();
                    showValidationMessage('Transaction code is required', 'error');
                    entryRefInput.focus();
                    return false;
                }

                // Validation based on payment type and account type
                if (currentType === 'inter_bank_client') {
                    if (!formObject['Bank_Account_From_ID'] || !formObject['Bank_Account_To_ID']) {
                        e.preventDefault();
                        alert('Please select both source and destination bank accounts');
                        return false;
                    }
                    if (formObject['Bank_Account_From_ID'] === formObject['Bank_Account_To_ID']) {
                        e.preventDefault();
                        alert('Source and destination bank accounts cannot be the same');
                        return false;
                    }
                } else if (currentType === 'inter_ledger') {
                    if (!formObject['Ledger_Ref_From'] || !formObject['Ledger_Ref_To']) {
                        e.preventDefault();
                        alert('Please select both source and destination ledger references');
                        return false;
                    }
                    if (formObject['Ledger_Ref_From'] === formObject['Ledger_Ref_To']) {
                        e.preventDefault();
                        alert('Source and destination ledgers cannot be the same');
                        return false;
                    }
                } else {
                    // For single bank account fields
                    if (!formObject['Bank_Account_ID']) {
                        e.preventDefault();
                        alert('Please select a bank account');
                        return false;
                    }

                    // Validate client bank for specific payment types
                    if (['payment', 'receipt', 'cheque'].includes(currentType)) {
                        const selectedOption = bankAccountDropdown.querySelector(
                            `option[value="${formObject['Bank_Account_ID']}"]`);
                        if (selectedOption && selectedOption.getAttribute('data-bank-type') !== '1') {
                            e.preventDefault();
                            alert('Please select a Client Bank Account for ' + currentType +
                                ' transactions');
                            return false;
                        }
                    }
                }

                syncTransactionCode();
                return true;
            });
        }

        function fetchLedgerDetails(ledgerRef) {
            fetch(`/transactions/ledger-details/${ledgerRef}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }

                    // Update ledger details panel
                    document.getElementById('clientName').textContent = data.Full_Name || 'N/A';
                    document.getElementById('fileFullName').textContent = data.Full_Name || 'N/A';
                    document.getElementById('clientAddress').textContent = data.Full_Address || 'N/A';
                    document.getElementById('ledgerRef').textContent = data.Ledger_Ref || 'N/A';

                    // Format Matter and Sub Matter as "Matter - Sub Matter"
                    const matter = data.Matter || '';
                    const subMatter = data.Sub_Matter || '';

                    let formattedMatter = '';
                    if (matter && subMatter) {
                        formattedMatter = `${matter} - ${subMatter}`;
                    } else if (matter) {
                        formattedMatter = matter;
                    } else if (subMatter) {
                        formattedMatter = subMatter;
                    } else {
                        formattedMatter = 'N/A';
                    }

                    document.getElementById('matter').textContent = formattedMatter;
                    document.getElementById('clientLedgerBalance').textContent = data
                        .Client_Ledger_Balance || '0.00';
                    document.getElementById('officeLedgerBalance').textContent = data
                        .Office_Ledger_Balance || '0.00';

                    // Show the ledger details panel
                    document.getElementById('ledgerDetails').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching ledger details:', error);
                });
        }

        // Initialize
        const currentPaymentType = currentPaymentTypeInput.value;
        toggleFieldsBasedOnPaymentType(currentPaymentType);

        if (window.paymentTypesData) {
            updatePaymentTypeDropdown(currentPaymentType);
        } else {
            console.error('Payment types data not available for initialization');
        }

        syncTransactionCode();

        const selectedLedgerRef = ledgerRefDropdown ? ledgerRefDropdown.value : null;
        if (selectedLedgerRef) {
            fetchLedgerDetails(selectedLedgerRef);
        }

        setTimeout(() => {
            checkBillGenerationVisibility();
        }, 200);

        (function() {
            const SUFFIX_LEN = 6;
            const CHECK_URL = "{{ route('transactions.check-code-unique') }}";

            let codeMinSuffix = parseInt(
                (document.getElementById('fmtMin')?.textContent || '1').replace(/\D/g, ''),
                10
            );
            if (!Number.isFinite(codeMinSuffix)) codeMinSuffix = 1;

            let codeManual = false;

            const $suffix = document.getElementById('codeSuffix');
            const $msg = document.getElementById('codeValidationMessage');

            function normalizeSuffix() {
                if (!$suffix) return;
                let v = ($suffix.value || '').replace(/\D/g, '').slice(0, SUFFIX_LEN);

                if (codeManual) {
                    $suffix.value = v;
                    return;
                }

                const n = parseInt(v || '0', 10);
                if (!Number.isNaN(n) && n < codeMinSuffix) v = String(codeMinSuffix);
                $suffix.value = String(v || '').padStart(SUFFIX_LEN, '0');
            }

            function setMinSuffix(n) {
                codeMinSuffix = Number(n) || 1;
                const fmtMinEl = document.getElementById('fmtMin');
                if (fmtMinEl) {
                    fmtMinEl.textContent = String(codeMinSuffix).toString().padStart(SUFFIX_LEN, '0');
                }
            }

            function getFullCode() {
                const p = document.getElementById('codePrefix')?.textContent?.trim() || '';
                const s = document.getElementById('codeSuffix')?.value?.trim() || '';
                return p + s;
            }

            function syncTransactionCode() {
                const prefix = document.getElementById('codePrefix')?.textContent?.trim() || '';
                const suffix = document.getElementById('codeSuffix')?.value?.trim() || '';
                const fullCode = prefix + suffix;

                const hiddenField = document.getElementById('hiddenTransactionCode');
                if (hiddenField) {
                    hiddenField.value = fullCode;
                }

                const entryRef = document.getElementById('entryRefInput');
                if (entryRef) {
                    entryRef.value = fullCode;
                }
            }

            async function checkCodeUnique() {
                const full = getFullCode();
                if (!full) return;

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const res = await fetch(CHECK_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            transaction_code: full
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (data.exists) {
                            showValidationMessage('This code already exists. Please change the number.',
                                'error');
                        } else {
                            showValidationMessage('Code is available.', 'success');
                        }
                    }
                } catch (e) {
                    console.error('Uniqueness check failed:', e);
                }
            }

            // Event listeners
            if ($suffix) {
                $suffix.addEventListener('input', () => {
                    codeManual = true;
                    normalizeSuffix();
                    syncTransactionCode();
                    clearValidationMessage();
                });

                $suffix.addEventListener('blur', () => {
                    codeManual = false;
                    normalizeSuffix();
                    syncTransactionCode();
                    checkCodeUnique();
                });
            }

            // Expose functions globally
            window.normalizeSuffix = normalizeSuffix;
            window.syncTransactionCode = syncTransactionCode;
            window.setMinSuffix = setMinSuffix;

            // Initialize
            normalizeSuffix();
            syncTransactionCode();
        })();
    });
</script>
@endsection
