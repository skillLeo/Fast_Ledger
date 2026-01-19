@extends('admin.layout.app')

@section('content')
    <style>
        /* ============================================
                       TRANSACTION CODE STYLING
                       ============================================ */
        #transaction-code-0,
        /* Add IDs as needed */
        [id^="transaction-code-"] {
            font-family: 'Courier New', monospace;
            text-align: center;
        }

        [id^="transaction-code-"] .text-success {
            color: #28a745 !important;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        [id^="transaction-code-"] .text-muted {
            font-size: 11px;
            font-style: italic;
        }


        /* ============================================
                         CORE VARIABLES - Single Source of Truth
                    ============================================ */
        :root {
            --row-height: 54px;
            --row-gap: 20px;
            --border-color: #999;
        }

        /* ============================================
                        LEFT TABLE - Column Widths
                    ============================================ */
        table.left-table-wrapper.transaction-table {
            table-layout: fixed;
            width: 100%;
        }

        table.left-table-wrapper.transaction-table th:nth-child(1),
        table.left-table-wrapper.transaction-table td:nth-child(1) {
            width: 12%;
        }

        table.left-table-wrapper.transaction-table th:nth-child(2),
        table.left-table-wrapper.transaction-table td:nth-child(2) {
            width: 38%;
        }

        table.left-table-wrapper.transaction-table th:nth-child(3),
        table.left-table-wrapper.transaction-table td:nth-child(3) {
            width: 18%;
        }

        table.left-table-wrapper.transaction-table th:nth-child(4),
        table.left-table-wrapper.transaction-table td:nth-child(4) {
            width: 18%;
        }

        table.left-table-wrapper.transaction-table th:nth-child(5),
        table.left-table-wrapper.transaction-table td:nth-child(5) {
            width: 3%;
        }

        /* ============================================
                                                                RIGHT TABLE - Column Widths
                                                            ============================================ */
        table.right-table-wrapper.transaction-table {
            table-layout: fixed;
            width: 100%;
        }

        table.right-table-wrapper.transaction-table th:nth-child(1),
        table.right-table-wrapper.transaction-table td:nth-child(1) {
            width: 23%;
        }

        table.right-table-wrapper.transaction-table th:nth-child(2),
        table.right-table-wrapper.transaction-table td:nth-child(2) {
            width: 23%;
        }

        table.right-table-wrapper.transaction-table th:nth-child(3),
        table.right-table-wrapper.transaction-table td:nth-child(3) {
            width: 23%;
        }

        table.right-table-wrapper.transaction-table th:nth-child(4),
        table.right-table-wrapper.transaction-table td:nth-child(4) {
            width: 12%;
        }

        table.right-table-wrapper.transaction-table th:nth-child(5),
        table.right-table-wrapper.transaction-table td:nth-child(5) {
            width: 18%;
        }

        table.right-table-wrapper.transaction-table th:nth-child(6),
        table.right-table-wrapper.transaction-table td:nth-child(6) {
            width: 3%;
        }

        /* ============================================
                                                               CLIENT BANK (Type 1) - Entry Details Column
                                                               ============================================ */
        table.right-table-wrapper.transaction-table.bank-type-1 th:nth-child(2),
        table.right-table-wrapper.transaction-table.bank-type-1 td:nth-child(2) {
            width: 58%;
            /* Combined width: 23% + 23% + 12% */
        }

        table.right-table-wrapper.transaction-table.bank-type-1 th:nth-child(3),
        table.right-table-wrapper.transaction-table.bank-type-1 td:nth-child(3) {
            width: 18%;
            /* Trans Code */
        }

        table.right-table-wrapper.transaction-table.bank-type-1 th:nth-child(4),
        table.right-table-wrapper.transaction-table.bank-type-1 td:nth-child(4) {
            width: 3%;
            /* Menu */
        }

        /* ============================================
                                                                 MAIN CONTAINERS
                                                            ============================================ */
        .main-table-container,
        .custom-card .main-table-container,
        .card-body .main-table-container {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            margin-bottom: 20px !important;
            background: #ffffff !important;
        }

        .tables-wrapper {
            display: flex !important;
            gap: 12px !important;
            align-items: flex-start !important;
            width: 100% !important;
        }

        /* ============================================
                                                                                                                   BASE TABLE STRUCTURE
                                                                                                                   ============================================ */
        table.transaction-table,
        .custom-card table.transaction-table,
        .card-body table.transaction-table {
            border-collapse: separate !important;
            border-spacing: 0 var(--row-gap) !important;
            background-color: white !important;
            width: 100% !important;
            margin-top: calc(var(--row-gap) * -1);
        }

        /* ============================================
                                                                                                                   TABLE HEADERS
                                                                                                                   ============================================ */
        .transaction-table th,
        .custom-card .transaction-table th {
            background-color: #dff3f9 !important;
            color: #333;
            font-weight: 600;
            font-size: 12px;
            padding: 12px 2px;
            text-align: center;
            white-space: nowrap;
            height: 44px;
            border: 1px solid var(--border-color);
            border-right: none;
            box-sizing: border-box;
        }

        .transaction-table th:last-child,
        .custom-card .transaction-table th:last-child {
            border-right: 1px solid var(--border-color);
        }

        .menu-header {
            width: 40px;
            text-align: center;
            padding: 12px 0px;
        }

        /* ============================================
                                                                                                                   TABLE ROWS & CELLS
                                                                                                                   ============================================ */
        .transaction-table tbody tr,
        .custom-card .transaction-table tbody tr,
        .card-body .transaction-table tbody tr {
            height: var(--row-height) !important;
            max-height: var(--row-height) !important;
            min-height: var(--row-height) !important;
            background-color: white;
            transition: background-color 0.2s;
        }

        .transaction-table th,
        .transaction-table td,
        .custom-card .transaction-table th,
        .custom-card .transaction-table td {
            padding: 0px 0px;
            font-size: 14px;
            color: #333;
            background-color: white;
            border: 1px solid var(--border-color);
            border-right: none;
            white-space: nowrap;
            vertical-align: middle;
            height: var(--row-height) !important;
            box-sizing: border-box;
            overflow: hidden;
            text-align: center !important;
        }

        .transaction-table td:last-child,
        .custom-card .transaction-table td:last-child {
            border-right: 1px solid var(--border-color);
        }

        .transaction-row:hover,
        .custom-card .transaction-row:hover {
            background-color: #f0f8ff !important;
        }

        .row-saved {
            opacity: 0.6;
            background-color: #f8f9fa !important;
        }

        /* ============================================
                                                                                                                   STATUS COLUMN - CRITICAL ALIGNMENT
                                                                                                                   ============================================ */
        .status-header-wrapper {
            min-width: 90px;
            max-width: 90px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .status-header {
            background-color: #f0f1f1;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            padding: 0;
            text-align: center;
            height: var(--row-height);
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            margin-bottom: var(--row-gap);
            box-sizing: border-box;
        }

        .status-column,
        .custom-card .status-column,
        .card-body .status-column {
            display: flex;
            flex-direction: column;
            gap: var(--row-gap);
            min-width: 90px;
            max-width: 90px;
            flex-shrink: 0;
        }

        .status-column .btn,
        .custom-card .status-column .btn {
            height: var(--row-height) !important;
            min-height: var(--row-height) !important;
            max-height: var(--row-height) !important;
            flex-shrink: 0;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-sizing: border-box;
        }

        /* ============================================
                                                                                                                   STATUS BUTTONS
                                                                                                                   ============================================ */
        .btn-okay,
        .btn-allocate,
        .btn-success {
            height: var(--row-height) !important;
            min-width: 80px;
            border: none;
            border-radius: 0px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.2s;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-okay {
            background-color: #01677d;
            color: white;
        }

        .btn-allocate {
            background-color: #fd7e14;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-okay:hover,
        .btn-allocate:hover {
            opacity: 0.85;
        }

        /* ============================================
                                                                                                                   FORM INPUTS
                                                                                                                   ============================================ */
        .transaction-table input,
        .transaction-table textarea,
        .transaction-table select,
        .custom-card .transaction-table input,
        .custom-card .transaction-table textarea,
        .custom-card .transaction-table select {
            box-sizing: border-box;
            height: 36px;
            border: none;
            padding: 6px 8px;
            font-size: 14px;
            width: 100%;
            text-align: center !important;
        }

        .transaction-table textarea,
        .custom-card .transaction-table textarea {
            resize: none;
            overflow: hidden;
            line-height: 1.5;
        }

        .right-table-wrapper input,
        .right-table-wrapper select,
        .right-table-wrapper textarea,
        .custom-card .right-table-wrapper input,
        .custom-card .right-table-wrapper select,
        .custom-card .right-table-wrapper textarea {
            width: 100%;
            padding: 6px 2px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-family: inherit;
            height: 36px;
            text-align: center !important;
        }

        .right-table-wrapper input:focus,
        .right-table-wrapper select:focus,
        .right-table-wrapper textarea:focus,
        .custom-card .right-table-wrapper input:focus,
        .custom-card .right-table-wrapper select:focus,
        .custom-card .right-table-wrapper textarea:focus {
            outline: none;
            background-color: #f8f9fa;
        }

        .left-table-wrapper input[type="date"],
        .left-table-wrapper textarea,
        .custom-card .left-table-wrapper input[type="date"],
        .custom-card .left-table-wrapper textarea {
            pointer-events: none;
            user-select: none;
            cursor: default;
            background-color: transparent;
        }

        /* ============================================
                                                                                                                   MENU COLUMN
                                                                                                                   ============================================ */
        .menu-cell {
            text-align: center !important;
            padding: 0 !important;
            width: 100%;
            vertical-align: middle !important;
        }

        .three-dots {
            background: none;
            border: none;
            font-size: 18px;
            color: #666;
            cursor: pointer;
            line-height: 1;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .three-dots i {
            display: block;
            text-align: center;
        }

        .three-dots:hover {
            color: #333;
        }

        /* ============================================
                                                                                                                   UTILITY CLASSES
                                                                                                                   ============================================ */
        .account-info {
            color: #6c757d;
            font-size: 12px;
        }

        /* ============================================
                                                                                                                   RESPONSIVE DESIGN
                                                                                                                   ============================================ */
        @media (max-width: 1024px) {

            .main-table-container,
            .custom-card .main-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .status-column,
            .custom-card .status-column {
                flex-direction: row;
                padding-top: 0;
                flex-wrap: wrap;
                min-width: auto;
            }
        }

        @media (max-width: 768px) {

            .main-table-container,
            .custom-card .main-table-container {
                flex-direction: column;
            }

            .transaction-table th,
            .transaction-table td,
            .custom-card .transaction-table th,
            .custom-card .transaction-table td {
                font-size: 12px;
                padding: 6px 8px;
            }

            .status-column,
            .custom-card .status-column {
                padding-top: 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        <div class="card-header justify-content-between d-flex">
                            <h4 class="page-title">Banking</h4>
                        </div>

                        <div class="d-flex gap-4 mb-3">
                            <a href="#" class="nav-link-btn active" data-section="upload-transaction">Upload
                                Transactions</a>
                            <a href="#" class="nav-link-btn" data-section="manual-entry">Manual Entry</a>
                        </div>

                        {{-- Bank Account Info Section --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                @if ($bankAccount ?? false)
                                    <input type="hidden" name="bank_account_id" id="bank_account_id"
                                        value="{{ $bankAccount->Bank_Account_ID }}">

                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bank-logo me-2"
                                            style="width:48px;height:48px;border-radius:6px;background:#eef6fb;display:flex;align-items:center;justify-content:center;font-weight:700">
                                            {{ strtoupper(substr($bankAccount->Bank_Name, 0, 4)) }}
                                        </div>
                                        <div class="ms-2">
                                            <div class="fw-bold">
                                                Bank - {{ $bankAccount->Bank_Name }}
                                                <span
                                                    class="text-muted">({{ $bankAccount->bankAccountType->Bank_Type ?? 'Unknown type' }})</span>
                                            </div>
                                            <div class="account-info text-muted">
                                                Sort Code: {{ $bankAccount->Sort_Code ?? '—' }}
                                                &nbsp;&nbsp; Account No: {{ $bankAccount->Account_No ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">No bank account selected.</div>
                                @endif
                            </div>

                            {{-- Dynamic Balance Information --}}
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end" style="padding-right: 20px; gap: 30px;">
                                    <div>
                                        <div class="d-flex align-items-center" style="gap: 15px;">
                                            <span style="font-size: 14px; font-weight: 700;">Statement Balance</span>
                                            <span id="statementBalance"
                                                class="{{ $bankAccount->statement_balance < 0 ? 'text-danger' : 'text-success' }}">
                                                £{{ number_format(abs($bankAccount->statement_balance ?? 0), 2) }}
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center" style="gap: 15px;">
                                            <span style="font-size: 14px; font-weight: 700; white-space: nowrap;">
                                                Fast Ledger Balance
                                            </span>
                                            <span id="fastLedgerBalance"
                                                class="{{ $bankAccount->fast_ledger_balance < 0 ? 'text-danger' : 'text-success' }}">
                                                £{{ number_format(abs($bankAccount->fast_ledger_balance ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-end" style="gap: 15px;">
                                        <span style="font-size: 14px; font-weight: 700; white-space: nowrap;">
                                            Balance to Reconcile
                                            <span
                                                style="display: inline-block; background: #e8e8e8; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-left: 4px;">
                                                (<span id="pendingCount">{{ $bankAccount->pending_count ?? 0 }}</span>)
                                            </span>
                                        </span>
                                        <span id="balanceToReconcile"
                                            class="{{ $bankAccount->balance_to_reconcile < 0 ? 'text-danger' : 'text-success' }}">
                                            £{{ number_format(abs($bankAccount->balance_to_reconcile ?? 0), 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-3">
                            <a class="nav-link-btn active" href="#">
                                Reconcile ({{ $pendingTransactions->count() ?? 0 }})
                            </a>
                            <a href="#" class="nav-link-btn" data-section="suppliers">Cash Coding</a>
                            <a href="#" class="nav-link-btn" data-section="suppliers">Bank Statement</a>
                            <a href="#" class="nav-link-btn" data-section="suppliers">Account Transactions</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (!$pendingTransactions || $pendingTransactions->isEmpty())
                            <div class="alert alert-warning text-center">
                                <h5><i class="fas fa-exclamation-triangle"></i> No Pending Transactions</h5>
                                <p>No pending transactions found for this bank account.</p>
                                <a href="{{ route('bulk-transactions.dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Banks
                                </a>
                            </div>
                        @else
                            <div class="card-body">
                                <form action="{{ route('bulk-transactions.save-row') }}" method="POST"
                                    id="bulkTransactionForm">
                                    @csrf
                                    <input type="hidden" name="bank_account_id"
                                        value="{{ $bankAccount->Bank_Account_ID }}">

                                    <!-- Split Table Layout -->
                                    <div class="main-table-container p-5 bg-light border rounded shadow-sm">
                                        <!-- Transaction Summary Bar -->
                                        <div class="d-flex justify-content-between align-items-center pb-4"
                                            style="width: 100%;">
                                            <div class="d-flex gap-4">
                                                <div>
                                                    <strong>Transactions:</strong>
                                                    <span id="totalRecords"
                                                        class="text-dark">{{ $pendingTransactions->count() }}</span>
                                                </div>
                                                <div>
                                                    <strong>Reconciled:</strong>
                                                    <span id="okayCount" class="text-dark">0</span>
                                                </div>
                                                <div>
                                                    <strong>Unreconciled:</strong>
                                                    <span id="unreconciled"
                                                        class="text-dark">{{ $pendingTransactions->count() }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-primary" id="saveAllocatedBtn"
                                                    style="background-color: #01677d; border: none;">
                                                    Save Allocated (<span id="allocatedCount">0</span>)
                                                </button>
                                                <a href="{{ route('bulk-transactions.upload') }}" class="btn text-dark"
                                                    style="background-color: #c6d92d; border: none;">
                                                    <i class="fas fa-plus"></i> Upload Transactions
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Tables Wrapper -->
                                        <div class="tables-wrapper">
                                            <!-- Left Table -->
                                            <table class="left-table-wrapper transaction-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Bank Description</th>
                                                        <th>Payment</th>
                                                        <th>Receipt</th>
                                                        <th class="menu-header"><i class="fas fa-ellipsis-v"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($pendingTransactions as $index => $transaction)
                                                        @php
                                                            $amount = floatval($transaction['amount'] ?? 0);
                                                            $date = $transaction['date'] ?? date('Y-m-d');
                                                            $description =
                                                                $transaction['description'] ?? 'Bank Transaction';
                                                            $isPayment =
                                                                $amount < 0 ||
                                                                ($transaction['type'] ?? '') === 'payment';
                                                        @endphp
                                                        <tr id="row-left-{{ $index }}" class="transaction-row"
                                                            data-row-index="{{ $index }}">
                                                            <td>
                                                                <input type="text"
                                                                    name="transactions[{{ $index }}][date]"
                                                                    class="form-control date-picker"
                                                                    value="{{ $date }}" placeholder="DD/MM/YYYY"
                                                                    required>
                                                            </td>
                                                            <td>
                                                                <textarea name="transactions[{{ $index }}][description]" rows="1" maxlength="500" required>{{ $description }}</textarea>
                                                                <input type="hidden"
                                                                    name="transactions[{{ $index }}][pending_id]"
                                                                    value="{{ $transaction['id'] }}">
                                                            </td>
                                                            <td class="text-end">
                                                                @if ($isPayment)
                                                                    <input type="number"
                                                                        name="transactions[{{ $index }}][amount]"
                                                                        class="text-end payment-amount"
                                                                        value="{{ abs($amount) }}" step="0.01"
                                                                        min="0.01" required>
                                                                    <input type="hidden"
                                                                        name="transactions[{{ $index }}][type]"
                                                                        value="payment">
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                @if (!$isPayment)
                                                                    <input type="number"
                                                                        name="transactions[{{ $index }}][amount]"
                                                                        class="text-end receipt-amount"
                                                                        value="{{ $amount }}" step="0.01"
                                                                        min="0.01" required>
                                                                    <input type="hidden"
                                                                        name="transactions[{{ $index }}][type]"
                                                                        value="receipt">
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="menu-cell">
                                                                <button type="button" class="three-dots">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            <!-- Status Header Wrapper -->
                                            <div class="status-header-wrapper">
                                                <div class="status-header">Status</div>

                                                <!-- Status Column -->
                                                <div class="status-column">
                                                    @foreach ($pendingTransactions as $index => $transaction)
                                                        <button type="button" class="btn btn-allocate status-save-btn"
                                                            id="status-badge-{{ $index }}"
                                                            data-index="{{ $index }}"
                                                            data-pending-id="{{ $transaction['id'] }}"
                                                            data-save-url="{{ route('bulk-transactions.save-row') }}">
                                                            Allocate
                                                        </button>
                                                        <input type="hidden"
                                                            name="transactions[{{ $index }}][status]"
                                                            id="status-input-{{ $index }}" value="allocate">
                                                        <input type="hidden"
                                                            name="transactions[{{ $index }}][enabled]"
                                                            value="1">
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Right Table -->
                                            <table
                                                class="right-table-wrapper transaction-table bank-type-{{ $bankAccount->Bank_Type_ID }}">

                                                <thead>
                                                    <tr>
                                                        @if ($bankAccount->Bank_Type_ID == 2)
                                                            <th>Payer</th>
                                                            <th>Ledger Ref</th>
                                                            <th>Account Ref</th>
                                                            <th>VAT</th>
                                                        @else
                                                            <th>Ledger Ref</th>
                                                            <th colspan="3">Entry Details</th>
                                                        @endif
                                                        <th>Trans Code</th>
                                                        <th class="menu-header"><i class="fas fa-ellipsis-v"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($pendingTransactions as $index => $transaction)
                                                        <tr id="row-right-{{ $index }}" class="transaction-row"
                                                            data-row-index="{{ $index }}">
                                                            <td>
                                                                <select name="transactions[{{ $index }}][file_id]"
                                                                    class="form-select form-select-sm">
                                                                    <option value="">Select Payer</option>
                                                                    @foreach ($files as $file)
                                                                        <option value="{{ $file->File_ID }}">
                                                                            {{ $file->File_Name ?? $file->Ledger_Ref }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            @if ($bankAccount->Bank_Type_ID == 2)
                                                                <td>
                                                                    <select
                                                                        name="transactions[{{ $index }}][ledger_ref]"
                                                                        class="form-select form-select-sm ledger-ref-select"
                                                                        data-index="{{ $index }}"
                                                                        onchange="updateAccountRefsByLedger({{ $index }}); updateTransactionStatus({{ $index }})">
                                                                        <option value="">Select Ledger</option>
                                                                        @foreach ($ledgerRefs ?? [] as $ledger)
                                                                            <option value="{{ $ledger->ledger_ref }}">
                                                                                {{ $ledger->ledger_ref }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        name="transactions[{{ $index }}][chart_of_account_id]"
                                                                        class="form-select form-select-sm chart-of-account-select"
                                                                        id="chart-of-account-{{ $index }}"
                                                                        onchange="updateTransactionStatus({{ $index }})"
                                                                        required>
                                                                        <option value="">Select Account</option>
                                                                        @foreach ($chartOfAccounts ?? [] as $chart)
                                                                            <option value="{{ $chart->id }}"
                                                                                data-ledger-ref="{{ $chart->ledger_ref }}">
                                                                                {{ $chart->account_code }} -
                                                                                {{ $chart->account_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        name="transactions[{{ $index }}][vat_id]"
                                                                        class="form-select form-select-sm">
                                                                        <option value="">Exempt</option>
                                                                        @foreach ($vatTypes as $vat)
                                                                            <option value="{{ $vat->VAT_ID }}">
                                                                                {{ $vat->VAT_Name }}
                                                                                ({{ $vat->Percentage }}%)
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            @else
                                                                <td colspan="3">
                                                                    <textarea name="transactions[{{ $index }}][entry_details]" class="entry-details-textarea" rows="2"
                                                                        maxlength="500" data-row-index="{{ $index }}" required></textarea>
                                                                    <small
                                                                        class="text-muted char-count-{{ $index }}">0</small>/500

                                                                    <input type="hidden"
                                                                        name="transactions[{{ $index }}][ledger_ref]"
                                                                        value="">
                                                                    <input type="hidden"
                                                                        name="transactions[{{ $index }}][chart_of_account_id]"
                                                                        value="">
                                                                    <input type="hidden"
                                                                        name="transactions[{{ $index }}][vat_id]"
                                                                        value="">
                                                                </td>
                                                            @endif
                                                            <td id="transaction-code-{{ $index }}">
                                                                @if (isset($transactionCodes[$index]))
                                                                    <span
                                                                        class="text-muted">{{ $transactionCodes[$index] }}</span>
                                                                @else
                                                                    <span class="text-muted">Will be assigned on
                                                                        save</span>
                                                                @endif
                                                            </td>
                                                            <td class="menu-cell">
                                                                <button type="button" class="three-dots">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelectorAll('.transaction-row').length) {
                document.querySelectorAll('.transaction-row').forEach(row => {
                    const index = row.dataset.rowIndex;
                    updateTransactionStatus(index);
                });
                updateSummary();
                updateStatusSummary();
            }
            setupFormValidation();
            setupRowHoverSync();
            setupSaveAllocatedButton();
            setupEntryDetailsCharCounter();

            // Bootstrap tooltip initialization with null check
            if (typeof bootstrap !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        function setupEntryDetailsCharCounter() {
            const bankTypeId = {{ $bankAccount->Bank_Type_ID }};

            if (bankTypeId == 1) {
                document.querySelectorAll('.entry-details-textarea').forEach(textarea => {
                    const index = textarea.name.match(/\[(\d+)\]/)[1];
                    const charCountSpan = document.querySelector(`.char-count-${index}`);

                    if (charCountSpan) {
                        textarea.addEventListener('input', function() {
                            const length = this.value.length;
                            charCountSpan.textContent = length;

                            if (length > 450) {
                                charCountSpan.style.color = 'red';
                                charCountSpan.style.fontWeight = 'bold';
                            } else if (length > 400) {
                                charCountSpan.style.color = 'orange';
                            } else {
                                charCountSpan.style.color = '#6c757d';
                                charCountSpan.style.fontWeight = 'normal';
                            }

                            updateTransactionStatus(index);
                        });

                        textarea.addEventListener('blur', function() {
                            updateTransactionStatus(index);
                        });
                    }
                });
            }
        }

        function setupSaveAllocatedButton() {
            const saveAllocatedBtn = document.getElementById('saveAllocatedBtn');
            const form = document.getElementById('bulkTransactionForm');
            if (!saveAllocatedBtn || !form) return;

            let isSaving = false;

            saveAllocatedBtn.addEventListener('click', async function() {
                if (isSaving) {
                    console.log('Already saving, ignoring duplicate click');
                    return;
                }

                const bankAccountEl = document.getElementById('bank_account_id');
                if (!bankAccountEl || !bankAccountEl.value) {
                    alert('Bank account not found.');
                    return;
                }

                const indices = collectSelectedIndices(form);

                console.log('Indices to save:', indices);

                if (indices.length === 0) {
                    alert(
                        'No valid transactions to save.\n\nPlease ensure:\n1. Transactions have status "Okay" (green)\n2. Transactions are not already saved'
                    );
                    return;
                }

                isSaving = true;

                const originalHtml = saveAllocatedBtn.innerHTML;
                saveAllocatedBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                saveAllocatedBtn.disabled = true;

                try {
                    const json = await sendBulkSave(form, indices);

                    console.log('Server response:', json);

                    indices.forEach(index => {
                        const statusInput = document.getElementById(`status-input-${index}`);
                        if (statusInput) statusInput.value = 'saved';

                        const statusBtn = document.getElementById(`status-badge-${index}`);
                        if (statusBtn) {
                            statusBtn.classList.remove('btn-okay', 'btn-allocate');
                            statusBtn.classList.add('btn-success');
                            statusBtn.textContent = 'Saved';
                            statusBtn.disabled = true;
                            statusBtn.dataset.saved = '1';
                        }

                        const leftRow = document.getElementById(`row-left-${index}`);
                        const rightRow = document.getElementById(`row-right-${index}`);
                        [leftRow, rightRow].forEach(row => {
                            if (!row) return;
                            row.classList.add('row-saved');
                            row.querySelectorAll('input, select, textarea').forEach(el => el
                                .disabled = true);
                        });
                    });

                    if (json && Array.isArray(json.created)) {
                        console.log('Updating transaction codes:', json.created);

                        json.created.forEach(created => {
                            let rowIndex = null;

                            if (created._index !== undefined && created._index !== null) {
                                rowIndex = created._index;
                            }

                            if (rowIndex === null && created.pending_id) {
                                const allButtons = document.querySelectorAll('.status-save-btn');
                                allButtons.forEach(btn => {
                                    if (btn.dataset.pendingId == created.pending_id) {
                                        rowIndex = btn.dataset.index;
                                    }
                                });
                            }

                            if (rowIndex !== null) {
                                const code = created.code ?? created.transaction_code ?? null;
                                if (code) {
                                    const codeEl = document.getElementById(
                                        `transaction-code-${rowIndex}`);
                                    if (codeEl) {
                                        codeEl.innerHTML =
                                            `<span class="text-success fw-bold">${code}</span>`;
                                        console.log(`✅ Updated row ${rowIndex} with code: ${code}`);
                                    } else {
                                        console.warn(`⚠️ Code element not found for row ${rowIndex}`);
                                    }
                                } else {
                                    console.warn(`⚠️ No code returned for row ${rowIndex}`);
                                }
                            } else {
                                console.warn('⚠️ Could not find row index for:', created);
                            }
                        });
                    } else {
                        console.warn('⚠️ No created array in response:', json);
                    }

                    updateStatusSummary();
                    updateSummary();

                    const savedCount = json.created ? json.created.length : indices.length;
                    alert(`✅ Successfully saved ${savedCount} transaction(s)`);
                    await refreshBalancesFromServer();
                } catch (err) {
                    console.error('Bulk save error', err);
                    alert('❌ Error saving transactions:\n\n' + (err && err.message ? err.message :
                        'Unknown error'));
                } finally {
                    isSaving = false;
                    saveAllocatedBtn.innerHTML = originalHtml;
                    saveAllocatedBtn.disabled = false;
                }
            });
        }

        function collectSelectedIndices(form) {
            const indicesSet = new Set();
            const allRows = document.querySelectorAll('.transaction-row[data-row-index]');

            allRows.forEach(row => {
                const index = row.dataset.rowIndex;
                if (!index) return;

                const statusInput = document.getElementById(`status-input-${index}`);
                const statusBtn = document.getElementById(`status-badge-${index}`);

                if (statusInput && statusInput.value === 'okay') {
                    if (!statusBtn || statusBtn.dataset.saved !== '1') {
                        indicesSet.add(index);
                    }
                }
            });

            console.log('Collected indices:', Array.from(indicesSet));
            return Array.from(indicesSet);
        }

        async function sendBulkSave(form, indicesArray) {
            if (!form.action) throw new Error('Form action URL missing');

            const uniqueIndices = [...new Set(indicesArray)];

            const transactions = [];
            uniqueIndices.forEach(index => {
                const leftRow = document.getElementById(`row-left-${index}`);
                const rightRow = document.getElementById(`row-right-${index}`);
                if (!leftRow || !rightRow) return;

                const get = (root, selector) => {
                    const el = root.querySelector(selector);
                    if (!el) return null;
                    return (el.value === '' || el.value === undefined) ? null : el.value;
                };

                const pending_id = get(leftRow, `input[name="transactions[${index}][pending_id]"]`);
                const date = get(leftRow, `input[name="transactions[${index}][date]"]`);
                const description = get(leftRow, `textarea[name="transactions[${index}][description]"]`);
                const amount = get(leftRow, `input[name="transactions[${index}][amount]"]`);
                const type = get(leftRow, `input[name="transactions[${index}][type]"]`);
                const file_id = get(rightRow, `select[name="transactions[${index}][file_id]"]`);
                const bankTypeId = {{ $bankAccount->Bank_Type_ID }};

                let ledger_ref = null;
                let chart_of_account_id = null;
                let vat_id = null;
                let entry_details = null;

                if (bankTypeId == 2) {
                    ledger_ref = get(rightRow, `select[name="transactions[${index}][ledger_ref]"]`);
                    chart_of_account_id = get(rightRow,
                        `select[name="transactions[${index}][chart_of_account_id]"]`);
                    vat_id = get(rightRow, `select[name="transactions[${index}][vat_id]"]`);

                    if (!ledger_ref || !chart_of_account_id) {
                        console.warn(`Skipping row ${index}: missing ledger_ref or chart_of_account_id`);
                        return;
                    }
                } else {
                    const entryDetailsEl = rightRow.querySelector(
                        `textarea[name="transactions[${index}][entry_details]"]`);

                    if (entryDetailsEl) {
                        entry_details = entryDetailsEl.value;
                        console.log(`Row ${index} - Entry Details Found:`, entry_details);
                    } else {
                        console.error(`Row ${index} - Entry Details textarea NOT FOUND`);
                    }

                    if (!entry_details || entry_details.trim() === '') {
                        console.warn(`Skipping row ${index}: missing entry_details`);
                        alert(`Row ${parseInt(index) + 1}: Please fill in Entry Details before saving.`);
                        return;
                    }
                }

                transactions.push({
                    pending_id,
                    date,
                    description,
                    amount,
                    type,
                    file_id,
                    ledger_ref,
                    chart_of_account_id,
                    vat_id,
                    entry_details,
                    enabled: 1,
                    selected: true,
                    _index: index
                });
            });

            console.log('=== Final Transactions Array ===');
            console.log('Total transactions:', transactions.length);
            transactions.forEach((txn, i) => {
                console.log(`Transaction ${i}:`, {
                    index: txn._index,
                    entry_details: txn.entry_details,
                    ledger_ref: txn.ledger_ref,
                    chart_of_account_id: txn.chart_of_account_id
                });
            });
            console.log('================================');

            if (transactions.length === 0) {
                throw new Error('No valid transactions found to save.');
            }

            const payload = {
                _token: getCsrfToken(),
                bank_account_id: document.getElementById('bank_account_id')?.value ?? null,
                transactions
            };

            console.log('Sending payload:', payload);

            const resp = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const json = await resp.json().catch(() => ({}));
            if (!resp.ok) {
                const message = json.message || json.error || 'Server error saving transactions';
                throw new Error(message);
            }

            return json;
        }

        document.addEventListener('click', async function(e) {
            const btn = e.target.closest && e.target.closest('.status-save-btn');
            if (!btn) return;

            const index = btn.dataset.index;
            if (!index) return;

            const statusInput = document.getElementById(`status-input-${index}`);
            if (!statusInput || statusInput.value !== 'okay') {
                return;
            }

            if (btn.dataset.saved === '1') {
                console.log('Row already saved, ignoring');
                return;
            }

            if (btn.dataset.saving === '1') {
                console.log('Already saving this row, ignoring duplicate click');
                return;
            }

            const saveUrl = btn.dataset.saveUrl;
            if (!saveUrl) {
                console.error('No save URL configured on button');
                return;
            }

            const data = collectRowData(index);
            if (!data) {
                alert('Unable to read row data.');
                return;
            }

            btn.dataset.saving = '1';
            const originalText = btn.textContent;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            const formData = {
                _token: getCsrfToken(),
                bank_account_id: data.bank_account_id,
                transactions: [{
                    pending_id: data.pending_id,
                    date: data.date,
                    description: data.description,
                    amount: data.amount,
                    type: data.type,
                    file_id: data.file_id,
                    ledger_ref: data.ledger_ref,
                    chart_of_account_id: data.chart_of_account_id,
                    vat_id: data.vat_id,
                    entry_details: data.entry_details,
                    enabled: 1,
                    selected: true,
                    _index: index
                }]
            };

            try {
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const json = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const msg = (json.message || json.error || 'Server error saving transaction');
                    throw new Error(msg);
                }

                btn.classList.remove('btn-okay');
                btn.classList.add('btn-success');
                btn.textContent = 'Saved';
                btn.disabled = true;
                btn.dataset.saved = '1';

                const leftRow = document.getElementById(`row-left-${index}`);
                const rightRow = document.getElementById(`row-right-${index}`);
                if (leftRow) leftRow.classList.add('row-saved');
                if (rightRow) rightRow.classList.add('row-saved');

                if (statusInput) statusInput.value = 'saved';

                console.log('Individual save response:', json);

                if (json.created && json.created.length > 0) {
                    const transCode = json.created[0].code ?? json.created[0].transaction_code;
                    const codeEl = document.getElementById(`transaction-code-${index}`);

                    if (codeEl && transCode) {
                        codeEl.innerHTML =
                            `<span class="text-success fw-bold" style="font-size: 13px;">${transCode}</span>`;
                        console.log(`✅ Transaction code updated: ${transCode}`);
                    } else if (!transCode) {
                        console.warn('⚠️ No transaction code in response');
                    } else {
                        console.warn(`⚠️ Code element not found for index ${index}`);
                    }
                } else {
                    console.warn('⚠️ No created array in response');
                }

                updateStatusSummary();
                await refreshBalancesFromServer();

                if (leftRow) leftRow.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
                if (rightRow) rightRow.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);

            } catch (err) {
                console.error('Save error', err);
                alert('Error saving row: ' + (err.message || 'Unknown error'));
                btn.disabled = false;
                btn.innerText = originalText;
            } finally {
                btn.dataset.saving = '0';
            }
        });

        function getCsrfToken() {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.content : '';
        }

        function collectRowData(index) {
            const leftRow = document.getElementById(`row-left-${index}`);
            const rightRow = document.getElementById(`row-right-${index}`);
            if (!leftRow || !rightRow) return null;

            const findInRow = (row, selector) => {
                const el = row.querySelector(selector);
                if (!el) return null;
                return (el.value === '' || el.value === undefined) ? null : el.value;
            };

            const bankTypeId = {{ $bankAccount->Bank_Type_ID }};

            const baseData = {
                bank_account_id: document.getElementById('bank_account_id')?.value ?? null,
                pending_id: findInRow(leftRow, `input[name="transactions[${index}][pending_id]"]`),
                date: findInRow(leftRow, `input[name="transactions[${index}][date]"]`),
                description: findInRow(leftRow, `textarea[name="transactions[${index}][description]"]`),
                amount: findInRow(leftRow, `input[name="transactions[${index}][amount]"]`),
                type: findInRow(leftRow, `input[name="transactions[${index}][type]"]`),
                file_id: findInRow(rightRow, `select[name="transactions[${index}][file_id]"]`),
                status: document.getElementById(`status-input-${index}`)?.value || 'okay',
                _index: index
            };

            if (bankTypeId == 2) {
                baseData.ledger_ref = findInRow(rightRow, `select[name="transactions[${index}][ledger_ref]"]`);
                baseData.chart_of_account_id = findInRow(rightRow,
                    `select[name="transactions[${index}][chart_of_account_id]"]`);
                baseData.vat_id = findInRow(rightRow, `select[name="transactions[${index}][vat_id]"]`);
                baseData.entry_details = null;
            } else {
                const entryDetailsEl = rightRow.querySelector(`textarea[name="transactions[${index}][entry_details]"]`);

                if (entryDetailsEl) {
                    baseData.entry_details = entryDetailsEl.value;
                } else {
                    baseData.entry_details = null;
                }

                baseData.ledger_ref = null;
                baseData.chart_of_account_id = null;
                baseData.vat_id = null;
            }

            return baseData;
        }

        function setupFormValidation() {
            const form = document.getElementById('bulkTransactionForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                const bankAccount = document.getElementById('bank_account_id');
                if (!bankAccount || !bankAccount.value) {
                    e.preventDefault();
                    alert('Bank account not found.');
                    return false;
                }

                const submitBtn = document.getElementById('saveAllocatedBtn');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitBtn.disabled = true;
                }

                return true;
            });
        }

        function setupRowHoverSync() {
            document.querySelectorAll('.transaction-row').forEach(row => {
                const index = row.dataset.rowIndex;
                const leftRow = document.getElementById(`row-left-${index}`);
                const rightRow = document.getElementById(`row-right-${index}`);

                if (leftRow && rightRow) {
                    [leftRow, rightRow].forEach(r => {
                        r.addEventListener('mouseenter', () => {
                            leftRow.classList.add('transaction-row-hover');
                            rightRow.classList.add('transaction-row-hover');
                        });
                        r.addEventListener('mouseleave', () => {
                            leftRow.classList.remove('transaction-row-hover');
                            rightRow.classList.remove('transaction-row-hover');
                        });
                    });
                }
            });
        }

        function updateTransactionStatus(index) {
            const statusButton = document.getElementById(`status-badge-${index}`);
            const statusInput = document.getElementById(`status-input-${index}`);
            const rightRow = document.getElementById(`row-right-${index}`);

            if (!statusButton || !statusInput) return;

            const bankTypeId = {{ $bankAccount->Bank_Type_ID }};
            let isValid = false;
            let statusClass, statusText;

            if (bankTypeId == 2) {
                const ledgerSelect = document.querySelector(`select[name="transactions[${index}][ledger_ref]"]`);
                const chartSelect = document.querySelector(`select[name="transactions[${index}][chart_of_account_id]"]`);

                if (!ledgerSelect || !chartSelect) return;

                const hasLedger = ledgerSelect.value !== '';
                const hasChart = chartSelect.value !== '';

                isValid = hasLedger && hasChart;

            } else {
                const entryDetailsTextarea = document.querySelector(
                    `textarea[name="transactions[${index}][entry_details]"]`);

                if (!entryDetailsTextarea) return;

                const entryDetailsValue = entryDetailsTextarea.value.trim();
                isValid = entryDetailsValue !== '';

                const charCountSpan = document.querySelector(`.char-count-${index}`);
                if (charCountSpan) {
                    charCountSpan.textContent = entryDetailsValue.length;
                }
            }

            if (isValid) {
                statusClass = 'btn-okay';
                statusText = 'Okay';

                if (rightRow) {
                    rightRow.querySelectorAll('td').forEach(td => {
                        td.style.backgroundColor = '#fcfdf5';
                    });
                }
            } else {
                statusClass = 'btn-allocate';
                statusText = 'Allocate';

                if (rightRow) {
                    rightRow.querySelectorAll('td').forEach(td => {
                        td.style.backgroundColor = '#fefaf4';
                    });
                }
            }

            statusButton.className = `btn ${statusClass} status-save-btn`;
            statusButton.textContent = statusText;
            statusInput.value = statusText.toLowerCase();

            updateStatusSummary();
        }

        function updateStatusSummary() {
            let okayCount = 0;
            const buttons = document.querySelectorAll('.status-save-btn');
            const totalRecords = buttons.length;

            buttons.forEach(btn => {
                const index = btn.dataset.index;
                const statusInput = document.getElementById(`status-input-${index}`);
                if (statusInput && statusInput.value === 'okay') {
                    okayCount++;
                }
            });

            const okayCountEl = document.getElementById('okayCount');
            const unreconciledEl = document.getElementById('unreconciled');
            const allocatedCountEl = document.getElementById('allocatedCount');

            if (okayCountEl) okayCountEl.textContent = okayCount;
            if (unreconciledEl) unreconciledEl.textContent = totalRecords - okayCount;
            if (allocatedCountEl) allocatedCountEl.textContent = okayCount;
        }

        function updateSummary() {
            const rows = document.querySelectorAll('[id^="row-left-"]');
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    visibleCount++;
                }
            });

            const totalRecordsEl = document.getElementById('totalRecords');
            if (totalRecordsEl) totalRecordsEl.textContent = visibleCount;
        }

        function updateAccountRefsByLedger(index) {
            const ledgerSelect = document.querySelector(`select[name="transactions[${index}][ledger_ref]"]`);
            const accountSelect = document.getElementById(`chart-of-account-${index}`);

            if (!ledgerSelect || !accountSelect) return;

            const selectedLedger = ledgerSelect.value;

            if (!selectedLedger) {
                accountSelect.querySelectorAll('option').forEach(option => {
                    if (option.value !== '') option.style.display = 'block';
                });
                accountSelect.value = '';
                updateTransactionStatus(index);
                return;
            }

            accountSelect.innerHTML = '<option value="">Loading accounts...</option>';
            accountSelect.disabled = true;

            fetch(`/bulk-transactions/account-refs-by-ledger?ledger_ref=${encodeURIComponent(selectedLedger)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        accountSelect.innerHTML = '<option value="">Select Account</option>';
                        data.account_refs.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.id;
                            option.textContent = account.account_ref;
                            accountSelect.appendChild(option);
                        });
                    } else {
                        accountSelect.innerHTML = '<option value="">Error loading accounts</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    accountSelect.innerHTML = '<option value="">Error loading accounts</option>';
                })
                .finally(() => {
                    accountSelect.disabled = false;
                    updateTransactionStatus(index);
                });
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('chart-of-account-select') ||
                e.target.classList.contains('ledger-ref-select')) {
                const row = e.target.closest('tr');
                if (!row) return;
                const index = row.dataset.rowIndex;
                updateTransactionStatus(index);
            }
        });

        function updateBalanceElement(elementId, value) {
            const element = document.getElementById(elementId);
            if (!element) return;

            element.classList.remove('text-danger', 'text-success');

            if (value < 0) {
                element.classList.add('text-danger');
            } else {
                element.classList.add('text-success');
            }

            element.textContent = '£' + Math.abs(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function parseCurrencyValue(currencyString) {
            if (!currencyString) return 0;

            const cleaned = currencyString.replace(/[£,\s]/g, '');
            return parseFloat(cleaned) || 0;
        }

        async function refreshBalancesFromServer() {
            const bankAccountId = document.getElementById('bank_account_id')?.value;
            if (!bankAccountId) return;

            try {
                const response = await fetch(`/bulk-transactions/get-balances/${bankAccountId}`, {
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();

                    if (data.success) {
                        updateBalanceElement('statementBalance', data.statement_balance);
                        updateBalanceElement('fastLedgerBalance', data.fast_ledger_balance);
                        updateBalanceElement('balanceToReconcile', data.balance_to_reconcile);

                        const pendingCountEl = document.getElementById('pendingCount');
                        if (pendingCountEl) {
                            pendingCountEl.textContent = data.pending_count;
                        }

                        console.log('✅ Balances updated successfully:', data);
                    }
                }
            } catch (error) {
                console.error('❌ Error refreshing balances:', error);
            }
        }
    </script>
@endsection