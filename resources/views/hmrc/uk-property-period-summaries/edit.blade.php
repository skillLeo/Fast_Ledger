@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-period-summaries.index') }}">UK Property Period Summaries</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-period-summaries.show', $summary) }}">{{ $summary->from_date->format('d M Y') }} - {{ $summary->to_date->format('d M Y') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Edit UK Property Period Summary</h1>
                <p class="text-muted mb-0">{{ $summary->business?->trading_name ?? $summary->business_id }} - {{ $summary->from_date->format('d M Y') }} to {{ $summary->to_date->format('d M Y') }}</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="period-summary-form" method="POST" action="{{ route('hmrc.uk-property-period-summaries.update', $summary) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="business_id" value="{{ $summary->business_id }}">
            <input type="hidden" name="tax_year" value="{{ $summary->tax_year }}">

            <!-- Period Summary Card Header -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Period Details
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $isCumulative = $summary->tax_year >= '2025-26';
                        $taxYearStart = (int)substr($summary->tax_year, 0, 4);
                        $minDate = $taxYearStart . '-04-06';
                        $maxDate = ($taxYearStart + 1) . '-04-05';
                    @endphp

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tax Year</label>
                            <div class="form-control-plaintext fw-bold">{{ $summary->tax_year }}</div>
                        </div>

                        @if($isCumulative)
                            <!-- Cumulative Period - Allow editing dates for 2025-26+ -->
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info border-start border-4 border-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Cumulative Period Summary (2025-26 onwards)</strong>
                                    <p class="mb-0 mt-2 small">
                                        You can edit the period dates. The period must fall between 6 April {{ $taxYearStart }} and 5 April {{ $taxYearStart + 1 }}.
                                    </p>
                                    <ul class="mb-0 mt-2 small">
                                        <li>End date must be equal to or later than your other submissions (cumulative)</li>
                                        <li>Cannot use identical dates as another submission</li>
                                        <li>Allows you to extend or update periods as your property income/expenses accumulate</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="from_date" class="form-label required">From Date</label>
                                <input type="date" name="from_date" id="from_date"
                                       class="form-control @error('from_date') is-invalid @enderror"
                                       value="{{ old('from_date', $summary->from_date->format('Y-m-d')) }}"
                                       min="{{ $minDate }}"
                                       max="{{ $maxDate }}"
                                       required>
                                <small class="text-muted">Must be within the selected tax year (6 April - 5 April)</small>
                                @error('from_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="to_date" class="form-label required">To Date</label>
                                <input type="date" name="to_date" id="to_date"
                                       class="form-control @error('to_date') is-invalid @enderror"
                                       value="{{ old('to_date', $summary->to_date->format('Y-m-d')) }}"
                                       min="{{ $minDate }}"
                                       max="{{ $maxDate }}"
                                       required>
                                <small class="text-muted">Must be within the selected tax year and after from date</small>
                                @error('to_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <!-- Legacy Period - Show as read-only for pre-2025-26 -->
                            <div class="col-md-9">
                                <label class="form-label">Period</label>
                                <div class="form-control-plaintext fw-bold">
                                    {{ $summary->from_date->format('d M Y') }} to {{ $summary->to_date->format('d M Y') }}
                                    <span class="badge bg-info">{{ $summary->from_date->diffInDays($summary->to_date) + 1 }} days</span>
                                </div>
                            </div>

                            <!-- Hidden fields for legacy periods -->
                            <input type="hidden" name="from_date" value="{{ $summary->from_date->format('Y-m-d') }}">
                            <input type="hidden" name="to_date" value="{{ $summary->to_date->format('Y-m-d') }}">
                        @endif
                    </div>
                </div>
            </div>

            <!-- Simplified Single Page Form for Edit -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-home me-2"></i>
                        Property Income & Expenses
                    </h5>
                </div>
                <div class="card-body">
                    @if($summary->isUnifiedProperty())
                        {{-- Unified Form for 2025-26+ --}}
                        @php
                            $ukPropertyIncome = old('uk_property_income', $summary->uk_property_income_json ?? []);
                            $ukPropertyExpenses = old('uk_property_expenses', $summary->uk_property_expenses_json ?? []);
                        @endphp
                        @include('hmrc.uk-property-period-summaries.partials.unified-property-form', [
                            'ukPropertyIncome' => $ukPropertyIncome,
                            'ukPropertyExpenses' => $ukPropertyExpenses
                        ])
                    @else
                        {{-- Legacy Form for <=2024-25 --}}
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
                            @php
                                $fhlIncome = old('fhl_income', $summary->fhl_income_json ?? []);
                                $fhlExpenses = old('fhl_expenses', $summary->fhl_expenses_json ?? []);
                            @endphp

                            <!-- FHL Income -->
                            <h5 class="mb-3">FHL Income</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Rental Income</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="fhl_income[period_amount]" class="form-control"
                                               value="{{ $fhlIncome['period_amount'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax Deducted</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="fhl_income[tax_deducted]" class="form-control"
                                               value="{{ $fhlIncome['tax_deducted'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rent a Room - Rents Received</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="fhl_income[rent_a_room][rents_received]" class="form-control"
                                               value="{{ $fhlIncome['rent_a_room']['rents_received'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['consolidated_expenses'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['premises_running_costs'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['repairs_and_maintenance'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['financial_costs'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['professional_fees'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['cost_of_services'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['travel_costs'] ?? '' }}"
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
                                               value="{{ $fhlExpenses['other'] ?? '' }}"
                                               step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                               data-expense-type="fhl">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rent a Room - Amount Claimed</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="fhl_expenses[rent_a_room][amount_claimed]" class="form-control"
                                               value="{{ $fhlExpenses['rent_a_room']['amount_claimed'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Non-FHL Tab -->
                        <div class="tab-pane fade" id="non-fhl" role="tabpanel">
                            @php
                                $nonFhlIncome = old('non_fhl_income', $summary->non_fhl_income_json ?? []);
                                $nonFhlExpenses = old('non_fhl_expenses', $summary->non_fhl_expenses_json ?? []);
                            @endphp

                            <!-- Non-FHL Income -->
                            <h5 class="mb-3">Non-FHL Income</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Rental Income</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_income[period_amount]" class="form-control"
                                               value="{{ $nonFhlIncome['period_amount'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax Deducted</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_income[tax_deducted]" class="form-control"
                                               value="{{ $nonFhlIncome['tax_deducted'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Premiums of Lease Grant</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_income[premiums_of_lease_grant]" class="form-control"
                                               value="{{ $nonFhlIncome['premiums_of_lease_grant'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reverse Premiums</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_income[reverse_premiums]" class="form-control"
                                               value="{{ $nonFhlIncome['reverse_premiums'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Other Income</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_income[other_income]" class="form-control"
                                               value="{{ $nonFhlIncome['other_income'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rent a Room - Rents Received</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_income[rent_a_room][rents_received]" class="form-control"
                                               value="{{ $nonFhlIncome['rent_a_room']['rents_received'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['consolidated_expenses'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['premises_running_costs'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['repairs_and_maintenance'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['financial_costs'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['professional_fees'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['cost_of_services'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['travel_costs'] ?? '' }}"
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
                                               value="{{ $nonFhlExpenses['other'] ?? '' }}"
                                               step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00"
                                               data-expense-type="non-fhl">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Residential Financial Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_expenses[residential_financial_cost]" class="form-control"
                                               value="{{ $nonFhlExpenses['residential_financial_cost'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Residential Financial Costs Carried Forward</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_expenses[residential_financial_costs_carried_forward]" class="form-control"
                                               value="{{ $nonFhlExpenses['residential_financial_costs_carried_forward'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rent a Room - Amount Claimed</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" name="non_fhl_expenses[rent_a_room][amount_claimed]" class="form-control"
                                               value="{{ $nonFhlExpenses['rent_a_room']['amount_claimed'] ?? '' }}"
                                               step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" maxlength="5000"
                              placeholder="Add any notes about this summary...">{{ old('notes', $summary->notes) }}</textarea>
                    <small class="text-muted">Maximum 5000 characters</small>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mb-4">
                <a href="{{ route('hmrc.uk-property-period-summaries.show', $summary) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> Update Draft
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date validation for cumulative periods (2025-26+)
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');

    if (fromDateInput && toDateInput && fromDateInput.type === 'date') {
        // Add event listener to update to_date minimum when from_date changes
        fromDateInput.addEventListener('change', function() {
            const fromDate = this.value;
            if (fromDate) {
                toDateInput.setAttribute('min', fromDate);
            }
        });

        // Validate that to_date is after from_date
        toDateInput.addEventListener('change', function() {
            const fromDate = fromDateInput.value;
            const toDate = this.value;

            if (fromDate && toDate && toDate < fromDate) {
                this.setCustomValidity('To date must be after from date');
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });

        // Set initial minimum for to_date if from_date has a value
        if (fromDateInput.value) {
            toDateInput.setAttribute('min', fromDateInput.value);
        }
    }

    // Consolidated vs Itemized Expense Validation
    function setupExpenseValidation() {
        // FHL Expenses
        const fhlConsolidated = document.getElementById('fhl_consolidated_expenses');
        const fhlItemized = document.querySelectorAll('.fhl-itemized-expense');

        if (fhlConsolidated) {
            fhlConsolidated.addEventListener('input', function() {
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

    // Unified Expense Validation for 2025-26+
    function setupUnifiedExpenseValidation() {
        const unifiedConsolidated = document.getElementById('unified_consolidated_expenses');
        const unifiedItemized = document.querySelectorAll('.unified-itemized-expense');

        if (unifiedConsolidated) {
            unifiedConsolidated.addEventListener('input', function() {
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

    // Initialize expense validation
    setupExpenseValidation();
    setupUnifiedExpenseValidation();
});
</script>
@endpush
