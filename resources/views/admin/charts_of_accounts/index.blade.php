@extends('admin.layout.app')
<style>
    thead tr.teal-custom th {
        background-color: #13667d !important;
        color: #fff !important;
    }

    thead tr.teal-custom th {
        background-color: #13667d !important;
        color: #fff !important;
    }


    /* Constrain only the search components, not the table itself */
    .table-search-header {
        position: relative;
        white-space: nowrap;
    }

    /* Make search input smaller to fit in column */
    .search-input-container {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        max-width: 100%;
        z-index: 100;
    }

    .search-input-container input {
        width: calc(100% - 35px) !important;
        font-size: 11px !important;
        padding: 2px 6px !important;
    }

    .search-input-container .btn-close {
        padding: 4px 6px !important;
        font-size: 11px !important;
    }

    /* Position dropdown to not expand column */
    .filter-dropdown {
        position: absolute !important;
        min-width: 180px !important;
        max-width: 250px !important;
        white-space: normal !important;
        z-index: 1050 !important;
    }

    /* Smaller icons */
    .table-search-header i {
        font-size: 12px !important;
        padding: 4px 6px !important;
        min-width: 22px !important;
        height: 22px !important;
    }
</style>
@section('content')
    <!-- Main content with exact original styling -->
    <div class="main-content app-content" style="font-family: Arial, sans-serif; background-color: #f5f5f5;">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class=" d-flex justify-content-between align-items-center mb-2">
                        <h4 class="page-title">Charts Of Accounts</h4>
                        <div class="d-flex gap-2">
                            <!-- Add Account Button with exact original colors -->
                            <button type="button" class="text-white border-0 teal-custom" data-bs-toggle="modal"
                                data-bs-target="#accountTypeModal">
                                <i class="fas fa-plus"></i> Add Account
                            </button>

                            <button type="button" class="text-white border-0 teal-custom" data-bs-toggle="modal"
                                data-bs-target="#accountTypeModal">
                                <i class="fas fa-plus"></i> Add Bank Account
                            </button>

                            <button id="delete-selected" class="btn-danger border-0">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="card custom-card" style="background-color: white; border: 1px solid #ccc;">


                        <div class="card-body">
                            <!-- Main Content Area -->
                            <div class="row">
                                <!-- Left Panel - Chart of Accounts -->
                                <div class="col-md-6">
                                    <div style="border: 1px solid black; overflow: hidden;">
                                        <!-- Category Navigation Buttons with exact original styling -->
                                        <div class="p-2 d-flex flex-wrap justify-content-between gap-1 bg-white position-sticky"
                                            style="top: 0; z-index: 10; margin-bottom: 2px;">

                                            @php
                                                $accountCategories = [
                                                    'income' => 'Income',
                                                    'cost_of_sales' => 'Cost of sales',
                                                    'expenses' => 'Expenses',
                                                    'taxation' => 'Taxation',
                                                    'fa_investments' => 'Fixed Assets',
                                                    'stocks' => 'Stocks',
                                                    'cash' => 'Cash',
                                                    'current_liability' => 'Current Liability (CRs)',
                                                    'non_current_liability' => 'Non-current liability (CRs)',
                                                    'deferred_tax' => 'Deferred tax',
                                                    'share_capital' => 'Share capital (Ord)',
                                                    'revaluation_reserve' => 'Revaluation reserve',
                                                    'share_premium' => 'Share premium',
                                                ];
                                            @endphp
                                            @foreach ($accountCategories as $key => $category)
                                                <button id="category-btn-{{ $key }}"
                                                    onclick="scrollToCategory('{{ \Str::slug($category) }}')"
                                                    class="category-nav-btn"
                                                    style="color: rgb(17, 16, 16) !important; border: 1px solid black !important; padding: 2px 4px !important; font-size: 10px !important; font-weight: bold !important; cursor: pointer !important; background-color: white; transition: all 0.2s ease;"
                                                    onmouseover="this.style.backgroundColor='#1b598c'; this.style.color='white';"
                                                    onmouseout="this.style.backgroundColor='white'; this.style.color='rgb(17, 16, 16)';">
                                                    {{ $category }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <div style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-bordered"
                                                style="width: 100% !important; border-collapse: collapse !important; font-size: 13px !important;">
                                                <thead>
                                                    <tr>
                                                        <th class="checkbox-col">
                                                        </th>
                                                        <x-table-search-header column="code" label="Code" type="search"
                                                            class="code-col" style="width: 60px !important;" />
                                                        <x-table-search-header column="name" label="Name" type="search"
                                                            class="name-col" style="width: 280px !important;" />
                                                        <x-table-search-header column="tax-rate" label="Tax Rate"
                                                            type="dropdown" class="tax-rate-col"
                                                            style="width: 200px !important;" :options="[
                                                                'No VAT' => 'No VAT',
                                                                '20%' => '20%',
                                                                '5%' => '5%',
                                                                '0%' => '0%',
                                                            ]" />
                                                        <x-table-search-header column="balance" label="Balance"
                                                            type="search" class="balance-col"
                                                            style="width: 80px !important;" />
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($groupedAccounts as $ledgerRef => $accounts)
                                                        <!-- SECTION HEADER -->
                                                        <tr id="section-{{ \Str::slug($ledgerRef) }}" class="section-header"
                                                            style="background-color: #e3f0f3 !important; font-weight: bold !important; color: #2c5282 !important;">
                                                            <td colspan="2"
                                                                style="background-color: #e3f0f3; border: 1px solid #ccc;">
                                                            </td>
                                                            <td style="background-color: #e3f0f3; border: 1px solid #ccc;">
                                                                <strong>{{ $ledgerRef }}</strong>
                                                            </td>
                                                            <td style="background-color: #e3f0f3; border: 1px solid #ccc;">
                                                            </td>
                                                            <td style="background-color: #e3f0f3; border: 1px solid #ccc;">
                                                                <strong>{{ number_format($accounts->sum('balance'), 0) }}</strong>
                                                            </td>
                                                        </tr>

                                                        @foreach ($accounts as $index => $account)
                                                            @php
                                                                $bgColor = $index % 2 === 0 ? 'white' : '#f0f4f8';
                                                                $vatRate = $account->vatType->VAT_Rate ?? null;
                                                                $vatDesc =
                                                                    $account->vatType->VAT_Description ?? 'No VAT';
                                                                $vatDisplay = $vatRate
                                                                    ? "{$vatRate}% ({$vatDesc})"
                                                                    : 'No VAT';
                                                            @endphp

                                                            <tr class="table-row-clickable"
                                                                style="background-color: {{ $bgColor }} !important;">
                                                                <td class="checkbox-col"
                                                                    style="text-align: center !important; padding: 6px 5px !important; border: 1px solid #ccc !important;">
                                                                    <input type="checkbox"
                                                                        style="width: 14px; height: 14px;">
                                                                </td>
                                                                <td class="code-col" data-column="code"
                                                                    style="text-align: center !important; font-weight: 500; padding: 6px 10px; border: 1px solid #ccc;">
                                                                    {{ $account->id }}</td>
                                                                <td data-column="name"
                                                                    style="padding: 6px 10px; border: 1px solid #ccc;">
                                                                    {{ $account->account_ref }}
                                                                    <span
                                                                        style="color: #666; font-size: 12px; margin-left: 3px;">ⓘ</span>
                                                                </td>
                                                                <td data-column="tax-rate"
                                                                    style="padding: 6px 10px; border: 1px solid #ccc;">
                                                                    {{ $vatDisplay }}</td>
                                                                <td data-column="balance"
                                                                    style="text-align: right !important; font-weight: 500; padding: 6px 10px; border: 1px solid #ccc;">
                                                                    {{ number_format($account->balance, 0) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                                <!-- Right Panel - Transaction Details -->
                                <div class="col-md-6 ps-0">
                                    <div style="max-height: 100%; overflow-y: auto;">
                                        <div id="transaction-panel"
                                            style="border: 1px solid #0a0a0a; overflow: hidden; min-height:100%;">
                                            <table class="table table-bordered resizable-draggable-table"
                                                style="width: 100%; border-collapse: collapse;">
                                                <thead>
                                                    <tr>
                                                        <x-table-search-header column="date" label="Date"
                                                            type="search" />
                                                        <x-table-search-header column="reference" label="Reference"
                                                            type="search" />
                                                        <x-table-search-header column="details" label="Details"
                                                            type="search" />
                                                        <x-table-search-header column="expense" label="Expense"
                                                            type="search" />
                                                        <x-table-search-header column="income" label="Income"
                                                            type="search" />
                                                        <x-table-search-header column="balance" label="Balance"
                                                            type="search" />
                                                    </tr>
                                                </thead>
                                                <tbody id="transaction-tbody">
                                                    <tr>
                                                        <td colspan="6"
                                                            style="padding: 40px; text-align: center; color: #6c757d;">
                                                            Select an account from the left to view transaction details
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div id="balance-footer"
                                                style="padding: 10px; background: #f8f9fa; text-align: right; font-weight: bold; display: none;">
                                                Balance: <span id="account-balance">300</span>
                                            </div>
                                        </div>
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
            function formatAmount(v) {
                if (v === '' || v === null || v === undefined) return '';
                const num = Number(v);
                if (Number.isNaN(num)) return '';
                return num.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }

            // Function to scroll to a specific category section
            function scrollToCategory(slug) {
                const sectionId = 'section-' + slug;
                const target = document.getElementById(sectionId);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } else {
                    console.warn('Section not found for:', sectionId);
                }
            }

            // Add functionality for delete button
            document.getElementById('delete-selected').addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select accounts to delete');
                    return;
                }

                if (confirm('Are you sure you want to delete the selected accounts?')) {
                    // Add your delete logic here
                    console.log('Deleting selected accounts');
                }
            });

            // Add click functionality to account rows to show transaction details
            document.querySelectorAll('.table-row-clickable').forEach(row => {
                row.addEventListener('click', function() {
                    document.querySelectorAll('.table-row-clickable').forEach(r => r.classList.remove(
                        'table-active'));
                    this.classList.add('table-active');

                    const accountId = this.querySelector('.code-col')?.textContent?.trim();
                    const accountName = this.cells[2]?.textContent?.trim();

                    if (accountId) {
                        updateTransactionPanel(accountId, accountName);
                    }
                });
            });


            function updateTransactionPanel(accountId, accountName) {
                const tbody = document.getElementById('transaction-tbody');
                const balanceFooter = document.getElementById('balance-footer');
                const balanceSpan = document.getElementById('account-balance');

                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:20px;">Loading...</td></tr>`;
                balanceFooter.style.display = 'none';

                fetch(`/charts-of-accounts/${accountId}/transactions`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            tbody.innerHTML =
                                `<tr><td colspan="6" style="text-align:center; padding:20px; color:red;">${data.error}</td></tr>`;
                            return;
                        }

                        tbody.innerHTML = '';

                        if (data.transactions.length === 0) {
                            tbody.innerHTML =
                                `<tr><td colspan="6" style="text-align:center; padding:20px;">No transactions found</td></tr>`;
                        }

                        data.transactions.forEach(transaction => {
                            const row = tbody.insertRow();
                            row.innerHTML = `
                                    <td data-column="date">${formatDate(transaction.date)}</td>
                                    <td data-column="reference">${transaction.reference}</td>
                                    <td data-column="details">${transaction.details}</td>
                                    <td data-column="expense">${formatAmount(transaction.debit)}</td>
                                    <td data-column="income">${formatAmount(transaction.credit)}</td>
                                    <td data-column="balance">${formatAmount(transaction.running_balance)}</td>
                                `;
                        });

                        balanceFooter.style.display = 'block';
                        balanceSpan.textContent = formatAmount(data.balance);
                    })
                    .catch(() => {
                        tbody.innerHTML =
                            `<tr><td colspan="6" style="text-align:center; padding:20px; color:red;">Error loading transactions</td></tr>`;
                    });

                function formatDate(dateString) {
                    const date = new Date(dateString);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                }

            }
        </script>
    @endsection
