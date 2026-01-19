@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.submissions.index') }}">Submissions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.submissions.show', $submission) }}">{{ $submission->period_label }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Edit Submission</h1>
                <p class="text-muted mb-0">{{ $submission->period_label }}</p>
            </div>
            <div>
                <button type="button" class="btn btn-info" onclick="openPlPreview()" id="view-pl-btn">
                    <i class="fas fa-chart-line me-2"></i> View P&L Report
                </button>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="periodic-submission-form" method="POST" action="{{ route('hmrc.submissions.update', $submission) }}">
            @csrf
            @method('PUT')

            <!-- Hidden fields -->
            <input type="hidden" name="business_id" value="{{ $submission->business_id }}">
            <input type="hidden" id="period_start_date" value="{{ $submission->period_start_date->format('Y-m-d') }}">
            <input type="hidden" id="period_end_date" value="{{ $submission->period_end_date->format('Y-m-d') }}">
            @if($submission->obligation_id)
                <input type="hidden" name="obligation_id" value="{{ $submission->obligation_id }}">
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Income Card -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-pound-sign me-2"></i>
                                Income
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="income_turnover" class="form-label">
                                        Turnover 
                                        <i class="fas fa-info-circle text-muted" 
                                           data-bs-toggle="tooltip" 
                                           title="Total business income from sales/services"></i>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="income[turnover]" id="income_turnover"
                                               class="form-control @error('income.turnover') is-invalid @enderror"
                                               value="{{ old('income.turnover', $submission->income_json['turnover'] ?? '') }}"
                                               step="0.01" min="0" max="99999999.99"
                                               placeholder="0.00">
                                    </div>
                                    @error('income.turnover')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="income_other" class="form-label">
                                        Other Income
                                        <i class="fas fa-info-circle text-muted" 
                                           data-bs-toggle="tooltip" 
                                           title="Any other business income"></i>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="income[other]" id="income_other"
                                               class="form-control @error('income.other') is-invalid @enderror"
                                               value="{{ old('income.other', $submission->income_json['other'] ?? '') }}"
                                               step="0.01" min="-99999999.99" max="99999999.99"
                                               placeholder="0.00">
                                    </div>
                                    @error('income.other')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="alert alert-light border">
                                    <strong>Total Income:</strong> 
                                    <span id="total-income" class="text-primary fs-5">£{{ number_format($submission->total_income, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Card -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Expenses
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Expense Mode Selection -->
                            <div class="mb-4">
                                <label class="form-label">How would you like to enter expenses?</label>
                                @php
                                    $hasConsolidated = isset($submission->expenses_json['consolidated_expenses']);
                                    $hasBreakdown = isset($submission->expenses_json['breakdown']);
                                    $defaultMode = $hasConsolidated ? 'consolidated' : 'breakdown';
                                @endphp
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="expense_mode" id="expense_consolidated" 
                                           value="consolidated" {{ old('expense_mode', $defaultMode) == 'consolidated' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="expense_consolidated">
                                        <i class="fas fa-calculator me-2"></i>
                                        Consolidated Total
                                    </label>

                                    <input type="radio" class="btn-check" name="expense_mode" id="expense_breakdown" 
                                           value="breakdown" {{ old('expense_mode', $defaultMode) == 'breakdown' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="expense_breakdown">
                                        <i class="fas fa-list-ul me-2"></i>
                                        Detailed Breakdown
                                    </label>
                                </div>
                            </div>

                            <!-- Consolidated View -->
                            <div id="consolidated-view" class="{{ old('expense_mode', $defaultMode) != 'consolidated' ? 'd-none' : '' }}">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="consolidated_expenses" class="form-label">Total Expenses</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="expenses[consolidated_expenses]" 
                                                   id="consolidated_expenses"
                                                   class="form-control @error('expenses.consolidated_expenses') is-invalid @enderror"
                                                   value="{{ old('expenses.consolidated_expenses', $submission->expenses_json['consolidated_expenses'] ?? '') }}"
                                                   step="0.01" min="0" max="99999999.99"
                                                   placeholder="0.00">
                                        </div>
                                        @error('expenses.consolidated_expenses')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Breakdown View -->
                            <div id="breakdown-view" class="{{ old('expense_mode', $defaultMode) != 'breakdown' ? 'd-none' : '' }}">
                                <div class="row g-3">
                                    @php
                                        $expenseFields = [
                                            'cost_of_goods' => ['label' => 'Cost of Goods', 'tooltip' => 'Cost of goods bought for resale'],
                                            'staff_costs' => ['label' => 'Staff Costs', 'tooltip' => 'Wages, salaries, employer NI, pensions'],
                                            'travel_costs' => ['label' => 'Travel Costs', 'tooltip' => 'Business travel and accommodation'],
                                            'premises_running_costs' => ['label' => 'Premises Running Costs', 'tooltip' => 'Rent, rates, utilities'],
                                            'maintenance_costs' => ['label' => 'Maintenance Costs', 'tooltip' => 'Repairs and renewals'],
                                            'admin_costs' => ['label' => 'Admin Costs', 'tooltip' => 'Phone, stationery, postage'],
                                            'business_entertainment_costs' => ['label' => 'Business Entertainment', 'tooltip' => 'Client entertainment (limited relief)'],
                                            'advertising_costs' => ['label' => 'Advertising Costs', 'tooltip' => 'Advertising and marketing'],
                                            'interest_on_bank_other_loans' => ['label' => 'Interest on Loans', 'tooltip' => 'Bank and loan interest'],
                                            'financial_charges' => ['label' => 'Financial Charges', 'tooltip' => 'Bank charges, credit card fees'],
                                            'bad_debt' => ['label' => 'Bad Debt', 'tooltip' => 'Debts written off'],
                                            'professional_fees' => ['label' => 'Professional Fees', 'tooltip' => 'Accountant, solicitor fees'],
                                            'depreciation' => ['label' => 'Depreciation', 'tooltip' => 'Depreciation of equipment'],
                                            'other_expenses' => ['label' => 'Other Expenses', 'tooltip' => 'Any other allowable expenses'],
                                        ];
                                    @endphp

                                    @foreach($expenseFields as $key => $field)
                                        <div class="col-md-6">
                                            <label for="expense_{{ $key }}" class="form-label">
                                                {{ $field['label'] }}
                                                <i class="fas fa-info-circle text-muted" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ $field['tooltip'] }}"></i>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">£</span>
                                                <input type="number" 
                                                       name="expenses[breakdown][{{ $key }}]" 
                                                       id="expense_{{ $key }}"
                                                       class="form-control expense-breakdown-input @error('expenses.breakdown.' . $key) is-invalid @enderror"
                                                       value="{{ old('expenses.breakdown.' . $key, $submission->expenses_json['breakdown'][$key] ?? '') }}"
                                                       step="0.01" min="-99999999.99" max="99999999.99"
                                                       placeholder="0.00">
                                            </div>
                                            @error('expenses.breakdown.' . $key)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="alert alert-light border">
                                    <strong>Total Expenses:</strong> 
                                    <span id="total-expenses" class="text-danger fs-5">£{{ number_format($submission->total_expenses, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Card -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                Notes (Optional)
                            </h5>
                        </div>
                        <div class="card-body">
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Add any notes about this submission...">{{ old('notes', $submission->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Summary Card -->
                    <div class="card shadow-sm border-primary rounded-3 mb-4">
                        <div class="card-header bg-primary bg-opacity-10">
                            <h5 class="card-title mb-0 text-primary">
                                <i class="fas fa-calculator me-2"></i>
                                Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-7">Total Income:</dt>
                                <dd class="col-5 text-end text-success" id="summary-income">
                                    £{{ number_format($submission->total_income, 2) }}
                                </dd>

                                <dt class="col-7">Total Expenses:</dt>
                                <dd class="col-5 text-end text-danger" id="summary-expenses">
                                    £{{ number_format($submission->total_expenses, 2) }}
                                </dd>

                                <dt class="col-7 border-top pt-2">Net Profit/Loss:</dt>
                                <dd class="col-5 text-end border-top pt-2 fw-bold" id="summary-profit">
                                    <span class="{{ $submission->net_profit >= 0 ? 'text-primary' : 'text-danger' }}">
                                        £{{ number_format($submission->net_profit, 2) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                                <a href="{{ route('hmrc.submissions.show', $submission) }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Include P&L Preview Modal -->
        @include('hmrc.submissions.partials.pl-preview-modal')
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle expense mode toggle
    document.querySelectorAll('input[name="expense_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'consolidated') {
                document.getElementById('consolidated-view').classList.remove('d-none');
                document.getElementById('breakdown-view').classList.add('d-none');
            } else {
                document.getElementById('consolidated-view').classList.add('d-none');
                document.getElementById('breakdown-view').classList.remove('d-none');
            }
            updateTotals();
        });
    });

    // Update totals
    function updateTotals() {
        // Calculate total income
        const turnover = parseFloat(document.getElementById('income_turnover').value) || 0;
        const other = parseFloat(document.getElementById('income_other').value) || 0;
        const totalIncome = turnover + other;
        
        document.getElementById('total-income').textContent = '£' + formatNumber(totalIncome);
        document.getElementById('summary-income').textContent = '£' + formatNumber(totalIncome);

        // Calculate total expenses
        let totalExpenses = 0;
        const expenseMode = document.querySelector('input[name="expense_mode"]:checked').value;
        
        if (expenseMode === 'consolidated') {
            totalExpenses = parseFloat(document.getElementById('consolidated_expenses').value) || 0;
        } else {
            document.querySelectorAll('.expense-breakdown-input').forEach(input => {
                totalExpenses += parseFloat(input.value) || 0;
            });
        }
        
        document.getElementById('total-expenses').textContent = '£' + formatNumber(totalExpenses);
        document.getElementById('summary-expenses').textContent = '£' + formatNumber(totalExpenses);

        // Calculate net profit
        const netProfit = totalIncome - totalExpenses;
        const profitElement = document.getElementById('summary-profit').querySelector('span');
        profitElement.textContent = '£' + formatNumber(netProfit);
        profitElement.className = netProfit >= 0 ? 'text-primary' : 'text-danger';
    }

    function formatNumber(num) {
        return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Listen to input changes
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', updateTotals);
    });

    // Initialize totals
    updateTotals();
});
</script>
@endpush
@endsection

