@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-annual-submissions.index') }}">UK Property Annual Submissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Submission</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Create UK Property Annual Submission</h1>
                <p class="text-muted mb-0">Submit annual adjustments and allowances for your UK property business</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="annual-submission-form" method="POST" action="{{ route('hmrc.uk-property-annual-submissions.store') }}">
            @csrf

            <!-- Progress Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="progress-steps">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Business & Year</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Property Data</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Adjustments</div>
                        </div>
                        <div class="step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-label">Allowances</div>
                        </div>
                        <div class="step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-label">Review</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Business & Tax Year Selection -->
            <div class="form-step active" id="step-1">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building me-2"></i>
                            Business & Tax Year
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Business Selection -->
                            <div class="col-md-6">
                                <label for="business_id" class="form-label required">UK Property Business</label>
                                <select name="business_id" id="business_id" class="form-select @error('business_id') is-invalid @enderror" required>
                                    <option value="">Select a business</option>
                                    @foreach($businesses as $business)
                                        <option value="{{ $business->business_id }}"
                                                {{ old('business_id', request('business_id')) == $business->business_id ? 'selected' : '' }}
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
                                        @endphp
                                        <option value="{{ $taxYear }}" {{ old('tax_year', $taxYear) == $taxYear ? 'selected' : '' }}>
                                            {{ $taxYear }} ({{ $year }}/{{ $year + 1 }})
                                        </option>
                                    @endfor
                                </select>
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

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary next-step">
                                Next: Property Data <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Property Data (FHL & Non-FHL) -->
            <div class="form-step" id="step-2">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-home me-2"></i>
                            Property Income & Expenses
                        </h5>
                    </div>
                    <div class="card-body">
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
                                @include('hmrc.uk-property-annual-submissions.partials.fhl-form')
                            </div>

                            <!-- Non-FHL Tab -->
                            <div class="tab-pane fade" id="non-fhl" role="tabpanel">
                                @include('hmrc.uk-property-annual-submissions.partials.non-fhl-form')
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary prev-step">
                                <i class="fas fa-arrow-left me-2"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next: Adjustments <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Adjustments -->
            <div class="form-step" id="step-3">
                @include('hmrc.uk-property-annual-submissions.partials.adjustments-form')
            </div>

            <!-- Step 4: Allowances -->
            <div class="form-step" id="step-4">
                @include('hmrc.uk-property-annual-submissions.partials.allowances-form')
            </div>

            <!-- Step 5: Review & Submit -->
            <div class="form-step" id="step-5">
                @include('hmrc.uk-property-annual-submissions.partials.review-form')
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
    });

    // Handle tax year changes - show/hide fields based on tax year
    document.getElementById('tax_year').addEventListener('change', function() {
        const taxYear = this.value;
        handleTaxYearChange(taxYear);
    });

    function handleTaxYearChange(taxYear) {
        const isNewStructure = taxYear >= '2024-25';
        const isFhlSupported = taxYear < '2025-26';

        // Toggle TY 2024-25+ specific fields
        const ty202425Fields = document.querySelectorAll('.ty-2024-25-fields');
        ty202425Fields.forEach(el => {
            el.style.display = isNewStructure ? 'block' : 'none';
        });

        // Toggle FHL tab for TY 2025-26+
        const fhlTabButton = document.getElementById('fhl-tab');
        const fhlTabPane = document.getElementById('fhl');
        const nonFhlTabButton = document.getElementById('non-fhl-tab');

        if (fhlTabButton && fhlTabPane) {
            if (!isFhlSupported) {
                // Hide FHL tab button and pane
                fhlTabButton.closest('.nav-item').style.display = 'none';
                fhlTabPane.classList.remove('show', 'active');

                // Show non-FHL tab by default
                if (nonFhlTabButton) {
                    nonFhlTabButton.classList.add('active');
                    const nonFhlPane = document.getElementById('non-fhl');
                    if (nonFhlPane) {
                        nonFhlPane.classList.add('show', 'active');
                    }
                }

                // Disable all FHL form inputs to prevent submission
                const fhlInputs = fhlTabPane.querySelectorAll('input, select, textarea');
                fhlInputs.forEach(input => {
                    input.disabled = true;
                    input.value = '';
                });
            } else {
                // Show FHL tab for older tax years
                fhlTabButton.closest('.nav-item').style.display = 'block';

                // Re-enable FHL form inputs
                const fhlInputs = fhlTabPane.querySelectorAll('input, select, textarea');
                fhlInputs.forEach(input => {
                    input.disabled = false;
                });
            }
        }

        // Hide old structure/buildings fields for TY 2024-25+
        if (isNewStructure) {
            const oldSbaFields = [
                'allowances_structure_and_buildings_allowance',
                'allowances_enhanced_structure_and_buildings_allowance',
                'allowances_zero_emissions_goods_vehicle_allowance'
            ];
            oldSbaFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.closest('.col-md-6').style.display = 'none';
                }
            });
        } else {
            // Show old fields for pre-2024-25
            const oldSbaFields = [
                'allowances_structure_and_buildings_allowance',
                'allowances_enhanced_structure_and_buildings_allowance',
                'allowances_zero_emissions_goods_vehicle_allowance'
            ];
            oldSbaFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.closest('.col-md-6').style.display = 'block';
                }
            });
        }
    }

    // Initialize on page load
    const initialTaxYear = document.getElementById('tax_year').value;
    if (initialTaxYear) {
        handleTaxYearChange(initialTaxYear);
    }

    // Dynamic Structured Building Allowances (SBA)
    let sbaIndex = 0;
    const addSbaBtn = document.getElementById('add-sba');
    if (addSbaBtn) {
        addSbaBtn.addEventListener('click', function() {
            const container = document.getElementById('sba-container');
            const sbaCard = createBuildingAllowanceCard('structured_building_allowance', sbaIndex);
            container.insertAdjacentHTML('beforeend', sbaCard);
            sbaIndex++;
        });
    }

    // Dynamic Enhanced Structured Building Allowances (ESBA)
    let esbaIndex = 0;
    const addEsbaBtn = document.getElementById('add-esba');
    if (addEsbaBtn) {
        addEsbaBtn.addEventListener('click', function() {
            const container = document.getElementById('esba-container');
            const esbaCard = createBuildingAllowanceCard('enhanced_structured_building_allowance', esbaIndex);
            container.insertAdjacentHTML('beforeend', esbaCard);
            esbaIndex++;
        });
    }

    function createBuildingAllowanceCard(type, index) {
        const displayType = type === 'structured_building_allowance' ? 'Structured Building' : 'Enhanced Structured Building';
        return `
            <div class="card mb-3 building-allowance-card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">${displayType} Allowance #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-building" onclick="this.closest('.building-allowance-card').remove();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount (Required)</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" name="allowances[${type}][${index}][amount]"
                                       class="form-control" required
                                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Year Qualifying Date</label>
                            <input type="date" name="allowances[${type}][${index}][first_year_qualifying_date]"
                                   class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Year Qualifying Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" name="allowances[${type}][${index}][first_year_qualifying_amount]"
                                       class="form-control"
                                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Building Name</label>
                            <input type="text" name="allowances[${type}][${index}][building_name]"
                                   class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Building Number</label>
                            <input type="text" name="allowances[${type}][${index}][building_number]"
                                   class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Building Postcode</label>
                            <input type="text" name="allowances[${type}][${index}][building_postcode]"
                                   class="form-control" maxlength="10" placeholder="SW1A 1AA">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Handle form step navigation
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', function() {
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
        }

        // Show next step
        currentStep = step;
        document.getElementById('step-' + currentStep).classList.add('active');
        document.querySelector('.step[data-step="' + currentStep + '"]').classList.add('active');

        // Build review summary when reaching step 5
        if (currentStep === 5 && typeof window.buildReviewSummary === 'function') {
            window.buildReviewSummary();
        }

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
});
</script>
@endpush
@endsection
