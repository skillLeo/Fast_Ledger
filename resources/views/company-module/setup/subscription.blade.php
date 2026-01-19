{{-- resources/views/company-module/setup/subscription.blade.php --}}

@extends('admin.layout.app')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8">
                
                {{-- Logo and Progress --}}
                <div class="text-center mb-4">
                    <img src="{{ asset('admin/assets/images/brand-logos/logo.png') }}" 
                         alt="logo" 
                         style="height: 40px; margin-bottom: 1rem;">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <span class="badge bg-success">✓ Email Verified</span>
                        <i class="ri-arrow-right-line text-white"></i>
                        <span class="badge bg-warning text-dark">Step 1: Subscription Plan</span>
                        <i class="ri-arrow-right-line text-white"></i>
                        <span class="badge bg-light text-dark">Step 2: Company Setup</span>
                    </div>
                </div>

                {{-- Card --}}
                <div class="card custom-card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0 text-white">
                            <i class="ri-price-tag-3-line me-2"></i>Choose Your Subscription Plan
                        </h4>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>⚠️ Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('company.subscription.store') }}">
                            @csrf

                            {{-- Number of Companies Input --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    How many companies do you want to manage? <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="number_of_companies" 
                                       id="number_of_companies"
                                       class="form-control form-control-lg @error('number_of_companies') is-invalid @enderror"
                                       value="{{ old('number_of_companies', 1) }}"
                                       min="1"
                                       max="10"
                                       required
                                       placeholder="Enter number (1-10)">
                                @error('number_of_companies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Enter a number between 1 and 10. You can upgrade later if needed.</small>
                            </div>

                            {{-- Pricing Display --}}
                            <div class="alert alert-light border mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h6 class="fw-bold mb-2">
                                            <i class="ri-calculator-line me-2 text-primary"></i>Price Breakdown
                                        </h6>
                                        <div id="pricingDetails" style="font-size: 15px;">
                                            <span id="companiesCount" class="fw-bold text-primary">1</span> 
                                            {{ Str::plural('company', 1) }} × £10 = 
                                            <strong id="totalPrice" class="text-success">£10</strong>
                                        </div>
                                        <small class="text-muted">£10 per company per month</small>
                                    </div>
                                    <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                        <div class="text-muted" style="font-size: 12px;">Total Monthly Cost</div>
                                        <div style="font-size: 48px; font-weight: 700; color: #667eea; line-height: 1;" id="totalPriceDisplay">
                                            £10
                                        </div>
                                        <small class="text-muted">per month</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Features --}}
                            <div class="alert alert-info border-0 mb-4">
                                <h6 class="fw-bold mb-2">
                                    <i class="ri-checkbox-circle-line me-2"></i>What's Included:
                                </h6>
                                <ul class="mb-0" style="font-size: 14px;">
                                    <li>✅ 14-day free trial</li>
                                    <li>✅ Unlimited invoices per company</li>
                                    <li>✅ Unlimited customers</li>
                                    <li>✅ Full feature access</li>
                                    <li>✅ Cancel anytime</li>
                                </ul>
                            </div>

                            {{-- Submit Button --}}
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ri-arrow-right-line me-2"></i>
                                    Continue to Company Setup
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-light">
                                    Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

                {{-- Help Text --}}
                <div class="text-center mt-4">
                    <small class="text-white">
                        <i class="ri-question-line me-1"></i>
                        Need help? <a href="#" class="text-white text-decoration-underline">Contact Support</a>
                    </small>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Real-time pricing calculation
    const input = document.getElementById('number_of_companies');
    const companiesCount = document.getElementById('companiesCount');
    const totalPrice = document.getElementById('totalPrice');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');

    function updatePricing() {
        const numberOfCompanies = parseInt(input.value) || 1;
        const pricePerCompany = 10;
        const total = numberOfCompanies * pricePerCompany;

        companiesCount.textContent = numberOfCompanies;
        totalPrice.textContent = `£${total}`;
        totalPriceDisplay.textContent = `£${total}`;
    }

    input.addEventListener('input', updatePricing);
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', updatePricing);
</script>
@endsection