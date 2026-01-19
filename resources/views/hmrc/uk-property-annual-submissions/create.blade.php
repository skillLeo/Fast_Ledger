@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="hmrc-icon-wrapper">
                    <i class="fas fa-home"></i>
                </div>
                <div>
                    <h4 class="page-title mb-1">UK Property - Annual Submission</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Submit annual property tax information to HMRC</p>
                </div>
            </div>
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
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <p class="step-title">Review</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form id="annual-submission-form" method="POST" action="{{ route('hmrc.uk-property-annual-submissions.store') }}">
                @csrf

                <!-- Hidden fields -->
                @if($obligation ?? false)
                    <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                @endif

                <!-- Step 1: Business & Tax Year -->
                <div class="form-step active" data-step="1">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Business & Tax Year Selection</h2>

                        @if($obligation ?? false)
                            <div class="alert alert-info border-start border-4 border-info mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-link me-2 mt-1"></i>
                                    <div>
                                        <strong class="d-block">Linked Obligation</strong>
                                        <small class="text-muted">
                                            Type: {{ $obligation->getObligationTypeLabel() }} |
                                            Tax Year: {{ $obligation->tax_year }} |
                                            Due: {{ optional($obligation->due_date)->format('d M Y') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-4">
                            <!-- Business Selection -->
                            <div class="col-md-12">
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

                            <!-- Tax Year Selection -->
                            <div class="col-md-6">
                                <label for="tax_year" class="form-label required">Tax Year</label>
                                <select name="tax_year" id="tax_year" class="form-select @error('tax_year') is-invalid @enderror" required>
                                    @php
                                        $currentYear = date('Y');
                                        $currentMonth = date('n');
                                        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
                                        $selectedTaxYear = old('tax_year', $obligation->tax_year ?? request('tax_year'));
                                    @endphp
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $year = $startYear - $i;
                                            $taxYear = $year . '-' . substr($year + 1, 2);
                                        @endphp
                                        <option value="{{ $taxYear }}" {{ $selectedTaxYear == $taxYear || (!$selectedTaxYear && $i == 0) ? 'selected' : '' }}>
                                            {{ $taxYear }} ({{ $year }}/{{ $year + 1 }})
                                        </option>
                                    @endfor
                                </select>
                                <small class="text-muted">Selected tax year affects available fields and validation rules</small>
                                @error('tax_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <small class="text-muted">Format: AB123456C (optional if already on business)</small>
                                @error('nino')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(config('hmrc.environment') === 'sandbox')
                            <!-- Test Scenario -->
                            <div class="col-md-12">
                                <label for="test_scenario" class="form-label">
                                    Test Scenario
                                    <span class="badge bg-warning text-dark ms-2">Sandbox Only</span>
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
                    </div>
                </div>

                <!-- Step 2: Adjustments -->
                <div class="form-step" data-step="2">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Adjustments</h2>

                        <!-- Property Type Tabs for TY 2024-25 only -->
                        <div class="property-type-tabs" style="display: none;">
                            <ul class="nav nav-tabs hmrc-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="fhl-adj-tab" data-bs-toggle="tab"
                                            data-bs-target="#fhl-adjustments" type="button" role="tab">
                                        <i class="fas fa-home me-2"></i>FHL Property
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="non-fhl-adj-tab" data-bs-toggle="tab"
                                            data-bs-target="#non-fhl-adjustments" type="button" role="tab">
                                        <i class="fas fa-building me-2"></i>Non-FHL Property
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-4">
                                <div class="tab-pane fade show active" id="fhl-adjustments" role="tabpanel">
                                    @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', ['propertyType' => 'fhl'])
                                </div>
                                <div class="tab-pane fade" id="non-fhl-adjustments" role="tabpanel">
                                    @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', ['propertyType' => 'non_fhl'])
                                </div>
                            </div>
                        </div>

                        <!-- Flat structure for other tax years -->
                        <div class="flat-structure">
                            @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', ['propertyType' => null])
                        </div>
                    </div>
                </div>

                <!-- Step 3: Allowances -->
                <div class="form-step" data-step="3">
                    <div class="hmrc-card">
                        <h2 class="step-heading mb-4">Capital Allowances</h2>

                        <!-- Property Type Tabs for TY 2024-25 only -->
                        <div class="property-type-tabs" style="display: none;">
                            <ul class="nav nav-tabs hmrc-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="fhl-all-tab" data-bs-toggle="tab"
                                            data-bs-target="#fhl-allowances" type="button" role="tab">
                                        <i class="fas fa-home me-2"></i>FHL Property
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="non-fhl-all-tab" data-bs-toggle="tab"
                                            data-bs-target="#non-fhl-allowances" type="button" role="tab">
                                        <i class="fas fa-building me-2"></i>Non-FHL Property
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-4">
                                <div class="tab-pane fade show active" id="fhl-allowances" role="tabpanel">
                                    @include('hmrc.uk-property-annual-submissions.partials.allowances-form', ['propertyType' => 'fhl'])
                                </div>
                                <div class="tab-pane fade" id="non-fhl-allowances" role="tabpanel">
                                    @include('hmrc.uk-property-annual-submissions.partials.allowances-form', ['propertyType' => 'non_fhl'])
                                </div>
                            </div>
                        </div>

                        <!-- Flat structure for other tax years -->
                        <div class="flat-structure">
                            @include('hmrc.uk-property-annual-submissions.partials.allowances-form', ['propertyType' => null])
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review & Submit -->
                <div class="form-step" data-step="4">
                    @include('hmrc.uk-property-annual-submissions.partials.review-form')
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

/* HMRC Tabs */
.hmrc-tabs {
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 2rem;
}

.hmrc-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.2s ease;
}

.hmrc-tabs .nav-link:hover {
    color: #17848e;
    background: #f8f9fa;
}

.hmrc-tabs .nav-link.active {
    color: #17848e;
    background: transparent;
    border-bottom-color: #17848e;
}

/* Alert Styles */
.alert-info {
    background: #e8f4f8;
    border: 1px solid #b3d9e6;
    color: #0c5460;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
}

.text-hmrc {
    color: #17848e !important;
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

.summary-title {
    color: #13667d;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.summary-subtitle {
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.75rem;
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

/* Form Navigation */
.form-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* TY 2024-25+ Fields */
.ty-2024-25-fields {
    display: none;
}

/* Building Allowance Cards */
.building-allowance-card .card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
}

.building-allowance-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.building-allowance-card .card-body {
    padding: 1.5rem;
}

.btn-outline-danger {
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-outline-danger:hover {
    transform: translateY(-1px);
}

/* Switch Component Styles */
.switch-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.switch-item:hover {
    background: #f8f9fa;
    border-color: #17848e;
}

.switch-info {
    flex: 1;
}

.switch-info label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
    display: block;
}

.switch-info p {
    color: #6c757d;
    font-size: 0.875rem;
    margin: 0;
}

.form-check-input:checked {
    background-color: #17848e;
    border-color: #17848e;
}

/* Building Allowance Cards */
.building-allowance-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.building-allowance-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
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
    const totalSteps = 4;

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
    });

    // Handle tax year changes
    document.getElementById('tax_year').addEventListener('change', function() {
        handleTaxYearChange(this.value);
    });

    function handleTaxYearChange(taxYear) {
        const taxYearNum = parseInt(taxYear.split('-')[0]);
        const isTY202425 = taxYear === '2024-25'; // Only TY 2024-25
        const isTY202526Plus = taxYearNum >= 2025;

        // Show building allowances fields for all tax years
        // TY < 2025-26: Under Non-FHL property
        // TY >= 2025-26: Under unified property (no FHL split)
        const ty202425Fields = document.querySelectorAll('.ty-2024-25-fields');
        ty202425Fields.forEach(el => {
            el.style.display = 'block';
        });

        // Toggle property type tabs (FHL/Non-FHL)
        // TY 2024-25: Show tabs (FHL and Non-FHL separate)
        // TY 2025-26+: Show flat structure (No FHL, only unified property)
        // TY < 2024-25: Show flat structure (FHL and Non-FHL separate but no tabs)
        const propertyTypeTabs = document.querySelectorAll('.property-type-tabs');
        const flatStructures = document.querySelectorAll('.flat-structure');

        if (isTY202425) {
            // Show tabs for TY 2024-25 ONLY
            propertyTypeTabs.forEach(el => el.style.display = 'block');
            flatStructures.forEach(el => el.style.display = 'none');
        } else if (isTY202526Plus) {
            // Show unified flat structure for TY 2025-26+ (no FHL support)
            propertyTypeTabs.forEach(el => el.style.display = 'none');
            flatStructures.forEach(el => el.style.display = 'block');
        } else {
            // Show flat structure for TY < 2024-25 (FHL and Non-FHL separate)
            propertyTypeTabs.forEach(el => el.style.display = 'none');
            flatStructures.forEach(el => el.style.display = 'block');
        }
    }

    // Initialize on page load
    const initialTaxYear = document.getElementById('tax_year').value;
    if (initialTaxYear) {
        handleTaxYearChange(initialTaxYear);
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

    // Dynamic Structured Building Allowances (SBA) - Handle multiple property types
    const sbaIndexes = {};
    const addSbaButtons = document.querySelectorAll('[id^="add-sba-"]');
    addSbaButtons.forEach(btn => {
        const propertyType = btn.id.replace('add-sba-', '');
        sbaIndexes[propertyType] = 0;

        btn.addEventListener('click', function() {
            const container = document.getElementById(`sba-container-${propertyType}`);
            const sbaCard = createBuildingAllowanceCard('structured_building_allowance', sbaIndexes[propertyType], propertyType);
            container.insertAdjacentHTML('beforeend', sbaCard);
            sbaIndexes[propertyType]++;
        });
    });

    // Dynamic Enhanced Structured Building Allowances (ESBA) - Handle multiple property types
    const esbaIndexes = {};
    const addEsbaButtons = document.querySelectorAll('[id^="add-esba-"]');
    addEsbaButtons.forEach(btn => {
        const propertyType = btn.id.replace('add-esba-', '');
        esbaIndexes[propertyType] = 0;

        btn.addEventListener('click', function() {
            const container = document.getElementById(`esba-container-${propertyType}`);
            const esbaCard = createBuildingAllowanceCard('enhanced_structured_building_allowance', esbaIndexes[propertyType], propertyType);
            container.insertAdjacentHTML('beforeend', esbaCard);
            esbaIndexes[propertyType]++;
        });
    });

    function createBuildingAllowanceCard(type, index, propertyType) {
        const displayType = type === 'structured_building_allowance' ? 'Structured Building' : 'Enhanced Structured Building';
        // Determine the field prefix based on property type
        const fieldPrefix = propertyType === 'default' ? 'allowances' :
                          propertyType === 'fhl' ? 'fhl_allowances' :
                          propertyType === 'non_fhl' ? 'non_fhl_allowances' : 'allowances';

        return `
            <div class="building-allowance-card mb-3">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">${displayType} Allowance #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-building" onclick="this.closest('.building-allowance-card').remove();">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number" name="${fieldPrefix}[${type}][${index}][amount]"
                                           class="form-control" required
                                           step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">First Year Qualifying Date</label>
                                <input type="date" name="${fieldPrefix}[${type}][${index}][first_year_qualifying_date]"
                                       class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">First Year Qualifying Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number" name="${fieldPrefix}[${type}][${index}][first_year_qualifying_amount]"
                                           class="form-control"
                                           step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Building Name</label>
                                <input type="text" name="${fieldPrefix}[${type}][${index}][building_name]"
                                       class="form-control" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Building Number</label>
                                <input type="text" name="${fieldPrefix}[${type}][${index}][building_number]"
                                       class="form-control" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Building Postcode</label>
                                <input type="text" name="${fieldPrefix}[${type}][${index}][building_postcode]"
                                       class="form-control" maxlength="10" placeholder="SW1A 1AA">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

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
        document.getElementById('prev-btn').style.display = currentStep === 1 ? 'none' : 'inline-flex';
        document.getElementById('next-btn').style.display = currentStep === totalSteps ? 'none' : 'inline-flex';
        document.getElementById('submit-btn').style.display = currentStep === totalSteps ? 'inline-flex' : 'none';

        // Build review summary when reaching step 4 (Review)
        if (currentStep === 4 && typeof window.buildReviewSummary === 'function') {
            window.buildReviewSummary();
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        if (!currentStepEl) return true;

        const requiredInputs = currentStepEl.querySelectorAll('[required]:not([disabled])');
        const requiredSelects = currentStepEl.querySelectorAll('select[required]:not([disabled])');

        let isValid = true;
        let missingFields = [];

        // Validate required inputs
        requiredInputs.forEach(input => {
            const value = input.value ? input.value.trim() : '';
            if (!value) {
                input.classList.add('is-invalid');
                isValid = false;

                // Get field label
                const label = input.closest('.col-md-6, .col-md-12, .col-12')?.querySelector('label');
                if (label) {
                    missingFields.push(label.textContent.replace('*', '').trim());
                }
            } else {
                input.classList.remove('is-invalid');
            }
        });

        // Validate required selects
        requiredSelects.forEach(select => {
            if (!select.value) {
                select.classList.add('is-invalid');
                isValid = false;

                // Get field label
                const label = select.closest('.col-md-6, .col-md-12, .col-12')?.querySelector('label');
                if (label) {
                    missingFields.push(label.textContent.replace('*', '').trim());
                }
            } else {
                select.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields Missing',
                html: `Please fill in the following required fields:<br><br><ul style="text-align: left;">${missingFields.map(f => '<li>' + f + '</li>').join('')}</ul>`,
                confirmButtonColor: '#17848e',
                confirmButtonText: 'OK'
            });
        }

        return isValid;
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
});
</script>
@endpush
