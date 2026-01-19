@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.calculations.index') }}">Tax Calculations</a></li>
                <li class="breadcrumb-item active" aria-current="page">Trigger New Calculation</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Trigger Tax Calculation</h1>
                <p class="text-muted mb-0">Request a Self Assessment tax calculation from HMRC</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('hmrc.calculations.store') }}">
                    @csrf

                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Calculation Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>What is a tax calculation?</strong><br>
                                A tax calculation shows how much Income Tax and National Insurance you owe based on your 
                                Self Assessment submissions. You can run calculations throughout the year to estimate your tax liability.
                            </div>

                            <div class="row g-3">
                                <!-- NINO -->
                                <div class="col-md-6">
                                    <label for="nino" class="form-label required">National Insurance Number</label>
                                    <input type="text" name="nino" id="nino" 
                                           class="form-control @error('nino') is-invalid @enderror"
                                           value="{{ old('nino', $defaultNino) }}"
                                           placeholder="AB123456C"
                                           pattern="^[A-Z]{2}[0-9]{6}[A-Z]$"
                                           required>
                                    <small class="text-muted">Format: AB123456C</small>
                                    @error('nino')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tax Year -->
                                <div class="col-md-6">
                                    <label for="tax_year" class="form-label required">Tax Year</label>
                                    <select name="tax_year" id="tax_year" 
                                            class="form-select @error('tax_year') is-invalid @enderror" 
                                            required>
                                        <option value="">Select tax year</option>
                                        @foreach($taxYears as $year)
                                            <option value="{{ $year }}" {{ old('tax_year') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tax_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <hr>
                                </div>

                                <!-- Calculation Type -->
                                <div class="col-12">
                                    <label class="form-label">Calculation Type</label>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="calculation_type" 
                                               id="type_forecast" value="forecast" 
                                               {{ old('calculation_type', 'forecast') == 'forecast' ? 'checked' : '' }}
                                               onchange="updateCalculationType()">
                                        <label class="form-check-label" for="type_forecast">
                                            <strong>In-Year Estimate (Forecast)</strong>
                                            <br>
                                            <small class="text-muted">
                                                Get an estimate of your tax liability during the tax year. This is not a final declaration.
                                            </small>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="calculation_type" 
                                               id="type_crystallisation" value="crystallisation"
                                               {{ old('calculation_type') == 'crystallisation' ? 'checked' : '' }}
                                               onchange="updateCalculationType()">
                                        <label class="form-check-label" for="type_crystallisation">
                                            <strong>Final Declaration (Crystallisation)</strong>
                                            <br>
                                            <small class="text-muted">
                                                Make a final declaration for the tax year. This calculates your final tax liability.
                                            </small>
                                        </label>
                                    </div>
                                </div>

                                <!-- Crystallisation Options (shown only when crystallisation is selected) -->
                                <div class="col-12" id="crystallisation-options" style="display: none;">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Important:</strong> Crystallisation is a formal declaration to HMRC. 
                                        Ensure all your income and expense submissions are complete and accurate before proceeding.
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="crystallise" 
                                               id="crystallise" value="1">
                                        <label class="form-check-label" for="crystallise">
                                            I confirm this is a crystallisation (final declaration)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('hmrc.calculations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calculator me-2"></i> Trigger Calculation
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            About Tax Calculations
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">When to run a calculation:</h6>
                        <ul class="small">
                            <li>After submitting quarterly updates</li>
                            <li>To estimate your tax liability</li>
                            <li>Before making payments on account</li>
                            <li>At the end of the tax year for final declaration</li>
                        </ul>

                        <hr>

                        <h6 class="mb-2">What you'll get:</h6>
                        <ul class="small mb-0">
                            <li>Total taxable income</li>
                            <li>Income tax and NICs due</li>
                            <li>Tax band breakdown</li>
                            <li>Allowances and deductions</li>
                            <li>Payment dates and amounts</li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('hmrc.submissions.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-invoice me-1"></i> View Submissions
                            </a>
                            <a href="{{ route('hmrc.obligations.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-calendar-check me-1"></i> View Obligations
                            </a>
                            <a href="{{ route('hmrc.calculations.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-history me-1"></i> Past Calculations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .required::after {
        content: ' *';
        color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
    function updateCalculationType() {
        const isCrystallisation = document.getElementById('type_crystallisation').checked;
        const crystallisationOptions = document.getElementById('crystallisation-options');
        
        if (isCrystallisation) {
            crystallisationOptions.style.display = 'block';
        } else {
            crystallisationOptions.style.display = 'none';
            document.getElementById('crystallise').checked = false;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCalculationType();
    });
</script>
@endpush
@endsection

