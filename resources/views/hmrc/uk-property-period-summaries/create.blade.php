@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-period-summaries.index') }}">UK Property Period Summaries</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Summary</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Create UK Property Period Summary</h1>
                <p class="text-muted mb-0">Submit quarterly or period income and expenses for your UK property business</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="period-summary-form" method="POST" action="{{ route('hmrc.uk-property-period-summaries.store') }}">
            @csrf

            <!-- Hidden fields -->
            @if($obligation ?? false)
                <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
            @endif

            <!-- Progress Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="progress-steps">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Period Details</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Property Data</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Review</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Period Details -->
            <div class="form-step active" id="step-1">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Period Details
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($obligation ?? false)
                            <div class="alert alert-info border-start border-4 border-info mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-link me-2 mt-1"></i>
                                    <div>
                                        <strong class="d-block">Linked Obligation</strong>
                                        <small class="text-muted">
                                            Period: {{ $obligation->period_key }} |
                                            Type: {{ $obligation->getObligationTypeLabel() }} |
                                            @if($obligation?->period_start_date && $obligation?->period_end_date)
                                                Dates: {{ $obligation?->period_start_date->format('d M Y') }} - {{ $obligation?->period_end_date->format('d M Y') }} |
                                            @endif
                                            Due: {{ optional($obligation?->due_date)->format('d M Y') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <!-- Business Selection -->
                            <div class="col-md-6">
                                <label for="business_id" class="form-label required">UK Property Business</label>
                                <select name="business_id" id="business_id" class="form-select @error('business_id') is-invalid @enderror" required>
                                    <option value="">Select a business</option>
                                    @foreach($businesses as $business)
                                        <option value="{{ $business->business_id }}"
                                                {{ old('business_id', $obligation->business_id ?? request('business_id')) == $business->business_id ? 'selected' : '' }}
                                                data-nino="{{ $business->nino ?? '' }}">
                                            {{ $business->trading_name ?? $business->business_id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('business_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tax Year -->
                            <div class="col-md-6">
                                <label for="tax_year" class="form-label required">Tax Year</label>
                                <select name="tax_year" id="tax_year" class="form-select @error('tax_year') is-invalid @enderror" required>
                                    @php
                                        $currentYear = date('Y');
                                        $currentMonth = date('n');
                                        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
                                    @endphp
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $year = $startYear - $i;
                                            $taxYear = $year . '-' . substr($year + 1, 2);
                                            $selectedTaxYear = old('tax_year', $obligation->tax_year ?? request('tax_year', $taxYear));
                                        @endphp
                                        <option value="{{ $taxYear }}" {{ $selectedTaxYear == $taxYear ? 'selected' : '' }}>
                                            {{ $taxYear }} ({{ $year }}/{{ $year + 1 }})
                                        </option>
                                    @endfor
                                </select>
                                @error('tax_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Quarterly Period (Legacy - before 2025-26) -->
                            <div class="col-md-12" id="quarterly_period_section">
                                <label for="quarterly_period" class="form-label required">Quarterly Period</label>
                                <select name="quarterly_period" id="quarterly_period"
                                        class="form-select @error('quarterly_period') is-invalid @enderror">
                                    <option value="">Select a tax year first</option>
                                </select>
                                <small class="text-muted">Standard HMRC quarterly periods for Making Tax Digital</small>
                                @error('quarterly_period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cumulative Period (2025-26 onwards) -->
                            <div class="col-md-6" id="from_date_section" style="display: none;">
                                <label for="from_date_input" class="form-label required">From Date</label>
                                <input type="date" name="from_date_input" id="from_date_input"
                                       class="form-control @error('from_date') is-invalid @enderror"
                                       value="{{ old('from_date', $obligation?->period_start_date->format('Y-m-d') ?? '') }}">
                                <small class="text-muted">Must be within the selected tax year (6 April - 5 April)</small>
                                @error('from_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="to_date_section" style="display: none;">
                                <label for="to_date_input" class="form-label required">To Date</label>
                                <input type="date" name="to_date_input" id="to_date_input"
                                       class="form-control @error('to_date') is-invalid @enderror"
                                       value="{{ old('to_date', $obligation?->period_end_date->format('Y-m-d') ?? '') }}">
                                <small class="text-muted">Must be within the selected tax year and after from date</small>
                                @error('to_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12" id="cumulative_info" style="display: none;">
                                <div class="alert alert-info border-start border-4 border-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Cumulative Period Summary (2025-26 onwards)</strong>
                                    <p class="mb-0 mt-2 small">
                                        You can submit cumulative periods within the tax year (6 April - 5 April).
                                        You may resubmit overlapping periods to update your data.
                                    </p>
                                    <ul class="mb-0 mt-2 small">
                                        <li>End date must be equal to or later than your previous submissions (cumulative)</li>
                                        <li>Cannot create duplicate submissions with identical dates</li>
                                        <li>Allows you to extend or update periods as your property income/expenses accumulate</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Hidden fields for actual submission -->
                            <input type="hidden" name="from_date" id="from_date" value="{{ old('from_date', $obligation?->period_start_date?->format('Y-m-d') ?? '') }}">
                            <input type="hidden" name="to_date" id="to_date" value="{{ old('to_date', $obligation?->period_end_date?->format('Y-m-d') ?? '') }}">

                            <!-- NINO -->
                            <div class="col-md-6">
                                <label for="nino" class="form-label">National Insurance Number (NINO)</label>
                                <input type="text" name="nino" id="nino"
                                       class="form-control @error('nino') is-invalid @enderror"
                                       value="{{ old('nino') }}"
                                       placeholder="AB123456C"
                                       pattern="^[A-Z]{2}[0-9]{6}[A-Z]$">
                                <small class="text-muted">Format: AB123456C (optional if already on business)</small>
                                @error('nino')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(config('hmrc.environment') === 'sandbox')
                                <div class="col-md-6">
                                    <label for="test_scenario" class="form-label">
                                        Test Scenario
                                        <span class="badge bg-warning text-dark">Sandbox Only</span>
                                    </label>
                                    <select name="test_scenario" id="test_scenario" class="form-select">
                                        <option value="">No Test Scenario</option>
                                        <option value="NOT_FOUND" {{ old('test_scenario') == 'NOT_FOUND' ? 'selected' : '' }}>NOT_FOUND</option>
                                        <option value="STATEFUL" {{ old('test_scenario') == 'STATEFUL' ? 'selected' : '' }}>STATEFUL</option>
                                        <option value="OUTSIDE_AMENDMENT_WINDOW" {{ old('test_scenario') == 'OUTSIDE_AMENDMENT_WINDOW' ? 'selected' : '' }}>OUTSIDE_AMENDMENT_WINDOW</option>
                                    </select>
                                    <small class="text-muted">Select a test scenario to simulate specific HMRC API responses</small>
                                </div>
                            @endif
                        </div>

                        <!-- Existing Periods Warning -->
                        @if(!empty($existingPeriods))
                            <div class="alert alert-info mt-4 mb-0">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Existing Periods for Selected Business
                                </h6>
                                <p class="mb-2 text-sm">To avoid overlapping submissions, here are the periods already created:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>From Date</th>
                                                <th>To Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($existingPeriods as $period)
                                                <tr>
                                                    <td>{{ $period->from_date->format('d M Y') }}</td>
                                                    <td>{{ $period->to_date->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $period->status_badge['class'] }}">
                                                            {{ $period->status_badge['text'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary next-step">
                                Next: Property Data <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Property Data (FHL & Non-FHL OR Unified) -->
            <div class="form-step" id="step-2">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-home me-2"></i>
                            Property Income & Expenses
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Unified Form for 2025-26+ (Initially hidden) -->
                        <div id="unified-property-container" style="display: none;">
                            @include('hmrc.uk-property-period-summaries.partials.unified-property-form', [
                                'ukPropertyIncome' => old('uk_property_income', []),
                                'ukPropertyExpenses' => old('uk_property_expenses', [])
                            ])
                        </div>

                        <!-- Legacy Form for <=2024-25 (Initially visible) -->
                        <div id="legacy-property-container">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs mb-4" id="propertyTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="fhl-tab" data-bs-toggle="tab" data-bs-target="#fhl" type="button" role="tab">
                                        <i class="fas fa-umbrella-beach me-2"></i>Furnished Holiday Lettings (FHL)
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="non-fhl-tab" data-bs-toggle="tab" data-bs-target="#non-fhl" type="button" role="tab">
                                        <i class="fas fa-building me-2"></i>Non-FHL Property
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="propertyTabsContent">
                            <!-- FHL Tab -->
                            <div class="tab-pane fade show active" id="fhl" role="tabpanel">
                                <!-- FHL Income -->
                                <h5 class="mb-3">FHL Income</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Rental Income</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_income[period_amount]" class="form-control"
                                                   value="{{ old('fhl_income.period_amount', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tax Deducted</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_income[tax_deducted]" class="form-control"
                                                   value="{{ old('fhl_income.tax_deducted', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rent a Room - Rents Received</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_income[rent_a_room][rents_received]" class="form-control"
                                                   value="{{ old('fhl_income.rent_a_room.rents_received', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <!-- FHL Expenses -->
                                <h5 class="mb-3 mt-4">FHL Expenses</h5>

                                <!-- Error message for consolidated vs itemized -->
                                @error('fhl_expenses')
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i> {{ $message }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @enderror

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Consolidated Expenses</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[consolidated_expenses]"
                                                   id="fhl_consolidated_expenses"
                                                   class="form-control @error('fhl_expenses') is-invalid @enderror"
                                                   value="{{ old('fhl_expenses.consolidated_expenses', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                        <small class="text-muted">Use this OR itemize expenses below (not both)</small>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                        <p class="text-muted mb-2"><strong>OR itemize individual expenses:</strong></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Premises Running Costs</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[premises_running_costs]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.premises_running_costs', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Repairs and Maintenance</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[repairs_and_maintenance]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.repairs_and_maintenance', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Financial Costs</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[financial_costs]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.financial_costs', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Professional Fees</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[professional_fees]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.professional_fees', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cost of Services</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[cost_of_services]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.cost_of_services', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Travel Costs</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[travel_costs]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.travel_costs', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Other Expenses</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[other]"
                                                   class="form-control fhl-itemized-expense"
                                                   value="{{ old('fhl_expenses.other', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rent a Room - Amount Claimed</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="fhl_expenses[rent_a_room][amount_claimed]" class="form-control"
                                                   value="{{ old('fhl_expenses.rent_a_room.amount_claimed', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Non-FHL Tab -->
                            <div class="tab-pane fade" id="non-fhl" role="tabpanel">
                                <!-- Non-FHL Income -->
                                <h5 class="mb-3">Non-FHL Income</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Rental Income</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_income[period_amount]" class="form-control"
                                                   value="{{ old('non_fhl_income.period_amount', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tax Deducted</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_income[tax_deducted]" class="form-control"
                                                   value="{{ old('non_fhl_income.tax_deducted', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Premiums of Lease Grant</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_income[premiums_of_lease_grant]" class="form-control"
                                                   value="{{ old('non_fhl_income.premiums_of_lease_grant', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Reverse Premiums</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_income[reverse_premiums]" class="form-control"
                                                   value="{{ old('non_fhl_income.reverse_premiums', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Other Income</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_income[other_income]" class="form-control"
                                                   value="{{ old('non_fhl_income.other_income', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rent a Room - Rents Received</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_income[rent_a_room][rents_received]" class="form-control"
                                                   value="{{ old('non_fhl_income.rent_a_room.rents_received', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <!-- Non-FHL Expenses -->
                                <h5 class="mb-3 mt-4">Non-FHL Expenses</h5>

                                <!-- Error message for consolidated vs itemized -->
                                @error('non_fhl_expenses')
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i> {{ $message }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @enderror

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Consolidated Expenses</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[consolidated_expenses]"
                                                   id="non_fhl_consolidated_expenses"
                                                   class="form-control @error('non_fhl_expenses') is-invalid @enderror"
                                                   value="{{ old('non_fhl_expenses.consolidated_expenses', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                        <small class="text-muted">Use this OR itemize expenses below (not both)</small>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                        <p class="text-muted mb-2"><strong>OR itemize individual expenses:</strong></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Premises Running Costs</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[premises_running_costs]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.premises_running_costs', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Repairs and Maintenance</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[repairs_and_maintenance]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.repairs_and_maintenance', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Financial Costs</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[financial_costs]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.financial_costs', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Professional Fees</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[professional_fees]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.professional_fees', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cost of Services</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[cost_of_services]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.cost_of_services', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Travel Costs</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[travel_costs]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.travel_costs', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Other Expenses</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[other]"
                                                   class="form-control non-fhl-itemized-expense"
                                                   value="{{ old('non_fhl_expenses.other', '') }}"
                                                   step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                                   data-expense-type="non-fhl">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Residential Financial Cost</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[residential_financial_cost]" class="form-control"
                                                   value="{{ old('non_fhl_expenses.residential_financial_cost', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Residential Financial Costs Carried Forward</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[residential_financial_costs_carried_forward]" class="form-control"
                                                   value="{{ old('non_fhl_expenses.residential_financial_costs_carried_forward', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rent a Room - Amount Claimed</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" name="non_fhl_expenses[rent_a_room][amount_claimed]" class="form-control"
                                                   value="{{ old('non_fhl_expenses.rent_a_room.amount_claimed', '') }}"
                                                   step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        {{-- End Legacy Property Container --}}

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary prev-step">
                                <i class="fas fa-arrow-left me-2"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next: Review <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Review & Submit -->
            <div class="form-step" id="step-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Review & Submit
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="review-content">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Preparing review...</p>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary prev-step">
                                <i class="fas fa-arrow-left me-2"></i> Previous
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-2"></i> Submit Summary
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .progress-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        padding: 20px 0;
    }

    .progress-steps::before {
        content: '';
        position: absolute;
        top: 45px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }

    .step.active .step-number {
        background: #0d6efd;
        color: white;
    }

    .step.completed .step-number {
        background: #198754;
        color: white;
    }

    .step-label {
        font-size: 14px;
        color: #6c757d;
    }

    .step.active .step-label {
        color: #0d6efd;
        font-weight: 600;
    }

    .form-step {
        display: none;
    }

    .form-step.active {
        display: block;
    }

    .required::after {
        content: ' *';
        color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle tax year selection - switch between legacy and unified forms
    function updatePropertyFormBasedOnTaxYear() {
        const taxYear = document.getElementById('tax_year').value;
        const legacyContainer = document.getElementById('legacy-property-container');
        const unifiedContainer = document.getElementById('unified-property-container');

        // Tax years 2025-26 and later use the unified structure
        if (taxYear >= '2025-26') {
            legacyContainer.style.display = 'none';
            unifiedContainer.style.display = 'block';
            // Disable legacy fields to prevent submission
            disableFormFields(legacyContainer);
            enableFormFields(unifiedContainer);
        } else {
            legacyContainer.style.display = 'block';
            unifiedContainer.style.display = 'none';
            // Disable unified fields to prevent submission
            disableFormFields(unifiedContainer);
            enableFormFields(legacyContainer);
        }
    }

    function disableFormFields(container) {
        const inputs = container.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.disabled = true;
        });
    }

    function enableFormFields(container) {
        const inputs = container.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.disabled = false;
        });
    }

    // Update quarterly periods based on selected tax year
    function updateQuarterlyPeriods() {
        const taxYearSelect = document.getElementById('tax_year');
        const quarterlyPeriodSelect = document.getElementById('quarterly_period');

        if (!taxYearSelect.value) {
            quarterlyPeriodSelect.innerHTML = '<option value="">Select a tax year first</option>';
            return;
        }

        // Parse tax year (e.g., "2024-25" -> 2024)
        const taxYear = taxYearSelect.value;
        const year = parseInt(taxYear.split('-')[0]);
        const nextYear = year + 1;

        // Define quarters with their dates and deadlines
        const quarters = [
            {label: 'Q1', start: '-04-06', end: '-07-05', deadline: 'Deadline: 5 August'},
            {label: 'Q2', start: '-07-06', end: '-10-05', deadline: 'Deadline: 5 November'},
            {label: 'Q3', start: '-10-06', end: '-01-05', deadline: 'Deadline: 5 February'},
            {label: 'Q4', start: '-01-06', end: '-04-05', deadline: 'Deadline: 5 May'}
        ];

        // Clear existing options
        quarterlyPeriodSelect.innerHTML = '<option value="">Select a quarterly period</option>';

        // Add quarterly options for the selected tax year
        quarters.forEach(quarter => {
            let startDate, endDate;

            // Handle Q3 and Q4 which span into next year
            if (quarter.label === 'Q3' || quarter.label === 'Q4') {
                if (quarter.label === 'Q3') {
                    startDate = year + quarter.start;
                    endDate = nextYear + quarter.end;
                } else { // Q4
                    startDate = nextYear + quarter.start;
                    endDate = nextYear + quarter.end;
                }
            } else {
                startDate = year + quarter.start;
                endDate = year + quarter.end;
            }

            const value = startDate + '|' + endDate;

            // Format display text
            const startDateObj = new Date(startDate);
            const endDateObj = new Date(endDate);
            const displayText = formatDate(startDateObj) + ' to ' + formatDate(endDateObj) + ' (' + quarter.deadline + ')';

            const option = document.createElement('option');
            option.value = value;
            option.textContent = displayText;

            // Restore old selection if it matches
            const oldValue = '{{ old("quarterly_period") }}';
            if (oldValue && oldValue === value) {
                option.selected = true;
            }

            quarterlyPeriodSelect.appendChild(option);
        });
    }

    // Helper function to format dates
    function formatDate(date) {
        const day = date.getDate().toString().padStart(2, '0');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        return day + ' ' + month + ' ' + year;
    }

    // Function to toggle between quarterly period and cumulative period based on tax year
    function togglePeriodInputs() {
        const taxYear = document.getElementById('tax_year').value;
        const isCumulative = taxYear >= '2025-26';

        const quarterlySection = document.getElementById('quarterly_period_section');
        const fromDateSection = document.getElementById('from_date_section');
        const toDateSection = document.getElementById('to_date_section');
        const cumulativeInfo = document.getElementById('cumulative_info');
        const quarterlyPeriod = document.getElementById('quarterly_period');
        const fromDateInput = document.getElementById('from_date_input');
        const toDateInput = document.getElementById('to_date_input');

        if (isCumulative) {
            // Show cumulative date inputs
            quarterlySection.style.display = 'none';
            fromDateSection.style.display = 'block';
            toDateSection.style.display = 'block';
            cumulativeInfo.style.display = 'block';

            // Set required attributes
            quarterlyPeriod.removeAttribute('required');
            fromDateInput.setAttribute('required', 'required');
            toDateInput.setAttribute('required', 'required');

            // Set min/max dates based on tax year
            if (taxYear) {
                const year = parseInt(taxYear.split('-')[0]);
                const minDate = year + '-04-06';
                const maxDate = (year + 1) + '-04-05';

                fromDateInput.setAttribute('min', minDate);
                fromDateInput.setAttribute('max', maxDate);
                toDateInput.setAttribute('min', minDate);
                toDateInput.setAttribute('max', maxDate);
            }
        } else {
            // Show quarterly period dropdown
            quarterlySection.style.display = 'block';
            fromDateSection.style.display = 'none';
            toDateSection.style.display = 'none';
            cumulativeInfo.style.display = 'none';

            // Set required attributes
            quarterlyPeriod.setAttribute('required', 'required');
            fromDateInput.removeAttribute('required');
            toDateInput.removeAttribute('required');
        }
    }

    // Listen for tax year changes
    document.getElementById('tax_year').addEventListener('change', function() {
        togglePeriodInputs();
        updatePropertyFormBasedOnTaxYear();
        updateQuarterlyPeriods();
        // Clear the dates when tax year changes
        document.getElementById('from_date').value = '';
        document.getElementById('to_date').value = '';
        document.getElementById('from_date_input').value = '';
        document.getElementById('to_date_input').value = '';
    });

    // Initialize form on page load
    togglePeriodInputs();
    updatePropertyFormBasedOnTaxYear();
    updateQuarterlyPeriods();

    // If there's an obligation, auto-select the quarterly period based on dates
    @if($obligation ?? false)
        const obligationFromDate = '{{ $obligation?->period_start_date?->format("Y-m-d") }}';
        const obligationToDate = '{{ $obligation?->period_end_date?->format("Y-m-d") }}';
        const obligationTaxYear = '{{ $obligation?->tax_year }}';

        if (obligationFromDate && obligationToDate) {
            const isCumulativeTaxYear = obligationTaxYear >= '2025-26';

            if (isCumulativeTaxYear) {
                // For cumulative periods (2025-26+), directly populate the date inputs
                setTimeout(function() {
                    const fromDateInput = document.getElementById('from_date_input');
                    const toDateInput = document.getElementById('to_date_input');

                    if (fromDateInput && toDateInput) {
                        fromDateInput.value = obligationFromDate;
                        toDateInput.value = obligationToDate;

                        // Trigger change events to update hidden fields
                        fromDateInput.dispatchEvent(new Event('change'));
                        toDateInput.dispatchEvent(new Event('change'));
                    }
                }, 100);
            } else {
                // For legacy periods (pre-2025-26), auto-select quarterly period
                setTimeout(function() {
                    const quarterlyPeriodSelect = document.getElementById('quarterly_period');
                    const expectedValue = obligationFromDate + '|' + obligationToDate;

                    // Try to find and select the matching quarterly period
                    for (let i = 0; i < quarterlyPeriodSelect.options.length; i++) {
                        if (quarterlyPeriodSelect.options[i].value === expectedValue) {
                            quarterlyPeriodSelect.value = expectedValue;
                            quarterlyPeriodSelect.dispatchEvent(new Event('change'));
                            break;
                        }
                    }
                }, 100);
            }
        }
    @endif

    // If there's an old quarterly period value, trigger the change to populate dates
    const oldQuarterlyPeriod = '{{ old("quarterly_period") }}';
    if (oldQuarterlyPeriod && document.getElementById('quarterly_period').value) {
        document.getElementById('quarterly_period').dispatchEvent(new Event('change'));
    }

    // Handle business selection - auto-fill NINO and get existing periods
    document.getElementById('business_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const nino = selectedOption.dataset.nino;
        if (nino) {
            document.getElementById('nino').value = nino;
        }
    });

    // Handle quarterly period selection
    const quarterlyPeriodSelect = document.getElementById('quarterly_period');
    if (quarterlyPeriodSelect) {
        quarterlyPeriodSelect.addEventListener('change', function() {
            if (this.value) {
                const dates = this.value.split('|');
                document.getElementById('from_date').value = dates[0];
                document.getElementById('to_date').value = dates[1];
            } else {
                document.getElementById('from_date').value = '';
                document.getElementById('to_date').value = '';
            }
        });
    }

    // Handle cumulative period date inputs
    const fromDateInput = document.getElementById('from_date_input');
    const toDateInput = document.getElementById('to_date_input');

    if (fromDateInput) {
        fromDateInput.addEventListener('change', function() {
            const fromDate = this.value;
            document.getElementById('from_date').value = fromDate;

            // Update to_date minimum to be after from_date
            if (fromDate && toDateInput) {
                toDateInput.setAttribute('min', fromDate);
            }
        });
    }

    if (toDateInput) {
        toDateInput.addEventListener('change', function() {
            const toDate = this.value;
            document.getElementById('to_date').value = toDate;

            // Validate that to_date is after from_date
            const fromDate = fromDateInput.value;
            if (fromDate && toDate && toDate < fromDate) {
                this.setCustomValidity('To date must be after from date');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Handle form step navigation
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', function() {
            if (currentStep === 2) {
                // On step 2, generate review before proceeding
                generateReview();
            }
            if (validateCurrentStep()) {
                goToStep(currentStep + 1);
            }
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', function() {
            goToStep(currentStep - 1);
        });
    });

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        // Hide current step
        document.getElementById('step-' + currentStep).classList.remove('active');
        document.querySelector('.step[data-step="' + currentStep + '"]').classList.remove('active');

        // Mark completed
        if (step > currentStep) {
            document.querySelector('.step[data-step="' + currentStep + '"]').classList.add('completed');
        } else if (step < currentStep) {
            document.querySelector('.step[data-step="' + currentStep + '"]').classList.remove('completed');
        }

        // Show next step
        currentStep = step;
        document.getElementById('step-' + currentStep).classList.add('active');
        document.querySelector('.step[data-step="' + currentStep + '"]').classList.add('active');

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        const currentStepEl = document.getElementById('step-' + currentStep);
        const requiredInputs = currentStepEl.querySelectorAll('[required]');

        let isValid = true;
        requiredInputs.forEach(input => {
            if (!input.value) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    function generateReview() {
        const reviewContent = document.getElementById('review-content');
        const taxYear = document.getElementById('tax_year').value;
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const businessOption = document.getElementById('business_id').options[document.getElementById('business_id').selectedIndex];
        const businessName = businessOption.text;

        let html = '<div class="alert alert-info"><strong>Period Summary Review</strong><br>';
        html += 'Business: ' + businessName + '<br>';
        html += 'Tax Year: ' + taxYear + '<br>';
        html += 'Period: ' + fromDate + ' to ' + toDate + '</div>';

        // Check if unified or legacy
        const isUnified = taxYear >= '2025-26';

        if (isUnified) {
            // Generate review for unified property (2025-26+)
            html += '<div class="alert alert-warning"><i class="fas fa-info-circle me-2"></i>Tax Year 2025-26+: FHL and Non-FHL properties are reported together</div>';

            html += '<h6 class="mt-4 mb-3">UK Property Income</h6>';
            html += '<div class="table-responsive"><table class="table table-sm table-bordered">';

            const unifiedIncomeFields = [
                { name: 'premiums_of_lease_grant', label: 'Premiums of Lease Grant' },
                { name: 'reverse_premiums', label: 'Reverse Premiums' },
                { name: 'period_amount', label: 'Rental Income' },
                { name: 'tax_deducted', label: 'Tax Deducted' },
                { name: 'other_income', label: 'Other Income' },
                { name: 'rent_a_room][rents_received', label: 'Rent a Room - Rents Received' }
            ];

            let totalIncome = 0;
            unifiedIncomeFields.forEach(field => {
                const input = document.querySelector(`input[name="uk_property_income[${field.name}]"]`);
                if (input && input.value && parseFloat(input.value) !== 0) {
                    const value = parseFloat(input.value || 0);
                    totalIncome += value;
                    html += `<tr><td>${field.label}</td><td class="text-end fw-bold text-success">£${value.toFixed(2)}</td></tr>`;
                }
            });
            html += `<tr class="table-light"><td class="fw-bold">Total Income</td><td class="text-end fw-bold text-success">£${totalIncome.toFixed(2)}</td></tr>`;
            html += '</table></div>';

            html += '<h6 class="mt-4 mb-3">UK Property Expenses</h6>';
            html += '<div class="table-responsive"><table class="table table-sm table-bordered">';

            const unifiedExpenseFields = [
                { name: 'consolidated_expenses', label: 'Consolidated Expenses' },
                { name: 'premises_running_costs', label: 'Premises Running Costs' },
                { name: 'repairs_and_maintenance', label: 'Repairs and Maintenance' },
                { name: 'financial_costs', label: 'Financial Costs' },
                { name: 'professional_fees', label: 'Professional Fees' },
                { name: 'cost_of_services', label: 'Cost of Services' },
                { name: 'travel_costs', label: 'Travel Costs' },
                { name: 'other', label: 'Other Expenses' },
                { name: 'residential_financial_cost', label: 'Residential Financial Cost' },
                { name: 'residential_financial_costs_carried_forward', label: 'Residential Financial Costs Carried Forward' },
                { name: 'rent_a_room][amount_claimed', label: 'Rent a Room - Amount Claimed' }
            ];

            let totalExpenses = 0;
            unifiedExpenseFields.forEach(field => {
                const input = document.querySelector(`input[name="uk_property_expenses[${field.name}]"]`);
                if (input && input.value && parseFloat(input.value) !== 0) {
                    const value = parseFloat(input.value || 0);
                    totalExpenses += Math.abs(value);
                    html += `<tr><td>${field.label}</td><td class="text-end fw-bold text-danger">£${Math.abs(value).toFixed(2)}</td></tr>`;
                }
            });
            html += `<tr class="table-light"><td class="fw-bold">Total Expenses</td><td class="text-end fw-bold text-danger">£${totalExpenses.toFixed(2)}</td></tr>`;
            html += '</table></div>';

            html += `<div class="alert alert-secondary mt-4">
                <h6 class="fw-bold">Net Position</h6>
                <p class="mb-0 fs-5">£${(totalIncome - totalExpenses).toFixed(2)}</p>
            </div>`;

        } else {
            // Generate review for legacy FHL/Non-FHL (<=2024-25)
            html += '<h6 class="mt-4">FHL Property</h6>';
            const fhlIncome = parseFloat(document.querySelector('input[name="fhl_income[period_amount]"]')?.value || 0);
            const fhlExpenses = parseFloat(document.querySelector('input[name="fhl_expenses[consolidated_expenses]"]')?.value || 0);
            html += '<p>Income: £' + fhlIncome.toFixed(2) + '<br>Expenses: £' + Math.abs(fhlExpenses).toFixed(2) + '</p>';

            html += '<h6 class="mt-4">Non-FHL Property</h6>';
            const nonFhlIncome = parseFloat(document.querySelector('input[name="non_fhl_income[period_amount]"]')?.value || 0);
            const nonFhlExpenses = parseFloat(document.querySelector('input[name="non_fhl_expenses[consolidated_expenses]"]')?.value || 0);
            html += '<p>Income: £' + nonFhlIncome.toFixed(2) + '<br>Expenses: £' + Math.abs(nonFhlExpenses).toFixed(2) + '</p>';

            const totalIncome = fhlIncome + nonFhlIncome;
            const totalExpenses = Math.abs(fhlExpenses) + Math.abs(nonFhlExpenses);
            html += `<div class="alert alert-secondary mt-4">
                <h6 class="fw-bold">Total Net Position</h6>
                <p class="mb-0">Total Income: £${totalIncome.toFixed(2)}<br>
                Total Expenses: £${totalExpenses.toFixed(2)}<br>
                <strong>Net: £${(totalIncome - totalExpenses).toFixed(2)}</strong></p>
            </div>`;
        }

        reviewContent.innerHTML = html;
    }

    // Consolidated vs Itemized Expense Validation
    function setupExpenseValidation() {
        // FHL Expenses
        const fhlConsolidated = document.getElementById('fhl_consolidated_expenses');
        const fhlItemized = document.querySelectorAll('.fhl-itemized-expense');

        if (fhlConsolidated) {
            fhlConsolidated.addEventListener('input', function() {
                // Check if field has been filled (even with 0)
                const hasValue = this.value !== '' && this.value !== null;
                fhlItemized.forEach(input => {
                    if (hasValue) {
                        input.disabled = true;
                        input.classList.add('bg-light');
                        input.value = '';
                    } else {
                        input.disabled = false;
                        input.classList.remove('bg-light');
                    }
                });
            });
        }

        fhlItemized.forEach(input => {
            input.addEventListener('input', function() {
                let hasAnyItemized = false;
                fhlItemized.forEach(item => {
                    // Check if field has been filled (even with 0)
                    if (item.value !== '' && item.value !== null) {
                        hasAnyItemized = true;
                    }
                });

                if (fhlConsolidated) {
                    if (hasAnyItemized) {
                        fhlConsolidated.disabled = true;
                        fhlConsolidated.classList.add('bg-light');
                        fhlConsolidated.value = '';
                    } else {
                        fhlConsolidated.disabled = false;
                        fhlConsolidated.classList.remove('bg-light');
                    }
                }
            });
        });

        // Non-FHL Expenses
        const nonFhlConsolidated = document.getElementById('non_fhl_consolidated_expenses');
        const nonFhlItemized = document.querySelectorAll('.non-fhl-itemized-expense');

        if (nonFhlConsolidated) {
            nonFhlConsolidated.addEventListener('input', function() {
                // Check if field has been filled (even with 0)
                const hasValue = this.value !== '' && this.value !== null;
                nonFhlItemized.forEach(input => {
                    // Except residential financial costs which can be used with consolidated
                    if (input.name.includes('residential_financial')) {
                        return;
                    }
                    if (hasValue) {
                        input.disabled = true;
                        input.classList.add('bg-light');
                        input.value = '';
                    } else {
                        input.disabled = false;
                        input.classList.remove('bg-light');
                    }
                });
            });
        }

        nonFhlItemized.forEach(input => {
            // Skip residential financial costs
            if (input.name.includes('residential_financial')) {
                return;
            }

            input.addEventListener('input', function() {
                let hasAnyItemized = false;
                nonFhlItemized.forEach(item => {
                    if (item.name.includes('residential_financial')) {
                        return;
                    }
                    // Check if field has been filled (even with 0)
                    if (item.value !== '' && item.value !== null) {
                        hasAnyItemized = true;
                    }
                });

                if (nonFhlConsolidated) {
                    if (hasAnyItemized) {
                        nonFhlConsolidated.disabled = true;
                        nonFhlConsolidated.classList.add('bg-light');
                        nonFhlConsolidated.value = '';
                    } else {
                        nonFhlConsolidated.disabled = false;
                        nonFhlConsolidated.classList.remove('bg-light');
                    }
                }
            });
        });
    }

    // Initialize expense validation
    setupExpenseValidation();
    setupUnifiedExpenseValidation();
});

// Unified Expense Validation for 2025-26+
function setupUnifiedExpenseValidation() {
    const unifiedConsolidated = document.getElementById('unified_consolidated_expenses');
    const unifiedItemized = document.querySelectorAll('.unified-itemized-expense');

    if (unifiedConsolidated) {
        unifiedConsolidated.addEventListener('input', function() {
            // Check if field has been filled (even with 0)
            const hasValue = this.value !== '' && this.value !== null;
            unifiedItemized.forEach(input => {
                if (hasValue) {
                    input.disabled = true;
                    input.classList.add('bg-light');
                    input.value = '';
                } else {
                    input.disabled = false;
                    input.classList.remove('bg-light');
                }
            });
        });
    }

    unifiedItemized.forEach(input => {
        input.addEventListener('input', function() {
            let hasAnyItemized = false;
            unifiedItemized.forEach(item => {
                // Check if field has been filled (even with 0)
                if (item.value !== '' && item.value !== null) {
                    hasAnyItemized = true;
                }
            });

            if (unifiedConsolidated) {
                if (hasAnyItemized) {
                    unifiedConsolidated.disabled = true;
                    unifiedConsolidated.classList.add('bg-light');
                    unifiedConsolidated.value = '';
                } else {
                    unifiedConsolidated.disabled = false;
                    unifiedConsolidated.classList.remove('bg-light');
                }
            }
        });
    });
}

</script>
@endpush
@endsection
