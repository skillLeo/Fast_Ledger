@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header mb-4">
            <div class="d-flex align-items-center">
                <div class="hmrc-icon-wrapper">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h4 class="page-title mb-1">Edit Annual Submission</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        {{ $annualSubmission->tax_year }} - {{ $annualSubmission->business?->trading_name ?? $annualSubmission->business_id }}
                    </p>
                </div>
            </div>
            <a href="{{ route('hmrc.annual-submissions.show', $annualSubmission) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="max-w-container-lg mx-auto">
            <form method="POST" action="{{ route('hmrc.annual-submissions.update', $annualSubmission) }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="business_id" value="{{ $annualSubmission->business_id }}">
                <input type="hidden" name="tax_year" value="{{ $annualSubmission->tax_year }}">

                <!-- Adjustments Section -->
                <div class="hmrc-card mb-4">
                    <h2 class="section-heading mb-4">
                        <i class="fas fa-dollar-sign me-2"></i>Adjustments
                    </h2>

                    @php
                    $taxYearNum = (int) substr($annualSubmission->tax_year, 0, 4);
                    $adjustments = $annualSubmission->adjustments_json ?? [];
                    @endphp

                    <!-- Base Adjustments (All Tax Years) -->
                    <h3 class="subsection-heading mb-3">Income & Expense Adjustments</h3>
                    <div class="row g-4 mb-4">
                        @php
                        $baseAdjustments = [
                            ['key' => 'included_non_taxable_profits', 'label' => 'Included Non-Taxable Profits', 'hint' => 'Non-taxable profits included in accounts', 'min' => '0'],
                            ['key' => 'basis_adjustment', 'label' => 'Basis Adjustment', 'hint' => 'Adjustment to basis period income', 'min' => '-99999999999.99'],
                            ['key' => 'overlap_relief_used', 'label' => 'Overlap Relief Used', 'hint' => 'Overlap relief claimed this year', 'min' => '0'],
                            ['key' => 'accounting_adjustment', 'label' => 'Accounting Adjustment', 'hint' => 'Adjustment for accounting changes', 'min' => '-99999999999.99'],
                            ['key' => 'averaging_adjustment', 'label' => 'Averaging Adjustment', 'hint' => 'Farmers/creative artists income averaging', 'min' => '-99999999999.99'],
                            ['key' => 'outstanding_business_income', 'label' => 'Outstanding Business Income', 'hint' => 'Outstanding business income', 'min' => '0'],
                            ['key' => 'balancing_charge_bpra', 'label' => 'Balancing Charge BPRA', 'hint' => 'Business premises renovation allowance balancing charge', 'min' => '0'],
                            ['key' => 'balancing_charge_other', 'label' => 'Balancing Charge Other', 'hint' => 'Other balancing charges on asset disposal', 'min' => '0'],
                            ['key' => 'goods_and_services_own_use', 'label' => 'Goods and Services Own Use', 'hint' => 'Value of goods/services for own use', 'min' => '0'],
                        ];
                        @endphp

                        @foreach($baseAdjustments as $field)
                        <div class="col-md-6">
                            <label for="adj_{{ $field['key'] }}" class="form-label">
                                {{ $field['label'] }}
                                <i class="fas fa-info-circle text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ $field['hint'] }}"></i>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number"
                                       name="adjustments[{{ $field['key'] }}]"
                                       id="adj_{{ $field['key'] }}"
                                       class="form-control adjustment-input @error('adjustments.' . $field['key']) is-invalid @enderror"
                                       value="{{ old('adjustments.' . $field['key'], $adjustments[$field['key']] ?? '') }}"
                                       step="0.01"
                                       min="{{ $field['min'] }}"
                                       max="99999999999.99"
                                       placeholder="0.00">
                            </div>
                            @error('adjustments.' . $field['key'])
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ $field['hint'] }}</small>
                        </div>
                        @endforeach
                    </div>

                    <!-- TY 2024-25+ Adjustments -->
                    @if($taxYearNum >= 2024)
                    <div class="alert alert-info border-start border-4 border-info bg-light mb-3">
                        <i class="fas fa-sparkles me-2 text-info"></i>
                        <strong>TY 2024-25+ Fields</strong> - Additional adjustments for tax year 2024-25 onwards
                    </div>

                    <div class="row g-4">
                        @php
                        $ty202425Adjustments = [
                            ['key' => 'transition_profit_amount', 'label' => 'Transition Profit Amount', 'hint' => 'Transition profit amount for basis period reform'],
                            ['key' => 'transition_profit_acceleration_amount', 'label' => 'Transition Profit Acceleration Amount', 'hint' => 'Transition profit acceleration amount'],
                        ];
                        @endphp

                        @foreach($ty202425Adjustments as $field)
                        <div class="col-md-6">
                            <label for="adj_{{ $field['key'] }}" class="form-label">
                                {{ $field['label'] }}
                                <i class="fas fa-info-circle text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ $field['hint'] }}"></i>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number"
                                       name="adjustments[{{ $field['key'] }}]"
                                       id="adj_{{ $field['key'] }}"
                                       class="form-control adjustment-input @error('adjustments.' . $field['key']) is-invalid @enderror"
                                       value="{{ old('adjustments.' . $field['key'], $adjustments[$field['key']] ?? '') }}"
                                       step="0.01"
                                       min="0"
                                       max="99999999999.99"
                                       placeholder="0.00">
                            </div>
                            @error('adjustments.' . $field['key'])
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ $field['hint'] }}</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Allowances Section -->
                <div class="hmrc-card mb-4">
                    <h2 class="section-heading mb-4">
                        <i class="fas fa-award me-2"></i>Allowances
                    </h2>

                    @php
                    $allowances = $annualSubmission->allowances_json ?? [];
                    $tradingAllowance = $allowances['trading_income_allowance'] ?? null;
                    $structuredBuildingAllowance = $allowances['structured_building_allowance'] ?? [];
                    $enhancedStructuredBuildingAllowance = $allowances['enhanced_structured_building_allowance'] ?? [];
                    @endphp

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Choose between Trading Allowance OR Capital Allowances. You cannot use both.
                    </div>

                    <!-- Trading Allowance Option -->
                    <div class="p-3 bg-light rounded mb-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong>Use Trading Allowance</strong>
                                <p class="text-muted mb-0 small">If you elect for the trading allowance (£1,000), you cannot claim any other capital allowances.</p>
                            </div>
                            <div class="form-check form-switch ms-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="use_trading_allowance"
                                       style="width: 3em; height: 1.5em; cursor: pointer;"
                                       {{ $tradingAllowance ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Trading Allowance Field -->
                    <div id="trading-allowance-section" style="display: {{ $tradingAllowance ? 'block' : 'none' }};">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="trading_income_allowance" class="form-label">Trading Income Allowance</label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number"
                                           name="allowances[trading_income_allowance]"
                                           id="trading_income_allowance"
                                           class="form-control @error('allowances.trading_income_allowance') is-invalid @enderror"
                                           value="{{ old('allowances.trading_income_allowance', $tradingAllowance ?? '1000') }}"
                                           step="0.01"
                                           min="0"
                                           max="1000"
                                           placeholder="1000.00"
                                           readonly>
                                </div>
                                @error('allowances.trading_income_allowance')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maximum £1,000 trading allowance</small>
                            </div>
                        </div>
                    </div>

                    <!-- Capital Allowances -->
                    <div id="capital-allowances-section" style="display: {{ $tradingAllowance ? 'none' : 'block' }};">
                        <h3 class="subsection-heading mb-3">Capital Allowances</h3>

                        <!-- Base Capital Allowances -->
                        <div class="row g-4 mb-4">
                            @php
                            $baseAllowances = [
                                ['key' => 'annual_investment_allowance', 'label' => 'Annual Investment Allowance (AIA)', 'hint' => '100% allowance on qualifying plant and machinery (up to £1m)'],
                                ['key' => 'capital_allowance_main_pool', 'label' => 'Capital Allowance Main Pool', 'hint' => '18% writing down allowance on general plant and machinery'],
                                ['key' => 'capital_allowance_special_rate_pool', 'label' => 'Capital Allowance Special Rate Pool', 'hint' => '6% writing down allowance on integral features and long-life assets'],
                                ['key' => 'business_premises_renovation_allowance', 'label' => 'Business Premises Renovation Allowance', 'hint' => '100% allowance on renovating qualifying business premises'],
                                ['key' => 'enhanced_capital_allowance', 'label' => 'Enhanced Capital Allowance', 'hint' => '100% FYA on qualifying energy/water efficient equipment'],
                                ['key' => 'allowance_on_sales', 'label' => 'Allowance on Sales', 'hint' => 'Allowance arising from disposal of assets'],
                                ['key' => 'capital_allowance_single_asset_pool', 'label' => 'Capital Allowance Single Asset Pool', 'hint' => 'Single asset pool allowances'],
                                ['key' => 'zero_emissions_car_allowance', 'label' => 'Zero Emissions Car Allowance', 'hint' => '100% FYA on new zero emission cars'],
                            ];
                            @endphp

                            @foreach($baseAllowances as $field)
                            <div class="col-md-6">
                                <label for="allow_{{ $field['key'] }}" class="form-label">
                                    {{ $field['label'] }}
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="{{ $field['hint'] }}"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number"
                                           name="allowances[{{ $field['key'] }}]"
                                           id="allow_{{ $field['key'] }}"
                                           class="form-control capital-allowance-input @error('allowances.' . $field['key']) is-invalid @enderror"
                                           value="{{ old('allowances.' . $field['key'], $allowances[$field['key']] ?? '') }}"
                                           step="0.01"
                                           min="0"
                                           max="99999999999.99"
                                           placeholder="0.00">
                                </div>
                                @error('allowances.' . $field['key'])
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ $field['hint'] }}</small>
                            </div>
                            @endforeach
                        </div>

                        <!-- TY ≤ 2024 Fields -->
                        @if($taxYearNum <= 2024)
                        <div class="alert alert-warning border-start border-4 border-warning bg-light mb-3">
                            <i class="fas fa-calendar-times me-2 text-warning"></i>
                            <strong>TY ≤ 2024 Fields</strong> - These fields are only available for tax years up to 2024-25
                        </div>

                        <div class="row g-4 mb-4">
                            @php
                            $ty2024Allowances = [
                                ['key' => 'zero_emissions_goods_vehicle_allowance', 'label' => 'Zero Emissions Goods Vehicle Allowance', 'hint' => '100% FYA on new zero emission goods vehicles (TY ≤ 2024)'],
                                ['key' => 'electric_charge_point_allowance', 'label' => 'Electric Charge Point Allowance', 'hint' => '100% FYA on electric vehicle charging points (TY ≤ 2024)'],
                            ];
                            @endphp

                            @foreach($ty2024Allowances as $field)
                            <div class="col-md-6">
                                <label for="allow_{{ $field['key'] }}" class="form-label">
                                    {{ $field['label'] }}
                                    <i class="fas fa-info-circle text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="{{ $field['hint'] }}"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number"
                                           name="allowances[{{ $field['key'] }}]"
                                           id="allow_{{ $field['key'] }}"
                                           class="form-control capital-allowance-input @error('allowances.' . $field['key']) is-invalid @enderror"
                                           value="{{ old('allowances.' . $field['key'], $allowances[$field['key']] ?? '') }}"
                                           step="0.01"
                                           min="0"
                                           max="99999999999.99"
                                           placeholder="0.00">
                                </div>
                                @error('allowances.' . $field['key'])
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ $field['hint'] }}</small>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Structured Building Allowances -->
                        <div class="mt-4">
                            <h4 class="subsection-heading mb-3">
                                <i class="fas fa-building me-2"></i>Structured Building Allowances
                            </h4>
                            <p class="text-muted small mb-3">Add details for qualifying non-residential structures (3% annual allowance)</p>

                            <div id="sba-container">
                                @if(is_array($structuredBuildingAllowance))
                                    @foreach($structuredBuildingAllowance as $index => $sba)
                                        <div class="card mb-3 building-allowance-entry" data-type="sba" data-index="{{ $index }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h5 class="mb-0">Structured Building Allowance #{{ $index + 1 }}</h5>
                                                    <button type="button" class="btn btn-sm btn-danger remove-building-allowance">
                                                        <i class="fas fa-times"></i> Remove
                                                    </button>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Amount</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">£</span>
                                                            <input type="number" name="allowances[structured_building_allowance][{{ $index }}][amount]"
                                                                   class="form-control building-allowance-amount" step="0.01" min="0" max="99999999999.99"
                                                                   value="{{ old('allowances.structured_building_allowance.' . $index . '.amount', $sba['amount'] ?? '') }}"
                                                                   placeholder="0.00" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Qualifying Date</label>
                                                        <input type="date" name="allowances[structured_building_allowance][{{ $index }}][first_year_qualifying_date]"
                                                               class="form-control" value="{{ old('allowances.structured_building_allowance.' . $index . '.first_year_qualifying_date', $sba['first_year_qualifying_date'] ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Qualifying Amount Expenditure</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">£</span>
                                                            <input type="number" name="allowances[structured_building_allowance][{{ $index }}][first_year_qualifying_amount]"
                                                                   class="form-control" step="0.01" min="0" max="99999999999.99"
                                                                   value="{{ old('allowances.structured_building_allowance.' . $index . '.first_year_qualifying_amount', $sba['first_year_qualifying_amount'] ?? '') }}"
                                                                   placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Building Name</label>
                                                        <input type="text" name="allowances[structured_building_allowance][{{ $index }}][building_name]"
                                                               class="form-control" maxlength="90" value="{{ old('allowances.structured_building_allowance.' . $index . '.building_name', $sba['building_name'] ?? '') }}"
                                                               placeholder="Building name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Building Postcode</label>
                                                        <input type="text" name="allowances[structured_building_allowance][{{ $index }}][building_postcode]"
                                                               class="form-control" maxlength="10" value="{{ old('allowances.structured_building_allowance.' . $index . '.building_postcode', $sba['building_postcode'] ?? '') }}"
                                                               placeholder="e.g., SW1A 1AA">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                                    @endforeach
                                @endif
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-sba">
                                <i class="fas fa-plus me-1"></i> Add Structured Building Allowance
                            </button>
                        </div>

                        <!-- Enhanced Structured Building Allowances -->
                        <div class="mt-4">
                            <h4 class="subsection-heading mb-3">
                                <i class="fas fa-building me-2"></i>Enhanced Structured Building Allowances
                            </h4>
                            <p class="text-muted small mb-3">Freeport enhanced allowance (10% per year)</p>

                            <div id="esba-container">
                                @if(is_array($enhancedStructuredBuildingAllowance))
                                    @foreach($enhancedStructuredBuildingAllowance as $index => $esba)
                                        <div class="card mb-3 building-allowance-entry" data-type="esba" data-index="{{ $index }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h5 class="mb-0">Enhanced Structured Building Allowance #{{ $index + 1 }}</h5>
                                                    <button type="button" class="btn btn-sm btn-danger remove-building-allowance">
                                                        <i class="fas fa-times"></i> Remove
                                                    </button>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Amount</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">£</span>
                                                            <input type="number" name="allowances[enhanced_structured_building_allowance][{{ $index }}][amount]"
                                                                   class="form-control building-allowance-amount" step="0.01" min="0" max="99999999999.99"
                                                                   value="{{ old('allowances.enhanced_structured_building_allowance.' . $index . '.amount', $esba['amount'] ?? '') }}"
                                                                   placeholder="0.00" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Qualifying Date</label>
                                                        <input type="date" name="allowances[enhanced_structured_building_allowance][{{ $index }}][first_year_qualifying_date]"
                                                               class="form-control" value="{{ old('allowances.enhanced_structured_building_allowance.' . $index . '.first_year_qualifying_date', $esba['first_year_qualifying_date'] ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Qualifying Amount Expenditure</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">£</span>
                                                            <input type="number" name="allowances[enhanced_structured_building_allowance][{{ $index }}][first_year_qualifying_amount]"
                                                                   class="form-control" step="0.01" min="0" max="99999999999.99"
                                                                   value="{{ old('allowances.enhanced_structured_building_allowance.' . $index . '.first_year_qualifying_amount', $esba['first_year_qualifying_amount'] ?? '') }}"
                                                                   placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Building Name</label>
                                                        <input type="text" name="allowances[enhanced_structured_building_allowance][{{ $index }}][building_name]"
                                                               class="form-control" maxlength="90" value="{{ old('allowances.enhanced_structured_building_allowance.' . $index . '.building_name', $esba['building_name'] ?? '') }}"
                                                               placeholder="Building name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Building Postcode</label>
                                                        <input type="text" name="allowances[enhanced_structured_building_allowance][{{ $index }}][building_postcode]"
                                                               class="form-control" maxlength="10" value="{{ old('allowances.enhanced_structured_building_allowance.' . $index . '.building_postcode', $esba['building_postcode'] ?? '') }}"
                                                               placeholder="e.g., SW1A 1AA">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-esba">
                                <i class="fas fa-plus me-1"></i> Add Enhanced Structured Building Allowance
                            </button>
                        </div>

                        <!-- Total Allowances -->
                        <div class="total-box mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="total-label">Total Allowances:</span>
                                <span class="total-value text-hmrc" id="total-allowances">£0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Non-Financials Section -->
                <div class="hmrc-card mb-4">
                    <h2 class="section-heading mb-4">
                        <i class="fas fa-map-marker-alt me-2"></i>Non-Financials
                    </h2>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        All fields in this section are optional. Complete only if the information is relevant to your submission.
                    </div>

                    <!-- Business Address -->
                    <h3 class="subsection-heading mb-3">Business Address</h3>
                    <div class="row g-4 mb-5">
                        @php
                        $nonFinancials = $annualSubmission->non_financials_json ?? [];
                        @endphp

                        <div class="col-md-12">
                            <label for="business_address_line_1" class="form-label">Address Line 1</label>
                            <input type="text"
                                   name="non_financials[business_address_line_1]"
                                   id="business_address_line_1"
                                   class="form-control"
                                   value="{{ old('non_financials.business_address_line_1', $nonFinancials['business_address_line_1'] ?? '') }}"
                                   placeholder="Enter address line 1"
                                   maxlength="35">
                        </div>

                        <div class="col-md-12">
                            <label for="business_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text"
                                   name="non_financials[business_address_line_2]"
                                   id="business_address_line_2"
                                   class="form-control"
                                   value="{{ old('non_financials.business_address_line_2', $nonFinancials['business_address_line_2'] ?? '') }}"
                                   placeholder="Enter address line 2"
                                   maxlength="35">
                        </div>

                        <div class="col-md-6">
                            <label for="business_address_line_3" class="form-label">Address Line 3 / Town</label>
                            <input type="text"
                                   name="non_financials[business_address_line_3]"
                                   id="business_address_line_3"
                                   class="form-control"
                                   value="{{ old('non_financials.business_address_line_3', $nonFinancials['business_address_line_3'] ?? '') }}"
                                   placeholder="Enter town/city"
                                   maxlength="35">
                        </div>

                        <div class="col-md-6">
                            <label for="business_address_line_4" class="form-label">Address Line 4 / County</label>
                            <input type="text"
                                   name="non_financials[business_address_line_4]"
                                   id="business_address_line_4"
                                   class="form-control"
                                   value="{{ old('non_financials.business_address_line_4', $nonFinancials['business_address_line_4'] ?? '') }}"
                                   placeholder="Enter county"
                                   maxlength="35">
                        </div>

                        <div class="col-md-6">
                            <label for="business_address_postcode" class="form-label">Postcode</label>
                            <input type="text"
                                   name="non_financials[business_address_postcode]"
                                   id="business_address_postcode"
                                   class="form-control"
                                   value="{{ old('non_financials.business_address_postcode', $nonFinancials['business_address_postcode'] ?? '') }}"
                                   placeholder="e.g., SW1A 1AA"
                                   maxlength="10">
                        </div>

                        <div class="col-md-6">
                            <label for="business_address_country_code" class="form-label">Country Code</label>
                            <input type="text"
                                   name="non_financials[business_address_country_code]"
                                   id="business_address_country_code"
                                   class="form-control"
                                   value="{{ old('non_financials.business_address_country_code', $nonFinancials['business_address_country_code'] ?? 'GB') }}"
                                   placeholder="GB"
                                   maxlength="2">
                            <small class="text-muted">Two letter ISO country code (e.g., GB, FR, US)</small>
                        </div>
                    </div>

                    <!-- Class 4 NICs -->
                    <h3 class="subsection-heading mb-3">Class 4 National Insurance Contributions</h3>
                    <div class="row g-4 mb-5">
                        <div class="col-md-12">
                            <label for="class_4_nics_exemption_reason" class="form-label">Exemption Reason (if applicable)</label>
                            <select name="non_financials[class_4_nics_exemption_reason]"
                                    id="class_4_nics_exemption_reason"
                                    class="form-select">
                                <option value="">No exemption</option>
                                <option value="001" {{ old('non_financials.class_4_nics_exemption_reason', $nonFinancials['class_4_nics_exemption_reason'] ?? '') == '001' ? 'selected' : '' }}>001 - Non-resident</option>
                                <option value="002" {{ old('non_financials.class_4_nics_exemption_reason', $nonFinancials['class_4_nics_exemption_reason'] ?? '') == '002' ? 'selected' : '' }}>002 - Trustee</option>
                                <option value="003" {{ old('non_financials.class_4_nics_exemption_reason', $nonFinancials['class_4_nics_exemption_reason'] ?? '') == '003' ? 'selected' : '' }}>003 - Diver</option>
                                <option value="004" {{ old('non_financials.class_4_nics_exemption_reason', $nonFinancials['class_4_nics_exemption_reason'] ?? '') == '004' ? 'selected' : '' }}>004 - Employed earner taxed under ITTOIA 2005</option>
                                <option value="005" {{ old('non_financials.class_4_nics_exemption_reason', $nonFinancials['class_4_nics_exemption_reason'] ?? '') == '005' ? 'selected' : '' }}>005 - Over state pension age</option>
                                <option value="006" {{ old('non_financials.class_4_nics_exemption_reason', $nonFinancials['class_4_nics_exemption_reason'] ?? '') == '006' ? 'selected' : '' }}>006 - Under 16</option>
                            </select>
                            <small class="text-muted">Select only if you qualify for an exemption from Class 4 NICs</small>
                        </div>
                    </div>

                    <!-- Business Details Changed -->
                    <h3 class="subsection-heading mb-3">Business Details</h3>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label">Have your business details changed recently?</label>
                            <div class="form-check">
                                <input class="form-check-input @error('non_financials.business_details_changed_recently') is-invalid @enderror"
                                       type="radio"
                                       name="non_financials[business_details_changed_recently]"
                                       id="business_details_yes"
                                       value="1"
                                       {{ old('non_financials.business_details_changed_recently', $nonFinancials['business_details_changed_recently'] ?? 0) == '1' || old('non_financials.business_details_changed_recently', $nonFinancials['business_details_changed_recently'] ?? 0) === 1 || old('non_financials.business_details_changed_recently', $nonFinancials['business_details_changed_recently'] ?? 0) === true ? 'checked' : '' }}>
                                <label class="form-check-label" for="business_details_yes">
                                    Yes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input @error('non_financials.business_details_changed_recently') is-invalid @enderror"
                                       type="radio"
                                       name="non_financials[business_details_changed_recently]"
                                       id="business_details_no"
                                       value="0"
                                       {{ old('non_financials.business_details_changed_recently', $nonFinancials['business_details_changed_recently'] ?? 0) == '0' || old('non_financials.business_details_changed_recently', $nonFinancials['business_details_changed_recently'] ?? 0) === 0 || old('non_financials.business_details_changed_recently', $nonFinancials['business_details_changed_recently'] ?? 0) === false ? 'checked' : '' }}>
                                <label class="form-check-label" for="business_details_no">
                                    No
                                </label>
                            </div>
                            @error('non_financials.business_details_changed_recently')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Indicate if your business address or other key details have changed this year</small>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="hmrc-card mb-4">
                    <h2 class="section-heading mb-3">
                        <i class="fas fa-sticky-note me-2"></i>Notes
                    </h2>
                    <textarea name="notes"
                              id="notes"
                              class="form-control"
                              rows="3"
                              maxlength="5000"
                              placeholder="Add any notes about this annual submission...">{{ old('notes', $annualSubmission->notes) }}</textarea>
                    <small class="text-muted"><span id="notes-count">{{ strlen($annualSubmission->notes ?? '') }}</span> / 5000 characters</small>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mb-4">
                    <a href="{{ route('hmrc.annual-submissions.show', $annualSubmission) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-hmrc-primary btn-lg">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Page Header */
.hmrc-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #e8f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.hmrc-icon-wrapper i {
    color: #17848e;
    font-size: 1.5rem;
}

.page-title {
    color: #13667d;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

/* Container */
.max-w-container-lg {
    max-width: 1200px;
}

/* HMRC Card */
.hmrc-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
}

/* Headings */
.section-heading {
    color: #13667d;
    font-size: 1.5rem;
    font-weight: 600;
}

.subsection-heading {
    color: #13667d;
    font-size: 1.125rem;
    font-weight: 600;
}

/* Form Elements */
.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 0.625rem 0.75rem;
}

.form-control:focus, .form-select:focus {
    border-color: #17848e;
    box-shadow: 0 0 0 0.2rem rgba(23, 132, 142, 0.25);
}

/* Total Box */
.total-box {
    background: #e8f4f8;
    border: 1px solid #b3d9e6;
    border-radius: 8px;
    padding: 1rem 1.5rem;
}

.total-label {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.total-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.text-hmrc {
    color: #17848e !important;
}

/* HMRC Primary Button */
.btn-hmrc-primary {
    background-color: #17848e;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.btn-hmrc-primary:hover {
    background-color: #136770;
    color: white;
}

.btn-hmrc-primary:active,
.btn-hmrc-primary:focus {
    background-color: #136770;
    color: white;
    box-shadow: none;
}

/* Responsive */
@media (max-width: 768px) {
    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
    }

    .hmrc-icon-wrapper i {
        font-size: 1.25rem;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .hmrc-card {
        padding: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle trading allowance toggle
    const tradingAllowanceToggle = document.getElementById('use_trading_allowance');
    const tradingAllowanceSection = document.getElementById('trading-allowance-section');
    const capitalAllowancesSection = document.getElementById('capital-allowances-section');
    const tradingAllowanceInput = document.getElementById('trading_income_allowance');

    // Initialize trading allowance input - remove name if toggle is off
    if (tradingAllowanceInput && !tradingAllowanceToggle.checked) {
        tradingAllowanceInput.removeAttribute('name');
        tradingAllowanceInput.value = '';
    }

    tradingAllowanceToggle.addEventListener('change', function() {
        if (this.checked) {
            // Show trading allowance, hide capital allowances
            tradingAllowanceSection.style.display = 'block';
            capitalAllowancesSection.style.display = 'none';

            // Enable trading allowance input and set to 1000
            if (tradingAllowanceInput) {
                tradingAllowanceInput.value = '1000';
                tradingAllowanceInput.removeAttribute('readonly');
                tradingAllowanceInput.setAttribute('name', 'allowances[trading_income_allowance]');
            }

            // Clear all capital allowance inputs and building allowances
            document.querySelectorAll('.capital-allowance-input').forEach(input => {
                input.value = '';
                input.setAttribute('disabled', 'disabled');
            });

            // Also disable building allowance add buttons
            document.getElementById('add-sba').setAttribute('disabled', 'disabled');
            document.getElementById('add-esba').setAttribute('disabled', 'disabled');

            // Hide existing building allowance entries
            document.querySelectorAll('.building-allowance-entry').forEach(entry => {
                entry.style.display = 'none';
            });
        } else {
            // Hide trading allowance, show capital allowances
            tradingAllowanceSection.style.display = 'none';
            capitalAllowancesSection.style.display = 'block';

            // Clear trading allowance input completely
            if (tradingAllowanceInput) {
                tradingAllowanceInput.value = '';
                tradingAllowanceInput.removeAttribute('name');
            }

            // Re-enable capital allowance inputs
            document.querySelectorAll('.capital-allowance-input').forEach(input => {
                input.removeAttribute('disabled');
            });

            // Re-enable building allowance add buttons
            document.getElementById('add-sba').removeAttribute('disabled');
            document.getElementById('add-esba').removeAttribute('disabled');

            // Show existing building allowance entries
            document.querySelectorAll('.building-allowance-entry').forEach(entry => {
                entry.style.display = 'block';
            });
        }

        // Recalculate totals
        calculateTotalAllowances();
    });

    // Structured Building Allowances - Dynamic Fields
    let sbaIndex = document.querySelectorAll('#sba-container .building-allowance-entry').length;
    let esbaIndex = document.querySelectorAll('#esba-container .building-allowance-entry').length;

    // Restore building allowances from old() input if validation failed
    @if(old('allowances.structured_building_allowance'))
        @php
            $oldSBA = old('allowances.structured_building_allowance', []);
        @endphp
        @foreach($oldSBA as $index => $sba)
            @if(!isset($structuredBuildingAllowance[$index]))
                // This SBA was added in the failed submission, restore it
                setTimeout(() => {
                    addBuildingAllowance('sba', {{ $index }});
                    // Fill in the values
                    document.querySelector(`[name="allowances[structured_building_allowance][{{ $index }}][amount]"]`).value = '{{ $sba['amount'] ?? '' }}';
                    document.querySelector(`[name="allowances[structured_building_allowance][{{ $index }}][first_year_qualifying_date]"]`).value = '{{ $sba['first_year_qualifying_date'] ?? '' }}';
                    document.querySelector(`[name="allowances[structured_building_allowance][{{ $index }}][first_year_qualifying_amount]"]`).value = '{{ $sba['first_year_qualifying_amount'] ?? '' }}';
                    document.querySelector(`[name="allowances[structured_building_allowance][{{ $index }}][building_name]"]`).value = '{{ $sba['building_name'] ?? '' }}';
                    document.querySelector(`[name="allowances[structured_building_allowance][{{ $index }}][building_postcode]"]`).value = '{{ $sba['building_postcode'] ?? '' }}';
                }, 100);
            @endif
        @endforeach
        sbaIndex = {{ count($oldSBA) }};
    @endif

    @if(old('allowances.enhanced_structured_building_allowance'))
        @php
            $oldESBA = old('allowances.enhanced_structured_building_allowance', []);
        @endphp
        @foreach($oldESBA as $index => $esba)
            @if(!isset($enhancedStructuredBuildingAllowance[$index]))
                // This ESBA was added in the failed submission, restore it
                setTimeout(() => {
                    addBuildingAllowance('esba', {{ $index }});
                    // Fill in the values
                    document.querySelector(`[name="allowances[enhanced_structured_building_allowance][{{ $index }}][amount]"]`).value = '{{ $esba['amount'] ?? '' }}';
                    document.querySelector(`[name="allowances[enhanced_structured_building_allowance][{{ $index }}][first_year_qualifying_date]"]`).value = '{{ $esba['first_year_qualifying_date'] ?? '' }}';
                    document.querySelector(`[name="allowances[enhanced_structured_building_allowance][{{ $index }}][first_year_qualifying_amount]"]`).value = '{{ $esba['first_year_qualifying_amount'] ?? '' }}';
                    document.querySelector(`[name="allowances[enhanced_structured_building_allowance][{{ $index }}][building_name]"]`).value = '{{ $esba['building_name'] ?? '' }}';
                    document.querySelector(`[name="allowances[enhanced_structured_building_allowance][{{ $index }}][building_postcode]"]`).value = '{{ $esba['building_postcode'] ?? '' }}';
                }, 100);
            @endif
        @endforeach
        esbaIndex = {{ count($oldESBA) }};
    @endif

    document.getElementById('add-sba').addEventListener('click', function() {
        addBuildingAllowance('sba', sbaIndex++);
    });

    document.getElementById('add-esba').addEventListener('click', function() {
        addBuildingAllowance('esba', esbaIndex++);
    });

    function addBuildingAllowance(type, index) {
        const container = document.getElementById(type === 'sba' ? 'sba-container' : 'esba-container');
        const label = type === 'sba' ? 'Structured Building Allowance' : 'Enhanced Structured Building Allowance';
        const fieldName = type === 'sba' ? 'structured_building_allowance' : 'enhanced_structured_building_allowance';

        const template = `
            <div class="card mb-3 building-allowance-entry" data-type="${type}" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">${label} #${index + 1}</h5>
                        <button type="button" class="btn btn-sm btn-danger remove-building-allowance">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number"
                                       name="allowances[${fieldName}][${index}][amount]"
                                       class="form-control building-allowance-amount"
                                       step="0.01"
                                       min="0"
                                       max="99999999999.99"
                                       placeholder="0.00"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Qualifying Date</label>
                            <input type="date"
                                   name="allowances[${fieldName}][${index}][first_year_qualifying_date]"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Qualifying Amount Expenditure</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number"
                                       name="allowances[${fieldName}][${index}][first_year_qualifying_amount]"
                                       class="form-control"
                                       step="0.01"
                                       min="0"
                                       max="99999999999.99"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Building Name</label>
                            <input type="text"
                                   name="allowances[${fieldName}][${index}][building_name]"
                                   class="form-control"
                                   maxlength="90"
                                   placeholder="Building name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Building Postcode</label>
                            <input type="text"
                                   name="allowances[${fieldName}][${index}][building_postcode]"
                                   class="form-control"
                                   maxlength="10"
                                   placeholder="e.g., SW1A 1AA">
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', template);

        // Add event listener to remove button
        const entry = container.lastElementChild;
        entry.querySelector('.remove-building-allowance').addEventListener('click', function() {
            entry.remove();
            calculateTotalAllowances();
        });

        // Add event listener to amount input
        entry.querySelector('.building-allowance-amount').addEventListener('input', calculateTotalAllowances);
    }

    // Add event listeners to existing building allowance remove buttons
    document.querySelectorAll('.remove-building-allowance').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.building-allowance-entry').remove();
            calculateTotalAllowances();
        });
    });

    // Real-time calculation for allowances
    const capitalAllowanceInputs = document.querySelectorAll('.capital-allowance-input');
    capitalAllowanceInputs.forEach(input => {
        input.addEventListener('input', calculateTotalAllowances);
    });

    // Also listen to trading allowance input
    if (tradingAllowanceInput) {
        tradingAllowanceInput.addEventListener('input', calculateTotalAllowances);
    }

    // Add listeners to existing building allowance amount inputs
    document.querySelectorAll('.building-allowance-amount').forEach(input => {
        input.addEventListener('input', calculateTotalAllowances);
    });

    function calculateTotalAllowances() {
        let total = 0;

        // Check if using trading allowance
        if (tradingAllowanceToggle.checked && tradingAllowanceInput) {
            total = parseFloat(tradingAllowanceInput.value || 0);
        } else {
            // Sum all capital allowances
            capitalAllowanceInputs.forEach(input => {
                if (!input.disabled) {
                    total += parseFloat(input.value || 0);
                }
            });

            // Sum all building allowances
            document.querySelectorAll('.building-allowance-amount').forEach(input => {
                total += parseFloat(input.value || 0);
            });
        }

        document.getElementById('total-allowances').textContent = formatCurrency(total);
    }

    // Initial calculation
    calculateTotalAllowances();

    // Notes character counter
    const notesInput = document.getElementById('notes');
    if (notesInput) {
        notesInput.addEventListener('input', function() {
            document.getElementById('notes-count').textContent = this.value.length;
        });
    }

    // Auto-uppercase postcode and country code
    const postcodeField = document.getElementById('business_address_postcode');
    const countryField = document.getElementById('business_address_country_code');

    if (postcodeField) {
        postcodeField.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }

    if (countryField) {
        countryField.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('en-GB', {
            style: 'currency',
            currency: 'GBP'
        }).format(value);
    }

    // Initial calculation
    calculateTotalAllowances();
});
</script>
@endpush



