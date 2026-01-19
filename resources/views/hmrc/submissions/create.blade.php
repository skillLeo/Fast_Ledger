@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="hmrc-icon-wrapper">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1 page-title">Periodic Submission</h4>
                    <p class="text-muted mb-0 small">Submit quarterly income and expenses to HMRC</p>
                </div>
            </div>
            <x-hmrc.secondary-button type="button" onclick="window.location.href='{{ route('hmrc.submissions.index') }}'" icon="fas fa-arrow-left">
                Back to Submissions
            </x-hmrc.secondary-button>
        </div>

        <div class="max-w-5xl mx-auto">
            <!-- Step Indicator -->
            <div class="step-indicator mb-4">
                <div class="step-track"></div>
                <div class="steps-container">
                    <div class="step active" data-step="1">
                        <div class="step-circle">
                            <i class="fas fa-building"></i>
                        </div>
                        <p class="step-title">Business & Period</p>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">
                            <i class="fas fa-pound-sign"></i>
                        </div>
                        <p class="step-title">Income</p>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <p class="step-title">Expenses</p>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                        </div>
                        <p class="step-title">Review</p>
                    </div>
                </div>
            </div>

            <form id="periodic-submission-form" method="POST" action="{{ route('hmrc.submissions.store') }}">
                @csrf

                <!-- Hidden fields -->
                @if($obligation)
                    <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                    <input type="hidden" name="period_start_date" value="{{ $obligation?->period_start_date?->format('Y-m-d') }}">
                    <input type="hidden" name="period_end_date" value="{{ $obligation?->period_end_date?->format('Y-m-d') }}">
                @endif

                <!-- Step 1: Business & Period -->
                <div class="form-step active" id="step-1">
                    <div class="card hmrc-card">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 text-hmrc">Business & Period Information</h5>

                            @if($obligation)
                                <div class="alert alert-info border-start border-4 border-info mb-4">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-link me-2 mt-1"></i>
                                        <div>
                                            <strong class="d-block">Linked Obligation</strong>
                                            <small class="text-muted">{{ $obligation->period_key }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="business_id" class="form-label">
                                        Business <span class="text-danger">*</span>
                                    </label>
                                    <select name="business_id" id="business_id"
                                            class="form-select @error('business_id') is-invalid @enderror" required>
                                        <option value="">Select a business</option>
                                        @foreach($businesses as $business)
                                            <option value="{{ $business->business_id }}"
                                                    {{ old('business_id', $obligation?->business_id) == $business->business_id ? 'selected' : '' }}
                                                    data-nino="{{ $business->nino ?? '' }}">
                                                {{ $business->trading_name ?? $business->business_id }}
                                                ({{ $business->type_of_business }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('business_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="nino" class="form-label">National Insurance Number (NINO)</label>
                                    <input type="text" name="nino" id="nino"
                                           class="form-control @error('nino') is-invalid @enderror"
                                           value="{{ old('nino', $obligation?->business?->nino) }}"
                                           placeholder="AB123456C"
                                           maxlength="9"
                                           pattern="^[A-Z]{2}[0-9]{6}[A-Z]$">
                                    <small class="text-muted">Format: AB123456C</small>
                                    @error('nino')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @unless($obligation)
                                <div class="col-md-12">
                                    <label for="quarterly_period" class="form-label">
                                        Quarterly Period <span class="text-danger">*</span>
                                    </label>
                                    <select name="quarterly_period" id="quarterly_period"
                                            class="form-select @error('quarterly_period') is-invalid @enderror" required>
                                        <option value="">Select a quarterly period</option>
                                        @php
                                            $currentYear = date('Y');
                                            $currentMonth = date('n');
                                            // Determine current tax year start year
                                            $taxYearStart = $currentMonth >= 4 ? $currentYear : $currentYear - 1;

                                            // Get existing periods
                                            $existingPeriods = $existingPeriods ?? [];

                                            // Define quarters with their dates and deadlines
                                            $quarters = [
                                                ['label' => 'Q1', 'start' => '-04-06', 'end' => '-07-05', 'deadline' => 'Deadline: 5 August'],
                                                ['label' => 'Q2', 'start' => '-07-06', 'end' => '-10-05', 'deadline' => 'Deadline: 5 November'],
                                                ['label' => 'Q3', 'start' => '-10-06', 'end' => '-01-05', 'deadline' => 'Deadline: 5 February'],
                                                ['label' => 'Q4', 'start' => '-01-06', 'end' => '-04-05', 'deadline' => 'Deadline: 5 May'],
                                            ];

                                            // Show last 2 tax years of quarters
                                            for ($yearOffset = 0; $yearOffset < 2; $yearOffset++) {
                                                $year = $taxYearStart - $yearOffset;
                                                $nextYear = $year + 1;

                                                foreach ($quarters as $quarter) {
                                                    // Handle Q3 and Q4 which span into next year
                                                    if ($quarter['label'] === 'Q3' || $quarter['label'] === 'Q4') {
                                                        if ($quarter['label'] === 'Q3') {
                                                            $startDate = $year . $quarter['start'];
                                                            $endDate = $nextYear . $quarter['end'];
                                                        } else { // Q4
                                                            $startDate = $nextYear . $quarter['start'];
                                                            $endDate = $nextYear . $quarter['end'];
                                                        }
                                                    } else {
                                                        $startDate = $year . $quarter['start'];
                                                        $endDate = $year . $quarter['end'];
                                                    }

                                                    // Check if this period already has a submission
                                                    $periodExists = false;
                                                    foreach ($existingPeriods as $existing) {
                                                        if ($existing['start'] === $startDate && $existing['end'] === $endDate) {
                                                            $periodExists = true;
                                                            break;
                                                        }
                                                    }

                                                    // Only show if period doesn't exist
                                                    if (!$periodExists) {
                                                        $value = $startDate . '|' . $endDate;
                                                        $displayText = date('d M Y', strtotime($startDate)) . ' to ' . date('d M Y', strtotime($endDate)) . ' (' . $quarter['deadline'] . ')';
                                                        $selected = old('quarterly_period') == $value ? 'selected' : '';

                                                        echo '<option value="' . $value . '" ' . $selected . '>' . $displayText . '</option>';
                                                    }
                                                }
                                            }
                                        @endphp
                                    </select>
                                    <small class="text-muted">Only showing periods without existing submissions. Standard HMRC quarterly periods for Making Tax Digital.</small>
                                    @error('quarterly_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Hidden fields for actual submission -->
                                <input type="hidden" name="period_start_date" id="period_start_date" value="{{ old('period_start_date') }}">
                                <input type="hidden" name="period_end_date" id="period_end_date" value="{{ old('period_end_date') }}">
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Income -->
                <div class="form-step" id="step-2">
                    <div class="card hmrc-card">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 text-hmrc">Income</h5>

                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label for="income_turnover" class="form-label">Turnover</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="income[turnover]" id="income_turnover"
                                               class="form-control @error('income.turnover') is-invalid @enderror"
                                               value="{{ old('income.turnover') }}"
                                               step="0.01" min="0" max="99999999.99"
                                               placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Total business income from sales/services</small>
                                    <div><small class="text-muted text-xs">Maximum: £99,999,999.99</small></div>
                                    @error('income.turnover')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="income_other" class="form-label">Other Income</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="income[other]" id="income_other"
                                               class="form-control @error('income.other') is-invalid @enderror"
                                               value="{{ old('income.other') }}"
                                               step="0.01" min="-99999999.99" max="99999999.99"
                                               placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Any other business income</small>
                                    @error('income.other')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-info-subtle border border-info rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Income:</span>
                                    <span id="total-income" class="text-hmrc fs-5 fw-bold">£0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Expenses -->
                <div class="form-step" id="step-3">
                    <div class="card hmrc-card">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 text-hmrc">Expenses</h5>

                            <div class="mb-4">
                                <label class="form-label">How would you like to enter expenses?</label>
                                <div class="expense-mode-selector">
                                    <input type="radio" class="btn-check" name="expense_mode" id="expense_consolidated"
                                           value="consolidated" {{ old('expense_mode', 'consolidated') == 'consolidated' ? 'checked' : '' }}>
                                    <label class="expense-mode-option" for="expense_consolidated">
                                        <i class="fas fa-calculator mb-2"></i>
                                        <div class="fw-semibold">Consolidated Total</div>
                                    </label>

                                    <input type="radio" class="btn-check" name="expense_mode" id="expense_breakdown"
                                           value="breakdown" {{ old('expense_mode') == 'breakdown' ? 'checked' : '' }}>
                                    <label class="expense-mode-option" for="expense_breakdown">
                                        <i class="fas fa-list-ul mb-2"></i>
                                        <div class="fw-semibold">Detailed Breakdown</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Consolidated View -->
                            <div id="consolidated-view" class="{{ old('expense_mode', 'consolidated') != 'consolidated' ? 'd-none' : '' }}">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="consolidated_expenses" class="form-label">Total Expenses</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="expenses[consolidated_expenses]"
                                                   id="consolidated_expenses"
                                                   class="form-control @error('expenses.consolidated_expenses') is-invalid @enderror"
                                                   value="{{ old('expenses.consolidated_expenses') }}"
                                                   step="0.01" min="0" max="99999999.99"
                                                   placeholder="0.00">
                                        </div>
                                        <small class="text-muted">Enter the total of all your business expenses</small>
                                        @error('expenses.consolidated_expenses')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Breakdown View -->
                            <div id="breakdown-view" class="{{ old('expense_mode') != 'breakdown' ? 'd-none' : '' }}">
                                <div class="row g-3">
                                    @php
                                        $expenseFields = [
                                            'cost_of_goods' => ['label' => 'Cost of Goods', 'hint' => 'Cost of goods bought for resale'],
                                            'staff_costs' => ['label' => 'Staff Costs', 'hint' => 'Wages, salaries, employer NI, pensions'],
                                            'travel_costs' => ['label' => 'Travel Costs', 'hint' => 'Business travel and accommodation'],
                                            'premises_running_costs' => ['label' => 'Premises Running Costs', 'hint' => 'Rent, rates, utilities'],
                                            'maintenance_costs' => ['label' => 'Maintenance Costs', 'hint' => 'Repairs and renewals'],
                                            'admin_costs' => ['label' => 'Admin Costs', 'hint' => 'Phone, stationery, postage'],
                                            'business_entertainment_costs' => ['label' => 'Business Entertainment', 'hint' => 'Client entertainment (limited relief)'],
                                            'advertising_costs' => ['label' => 'Advertising Costs', 'hint' => 'Advertising and marketing'],
                                            'interest_on_bank_other_loans' => ['label' => 'Interest on Loans', 'hint' => 'Bank and loan interest'],
                                            'financial_charges' => ['label' => 'Financial Charges', 'hint' => 'Bank charges, credit card fees'],
                                            'bad_debt' => ['label' => 'Bad Debt', 'hint' => 'Debts written off'],
                                            'professional_fees' => ['label' => 'Professional Fees', 'hint' => 'Accountant, solicitor fees'],
                                            'depreciation' => ['label' => 'Depreciation', 'hint' => 'Depreciation of equipment'],
                                            'other_expenses' => ['label' => 'Other Expenses', 'hint' => 'Any other allowable expenses'],
                                        ];
                                    @endphp

                                    @foreach($expenseFields as $key => $field)
                                        <div class="col-md-6">
                                            <label for="expense_{{ $key }}" class="form-label">{{ $field['label'] }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">£</span>
                                                <input type="number"
                                                       name="expenses[breakdown][{{ $key }}]"
                                                       id="expense_{{ $key }}"
                                                       class="form-control expense-breakdown-input @error('expenses.breakdown.' . $key) is-invalid @enderror"
                                                       value="{{ old('expenses.breakdown.' . $key) }}"
                                                       step="0.01" min="-99999999.99" max="99999999.99"
                                                       placeholder="0.00">
                                            </div>
                                            <small class="text-muted">{{ $field['hint'] }}</small>
                                            @error('expenses.breakdown.' . $key)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-info-subtle border border-info rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Expenses:</span>
                                    <span id="total-expenses" class="text-hmrc fs-5 fw-bold">£0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review & Submit -->
                <div class="form-step" id="step-4">
                    <div class="card hmrc-card">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 text-hmrc">Review & Submit</h5>

                            <div class="alert alert-info border-start border-4 border-info mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-circle me-2 mt-1"></i>
                                    <div>
                                        <strong class="d-block">Draft Submission</strong>
                                        <small class="text-muted">This will create a draft submission. You can review it before submitting to HMRC.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">Summary</h6>
                                    <div id="review-summary">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="form-control @error('notes') is-invalid @enderror"
                                          maxlength="5000"
                                          placeholder="Add any notes about this submission...">{{ old('notes') }}</textarea>
                                <small class="text-muted"><span id="notes-count">0</span> / 5000 characters</small>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mt-4 pb-5">
                    <x-hmrc.secondary-button type="button" class="prev-step" id="prev-btn" icon="fas fa-chevron-left">
                        Previous
                    </x-hmrc.secondary-button>
                    <x-hmrc.primary-button type="button" class="next-step" id="next-btn" icon="fas fa-chevron-right" iconPosition="right">
                        Next
                    </x-hmrc.primary-button>
                    <x-hmrc.primary-button type="submit" class="d-none" id="submit-btn" icon="fas fa-check">
                        Create Draft Submission
                    </x-hmrc.primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Page Header */
.hmrc-page-header {
    background: white;
    border-bottom: 1px solid #e3e6ea;
    padding: 1rem 1.5rem;
    margin: -1rem -1.5rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #f0f4f8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #17848e;
    font-size: 1.25rem;
}

/* Max Width Container */
.max-w-5xl {
    max-width: 1024px;
    margin: 0 auto;
}

/* Step Indicator */
.step-indicator {
    position: relative;
    padding: 2rem 0;
}

.step-track {
    position: absolute;
    top: 50%;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #e9ecef;
    transform: translateY(-50%);
    z-index: 0;
}

.steps-container {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}

.step {
    flex: 1;
    text-align: center;
    cursor: pointer;
}

.step-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step.active .step-circle {
    background: #17848e;
    color: white;
}

.step.completed .step-circle {
    background: #28a745;
    color: white;
}

.step-title {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

.step.active .step-title {
    color: #17848e;
    font-weight: 600;
}

/* HMRC Card */
.hmrc-card {
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.2s ease;
}

.hmrc-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.hmrc-card .card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #2c3e50;
}

.text-hmrc {
    color: #17848e !important;
}

/* Form Steps */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

/* Expense Mode Selector */
.expense-mode-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.expense-mode-option {
    padding: 1.5rem;
    border: 2px solid #e3e6ea;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.expense-mode-option:hover {
    border-color: #17848e;
    background-color: #f0f4f8;
}

.expense-mode-option i {
    font-size: 1.5rem;
    color: #6c757d;
}

.btn-check:checked + .expense-mode-option {
    border-color: #17848e;
    background-color: #e8f4f8;
}

.btn-check:checked + .expense-mode-option i {
    color: #17848e;
}

/* Info Subtle Background */
.bg-info-subtle {
    background-color: #d1ecf1;
}

/* Text size */
.text-xs {
    font-size: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .hmrc-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .step-title {
        font-size: 0.75rem;
    }

    .expense-mode-selector {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;

    // Initialize
    updateStepDisplay();

    // Business selection - auto-fill NINO
    document.getElementById('business_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const nino = selectedOption.dataset.nino || '';
        document.getElementById('nino').value = nino;
    });

    // NINO input - uppercase
    document.getElementById('nino').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Handle quarterly period selection
    const quarterlyPeriodSelect = document.getElementById('quarterly_period');
    if (quarterlyPeriodSelect) {
        quarterlyPeriodSelect.addEventListener('change', function() {
            if (this.value) {
                const dates = this.value.split('|');
                document.getElementById('period_start_date').value = dates[0];
                document.getElementById('period_end_date').value = dates[1];
            } else {
                document.getElementById('period_start_date').value = '';
                document.getElementById('period_end_date').value = '';
            }
        });
    }

    // Expense mode toggle
    document.querySelectorAll('input[name="expense_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'consolidated') {
                document.getElementById('consolidated-view').classList.remove('d-none');
                document.getElementById('breakdown-view').classList.add('d-none');
                document.querySelectorAll('.expense-breakdown-input').forEach(input => input.value = '');
            } else {
                document.getElementById('consolidated-view').classList.add('d-none');
                document.getElementById('breakdown-view').classList.remove('d-none');
                document.getElementById('consolidated_expenses').value = '';
            }
            calculateTotalExpenses();
        });
    });

    // Calculate totals
    function calculateTotalIncome() {
        const turnover = parseFloat(document.getElementById('income_turnover').value || 0);
        const other = parseFloat(document.getElementById('income_other').value || 0);
        const total = turnover + other;
        document.getElementById('total-income').textContent = formatCurrency(total);
        return total;
    }

    function calculateTotalExpenses() {
        let total = 0;
        const mode = document.querySelector('input[name="expense_mode"]:checked').value;

        if (mode === 'consolidated') {
            total = parseFloat(document.getElementById('consolidated_expenses').value || 0);
        } else {
            document.querySelectorAll('.expense-breakdown-input').forEach(input => {
                total += parseFloat(input.value || 0);
            });
        }

        document.getElementById('total-expenses').textContent = formatCurrency(total);
        return total;
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('en-GB', {
            style: 'currency',
            currency: 'GBP'
        }).format(value);
    }

    // Income inputs
    document.getElementById('income_turnover').addEventListener('input', calculateTotalIncome);
    document.getElementById('income_other').addEventListener('input', calculateTotalIncome);

    // Expense inputs
    document.getElementById('consolidated_expenses').addEventListener('input', calculateTotalExpenses);
    document.querySelectorAll('.expense-breakdown-input').forEach(input => {
        input.addEventListener('input', calculateTotalExpenses);
    });

    // Notes character count
    document.getElementById('notes').addEventListener('input', function() {
        document.getElementById('notes-count').textContent = this.value.length;
    });

    // Step navigation
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                updateStepDisplay();
            }
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', function() {
            currentStep--;
            updateStepDisplay();
        });
    });

    function validateStep(step) {
        if (step === 1) {
            const business = document.getElementById('business_id');
            if (!business.value) {
                alert('Please select a business');
                business.focus();
                return false;
            }

            @unless($obligation)
            const quarterlyPeriod = document.getElementById('quarterly_period');

            if (!quarterlyPeriod.value) {
                alert('Please select a quarterly period');
                quarterlyPeriod.focus();
                return false;
            }
            @endunless
        }

        return true;
    }

    function updateStepDisplay() {
        // Update form steps
        document.querySelectorAll('.form-step').forEach((step, index) => {
            step.classList.toggle('active', index + 1 === currentStep);
        });

        // Update step indicator
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.toggle('active', stepNum === currentStep);
            step.classList.toggle('completed', stepNum < currentStep);
        });

        // Update buttons
        document.getElementById('prev-btn').classList.toggle('d-none', currentStep === 1);
        document.getElementById('next-btn').classList.toggle('d-none', currentStep === totalSteps);
        document.getElementById('submit-btn').classList.toggle('d-none', currentStep !== totalSteps);

        // Update review summary if on step 4
        if (currentStep === 4) {
            updateReviewSummary();
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateReviewSummary() {
        const business = document.getElementById('business_id');
        const businessName = business.options[business.selectedIndex].text;

        @if($obligation)
        const periodStart = '{{ $obligation?->period_start_date?->format("d M Y") }}';
        const periodEnd = '{{ $obligation?->period_end_date?->format("d M Y") }}';
        @else
        const startDateValue = document.getElementById('period_start_date').value;
        const endDateValue = document.getElementById('period_end_date').value;
        const periodStart = startDateValue ? new Date(startDateValue).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) : '';
        const periodEnd = endDateValue ? new Date(endDateValue).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) : '';
        @endif

        const totalIncome = calculateTotalIncome();
        const totalExpenses = calculateTotalExpenses();
        const netProfit = totalIncome - totalExpenses;

        const summary = `
            <div class="review-item">
                <span class="text-muted">Business:</span>
                <span class="fw-semibold">${businessName}</span>
            </div>
            <div class="review-item">
                <span class="text-muted">Period:</span>
                <span class="fw-semibold">${periodStart} - ${periodEnd}</span>
            </div>
            <div class="border-top pt-3 mt-3">
                <div class="review-item">
                    <span class="text-muted">Total Income:</span>
                    <span class="text-success fw-bold">${formatCurrency(totalIncome)}</span>
                </div>
            </div>
            <div class="review-item">
                <span class="text-muted">Total Expenses:</span>
                <span class="text-danger fw-bold">${formatCurrency(totalExpenses)}</span>
            </div>
            <div class="border-top pt-3 mt-3">
                <div class="review-item">
                    <span class="fw-semibold">Net Profit/Loss:</span>
                    <span class="fw-bold ${netProfit >= 0 ? 'text-success' : 'text-danger'}">${formatCurrency(netProfit)}</span>
                </div>
            </div>
        `;

        document.getElementById('review-summary').innerHTML = summary;
    }
});
</script>
<style>
.review-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.review-item:last-child {
    margin-bottom: 0;
}
</style>
@endpush
@endsection
