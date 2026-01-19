<!-- P&L Preview Modal -->
<div class="modal fade" id="plPreviewModal" tabindex="-1" aria-labelledby="plPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="plPreviewModalLabel">
                    <i class="fas fa-chart-line me-2"></i>
                    Profit & Loss Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="pl-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Fetching your financial data...</p>
                </div>

                <!-- Error State -->
                <div id="pl-error" class="alert alert-danger d-none" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="pl-error-message"></span>
                </div>

                <!-- P&L Data -->
                <div id="pl-data-container" class="d-none">
                    <!-- Period Info -->
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-calendar me-2"></i>
                        <strong>Period:</strong> <span id="pl-period-display"></span>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Income</h6>
                                    <h3 class="mb-0 text-success" id="pl-total-income">£0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Expenses</h6>
                                    <h3 class="mb-0 text-danger" id="pl-total-expenses">£0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Net Profit/Loss</h6>
                                    <h3 class="mb-0 text-primary" id="pl-net-profit">£0.00</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs for Income and Expenses -->
                    <ul class="nav nav-tabs mb-3" id="plTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="income-tab" data-bs-toggle="tab" 
                                    data-bs-target="#income-content" type="button" role="tab">
                                <i class="fas fa-arrow-up text-success me-1"></i> Income
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" 
                                    data-bs-target="#expenses-content" type="button" role="tab">
                                <i class="fas fa-arrow-down text-danger me-1"></i> Expenses
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="suggestions-tab" data-bs-toggle="tab" 
                                    data-bs-target="#suggestions-content" type="button" role="tab">
                                <i class="fas fa-magic text-primary me-1"></i> HMRC Mapping
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="plTabContent">
                        <!-- Income Tab -->
                        <div class="tab-pane fade show active" id="income-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Ledger Reference</th>
                                            <th>Account</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pl-income-body">
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Expenses Tab -->
                        <div class="tab-pane fade" id="expenses-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Ledger Reference</th>
                                            <th>Account</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pl-expenses-body">
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- HMRC Mapping Tab -->
                        <div class="tab-pane fade" id="suggestions-content" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>HMRC Mapping:</strong> Below are suggested values based on your P&L data. 
                                You can use these as a reference when filling out the HMRC submission form.
                            </div>

                            <h6 class="mb-3">Suggested Income Values</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Field</th>
                                            <th class="text-end">Suggested Value</th>
                                            <th width="120">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="hmrc-income-suggestions">
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="mb-3">Suggested Expense Values</h6>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Automatic mapping is best-effort. Please review and adjust values as needed.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>HMRC Category</th>
                                            <th class="text-end">Suggested Value</th>
                                            <th width="120">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="hmrc-expense-suggestions">
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="apply-suggestions-btn" disabled>
                    <i class="fas fa-magic me-1"></i> Apply Suggested Values
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #plPreviewModal .modal-xl {
        max-width: 1200px;
    }

    #plPreviewModal .table td,
    #plPreviewModal .table th {
        vertical-align: middle;
    }

    #plPreviewModal .ledger-group {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    #plPreviewModal .account-row {
        padding-left: 20px;
    }

    #plPreviewModal .btn-copy-value {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>

<script>
// P&L Preview Functionality
let currentPlData = null;
let currentSuggestions = null;

function openPlPreview() {
    // Get period dates
    const periodStartInput = document.getElementById('period_start_date');
    const periodEndInput = document.getElementById('period_end_date');
    
    @if($obligation ?? false)
        const fromDate = '{{ $obligation->period_start_date->format('Y-m-d') }}';
        const toDate = '{{ $obligation->period_end_date->format('Y-m-d') }}';
    @else
        if (!periodStartInput || !periodEndInput) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select period start and end dates first.'
            });
            return;
        }

        const fromDate = periodStartInput.value;
        const toDate = periodEndInput.value;

        if (!fromDate || !toDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select period start and end dates first.'
            });
            return;
        }
    @endif

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('plPreviewModal'));
    modal.show();

    // Reset states
    document.getElementById('pl-loading').classList.remove('d-none');
    document.getElementById('pl-error').classList.add('d-none');
    document.getElementById('pl-data-container').classList.add('d-none');
    document.getElementById('apply-suggestions-btn').disabled = true;

    // Fetch P&L data
    fetch('{{ route('hmrc.submissions.profit-loss-data') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            from_date: fromDate,
            to_date: toDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPlData = data.data;
            currentSuggestions = data.suggestions;
            displayPlData(data.data, data.suggestions);
        } else {
            showPlError(data.message || 'Failed to fetch P&L data');
        }
    })
    .catch(error => {
        console.error('Error fetching P&L data:', error);
        showPlError('Network error occurred. Please try again.');
    });
}

function displayPlData(plData, suggestions) {
    // Hide loading, show data
    document.getElementById('pl-loading').classList.add('d-none');
    document.getElementById('pl-data-container').classList.remove('d-none');
    document.getElementById('apply-suggestions-btn').disabled = false;

    // Update period display
    const fromDate = new Date(plData.period_from);
    const toDate = new Date(plData.period_to);
    document.getElementById('pl-period-display').textContent = 
        `${fromDate.toLocaleDateString('en-GB')} to ${toDate.toLocaleDateString('en-GB')}`;

    // Update summary cards
    document.getElementById('pl-total-income').textContent = formatCurrency(plData.total_income);
    document.getElementById('pl-total-expenses').textContent = formatCurrency(Math.abs(plData.total_expenses));
    document.getElementById('pl-net-profit').textContent = formatCurrency(plData.net_profit);

    // Color code net profit
    const netProfitEl = document.getElementById('pl-net-profit');
    netProfitEl.className = 'mb-0 ' + (plData.net_profit >= 0 ? 'text-success' : 'text-danger');

    // Populate income table
    populateIncomeTable(plData.income);

    // Populate expenses table
    populateExpensesTable(plData.expenses);

    // Populate HMRC suggestions
    populateHmrcSuggestions(suggestions);
}

function populateIncomeTable(income) {
    const tbody = document.getElementById('pl-income-body');
    tbody.innerHTML = '';

    if (!income || income.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No income data found</td></tr>';
        return;
    }

    income.forEach(group => {
        // Ledger group row
        const groupRow = document.createElement('tr');
        groupRow.className = 'ledger-group';
        groupRow.innerHTML = `
            <td colspan="2">${escapeHtml(group.ledger_ref)}</td>
            <td class="text-end fw-bold">${formatCurrency(group.subtotal)}</td>
        `;
        tbody.appendChild(groupRow);

        // Account detail rows
        group.accounts.forEach(account => {
            if (account.balance !== 0) {
                const accountRow = document.createElement('tr');
                accountRow.className = 'account-row';
                accountRow.innerHTML = `
                    <td></td>
                    <td class="ps-4">${escapeHtml(account.account_ref)}</td>
                    <td class="text-end">${formatCurrency(account.balance)}</td>
                `;
                tbody.appendChild(accountRow);
            }
        });
    });
}

function populateExpensesTable(expenses) {
    const tbody = document.getElementById('pl-expenses-body');
    tbody.innerHTML = '';

    if (!expenses || expenses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No expense data found</td></tr>';
        return;
    }

    expenses.forEach(group => {
        // Ledger group row
        const groupRow = document.createElement('tr');
        groupRow.className = 'ledger-group';
        groupRow.innerHTML = `
            <td colspan="2">${escapeHtml(group.ledger_ref)}</td>
            <td class="text-end fw-bold">${formatCurrency(Math.abs(group.subtotal))}</td>
        `;
        tbody.appendChild(groupRow);

        // Account detail rows
        group.accounts.forEach(account => {
            if (account.balance !== 0) {
                const accountRow = document.createElement('tr');
                accountRow.className = 'account-row';
                accountRow.innerHTML = `
                    <td></td>
                    <td class="ps-4">${escapeHtml(account.account_ref)}</td>
                    <td class="text-end">${formatCurrency(Math.abs(account.balance))}</td>
                `;
                tbody.appendChild(accountRow);
            }
        });
    });
}

function populateHmrcSuggestions(suggestions) {
    // Income suggestions
    const incomeTbody = document.getElementById('hmrc-income-suggestions');
    incomeTbody.innerHTML = `
        <tr>
            <td>Turnover</td>
            <td class="text-end fw-bold">${formatCurrency(suggestions.income.turnover)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary btn-copy-value" 
                        onclick="copyValueToForm('income_turnover', ${suggestions.income.turnover})">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </td>
        </tr>
        <tr>
            <td>Other Income</td>
            <td class="text-end fw-bold">${formatCurrency(suggestions.income.other)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary btn-copy-value" 
                        onclick="copyValueToForm('income_other', ${suggestions.income.other})">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </td>
        </tr>
    `;

    // Expense suggestions (breakdown)
    const expenseTbody = document.getElementById('hmrc-expense-suggestions');
    expenseTbody.innerHTML = '';

    const expenseLabels = {
        cost_of_goods: 'Cost of Goods',
        staff_costs: 'Staff Costs',
        travel_costs: 'Travel Costs',
        premises_running_costs: 'Premises Running Costs',
        maintenance_costs: 'Maintenance Costs',
        admin_costs: 'Admin Costs',
        business_entertainment_costs: 'Business Entertainment',
        advertising_costs: 'Advertising Costs',
        interest_on_bank_other_loans: 'Interest on Loans',
        financial_charges: 'Financial Charges',
        bad_debt: 'Bad Debt',
        professional_fees: 'Professional Fees',
        depreciation: 'Depreciation',
        other_expenses: 'Other Expenses'
    };

    Object.entries(suggestions.breakdown).forEach(([key, value]) => {
        if (value > 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${expenseLabels[key]}</td>
                <td class="text-end fw-bold">${formatCurrency(value)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-copy-value" 
                            onclick="copyValueToForm('expense_${key}', ${value})">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </td>
            `;
            expenseTbody.appendChild(row);
        }
    });

    // Consolidated expense option
    const consolidatedRow = document.createElement('tr');
    consolidatedRow.className = 'table-warning';
    consolidatedRow.innerHTML = `
        <td colspan="2" class="fw-bold">OR Use Consolidated Total</td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-primary btn-copy-value" 
                    onclick="copyValueToForm('consolidated_expenses', ${suggestions.expenses.consolidated_expenses})">
                <i class="fas fa-copy"></i> Copy
            </button>
        </td>
    `;
    expenseTbody.appendChild(consolidatedRow);
}

function copyValueToForm(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.value = value.toFixed(2);
        field.dispatchEvent(new Event('input')); // Trigger recalculation
        
        // Visual feedback
        field.classList.add('border-success');
        setTimeout(() => field.classList.remove('border-success'), 1000);
        
        // Show toast
        showToast('Value copied to form', 'success');
    }
}

function showPlError(message) {
    document.getElementById('pl-loading').classList.add('d-none');
    document.getElementById('pl-error').classList.remove('d-none');
    document.getElementById('pl-error-message').textContent = message;
}

function formatCurrency(value) {
    return '£' + Math.abs(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Apply all suggested values
document.getElementById('apply-suggestions-btn')?.addEventListener('click', function() {
    if (!currentSuggestions) return;

    Swal.fire({
        title: 'Apply Suggested Values?',
        text: 'This will fill the form with values from your P&L report.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, apply them',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Apply income values
            copyValueToForm('income_turnover', currentSuggestions.income.turnover);
            copyValueToForm('income_other', currentSuggestions.income.other);

            // Switch to breakdown mode
            const breakdownRadio = document.getElementById('expense_breakdown');
            if (breakdownRadio) {
                breakdownRadio.checked = true;
                breakdownRadio.dispatchEvent(new Event('change'));

                // Apply breakdown values
                setTimeout(() => {
                    Object.entries(currentSuggestions.breakdown).forEach(([key, value]) => {
                        if (value > 0) {
                            copyValueToForm('expense_' + key, value);
                        }
                    });
                }, 100);
            }

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('plPreviewModal')).hide();

            Swal.fire({
                icon: 'success',
                title: 'Values Applied!',
                text: 'P&L values have been applied to the form. Please review and adjust as needed.',
                timer: 3000
            });
        }
    });
});
</script>

