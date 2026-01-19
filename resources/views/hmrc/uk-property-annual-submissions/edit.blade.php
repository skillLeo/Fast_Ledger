@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-annual-submissions.index') }}">UK Property Annual Submissions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-annual-submissions.show', $submission) }}">{{ $submission->tax_year }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    @if($isAmendment ?? false)
                        Amend UK Property Annual Submission
                    @else
                        Edit UK Property Annual Submission
                    @endif
                </h1>
                <p class="text-muted mb-0">{{ $submission->business?->trading_name ?? $submission->business_id }} - {{ $submission->tax_year }}</p>
            </div>
        </div>

        @if($isAmendment ?? false)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Amendment Mode:</strong> This submission has already been submitted to HMRC. Any changes you make will <strong>amend</strong> the existing submission.
                The amended data will completely replace the original submission when you resubmit.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h6>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="annual-submission-form" method="POST" action="{{ route('hmrc.uk-property-annual-submissions.update', $submission) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="business_id" value="{{ $submission->business_id }}">
            <input type="hidden" name="tax_year" value="{{ $submission->tax_year }}">

            <!-- Adjustments -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-balance-scale me-2"></i>Adjustments</h5>
                </div>
                <div class="card-body">
                    @php
                        // Extract adjustments data based on tax year structure
                        $taxYear = $submission->tax_year;
                        $taxYearNum = (int) substr($taxYear, 0, 4);
                        $isTY202425 = $taxYear === '2024-25';
                        $isTY202526Plus = $taxYearNum >= 2025;

                        $adjustmentsJson = $submission->adjustments_json ?? [];
                    @endphp

                    @if($isTY202425)
                        <!-- Property Type Tabs for TY 2024-25 -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fhl-adj-tab" data-bs-toggle="tab"
                                        data-bs-target="#fhl-adjustments-edit" type="button" role="tab">
                                    <i class="fas fa-home me-2"></i>FHL Property
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="non-fhl-adj-tab" data-bs-toggle="tab"
                                        data-bs-target="#non-fhl-adjustments-edit" type="button" role="tab">
                                    <i class="fas fa-building me-2"></i>Non-FHL Property
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="fhl-adjustments-edit" role="tabpanel">
                                @php
                                    $fhlAdjustments = $adjustmentsJson['ukFhlProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', [
                                    'propertyType' => 'fhl',
                                    'existingData' => $fhlAdjustments
                                ])
                            </div>
                            <div class="tab-pane fade" id="non-fhl-adjustments-edit" role="tabpanel">
                                @php
                                    $nonFhlAdjustments = $adjustmentsJson['ukProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', [
                                    'propertyType' => 'non_fhl',
                                    'existingData' => $nonFhlAdjustments
                                ])
                            </div>
                        </div>
                    @elseif($isTY202526Plus)
                        <!-- Flat structure for TY 2025-26+ (No FHL) -->
                        @php
                            $adjustments = $adjustmentsJson['ukProperty'] ?? [];
                        @endphp
                        @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', [
                            'propertyType' => null,
                            'existingData' => $adjustments
                        ])
                    @else
                        <!-- Tabs for TY before 2024-25 (Both FHL and Non-FHL) -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fhl-adj-old-tab" data-bs-toggle="tab"
                                        data-bs-target="#fhl-adjustments-old-edit" type="button" role="tab">
                                    <i class="fas fa-home me-2"></i>FHL Property
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="non-fhl-adj-old-tab" data-bs-toggle="tab"
                                        data-bs-target="#non-fhl-adjustments-old-edit" type="button" role="tab">
                                    <i class="fas fa-building me-2"></i>Non-FHL Property
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="fhl-adjustments-old-edit" role="tabpanel">
                                @php
                                    $fhlAdjustments = $adjustmentsJson['ukFhlProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', [
                                    'propertyType' => 'fhl',
                                    'existingData' => $fhlAdjustments
                                ])
                            </div>
                            <div class="tab-pane fade" id="non-fhl-adjustments-old-edit" role="tabpanel">
                                @php
                                    $nonFhlAdjustments = $adjustmentsJson['ukProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.adjustments-form', [
                                    'propertyType' => 'non_fhl',
                                    'existingData' => $nonFhlAdjustments
                                ])
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Allowances -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-money-bill-wave me-2"></i>Capital Allowances</h5>
                </div>
                <div class="card-body">
                    @php
                        $allowancesJson = $submission->allowances_json ?? [];
                    @endphp

                    @if($isTY202425)
                        <!-- Property Type Tabs for TY 2024-25 -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fhl-all-tab" data-bs-toggle="tab"
                                        data-bs-target="#fhl-allowances-edit" type="button" role="tab">
                                    <i class="fas fa-home me-2"></i>FHL Property
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="non-fhl-all-tab" data-bs-toggle="tab"
                                        data-bs-target="#non-fhl-allowances-edit" type="button" role="tab">
                                    <i class="fas fa-building me-2"></i>Non-FHL Property
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="fhl-allowances-edit" role="tabpanel">
                                @php
                                    $fhlAllowances = $allowancesJson['ukFhlProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.allowances-form', [
                                    'propertyType' => 'fhl',
                                    'existingData' => $fhlAllowances
                                ])
                            </div>
                            <div class="tab-pane fade" id="non-fhl-allowances-edit" role="tabpanel">
                                @php
                                    $nonFhlAllowances = $allowancesJson['ukProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.allowances-form', [
                                    'propertyType' => 'non_fhl',
                                    'existingData' => $nonFhlAllowances
                                ])
                            </div>
                        </div>
                    @elseif($isTY202526Plus)
                        <!-- Flat structure for TY 2025-26+ (No FHL) -->
                        @php
                            $allowances = $allowancesJson['ukProperty'] ?? [];
                        @endphp
                        @include('hmrc.uk-property-annual-submissions.partials.allowances-form', [
                            'propertyType' => null,
                            'existingData' => $allowances
                        ])
                    @else
                        <!-- Tabs for TY before 2024-25 (Both FHL and Non-FHL) -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="fhl-all-old-tab" data-bs-toggle="tab"
                                        data-bs-target="#fhl-allowances-old-edit" type="button" role="tab">
                                    <i class="fas fa-home me-2"></i>FHL Property
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="non-fhl-all-old-tab" data-bs-toggle="tab"
                                        data-bs-target="#non-fhl-allowances-old-edit" type="button" role="tab">
                                    <i class="fas fa-building me-2"></i>Non-FHL Property
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="fhl-allowances-old-edit" role="tabpanel">
                                @php
                                    $fhlAllowances = $allowancesJson['ukFhlProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.allowances-form', [
                                    'propertyType' => 'fhl',
                                    'existingData' => $fhlAllowances
                                ])
                            </div>
                            <div class="tab-pane fade" id="non-fhl-allowances-old-edit" role="tabpanel">
                                @php
                                    $nonFhlAllowances = $allowancesJson['ukProperty'] ?? [];
                                @endphp
                                @include('hmrc.uk-property-annual-submissions.partials.allowances-form', [
                                    'propertyType' => 'non_fhl',
                                    'existingData' => $nonFhlAllowances
                                ])
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
                              placeholder="Add any notes about this submission...">{{ old('notes', $submission->notes) }}</textarea>
                    <small class="text-muted">Maximum 5000 characters</small>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mb-4">
                <a href="{{ route('hmrc.uk-property-annual-submissions.show', $submission) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
                <button type="submit" class="btn {{ ($isAmendment ?? false) ? 'btn-warning' : 'btn-success' }}">
                    <i class="fas fa-save me-2"></i> {{ ($isAmendment ?? false) ? 'Save Amendment' : 'Update Draft' }}
                </button>
            </div>
        </form>
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

    // Tax year-based field visibility
    // Show building allowances sections for all tax years
    // TY < 2025-26: Under Non-FHL property
    // TY >= 2025-26: Under unified property (no FHL split)
    document.querySelectorAll('.ty-2024-25-fields').forEach(el => {
        el.style.display = 'block';
    });

    // Allowance Type Switching (All Tax Years)
    const allowanceTypeSelector = document.querySelector('.allowance-type-selector');
    const propertyIncomeRadio = document.getElementById('allowance_type_property_income');
    const otherAllowancesRadio = document.getElementById('allowance_type_other');
    const propertyIncomeSection = document.getElementById('property-income-section');
    const otherAllowancesSection = document.getElementById('other-allowances-section');
    const propertyIncomeInput = document.getElementById('allowances_property_income_allowance');

    if (allowanceTypeSelector) {
        // Detect which option should be selected by default based on existing data
        const hasPropertyIncomeValue = propertyIncomeInput && propertyIncomeInput.value && parseFloat(propertyIncomeInput.value) > 0;

        if (hasPropertyIncomeValue) {
            propertyIncomeRadio.checked = true;
            showPropertyIncomeSection();
        } else {
            otherAllowancesRadio.checked = true;
            showOtherAllowancesSection();
        }

        // Add event listeners for radio button changes
        propertyIncomeRadio.addEventListener('change', function() {
            if (this.checked) {
                showPropertyIncomeSection();
                clearOtherAllowancesData();
            }
        });

        otherAllowancesRadio.addEventListener('change', function() {
            if (this.checked) {
                showOtherAllowancesSection();
                clearPropertyIncomeData();
            }
        });
    }

    function showPropertyIncomeSection() {
        if (propertyIncomeSection) propertyIncomeSection.style.display = 'block';
        if (otherAllowancesSection) otherAllowancesSection.style.display = 'none';
    }

    function showOtherAllowancesSection() {
        if (propertyIncomeSection) propertyIncomeSection.style.display = 'none';
        if (otherAllowancesSection) otherAllowancesSection.style.display = 'block';
    }

    function clearPropertyIncomeData() {
        if (propertyIncomeInput) {
            propertyIncomeInput.value = '';
        }
    }

    function clearOtherAllowancesData() {
        // Clear all standard allowance fields
        const otherInputs = otherAllowancesSection.querySelectorAll('input[type="number"]');
        otherInputs.forEach(input => {
            if (input.id !== 'allowances_property_income_allowance') {
                input.value = '';
            }
        });

        // Clear structured building allowances
        const sbaContainer = document.getElementById('sba-container');
        if (sbaContainer) {
            sbaContainer.innerHTML = '';
        }

        // Clear enhanced structured building allowances
        const esbaContainer = document.getElementById('esba-container');
        if (esbaContainer) {
            esbaContainer.innerHTML = '';
        }
    }

    // Dynamic Structured Building Allowances (SBA) - Handle multiple property types
    const sbaIndexes = {};
    const addSbaButtons = document.querySelectorAll('[id^="add-sba-"]');
    addSbaButtons.forEach(btn => {
        const propertyType = btn.id.replace('add-sba-', '');
        // Determine the field prefix based on property type
        const fieldPrefix = propertyType === 'default' ? 'allowances' :
                          propertyType === 'fhl' ? 'fhl_allowances' :
                          propertyType === 'non_fhl' ? 'non_fhl_allowances' : 'allowances';

        // Count existing SBA entries for this property type
        const container = document.getElementById(`sba-container-${propertyType}`);
        if (container) {
            sbaIndexes[propertyType] = container.querySelectorAll('.building-allowance-card').length;
        } else {
            sbaIndexes[propertyType] = 0;
        }

        btn.addEventListener('click', function() {
            const sbaCard = createBuildingAllowanceCard('structured_building_allowance', sbaIndexes[propertyType], fieldPrefix);
            container.insertAdjacentHTML('beforeend', sbaCard);
            sbaIndexes[propertyType]++;
        });
    });

    // Dynamic Enhanced Structured Building Allowances (ESBA) - Handle multiple property types
    const esbaIndexes = {};
    const addEsbaButtons = document.querySelectorAll('[id^="add-esba-"]');
    addEsbaButtons.forEach(btn => {
        const propertyType = btn.id.replace('add-esba-', '');
        // Determine the field prefix based on property type
        const fieldPrefix = propertyType === 'default' ? 'allowances' :
                          propertyType === 'fhl' ? 'fhl_allowances' :
                          propertyType === 'non_fhl' ? 'non_fhl_allowances' : 'allowances';

        // Count existing ESBA entries for this property type
        const container = document.getElementById(`esba-container-${propertyType}`);
        if (container) {
            esbaIndexes[propertyType] = container.querySelectorAll('.building-allowance-card').length;
        } else {
            esbaIndexes[propertyType] = 0;
        }

        btn.addEventListener('click', function() {
            const esbaCard = createBuildingAllowanceCard('enhanced_structured_building_allowance', esbaIndexes[propertyType], fieldPrefix);
            container.insertAdjacentHTML('beforeend', esbaCard);
            esbaIndexes[propertyType]++;
        });
    });

    function createBuildingAllowanceCard(type, index, fieldPrefix) {
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
                        <div class="col-md-3">
                            <label class="form-label">Building Number</label>
                            <input type="text" name="${fieldPrefix}[${type}][${index}][building_number]"
                                   class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Building Postcode</label>
                            <input type="text" name="${fieldPrefix}[${type}][${index}][building_postcode]"
                                   class="form-control" maxlength="10" placeholder="SW1A 1AA">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
});
</script>
@endpush
@endsection
