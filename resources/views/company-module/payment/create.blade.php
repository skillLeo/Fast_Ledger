{{-- resources/views/company-module/payment/create.blade.php --}}
@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-10">

                    {{-- Page Title --}}
                    <div class="mb-4">
                        <h3 class="fw-bold mb-1">
                            @if($pricing['is_upgrade'])
                                Add More Companies
                            @else
                                Choose Your Plan
                            @endif
                        </h3>

                        {{-- Progress Indicator --}}
                        @if(!$pricing['is_upgrade'])
                            <div class="d-flex align-items-center gap-2 mt-3">
                                <span class="badge bg-success-subtle text-success border border-success">✓ Email Verified</span>
                                <div style="width: 30px; height: 2px; background: #dee2e6;"></div>
                                <span class="badge bg-success-subtle text-success border border-success">✓ Company
                                    Created</span>
                                <div style="width: 30px; height: 2px; background: #dee2e6;"></div>
                                <span class="badge bg-primary text-white">Payment</span>
                            </div>
                        @endif
                    </div>

                    {{-- Test Mode --}}
                    @if($isTestMode)
                        <div class="alert alert-warning border-warning mb-4">
                            <i class="ri-test-tube-line me-2"></i>
                            <strong>TEST MODE</strong> - Use test card: 4242 4242 4242 4242
                        </div>
                    @endif

                    {{-- Error Messages --}}
                    <div id="error-message" class="alert alert-danger border-danger d-none mb-4" role="alert">
                        <strong>⚠️ Error:</strong>
                        <span id="error-text"></span>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-danger alert-dismissible fade show mb-4" role="alert">
                            <strong>⚠️ Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form id="payment-form">
                        @csrf

                        <div class="row g-4">

                            {{-- LEFT COLUMN: Plan Details --}}
                            <div class="col-xl-7">

                                {{-- Current Subscription (Upgrade Only) --}}
                                @if($pricing['is_upgrade'])
                                    <div class="mb-4 p-3 bg-light rounded-3">
                                        <small class="text-muted d-block mb-1">Current Subscription</small>
                                        <strong>{{ $pricing['current_companies'] }}
                                            {{ Str::plural('company', $pricing['current_companies']) }}</strong>
                                        <span class="text-muted">=
                                            {{ $pricing['currency'] }}{{ $pricing['current_companies'] * $pricing['price_per_company'] }}/month</span>
                                    </div>
                                @endif

                                {{-- ✅ FREE TRIAL vs PAY NOW (Only for new subscriptions) --}}
                                @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                    <div class="mb-4">
                                        <h5 class="fw-semibold mb-3">Get Started</h5>

                                        <div class="row g-3">
                                            {{-- Free Trial Option --}}
                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_option" id="free_trial"
                                                    value="trial" checked>
                                                <label class="btn btn-outline-success w-100 text-start p-4" for="free_trial"
                                                    style="border: 3px solid #198754; height: 100%;">
                                                    <div class="mb-2">
                                                        <span class="badge bg-success mb-2">✨ RECOMMENDED</span>
                                                        <h6 class="fw-bold mb-1">Start Free Trial</h6>
                                                    </div>
                                                    <ul class="mb-0 small ps-3">
                                                        <li><strong>3 months FREE</strong> (no charge today)</li>
                                                        <li><strong>Then 6 months 90% OFF</strong></li>
                                                        <li>Then full price</li>
                                                        <li>Card required</li>
                                                    </ul>
                                                </label>
                                            </div>

                                            {{-- Pay Now Option --}}
                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_option" id="pay_now"
                                                    value="paid">
                                                <label class="btn btn-outline-primary w-100 text-start p-4" for="pay_now"
                                                    style="border: 2px solid #e0e0e0; height: 100%;">
                                                    <div class="mb-2">
                                                        <h6 class="fw-bold mb-1">Pay Now</h6>
                                                    </div>
                                                    <ul class="mb-0 small ps-3">
                                                        <li>Charged today</li>
                                                        <li>Immediate full access</li>
                                                        <li>Monthly or yearly</li>
                                                    </ul>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Plan Details --}}
                                <div class="mb-4">
                                    <h5 class="fw-semibold mb-3">Plan details</h5>

                                    {{-- Number of Companies --}}
                                    <div class="mb-4">
                                        <label class="form-label text-dark mb-2">
                                            @if($pricing['is_upgrade'])
                                                Number of companies to add
                                            @else
                                                Number of companies
                                            @endif
                                        </label>

                                        <p class="text-muted small mb-2">
                                            @if($pricing['is_upgrade'])
                                                Additional companies will be charged immediately.
                                            @else
                                                Select how many companies you need to manage.
                                            @endif
                                        </p>

                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="decrementCompanies()"
                                                style="width: 40px; height: 40px; padding: 0;">−</button>
                                            <input type="number" id="additional_companies"
                                                class="form-control text-center fw-bold" value="1" min="1" max="50" readonly
                                                style="width: 80px; font-size: 1.5rem;">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="incrementCompanies()"
                                                style="width: 40px; height: 40px; padding: 0;">+</button>
                                        </div>
                                    </div>

                                    {{-- ✅ Modules (ONLY new subscriptions) --}}
                                    @if(!$pricing['is_upgrade'])
                                        <div class="mb-4">
                                            <label class="form-label text-dark mb-2">Modules</label>

                                            <div class="p-3 border rounded-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <div class="fw-semibold">Fast Books</div>
                                                        <div class="text-muted small">Always included</div>
                                                    </div>
                                                    <span class="badge bg-success">Included</span>
                                                </div>

                                                <div
                                                    class="form-check d-flex align-items-start justify-content-between py-2 border-top">
                                                    <div>
                                                        <input class="form-check-input" type="checkbox" id="module_manager">
                                                        <label class="form-check-label" for="module_manager">
                                                            <div class="fw-semibold">Fast Manager</div>
                                                            <div class="text-muted small">+{{ $pricing['currency'] }}20 / month
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center justify-content-between py-2 border-top opacity-75">
                                                    <div>
                                                        <div class="fw-semibold">Fast Payroll</div>
                                                        <div class="text-muted small">Coming soon</div>
                                                    </div>
                                                    <span class="badge bg-secondary">Coming soon</span>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center justify-content-between py-2 border-top opacity-75">
                                                    <div>
                                                        <div class="fw-semibold">Fast Taxation</div>
                                                        <div class="text-muted small">Coming soon</div>
                                                    </div>
                                                    <span class="badge bg-secondary">Coming soon</span>
                                                </div>
                                            </div>

                                            <small class="text-muted d-block mt-2">
                                                Fast Books is always included. Select Fast Manager if needed.
                                            </small>
                                        </div>
                                    @endif

                                    {{-- Billing Cycle --}}
                                    <div class="mb-4">
                                        <label class="form-label text-dark mb-2">Billing cycle</label>

                                        <p class="text-muted small mb-3">
                                            <span id="billing_description">
                                                @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                                    No charge today if you start the trial.
                                                @else
                                                    Pay annually for a 20% discount.
                                                @endif
                                            </span>
                                        </p>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_frequency" id="monthly"
                                                    value="monthly" checked>
                                                <label class="btn btn-outline-primary w-100 text-start p-3" for="monthly"
                                                    style="border: 2px solid #e0e0e0;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check-input-custom me-3"></div>
                                                        <div>
                                                            <div class="fw-semibold">Pay monthly</div>
                                                            <div class="text-muted small" id="monthlyRecurring">
                                                                {{ $pricing['currency'] }}{{ $pricing['price_per_company'] }}
                                                                per company / month
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_frequency" id="yearly"
                                                    value="yearly">
                                                <label class="btn btn-outline-primary w-100 text-start p-3" for="yearly"
                                                    style="border: 2px solid #e0e0e0;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check-input-custom me-3"></div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <span class="fw-semibold">Pay annually</span>
                                                                <span class="badge bg-success">Save 20%</span>
                                                            </div>
                                                            <div class="text-muted small" id="yearlyRecurring">
                                                                {{ $pricing['currency'] }}{{ round($pricing['price_per_company'] * 0.8, 2) }}
                                                                per company / month
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                {{-- ✅ Payment Details --}}
                                <div class="mb-4">
                                    <h5 class="fw-semibold mb-3">
                                        Payment details
                                        <span class="badge bg-danger-subtle text-danger ms-2">Required</span>
                                    </h5>

                                    <div class="mb-3">
                                        <label class="form-label text-dark mb-2">Card information</label>
                                        <div id="card-element" class="form-control"
                                            style="height: 50px; padding: 15px; border: 2px solid #e0e0e0;">
                                            <!-- Stripe card element -->
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            @if($isTestMode)
                                                💡 Test: 4242 4242 4242 4242, any future date, any CVC
                                            @else
                                                🔒 Secured by Stripe encryption
                                            @endif
                                        </small>
                                    </div>

                                    {{-- ✅ Trial Notice --}}
                                    <div id="trial_notice" class="alert alert-info border-info"
                                        style="display: @if(!$pricing['is_upgrade'] && $pricing['can_use_trial']) block @else none @endif;">
                                        <div class="d-flex">
                                            <i class="ri-information-line me-2 fs-5"></i>
                                            <div>
                                                <strong>No charge today on trial</strong>
                                                <p class="mb-0 small">
                                                    Trial rules: <strong>3 months FREE</strong> → <strong>6 months 90%
                                                        OFF</strong> → full price.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Terms --}}
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="agree_terms" required>
                                        <label class="form-check-label text-muted" for="agree_terms">
                                            I agree to the <a href="#" class="text-primary">Terms & Conditions</a> and
                                            <a href="#" class="text-primary">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            {{-- RIGHT COLUMN: Summary --}}
                            <div class="col-xl-5">
                                <div class="card border-0 shadow-sm" style="position: sticky; top: 20px;">
                                    <div class="card-body p-4">

                                        <h5 class="fw-semibold mb-4">Summary</h5>

                                        {{-- Company Info --}}
                                        <div class="d-flex align-items-start mb-4 pb-4 border-bottom">
                                            <div class="bg-dark rounded me-3"
                                                style="width: 48px; height: 48px; flex-shrink: 0;"></div>
                                            <div>
                                                <div class="fw-semibold">FastLedger</div>
                                                <div class="text-muted small">
                                                    <span id="summaryCompanies">
                                                        @if($pricing['is_upgrade'])
                                                            {{ $pricing['current_companies'] + 1 }}x
                                                        @else
                                                            1x
                                                        @endif
                                                    </span>
                                                    companies
                                                </div>
                                                <div class="text-muted small" id="trial_badge">
                                                    @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                                        3 months free trial
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Pricing Breakdown --}}
                                        @if($pricing['is_upgrade'])
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Current subscription</span>
                                                    <span>{{ $pricing['currency'] }}{{ $pricing['current_companies'] * $pricing['price_per_company'] }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3 text-success">
                                                    <span>+ <span id="addCount">1</span> <span
                                                            id="addText">company</span></span>
                                                    <span>+{{ $pricing['currency'] }}<span
                                                            id="addPrice">{{ $pricing['price_per_company'] }}</span></span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Companies</span>
                                                    <span>{{ $pricing['currency'] }}<span
                                                            id="companiesLine">{{ $pricing['price_per_company'] }}</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Fast Manager</span>
                                                    <span>{{ $pricing['currency'] }}<span id="managerLine">0</span></span>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between text-success">
                                                <span>Discount</span>
                                                <span id="discountAmount">−{{ $pricing['currency'] }}0</span>
                                            </div>
                                        </div>

                                        {{-- Total --}}
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-semibold">Due today</div>
                                                    <div class="text-muted small" id="dueDescription">
                                                        @if($pricing['is_upgrade'])
                                                            Charged now
                                                        @elseif($pricing['can_use_trial'])
                                                            Free trial - no charge
                                                        @else
                                                            Charged now
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="h4 fw-bold mb-0">{{ $pricing['currency'] }}<span
                                                            id="paymentAmountValue">0</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- What's Included --}}
                                        <div class="mb-4 p-3 bg-light rounded-3">
                                            <div class="fw-semibold mb-2 small">What's included:</div>
                                            <ul class="mb-0 small text-muted ps-3">
                                                <li id="trial_feature"
                                                    style="display: @if(!$pricing['is_upgrade'] && $pricing['can_use_trial']) block @else none @endif;">
                                                    3 months free trial</li>
                                                <li>Fast Books included</li>
                                                <li id="manager_feature" style="display:none;">Fast Manager module</li>
                                                <li>Unlimited invoices</li>
                                                <li>Unlimited customers</li>
                                                <li>Cancel anytime</li>
                                            </ul>
                                        </div>

                                        {{-- Action Button --}}
                                        <button type="submit" id="submit-button"
                                            class="btn btn-dark w-100 py-3 fw-semibold">
                                            <span id="button-text">
                                                <span id="button_text_content">
                                                    @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                                        Start Free Trial
                                                    @else
                                                        Pay
                                                    @endif
                                                </span>
                                            </span>
                                            <span id="spinner" class="spinner-border spinner-border-sm d-none"></span>
                                        </button>

                                        <div class="text-center mt-3">
                                            <a href="{{ route('company.select') }}"
                                                class="text-muted small text-decoration-none">Cancel</a>
                                        </div>

                                        {{-- Security --}}
                                        <div class="text-center mt-3 pt-3 border-top">
                                            <small class="text-muted">
                                                <i class="ri-shield-check-line me-1"></i>
                                                Secured by Stripe
                                            </small>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Radio Button Style */
        .btn-check:checked+label {
            border-color: #0d6efd !important;
            background-color: #f8f9ff !important;
        }

        #free_trial:checked+label {
            border-color: #198754 !important;
            background-color: #f0fff4 !important;
        }

        .form-check-input-custom {
            width: 18px;
            height: 18px;
            border: 2px solid #d0d0d0;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .btn-check:checked+label .form-check-input-custom {
            border-color: #0d6efd;
            background-color: #0d6efd;
            position: relative;
        }

        .btn-check:checked+label .form-check-input-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // ============================================
        // STRIPE CONFIGURATION
        // ============================================
        const stripeKey = '{{ $stripeKey }}';
        const stripe = Stripe(stripeKey);
        const elements = stripe.elements();

        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    '::placeholder': { color: '#aab7c4' },
                },
                invalid: { color: '#fa755a', iconColor: '#fa755a' },
            },
        });

        cardElement.mount('#card-element');

        // ============================================
        // CONFIG / ELEMENTS
        // ============================================
        const currency = '{{ $pricing["currency"] }}';
        const pricePerCompany = {{ $pricing['price_per_company'] }};
        const fastManagerMonthly = 20; // +£20/month

        const currentCompanies = {{ $pricing['current_companies'] ?? 0 }};
        const isUpgrade = {{ $pricing['is_upgrade'] ? 'true' : 'false' }};
        const canUseTrial = {{ $pricing['can_use_trial'] ? 'true' : 'false' }};

        const input = document.getElementById('additional_companies');
        const monthlyRadio = document.getElementById('monthly');
        const yearlyRadio = document.getElementById('yearly');
        const freeTrialRadio = document.getElementById('free_trial');
        const payNowRadio = document.getElementById('pay_now');

        const moduleManager = document.getElementById('module_manager'); // may be null in upgrade

        // ============================================
        // COMPANY COUNT CONTROLS
        // ============================================
        function incrementCompanies() {
            const current = parseInt(input.value) || 1;
            if (current < 50) {
                input.value = current + 1;
                updatePricing();
            }
        }

        function decrementCompanies() {
            const current = parseInt(input.value) || 1;
            if (current > 1) {
                input.value = current - 1;
                updatePricing();
            }
        }

        // ============================================
        // UI UPDATE
        // ============================================
        function updateUI() {
            const isYearly = yearlyRadio.checked;
            const isTrial = (!isUpgrade && canUseTrial && freeTrialRadio && freeTrialRadio.checked);

            const trialNotice = document.getElementById('trial_notice');
            if (trialNotice) trialNotice.style.display = (isTrial ? 'block' : 'none');

            const trialBadge = document.getElementById('trial_badge');
            if (trialBadge) trialBadge.textContent = (isTrial ? '3 months free trial' : '');

            const trialFeature = document.getElementById('trial_feature');
            if (trialFeature) trialFeature.style.display = (isTrial ? 'block' : 'none');

            const billingDesc = document.getElementById('billing_description');
            if (billingDesc) {
                billingDesc.textContent = isTrial
                    ? 'No charge today if you start the trial.'
                    : 'Pay annually for a 20% discount.';
            }

            const btn = document.getElementById('button_text_content');
            if (!btn) return;

            if (isTrial) {
                btn.textContent = 'Start Free Trial';
                return;
            }

            // non-trial: show pay amount
            const additionalCompanies = parseInt(input.value) || 1;
            const managerOn = moduleManager ? moduleManager.checked : false;

            const baseMonthly = additionalCompanies * pricePerCompany;
            const managerMonthly = managerOn ? fastManagerMonthly : 0;

            const monthlyTotal = baseMonthly + (isUpgrade ? 0 : managerMonthly);

            const discount = isYearly ? 0.2 : 0;
            const payAmount = isYearly
                ? Math.round(monthlyTotal * 12 * (1 - discount) * 100) / 100
                : monthlyTotal;

            btn.textContent = `Pay ${currency}${payAmount}`;
        }

        // ============================================
        // PRICING CALCULATIONS
        // ============================================
        function updatePricing() {
            const additionalCompanies = parseInt(input.value) || 1;
            const isYearly = yearlyRadio.checked;
            const discount = isYearly ? 0.2 : 0;

            const isTrial = (!isUpgrade && canUseTrial && freeTrialRadio && freeTrialRadio.checked);

            const managerOn = moduleManager ? moduleManager.checked : false;
            const managerMonthly = (!isUpgrade && managerOn) ? fastManagerMonthly : 0;

            if (isUpgrade) {
                const newTotal = currentCompanies + additionalCompanies;

                const additionalMonthlyCost = additionalCompanies * pricePerCompany; // upgrade charges only companies
                const monthlyPayment = additionalMonthlyCost;
                const yearlyPayment = Math.round(additionalMonthlyCost * 12 * (1 - discount) * 100) / 100;
                const paymentAmount = isYearly ? yearlyPayment : monthlyPayment;
                const discountAmt = isYearly ? Math.round(additionalMonthlyCost * 12 * discount * 100) / 100 : 0;

                document.getElementById('addCount').textContent = additionalCompanies;
                document.getElementById('addText').textContent = additionalCompanies === 1 ? 'company' : 'companies';
                document.getElementById('addPrice').textContent = additionalMonthlyCost;
                document.getElementById('summaryCompanies').textContent = newTotal + 'x';
                document.getElementById('paymentAmountValue').textContent = paymentAmount;

                document.getElementById('discountAmount').textContent = discountAmt > 0 ? `−${currency}${discountAmt}` : `−${currency}0`;
                document.getElementById('dueDescription').textContent = isYearly ? 'One year prepaid (Save 20%)' : 'Charged now';

            } else {
                const companiesMonthly = additionalCompanies * pricePerCompany;
                const totalMonthly = companiesMonthly + managerMonthly;

                let displayAmount = 0;
                let discountAmt = 0;
                let dueDesc = '';

                if (isTrial) {
                    displayAmount = 0;
                    discountAmt = 0;
                    dueDesc = 'Free trial - no charge';
                } else {
                    const monthlyPayment = totalMonthly;
                    const yearlyPayment = Math.round(totalMonthly * 12 * (1 - discount) * 100) / 100;
                    displayAmount = isYearly ? yearlyPayment : monthlyPayment;
                    discountAmt = isYearly ? Math.round(totalMonthly * 12 * discount * 100) / 100 : 0;
                    dueDesc = isYearly ? 'One year prepaid (Save 20%)' : 'Charged now';
                }

                document.getElementById('summaryCompanies').textContent = additionalCompanies + 'x';
                document.getElementById('companiesLine').textContent = companiesMonthly.toFixed(0);
                document.getElementById('managerLine').textContent = managerMonthly.toFixed(0);
                document.getElementById('paymentAmountValue').textContent = displayAmount;
                document.getElementById('discountAmount').textContent = discountAmt > 0 ? `−${currency}${discountAmt}` : `−${currency}0`;
                document.getElementById('dueDescription').textContent = dueDesc;

                const managerFeature = document.getElementById('manager_feature');
                if (managerFeature) managerFeature.style.display = managerOn ? 'list-item' : 'none';
            }

            // recurring text (keep original style)
            const perSeatMonthly = pricePerCompany;
            const perSeatYearly = Math.round(pricePerCompany * (1 - discount) * 100) / 100;
            document.getElementById('monthlyRecurring').textContent = `${currency}${perSeatMonthly} per company / month`;
            document.getElementById('yearlyRecurring').textContent = `${currency}${perSeatYearly} per company / month`;

            updateUI();
        }

        // listeners
        monthlyRadio.addEventListener('change', updatePricing);
        yearlyRadio.addEventListener('change', updatePricing);
        input.addEventListener('input', updatePricing);

        if (freeTrialRadio) freeTrialRadio.addEventListener('change', updatePricing);
        if (payNowRadio) payNowRadio.addEventListener('change', updatePricing);
        if (moduleManager) moduleManager.addEventListener('change', updatePricing);

        // ============================================
        // PAYMENT FORM SUBMISSION
        // ============================================
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        const errorMessage = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const agreeTerms = document.getElementById('agree_terms');
            if (!agreeTerms.checked) {
                showError('Please agree to the Terms & Conditions');
                return;
            }

            setLoading(true);

            const additionalCompanies = parseInt(input.value) || 1;
            const paymentFrequency = document.querySelector('input[name="payment_frequency"]:checked').value;
            const paymentOption = document.querySelector('input[name="payment_option"]:checked')?.value || 'paid';
            const isYearly = paymentFrequency === 'yearly';
            const discount = isYearly ? 0.2 : 0;

            const managerOn = moduleManager ? moduleManager.checked : false;
            const managerMonthly = (!isUpgrade && managerOn) ? fastManagerMonthly : 0;

            const totalCompanies = isUpgrade ? (currentCompanies + additionalCompanies) : additionalCompanies;

            // monthly amount for THIS checkout
            const monthlyBase = additionalCompanies * pricePerCompany;
            const monthlyTotal = isUpgrade ? monthlyBase : (monthlyBase + managerMonthly);

            // trial only for new subscriptions and allowed
            const isTrial = (!isUpgrade && canUseTrial && paymentOption === 'trial');

            // amount to charge NOW
            let chargeAmount = 0;
            if (!isTrial) {
                chargeAmount = isYearly
                    ? Math.round(monthlyTotal * 12 * (1 - discount) * 100) / 100
                    : monthlyTotal;
            }

            try {
                // STEP 1: Create Payment Method
                const { error: pmError, paymentMethod } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (pmError) throw new Error(pmError.message);

                // STEP 2: Create Payment Intent (also saves card for £0)
                const setupResponse = await fetch('{{ route("company.payment.create-intent") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        amount: chargeAmount,
                        number_of_companies: totalCompanies,
                        payment_method_id: paymentMethod.id,
                    }),
                });

                const setupData = await setupResponse.json();
                if (!setupData.success) throw new Error(setupData.error || 'Failed to setup payment');

                let paymentIntentId = null;

                // STEP 3: Confirm payment if charging now
                if (chargeAmount > 0 && setupData.clientSecret) {
                    const { error, paymentIntent } = await stripe.confirmCardPayment(setupData.clientSecret, {
                        payment_method: paymentMethod.id,
                    });

                    if (error) throw new Error(error.message);
                    if (paymentIntent.status !== 'succeeded') throw new Error('Payment failed');

                    paymentIntentId = paymentIntent.id;
                }

                // STEP 4: Save subscription
                const finalResponse = await fetch('{{ route("company.payment.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        payment_intent_id: paymentIntentId,
                        payment_method_id: paymentMethod.id,
                        number_of_companies: totalCompanies,
                        payment_frequency: paymentFrequency,
                        is_trial: isTrial,
                        modules: { fast_books: true, fast_manager: managerOn }, // safe extra payload
                    }),
                });

                const finalData = await finalResponse.json();

                if (finalData.success) {
                    window.location.href = finalData.redirect_url;
                } else {
                    throw new Error(finalData.message || 'Subscription setup failed');
                }

            } catch (err) {
                showError(err.message);
                setLoading(false);
            }
        });

        function setLoading(isLoading) {
            submitButton.disabled = isLoading;
            if (isLoading) {
                buttonText.classList.add('d-none');
                spinner.classList.remove('d-none');
            } else {
                buttonText.classList.remove('d-none');
                spinner.classList.add('d-none');
            }
        }

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => errorMessage.classList.add('d-none'), 5000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateUI();
            updatePricing();
        });
    </script>
@endsection