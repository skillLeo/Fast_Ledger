@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="hmrc-icon-wrapper">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <h4 class="page-title mb-1">Annual Submission</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Submit annual adjustments, allowances, and non-financial data to HMRC</p>
                </div>
            </div>
        </div>

        <div class="max-w-container-lg mx-auto">
            <!-- Step Indicator -->
            <div class="step-indicator mb-5">
                <div class="step-track"></div>
                <div class="steps-container">
                    <div class="step active" data-step="1">
                        <div class="step-circle">
                            <i class="fas fa-building"></i>
                        </div>
                        <p class="step-title">Business & Year</p>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <p class="step-title">Adjustments</p>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">
                            <i class="fas fa-award"></i>
                        </div>
                        <p class="step-title">Allowances</p>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <p class="step-title">Non-Financials</p>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                        </div>
                        <p class="step-title">Review</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form id="annual-submission-form" method="POST" action="{{ route('hmrc.annual-submissions.store') }}">
                @csrf

                <!-- Step 1: Business & Tax Year -->
                <div class="form-step active" data-step="1">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Business & Tax Year</h2>

                        <div class="row g-4">
                            <!-- Business Selection -->
                            <div class="col-md-12">
                                <label for="business_id" class="form-label required">Business</label>
                                <select name="business_id" id="business_id" class="form-select @error('business_id') is-invalid @enderror" required>
                                    <option value="">Select a business</option>
                                    @foreach($businesses as $business)
                                        <option value="{{ $business->business_id }}"
                                                {{ old('business_id', request('business_id')) == $business->business_id ? 'selected' : '' }}
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

                            <!-- Tax Year Selection -->
                            <div class="col-md-6">
                                <label for="tax_year" class="form-label required">Tax Year</label>
                                <select name="tax_year" id="tax_year" class="form-select @error('tax_year') is-invalid @enderror" required>
                                    @php
                                        $currentYear = date('Y');
                                        $currentMonth = date('n');
                                        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
                                        $existingTaxYears = $existingTaxYears ?? [];
                                    @endphp
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $year = $startYear - $i;
                                            $taxYear = $year . '-' . substr($year + 1, 2);
                                            $isExisting = in_array($taxYear, $existingTaxYears);
                                        @endphp
                                        @if(!$isExisting)
                                            <option value="{{ $taxYear }}" {{ old('tax_year') == $taxYear || $i == 0 ? 'selected' : '' }}>
                                                {{ $taxYear }} ({{ $year }}/{{ $year + 1 }})
                                            </option>
                                        @endif
                                    @endfor
                                </select>
                                @error('tax_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Only showing tax years without existing submissions</small>
                            </div>

                            <!-- NINO -->
                            <div class="col-md-6">
                                <label for="nino" class="form-label">National Insurance Number (NINO)</label>
                                <input type="text" name="nino" id="nino"
                                       class="form-control @error('nino') is-invalid @enderror"
                                       value="{{ old('nino') }}"
                                       placeholder="AB123456C"
                                       pattern="^[A-Z]{2}[0-9]{6}[A-Z]$"
                                       maxlength="9">
                                <small class="text-muted">Format: AB123456C</small>
                                @error('nino')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Quarterly Summary -->
                        <div id="quarterly-summary" class="quarterly-summary mt-4" style="display: none;">
                            <h3 class="summary-title mb-3">Quarterly Summary</h3>

                            <!-- Warning message container -->
                            <div id="summary-warning" style="display: none;"></div>

                            <!-- Summary data container -->
                            <div id="summary-data">
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-3">
                                        <div class="summary-item">
                                            <span class="summary-label">Periods Submitted:</span>
                                            <span class="summary-value" id="periods-count">-</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="summary-item">
                                            <span class="summary-label">Total Income:</span>
                                            <span class="summary-value text-success" id="total-income">£0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="summary-item">
                                            <span class="summary-label">Total Expenses:</span>
                                            <span class="summary-value text-danger" id="total-expenses">£0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="summary-item">
                                            <span class="summary-label">Net Profit:</span>
                                            <span class="summary-value text-hmrc" id="net-profit">£0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Adjustments -->
                <div class="form-step" data-step="2">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Adjustments</h2>

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            All adjustments are optional. Enter only the adjustments that apply to your business.
                        </div>

                        <!-- Base Adjustments (All Tax Years) -->
                        <h3 class="section-heading mb-3">Income & Expense Adjustments</h3>
                        <div class="row g-4 mb-5">
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
                                           value="{{ old('adjustments.' . $field['key']) }}"
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
                        <div class="ty-2024-25-adjustments" style="display: none;">
                            <div class="alert alert-info border-start border-4 border-info bg-light mb-4">
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
                                               value="{{ old('adjustments.' . $field['key']) }}"
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
                        </div>
                    </div>
                </div>

                <!-- Step 3: Allowances -->
                <div class="form-step" data-step="3">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Allowances</h2>

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
                                    <input class="form-check-input" type="checkbox" role="switch" id="use_trading_allowance" style="width: 3em; height: 1.5em; cursor: pointer;">
                                </div>
                            </div>
                        </div>

                        <!-- Trading Allowance Field (hidden by default) -->
                        <div id="trading-allowance-section" style="display: none;">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="trading_income_allowance" class="form-label">Trading Income Allowance</label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number"
                                               name="allowances[trading_income_allowance]"
                                               id="trading_income_allowance"
                                               class="form-control @error('allowances.trading_income_allowance') is-invalid @enderror"
                                               value="{{ old('allowances.trading_income_allowance', '1000') }}"
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

                        <!-- Capital Allowances (shown by default) -->
                        <div id="capital-allowances-section">
                            <h3 class="section-heading mb-3">Capital Allowances</h3>

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
                                               value="{{ old('allowances.' . $field['key']) }}"
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
                            <div class="ty-up-to-2024-allowances mb-4" style="display: none;">
                                <div class="alert alert-warning border-start border-4 border-warning bg-light mb-3">
                                    <i class="fas fa-calendar-times me-2 text-warning"></i>
                                    <strong>TY ≤ 2024 Fields</strong> - These fields are only available for tax years up to 2024-25
                                </div>

                                <div class="row g-4">
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
                                                   value="{{ old('allowances.' . $field['key']) }}"
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
                            </div>

                            <!-- Structured Building Allowances -->
                            <div class="mt-4">
                                <h4 class="section-heading mb-3">
                                    <i class="fas fa-building me-2"></i>Structured Building Allowances
                                </h4>
                                <p class="text-muted small mb-3">Add details for qualifying non-residential structures (3% annual allowance)</p>

                                <div id="sba-container">
                                    <!-- SBA entries will be added here -->
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-sba">
                                    <i class="fas fa-plus me-1"></i> Add Structured Building Allowance
                                </button>
                            </div>

                            <!-- Enhanced Structured Building Allowances -->
                            <div class="mt-4">
                                <h4 class="section-heading mb-3">
                                    <i class="fas fa-building me-2"></i>Enhanced Structured Building Allowances
                                </h4>
                                <p class="text-muted small mb-3">Freeport enhanced allowance (10% per year)</p>

                                <div id="esba-container">
                                    <!-- ESBA entries will be added here -->
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
                </div>

                <!-- Step 4: Non-Financials -->
                <div class="form-step" data-step="4">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Non-Financials</h2>

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            All fields in this section are optional. Complete only if the information is relevant to your submission.
                        </div>

                        <!-- Business Address -->
                        <h3 class="section-heading mb-3">Business Address</h3>
                        <div class="row g-4 mb-5">
                            <div class="col-md-12">
                                <label for="business_address_line_1" class="form-label">Address Line 1</label>
                                <input type="text"
                                       name="non_financials[business_address_line_1]"
                                       id="business_address_line_1"
                                       class="form-control"
                                       value="{{ old('non_financials.business_address_line_1') }}"
                                       placeholder="Enter address line 1"
                                       maxlength="35">
                            </div>

                            <div class="col-md-12">
                                <label for="business_address_line_2" class="form-label">Address Line 2</label>
                                <input type="text"
                                       name="non_financials[business_address_line_2]"
                                       id="business_address_line_2"
                                       class="form-control"
                                       value="{{ old('non_financials.business_address_line_2') }}"
                                       placeholder="Enter address line 2"
                                       maxlength="35">
                            </div>

                            <div class="col-md-6">
                                <label for="business_address_line_3" class="form-label">Address Line 3 / Town</label>
                                <input type="text"
                                       name="non_financials[business_address_line_3]"
                                       id="business_address_line_3"
                                       class="form-control"
                                       value="{{ old('non_financials.business_address_line_3') }}"
                                       placeholder="Enter town/city"
                                       maxlength="35">
                            </div>

                            <div class="col-md-6">
                                <label for="business_address_line_4" class="form-label">Address Line 4 / County</label>
                                <input type="text"
                                       name="non_financials[business_address_line_4]"
                                       id="business_address_line_4"
                                       class="form-control"
                                       value="{{ old('non_financials.business_address_line_4') }}"
                                       placeholder="Enter county"
                                       maxlength="35">
                            </div>

                            <div class="col-md-6">
                                <label for="business_address_postcode" class="form-label">Postcode</label>
                                <input type="text"
                                       name="non_financials[business_address_postcode]"
                                       id="business_address_postcode"
                                       class="form-control"
                                       value="{{ old('non_financials.business_address_postcode') }}"
                                       placeholder="e.g., SW1A 1AA"
                                       maxlength="10">
                            </div>

                            <div class="col-md-6">
                                <label for="business_address_country_code" class="form-label">Country Code</label>
                                <input type="text"
                                       name="non_financials[business_address_country_code]"
                                       id="business_address_country_code"
                                       class="form-control"
                                       value="{{ old('non_financials.business_address_country_code', 'GB') }}"
                                       placeholder="GB"
                                       maxlength="2">
                                <small class="text-muted">Two letter ISO country code (e.g., GB, FR, US)</small>
                            </div>
                        </div>

                        <!-- Class 4 NICs -->
                        <h3 class="section-heading mb-3">Class 4 National Insurance Contributions</h3>
                        <div class="row g-4 mb-5">
                            <div class="col-md-12">
                                <label for="class_4_nics_exemption_reason" class="form-label">Exemption Reason (if applicable)</label>
                                <select name="non_financials[class_4_nics_exemption_reason]"
                                        id="class_4_nics_exemption_reason"
                                        class="form-select">
                                    <option value="">No exemption</option>
                                    <option value="001" {{ old('non_financials.class_4_nics_exemption_reason') == '001' ? 'selected' : '' }}>001 - Non-resident</option>
                                    <option value="002" {{ old('non_financials.class_4_nics_exemption_reason') == '002' ? 'selected' : '' }}>002 - Trustee</option>
                                    <option value="003" {{ old('non_financials.class_4_nics_exemption_reason') == '003' ? 'selected' : '' }}>003 - Diver</option>
                                    <option value="004" {{ old('non_financials.class_4_nics_exemption_reason') == '004' ? 'selected' : '' }}>004 - Employed earner taxed under ITTOIA 2005</option>
                                    <option value="005" {{ old('non_financials.class_4_nics_exemption_reason') == '005' ? 'selected' : '' }}>005 - Over state pension age</option>
                                    <option value="006" {{ old('non_financials.class_4_nics_exemption_reason') == '006' ? 'selected' : '' }}>006 - Under 16</option>
                                </select>
                                <small class="text-muted">Select only if you qualify for an exemption from Class 4 NICs</small>
                            </div>
                        </div>

                        <!-- Business Details Changed -->
                        <h3 class="section-heading mb-3">Business Details</h3>
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label">Have your business details changed recently?</label>
                                <div class="form-check">
                                    <input class="form-check-input @error('non_financials.business_details_changed_recently') is-invalid @enderror"
                                           type="radio"
                                           name="non_financials[business_details_changed_recently]"
                                           id="business_details_yes"
                                           value="1"
                                           {{ old('non_financials.business_details_changed_recently') == '1' ? 'checked' : '' }}>
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
                                           {{ old('non_financials.business_details_changed_recently', '0') == '0' ? 'checked' : '' }}>
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
                </div>

                <!-- Step 5: Review -->
                <div class="form-step" data-step="5">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Review & Submit</h2>

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            This will create a draft submission. You can review it before submitting to HMRC.
                        </div>

                        <!-- Review Summary -->
                        <div class="review-summary">
                            <h3 class="summary-title mb-3">Summary</h3>

                            <div class="summary-section mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="summary-item">
                                            <span class="summary-label">Business:</span>
                                            <span class="summary-value" id="review-business">-</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="summary-item">
                                            <span class="summary-label">Tax Year:</span>
                                            <span class="summary-value" id="review-tax-year">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="summary-section mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="summary-item">
                                            <span class="summary-label">Total Allowances:</span>
                                            <span class="summary-value text-success" id="review-allowances">£0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="summary-item">
                                            <span class="summary-label">Income Adjustments:</span>
                                            <span class="summary-value" id="review-income-adj">£0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="summary-item">
                                            <span class="summary-label">Expense Adjustments:</span>
                                            <span class="summary-value text-danger" id="review-expense-adj">£0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="total-box mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="total-label">Net Impact:</span>
                                    <span class="total-value" id="review-net-impact">£0.00</span>
                                </div>
                            </div>

                            <div class="summary-section">
                                <h4 class="summary-subtitle mb-3">Entries by Category:</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="summary-item">
                                            <span class="summary-label">Allowances:</span>
                                            <span class="summary-value" id="review-allowances-count">0 fields</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="summary-item">
                                            <span class="summary-label">Income Adjustments:</span>
                                            <span class="summary-value" id="review-income-count">0 fields</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="summary-item">
                                            <span class="summary-label">Expense Adjustments:</span>
                                            <span class="summary-value" id="review-expense-count">0 fields</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="summary-item">
                                            <span class="summary-label">Non-Financials:</span>
                                            <span class="summary-value" id="review-nonfinancial-count">0 fields</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="review-address" class="summary-section mt-4" style="display:none;">
                                <h4 class="summary-subtitle mb-3">Business Address:</h4>
                                <div id="review-address-content" class="text-muted"></div>
                            </div>

                            <div id="review-nics" class="summary-section mt-4" style="display:none;">
                                <h4 class="summary-subtitle mb-3">NICs Exemption:</h4>
                                <div id="review-nics-content" class="text-muted"></div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes"
                                      id="notes"
                                      class="form-control"
                                      rows="3"
                                      maxlength="5000"
                                      placeholder="Add any notes about this annual submission...">{{ old('notes') }}</textarea>
                            <small class="text-muted"><span id="notes-count">0</span> / 5000 characters</small>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="form-navigation">
                    <x-hmrc.secondary-button type="button" class="btn-prev" id="prev-btn" icon="fas fa-chevron-left" style="display:none;">
                        Previous
                    </x-hmrc.secondary-button>
                    <x-hmrc.primary-button type="button" class="btn-next" id="next-btn" icon="fas fa-chevron-right" iconPosition="right">
                        Next
                    </x-hmrc.primary-button>
                    <x-hmrc.primary-button type="submit" class="btn-submit" id="submit-btn" icon="fas fa-check" style="display:none;">
                        Create Draft Submission
                    </x-hmrc.primary-button>
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

/* Step Indicator */
.step-indicator {
    position: relative;
    padding: 2rem 0;
}

.step-track {
    position: absolute;
    top: 50%;
    left: 5%;
    right: 5%;
    height: 2px;
    background: #e5e7eb;
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
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.step.active .step-circle,
.step.completed .step-circle {
    background: #17848e;
    color: white;
}

.step.has-error .step-circle {
    background: #dc3545;
    color: white;
    animation: pulse-error 2s infinite;
}

@keyframes pulse-error {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
}

.step-title {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: center;
    margin: 0;
}

.step.active .step-title {
    color: #17848e;
    font-weight: 600;
}

.step.has-error .step-title {
    color: #dc3545;
    font-weight: 600;
}

/* HMRC Card */
.hmrc-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
}

/* Form Steps */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Headings */
.step-heading {
    color: #13667d;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.section-heading {
    color: #13667d;
    font-size: 1.125rem;
    font-weight: 600;
}

/* Form Elements */
.required::after {
    content: ' *';
    color: #dc3545;
}

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

/* Quarterly Summary */
.quarterly-summary {
    background: #e8f4f8;
    border: 1px solid #b3d9e6;
    border-radius: 8px;
    padding: 1.5rem;
}

.summary-title {
    color: #13667d;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
}

.summary-label {
    color: #6c757d;
    font-size: 0.875rem;
}

.summary-value {
    font-weight: 600;
    color: #212529;
}

.text-hmrc {
    color: #17848e !important;
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

/* Building Allowance Cards */
.building-allowance-entry .card {
    border-left: 4px solid #17848e;
}

.building-allowance-entry .card-body {
    background: #f8f9fa;
}

/* Review Summary */
.review-summary {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
}

.summary-section {
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.summary-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.summary-subtitle {
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.75rem;
}

/* Form Navigation */
.form-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 767px) {
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

    .step-circle {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }

    .step-title {
        font-size: 0.625rem;
    }

    .hmrc-card {
        padding: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 5;

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle business selection - auto-fill NINO
    document.getElementById('business_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const nino = selectedOption.dataset.nino;
        if (nino) {
            document.getElementById('nino').value = nino;
        }

        // Show quarterly summary
        if (this.value) {
            fetchQuarterlySummary();
        }
    });

    // Handle tax year selection - refresh quarterly summary AND show/hide tax-year-specific fields
    document.getElementById('tax_year').addEventListener('change', function() {
        const businessId = document.getElementById('business_id').value;
        if (businessId && this.value) {
            fetchQuarterlySummary();
        }
        updateTaxYearFields(this.value);
    });

    // Update field visibility based on tax year
    function updateTaxYearFields(taxYear) {
        if (!taxYear) return;

        const taxYearNum = parseInt(taxYear.substring(0, 4));

        // TY 2024-25+ Adjustment fields
        const ty202425AdjSection = document.querySelector('.ty-2024-25-adjustments');
        if (ty202425AdjSection) {
            ty202425AdjSection.style.display = taxYearNum >= 2024 ? 'block' : 'none';
        }

        // TY ≤ 2024 Allowance fields
        const tyUpTo2024AllowSection = document.querySelector('.ty-up-to-2024-allowances');
        if (tyUpTo2024AllowSection) {
            tyUpTo2024AllowSection.style.display = taxYearNum <= 2024 ? 'block' : 'none';
        }
    }

    // Initialize tax year fields on page load
    const initialTaxYear = document.getElementById('tax_year').value;
    if (initialTaxYear) {
        updateTaxYearFields(initialTaxYear);
    }

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
                tradingAllowanceInput.setAttribute('name', 'allowances[trading_income_allowance]'); // Restore name
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
                tradingAllowanceInput.removeAttribute('name'); // Don't submit this field
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

    // Fetch and show quarterly summary from database
    function fetchQuarterlySummary() {
        const businessId = document.getElementById('business_id').value;
        const taxYear = document.getElementById('tax_year').value;

        if (!businessId || !taxYear) {
            return;
        }

        const summaryDiv = document.getElementById('quarterly-summary');
        summaryDiv.style.display = 'block';

        // Show loading state
        document.getElementById('periods-count').textContent = 'Loading...';
        document.getElementById('total-income').textContent = 'Loading...';
        document.getElementById('total-expenses').textContent = 'Loading...';
        document.getElementById('net-profit').textContent = 'Loading...';

        // Fetch data via AJAX
        fetch('{{ route("hmrc.annual-submissions.quarterly-summary") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                business_id: businessId,
                tax_year: taxYear
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const summary = data.data;

                // Check if any periodic submissions exist
                if (summary.period_count === 0) {
                    // Show warning and hide data
                    document.getElementById('summary-warning').style.display = 'block';
                    document.getElementById('summary-warning').innerHTML = `
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>No Periodic Submissions Found</strong>
                                <p class="mb-0">You must submit quarterly/periodic updates before submitting an annual return for this business and tax year.</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('summary-data').style.display = 'none';
                } else {
                    // Hide warning and show data
                    document.getElementById('summary-warning').style.display = 'none';
                    document.getElementById('summary-data').style.display = 'block';

                    // Display the summary data
                    document.getElementById('periods-count').textContent = summary.period_count;
                    document.getElementById('total-income').textContent = formatCurrency(summary.total_income);
                    document.getElementById('total-expenses').textContent = formatCurrency(summary.total_expenses);
                    document.getElementById('net-profit').textContent = formatCurrency(summary.net_profit);
                }
            } else {
                showSummaryError('Failed to load quarterly summary');
            }
        })
        .catch(error => {
            console.error('Error fetching quarterly summary:', error);
            showSummaryError('Error loading quarterly summary. Please try again.');
        });
    }

    // Show error in summary
    function showSummaryError(message) {
        document.getElementById('summary-warning').style.display = 'block';
        document.getElementById('summary-warning').innerHTML = `
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                <div>${message}</div>
            </div>
        `;
        document.getElementById('summary-data').style.display = 'none';
    }

    // Structured Building Allowances - Dynamic Fields
    let sbaIndex = 0;
    let esbaIndex = 0;

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

    // Real-time calculation for allowances
    const capitalAllowanceInputs = document.querySelectorAll('.capital-allowance-input');
    capitalAllowanceInputs.forEach(input => {
        input.addEventListener('input', calculateTotalAllowances);
    });

    // Also listen to trading allowance input
    if (tradingAllowanceInput) {
        tradingAllowanceInput.addEventListener('input', calculateTotalAllowances);
    }

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

    // Notes character counter
    const notesInput = document.getElementById('notes');
    if (notesInput) {
        notesInput.addEventListener('input', function() {
            document.getElementById('notes-count').textContent = this.value.length;
        });
    }

    // Navigation
    document.getElementById('next-btn').addEventListener('click', function() {
        if (validateCurrentStep()) {
            goToStep(currentStep + 1);
        }
    });

    document.getElementById('prev-btn').addEventListener('click', function() {
        goToStep(currentStep - 1);
    });

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        // Hide current step
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

        // Mark as completed
        if (step > currentStep) {
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('completed');
        }

        // Show next step
        currentStep = step;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');

        // Update buttons
        document.getElementById('prev-btn').style.display = currentStep === 1 ? 'none' : 'inline-block';
        document.getElementById('next-btn').style.display = currentStep === totalSteps ? 'none' : 'inline-block';
        document.getElementById('submit-btn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';

        // Update review if on step 5
        if (currentStep === 5) {
            updateReviewSummary();
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
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

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields',
                text: 'Please fill in all required fields before continuing.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }

        return isValid;
    }

    function updateReviewSummary() {
        // Business & Tax Year
        const businessSelect = document.getElementById('business_id');
        const businessName = businessSelect.options[businessSelect.selectedIndex].text;
        const taxYearSelect = document.getElementById('tax_year');
        const taxYear = taxYearSelect.options[taxYearSelect.selectedIndex].text;

        document.getElementById('review-business').textContent = businessName;
        document.getElementById('review-tax-year').textContent = taxYear;

        // Calculate totals
        let totalAllowances = 0;
        if (tradingAllowanceToggle.checked && tradingAllowanceInput) {
            totalAllowances = parseFloat(tradingAllowanceInput.value || 0);
        } else {
            document.querySelectorAll('.capital-allowance-input').forEach(input => {
                if (!input.disabled) {
                    totalAllowances += parseFloat(input.value || 0);
                }
            });
        }

        let adjustmentsTotal = 0;
        document.querySelectorAll('.adjustment-input').forEach(input => {
            adjustmentsTotal += parseFloat(input.value || 0);
        });

        const netImpact = totalAllowances + adjustmentsTotal;

        document.getElementById('review-allowances').textContent = formatCurrency(totalAllowances);
        document.getElementById('review-income-adj').textContent = formatCurrency(adjustmentsTotal);
        document.getElementById('review-income-adj').className = 'summary-value ' + (adjustmentsTotal >= 0 ? 'text-success' : 'text-danger');
        document.getElementById('review-expense-adj').textContent = formatCurrency(0);
        document.getElementById('review-net-impact').textContent = formatCurrency(netImpact);
        document.getElementById('review-net-impact').className = 'total-value ' + (netImpact >= 0 ? 'text-success' : 'text-danger');

        // Count entries
        let allowancesCount = countNonZeroInputs('input[name^="allowances["]');
        allowancesCount += document.querySelectorAll('.building-allowance-entry').length; // Add building allowances count

        const adjustmentsCount = countNonZeroInputs('.adjustment-input');
        const nonFinancialCount = countNonZeroInputs('input[name^="non_financials["]') + (document.getElementById('class_4_nics_exemption_reason').value ? 1 : 0);

        document.getElementById('review-allowances-count').textContent = allowancesCount + ' fields';
        document.getElementById('review-income-count').textContent = adjustmentsCount + ' fields';
        document.getElementById('review-expense-count').textContent = '0 fields';
        document.getElementById('review-nonfinancial-count').textContent = nonFinancialCount + ' fields';

        // Address
        const addressLines = [];
        ['business_address_line_1', 'business_address_line_2', 'business_address_line_3', 'business_address_line_4', 'business_address_postcode', 'business_address_country_code'].forEach(id => {
            const val = document.getElementById(id).value.trim();
            if (val) addressLines.push(val);
        });

        if (addressLines.length > 0) {
            document.getElementById('review-address').style.display = 'block';
            document.getElementById('review-address-content').innerHTML = addressLines.join('<br>');
        } else {
            document.getElementById('review-address').style.display = 'none';
        }

        // NICs
        const nicsReason = document.getElementById('class_4_nics_exemption_reason');
        if (nicsReason.value) {
            document.getElementById('review-nics').style.display = 'block';
            document.getElementById('review-nics-content').textContent = 'Code: ' + nicsReason.options[nicsReason.selectedIndex].text;
        } else {
            document.getElementById('review-nics').style.display = 'none';
        }
    }

    function countNonZeroInputs(selector) {
        let count = 0;
        document.querySelectorAll(selector).forEach(input => {
            if (input.value && parseFloat(input.value) !== 0) {
                count++;
            }
        });
        return count;
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('en-GB', {
            style: 'currency',
            currency: 'GBP'
        }).format(value);
    }

    // Show toast for session messages
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    @endif

    // Handle validation errors - scroll to first error and show step indicator
    @if($errors->any())
        // Find which steps have errors and mark them
        const stepsWithErrors = [];

        // Check step 1 (business/tax year)
        if (document.querySelector('#business_id.is-invalid') ||
            document.querySelector('#tax_year.is-invalid') ||
            document.querySelector('#nino.is-invalid')) {
            stepsWithErrors.push(1);
        }

        // Check step 2 (adjustments)
        if (document.querySelector('.adjustment-input.is-invalid')) {
            stepsWithErrors.push(2);
        }

        // Check step 3 (allowances)
        if (document.querySelector('.capital-allowance-input.is-invalid') ||
            document.querySelector('#trading_income_allowance.is-invalid')) {
            stepsWithErrors.push(3);
        }

        // Check step 4 (non-financials)
        if (document.querySelector('input[name^="non_financials"].is-invalid') ||
            document.querySelector('#class_4_nics_exemption_reason.is-invalid')) {
            stepsWithErrors.push(4);
        }

        // Mark steps with errors
        stepsWithErrors.forEach(step => {
            const stepEl = document.querySelector(`.step[data-step="${step}"]`);
            if (stepEl) {
                stepEl.classList.add('has-error');
            }
        });

        // Go to the first step with errors
        if (stepsWithErrors.length > 0) {
            goToStep(stepsWithErrors[0]);
        }

        // Scroll to first error
        setTimeout(() => {
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }, 300);

        // Show error notification with count
        const errorCount = document.querySelectorAll('.is-invalid').length;
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: `Please fix ${errorCount} error${errorCount > 1 ? 's' : ''} in the highlighted fields.`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
    @endif
});
</script>
@endpush

