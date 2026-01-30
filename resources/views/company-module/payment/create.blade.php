{{-- resources/views/company-module/payment/create.blade.php --}}
@extends('admin.layout.app')

@section('content')
<div class="main-content app-content fl-checkout">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-10">

                {{-- Page Title --}}
                <div class="fl-pagehead">
                    <h3 class="fl-title">
                        @if($pricing['is_upgrade'])
                            Add More Companies
                        @else
                            Upgrade to PRO
                        @endif
                    </h3>

                    {{-- Progress Indicator (keep logic, just restyled) --}}
                    @if(!$pricing['is_upgrade'])
                        <div class="fl-steps">
                            <span class="fl-step fl-step--done">✓ Email Verified</span>
                            <span class="fl-step-line"></span>
                            <span class="fl-step fl-step--done">✓ Company Created</span>
                            <span class="fl-step-line"></span>
                            <span class="fl-step fl-step--active">Payment</span>
                        </div>
                    @endif
                </div>

                {{-- Test Mode --}}
                @if($isTestMode)
                    <div class="fl-alert fl-alert--warning mb-3">
                        <span class="fl-alert-dot"></span>
                        <div>
                            <strong>TEST MODE</strong> - Use test card: 4242 4242 4242 4242
                        </div>
                    </div>
                @endif

                {{-- Error Messages --}}
                <div id="error-message" class="fl-alert fl-alert--danger d-none mb-3" role="alert">
                    <strong>⚠️ Error:</strong>
                    <span id="error-text"></span>
                </div>

                @if ($errors->any())
                    <div class="fl-alert fl-alert--danger mb-3" role="alert">
                        <strong>⚠️ Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="payment-form">
                    @csrf

                    <div class="fl-surface">
                        <div class="row g-4">

                            {{-- LEFT COLUMN --}}
                            <div class="col-xl-7">

                                {{-- ✅ FREE TRIAL vs PAY NOW (Only for new subscriptions) --}}
                                @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                    <div class="fl-section">
                                        <div class="fl-section-title">Get started</div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_option" id="free_trial" value="trial" checked>
                                                <label class="fl-choicecard" for="free_trial">
                                                    <div class="fl-choicecard-top">
                                                        <span class="fl-pill fl-pill--green">recommended</span>
                                                        <div class="fl-choicecard-title">Start free trial</div>
                                                    </div>
                                                    <ul class="fl-choicecard-list">
                                                        <li><strong>3 months free</strong> (no charge today)</li>
                                                        <li><strong>then 6 months 90% off</strong></li>
                                                        <li>then full price</li>
                                                        <li>card required</li>
                                                    </ul>
                                                </label>
                                            </div>

                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_option" id="pay_now" value="paid">
                                                <label class="fl-choicecard" for="pay_now">
                                                    <div class="fl-choicecard-top">
                                                        <div class="fl-choicecard-title">Pay now</div>
                                                    </div>
                                                    <ul class="fl-choicecard-list">
                                                        <li>charged today</li>
                                                        <li>immediate full access</li>
                                                        <li>monthly or yearly</li>
                                                    </ul>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Plan Details --}}
                                <div class="fl-section">
                                    <div class="fl-section-title">Plan details</div>

                                    {{-- Number of Companies --}}
                                    <div class="fl-block">
                                        <label class="fl-label">
                                            @if($pricing['is_upgrade'])
                                                Number of companies to add
                                            @else
                                                Number of companies
                                            @endif
                                        </label>

                                        <div class="fl-helper">
                                            @if($pricing['is_upgrade'])
                                                select how many additional companies you need.
                                            @else
                                                select how many companies you need.
                                            @endif
                                        </div>

                                        <div class="fl-stepper">
                                            <button type="button" class="fl-stepper-btn" onclick="decrementCompanies()" aria-label="decrease">−</button>
                                            <input
                                                type="number"
                                                id="additional_companies"
                                                class="fl-stepper-input"
                                                value="1"
                                                min="1"
                                                max="50"
                                                readonly
                                            >
                                            <button type="button" class="fl-stepper-btn" onclick="incrementCompanies()" aria-label="increase">+</button>
                                        </div>
                                    </div>

                                    {{-- ✅ Modules (ONLY new subscriptions) --}}
                                    @if(!$pricing['is_upgrade'])
                                        <div class="fl-block">
                                            <label class="fl-label">Modules</label>

                                            <div class="fl-cardlist">
                                                <div class="fl-cardrow">
                                                    <div>
                                                        <div class="fl-cardrow-title">Fast Books</div>
                                                        <div class="fl-cardrow-sub">always included</div>
                                                    </div>
                                                    <span class="fl-pill fl-pill--green">included</span>
                                                </div>

                                                <div class="fl-cardrow fl-cardrow--border">
                                                    <div class="fl-cardrow-left">
                                                        <input class="form-check-input fl-check" type="checkbox" id="module_manager">
                                                        <label class="fl-checklabel" for="module_manager">
                                                            <div class="fl-cardrow-title">Fast Manager</div>
                                                            <div class="fl-cardrow-sub">+{{ $pricing['currency'] }}20 / month</div>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="fl-cardrow fl-cardrow--border fl-muted">
                                                    <div>
                                                        <div class="fl-cardrow-title">Fast Payroll</div>
                                                        <div class="fl-cardrow-sub">coming soon</div>
                                                    </div>
                                                    <span class="fl-pill fl-pill--gray">coming soon</span>
                                                </div>

                                                <div class="fl-cardrow fl-cardrow--border fl-muted">
                                                    <div>
                                                        <div class="fl-cardrow-title">Fast Taxation</div>
                                                        <div class="fl-cardrow-sub">coming soon</div>
                                                    </div>
                                                    <span class="fl-pill fl-pill--gray">coming soon</span>
                                                </div>
                                            </div>

                                            <div class="fl-helper mt-2">
                                                fast books is always included. select fast manager if needed.
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Billing Cycle --}}
                                    <div class="fl-block">
                                        <label class="fl-label">Billing cycle</label>
                                        <div class="fl-helper" id="billing_description">
                                            @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                                no charge today if you start the trial.
                                            @else
                                                pay annually for a 20% discount.
                                            @endif
                                        </div>

                                        <div class="row g-3 mt-1">
                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_frequency" id="monthly" value="monthly" checked>
                                                <label class="fl-option" for="monthly">
                                                    <span class="fl-radio"></span>
                                                    <span class="fl-option-text">
                                                        <span class="fl-option-title">Pay monthly</span>
                                                        <span class="fl-option-sub" id="monthlyRecurring">
                                                            {{ $pricing['currency'] }}{{ $pricing['price_per_company'] }} per company / month
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="col-md-6">
                                                <input class="btn-check" type="radio" name="payment_frequency" id="yearly" value="yearly">
                                                <label class="fl-option" for="yearly">
                                                    <span class="fl-radio"></span>
                                                    <span class="fl-option-text">
                                                        <span class="fl-option-topline">
                                                            <span class="fl-option-title">Pay annually</span>
                                                            <span class="fl-pill fl-pill--green">save 20%</span>
                                                        </span>
                                                        <span class="fl-option-sub" id="yearlyRecurring">
                                                            {{ $pricing['currency'] }}{{ round($pricing['price_per_company'] * 0.8, 2) }} per company / month
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Details --}}
                                <div class="fl-section">
                                    <div class="fl-section-title">Payment details</div>

                                    <div class="fl-block">
                                        <label class="fl-label">Card information</label>
                                        <div id="card-element" class="fl-card-element">
                                            <!-- Stripe card element -->
                                        </div>
                                        <div class="fl-helper mt-2">
                                            @if($isTestMode)
                                                test: 4242 4242 4242 4242, any future date, any cvc
                                            @else
                                                secured by stripe encryption
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Trial Notice --}}
                                    <div id="trial_notice" class="fl-alert fl-alert--info"
                                        style="display: @if(!$pricing['is_upgrade'] && $pricing['can_use_trial']) block @else none @endif;">
                                        <span class="fl-alert-dot fl-alert-dot--info"></span>
                                        <div>
                                            <strong>No charge today on trial</strong>
                                            <div class="fl-helper mt-1">
                                                3 months free → 6 months 90% off → full price
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Terms --}}
                                    <div class="fl-terms">
                                        <input class="form-check-input fl-check" type="checkbox" id="agree_terms" required>
                                        <label class="fl-terms-text" for="agree_terms">
                                            i agree to the <a href="#" class="fl-link">terms &amp; conditions</a> and
                                            <a href="#" class="fl-link">privacy policy</a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Summary --}}
                            <div class="col-xl-5">
                                <div class="fl-summary">
                                    <div class="fl-summary-head">Summary</div>

                                    {{-- Brand --}}
                                    <div class="fl-brand">
                                        <div class="fl-logo"></div>
                                        <div class="fl-brand-meta">
                                            <div class="fl-brand-name">FastLedger</div>
                                            <div class="fl-brand-sub">invoicing/stock management app</div>
                                            <div class="fl-brand-sub" id="summaryPlanLine">
                                                {{-- filled by js --}}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Breakdown --}}
                                    <div class="fl-breakdown">
                                        <div class="fl-row">
                                            <span class="fl-row-label fl-row-label--blue">Current subscription</span>
                                            <span class="fl-row-value" id="basePriceText">{{ $pricing['currency'] }}0</span>
                                        </div>

                                        <div class="fl-row fl-row--green" id="extraRow">
                                            <span class="fl-row-label">+ <span id="extraCount">1</span> <span id="extraText">company</span></span>
                                            <span class="fl-row-value">+{{ $pricing['currency'] }}<span id="extraPrice">0</span></span>
                                        </div>

                                        <div class="fl-row" id="managerRow" style="display:none;">
                                            <span class="fl-row-label">Fast Manager</span>
                                            <span class="fl-row-value">{{ $pricing['currency'] }}<span id="managerPrice">0</span></span>
                                        </div>

                                        <div class="fl-row fl-row--teal fl-row-divider-top">
                                            <span class="fl-row-label">Discount</span>
                                            <span class="fl-row-value" id="discountAmount">−{{ $pricing['currency'] }}0</span>
                                        </div>
                                    </div>

                                    {{-- Due today --}}
                                    <div class="fl-due">
                                        <div class="fl-due-left">
                                            <div class="fl-due-title">Due today</div>
                                            <div class="fl-due-sub" id="dueDescription">
                                                additional companies charge
                                            </div>
                                        </div>
                                        <div class="fl-due-right">
                                            <div class="fl-due-amount">{{ $pricing['currency'] }}<span id="paymentAmountValue">0</span></div>
                                        </div>
                                    </div>

                                    {{-- What's included --}}
                                    <div class="fl-includes">
                                        <div class="fl-includes-title">What’s included:</div>
                                        <ul class="fl-includes-list">
                                            <li id="trial_feature" style="display:none;">3 months free trial</li>
                                            <li>Unlimited invoices</li>
                                            <li>Unlimited customers</li>
                                            <li>Full feature access</li>
                                            <li>Cancel anytime</li>
                                            <li id="manager_feature" style="display:none;">Fast Manager module</li>
                                        </ul>
                                    </div>

                                    {{-- Button --}}
                                    <button type="submit" id="submit-button" class="fl-btn-primary">
                                        <span id="button-text">
                                            <span id="button_text_content">
                                                @if(!$pricing['is_upgrade'] && $pricing['can_use_trial'])
                                                    Start free trial
                                                @else
                                                    Continue
                                                @endif
                                            </span>
                                        </span>
                                        <span id="spinner" class="spinner-border spinner-border-sm d-none"></span>
                                    </button>

                                    <div class="fl-cancel">
                                        <a href="{{ route('company.select') }}" class="fl-cancel-link">Cancel</a>
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
/* =========================
   FASTLEDGER CHECKOUT UI
   (scoped + pixel-clean)
========================= */
.fl-checkout{
    --bg:#f6f7fb;
    --card:#ffffff;
    --ink:#0b1020;
    --muted:#6b7280;
    --stroke:#e5e7eb;
    --stroke-2:#d1d5db;
    --shadow: 0 1px 2px rgba(16,24,40,.06), 0 14px 30px rgba(16,24,40,.10);
    --radius:18px;
    --radius-sm:14px;
}
.fl-checkout .container-fluid{ max-width: 1180px; }
.fl-checkout { background: transparent; }
.fl-checkout .fl-pagehead{ margin-bottom: 14px; }
.fl-checkout .fl-title{
    font-size: 26px;
    font-weight: 700;
    color: var(--ink);
    margin: 0;
    letter-spacing: -0.02em;
}
.fl-checkout .fl-steps{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top: 10px;
    flex-wrap: wrap;
}
.fl-checkout .fl-step{
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
    border:1px solid var(--stroke);
    background:#fff;
    padding:6px 10px;
    border-radius: 999px;
}
.fl-checkout .fl-step--done{
    color:#065f46;
    border-color:#a7f3d0;
    background:#ecfdf5;
}
.fl-checkout .fl-step--active{
    color:#fff;
    border-color:#111827;
    background:#111827;
}
.fl-checkout .fl-step-line{
    width: 28px;
    height: 2px;
    background: var(--stroke);
}

/* alerts */
.fl-checkout .fl-alert{
    display:flex;
    gap:10px;
    align-items:flex-start;
    padding: 12px 14px;
    border-radius: 12px;
    border:1px solid var(--stroke);
    background:#fff;
    color: var(--ink);
}
.fl-checkout .fl-alert-dot{
    width:10px;height:10px;border-radius:999px;margin-top:4px;background:#f59e0b;
}
.fl-checkout .fl-alert--warning{ border-color:#fbbf24; background:#fffbeb; }
.fl-checkout .fl-alert--danger{ border-color:#fecaca; background:#fef2f2; }
.fl-checkout .fl-alert--info{ border-color:#bfdbfe; background:#eff6ff; }
.fl-checkout .fl-alert-dot--info{ background:#3b82f6; }

/* main surface */
.fl-checkout .fl-surface{
    background: var(--card);
    border:1px solid var(--stroke);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 18px;
}

/* sections */
.fl-checkout .fl-section{ margin-bottom: 18px; }
.fl-checkout .fl-section-title{
    font-size: 16px;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 10px;
}
.fl-checkout .fl-block{ padding: 12px 0; border-top:1px solid var(--stroke); }
.fl-checkout .fl-block:first-of-type{ border-top: none; padding-top: 0; }
.fl-checkout .fl-label{
    display:block;
    font-size: 13px;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 4px;
}
.fl-checkout .fl-helper{
    font-size: 12px;
    color: var(--muted);
    line-height: 1.4;
}

/* stepper (minus / value / plus) */
.fl-checkout .fl-stepper{
    display:flex;
    align-items:center;
    gap: 8px; /* reduced distance */
    margin-top: 10px;
}
.fl-checkout .fl-stepper-btn{
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border:1px solid var(--stroke-2);
    background:#fff;
    color: var(--ink); /* black */
    font-size: 20px;
    font-weight: 600;
    line-height: 1;
    display:flex;
    align-items:center;
    justify-content:center;
}
.fl-checkout .fl-stepper-btn:hover{ border-color:#111827; }
.fl-checkout .fl-stepper-input{
    width: 90px;
    height: 42px;
    border-radius: 12px;
    border:1px solid var(--stroke-2);
    background:#fff;
    color: var(--ink);
    font-size: 20px;
    font-weight: 700;
    text-align:center;
    outline: none;
}

/* option cards (billing) */
.fl-checkout .fl-option{
    display:flex;
    gap: 12px;
    align-items:flex-start;
    width:100%;
    padding: 14px 14px;
    border-radius: 14px;
    border:1px solid var(--stroke-2);
    background:#fff;
    cursor:pointer;
}
.fl-checkout .fl-option-text{ display:flex; flex-direction:column; gap:4px; width:100%; }
.fl-checkout .fl-option-title{
    font-size: 13px;
    font-weight: 700;
    color: var(--ink); /* black */
}
.fl-checkout .fl-option-sub{
    font-size: 12px;
    color: var(--muted);
}
.fl-checkout .fl-option-topline{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}

/* radio look */
.fl-checkout .fl-radio{
    width: 18px;
    height: 18px;
    border-radius: 999px;
    border:2px solid var(--stroke-2);
    background:#fff;
    position: relative;
    margin-top: 2px;
    flex: 0 0 auto;
}

/* IMPORTANT: selected = bold black border, no fill */
.fl-checkout .btn-check:checked + .fl-option{
    border:2px solid #111827 !important;
    background:#fff !important;
    box-shadow: none !important;
}
.fl-checkout .btn-check:checked + .fl-option .fl-radio{
    border-color:#111827;
}
.fl-checkout .btn-check:checked + .fl-option .fl-radio::after{
    content:"";
    position:absolute;
    inset: 4px;
    border-radius:999px;
    background:#111827;
}

/* choice cards (trial / pay now) */
.fl-checkout .fl-choicecard{
    display:block;
    width:100%;
    height:100%;
    padding: 14px 14px;
    border-radius: 14px;
    border:1px solid var(--stroke-2);
    background:#fff;
    cursor:pointer;
}
.fl-checkout .fl-choicecard-top{ display:flex; flex-direction:column; gap:8px; margin-bottom: 8px; }
.fl-checkout .fl-choicecard-title{
    font-size: 14px;
    font-weight: 800;
    color: var(--ink);
}
.fl-checkout .fl-choicecard-list{
    margin: 0;
    padding-left: 16px;
    font-size: 12px;
    color: var(--muted);
}
.fl-checkout .btn-check:checked + .fl-choicecard{
    border:2px solid #111827 !important;
    background:#fff !important;
}

/* pills */
.fl-checkout .fl-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 999px;
    border:1px solid var(--stroke);
    color: var(--ink);
    background:#fff;
    width: fit-content;
}
.fl-checkout .fl-pill--green{ border-color:#a7f3d0; background:#ecfdf5; color:#065f46; }
.fl-checkout .fl-pill--gray{ border-color: var(--stroke); background:#f3f4f6; color:#374151; }

/* card element */
.fl-checkout .fl-card-element{
    height: 50px;
    border-radius: 12px;
    border:1px solid var(--stroke-2);
    background:#fff;
    padding: 14px 14px;
}

/* modules list */
.fl-checkout .fl-cardlist{
    border:1px solid var(--stroke-2);
    border-radius: 14px;
    background:#fff;
    overflow:hidden;
}
.fl-checkout .fl-cardrow{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding: 12px 12px;
}
.fl-checkout .fl-cardrow--border{ border-top:1px solid var(--stroke); }
.fl-checkout .fl-cardrow-title{ font-size: 13px; font-weight: 700; color: var(--ink); }
.fl-checkout .fl-cardrow-sub{ font-size: 12px; color: var(--muted); }
.fl-checkout .fl-cardrow-left{ display:flex; gap:10px; align-items:flex-start; }
.fl-checkout .fl-check{ margin-top: 3px; }
.fl-checkout .fl-checklabel{ cursor:pointer; }

/* terms */
.fl-checkout .fl-terms{
    display:flex;
    gap:10px;
    align-items:flex-start;
    margin-top: 12px;
}
.fl-checkout .fl-terms-text{
    font-size: 12px;
    color: var(--muted);
}
.fl-checkout .fl-link{
    color:#111827;
    text-decoration: underline;
    text-underline-offset: 2px;
}

/* summary */
.fl-checkout .fl-summary{
    position: sticky;
    top: 18px;
    background:#fff;
    border:1px solid var(--stroke);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 18px;
}
.fl-checkout .fl-summary-head{
    font-size: 16px;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 14px;
}
.fl-checkout .fl-brand{
    display:flex;
    gap:12px;
    align-items:flex-start;
    padding-bottom: 14px;
    border-bottom:1px solid var(--stroke);
    margin-bottom: 14px;
}
.fl-checkout .fl-logo{
    width: 46px; height: 46px;
    border-radius: 12px;
    background:#0b1020; /* placeholder square like screenshot */
}
.fl-checkout .fl-brand-name{ font-size: 13px; font-weight: 800; color: var(--ink); }
.fl-checkout .fl-brand-sub{ font-size: 12px; color: var(--muted); }

.fl-checkout .fl-breakdown{ padding: 2px 0 10px; }
.fl-checkout .fl-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    font-size: 12px;
    padding: 6px 0;
}
.fl-checkout .fl-row-label{ color: var(--ink); }
.fl-checkout .fl-row-value{ color: var(--ink); font-weight: 700; }
.fl-checkout .fl-row--green .fl-row-label,
.fl-checkout .fl-row--green .fl-row-value{ color:#16a34a; }
.fl-checkout .fl-row-label--blue{ color:#2563eb; font-weight: 700; }
.fl-checkout .fl-row--teal .fl-row-label,
.fl-checkout .fl-row--teal .fl-row-value{ color:#06b6d4; }
.fl-checkout .fl-row-divider-top{ border-top:1px solid var(--stroke); margin-top: 6px; padding-top: 10px; }

.fl-checkout .fl-due{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    padding: 12px 0 14px;
    border-top:1px solid var(--stroke);
}
.fl-checkout .fl-due-title{ font-size: 12px; font-weight: 800; color: var(--ink); }
.fl-checkout .fl-due-sub{ font-size: 11px; color: var(--muted); }
.fl-checkout .fl-due-amount{
    font-size: 28px;
    font-weight: 900;
    color: var(--ink);
    letter-spacing: -0.02em;
}

.fl-checkout .fl-includes{
    background:#f3f4f6;
    border-radius: 14px;
    padding: 12px 12px;
}
.fl-checkout .fl-includes-title{
    font-size: 12px;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 8px;
}
.fl-checkout .fl-includes-list{
    margin: 0;
    padding-left: 18px;
    color: var(--muted);
    font-size: 12px;
}
.fl-checkout .fl-btn-primary{
    width:100%;
    margin-top: 14px;
    height: 46px;
    border-radius: 14px;
    border:1px solid #111827;
    background:#111827;
    color:#fff;
    font-weight: 800;
    font-size: 13px;
}
.fl-checkout .fl-btn-primary:hover{ filter: brightness(.95); }
.fl-checkout .fl-cancel{ text-align:center; margin-top: 10px; }
.fl-checkout .fl-cancel-link{ font-size: 12px; color: var(--muted); text-decoration:none; }
.fl-checkout .fl-cancel-link:hover{ text-decoration: underline; }

/* make bootstrap .text-muted not turn important labels gray inside our scope */
.fl-checkout .text-success, .fl-checkout .text-primary { color: inherit !important; }
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
                color: '#0b1020',
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                '::placeholder': { color: '#9ca3af' },
            },
            invalid: { color: '#ef4444', iconColor: '#ef4444' },
        },
    });

    cardElement.mount('#card-element');

    // ============================================
    // CONFIG / ELEMENTS
    // ============================================
    const currency = '{{ $pricing["currency"] }}';
    const pricePerCompany = {{ $pricing['price_per_company'] }};
    const fastManagerMonthly = 20;

    const currentCompanies = {{ $pricing['current_companies'] ?? 0 }};
    const isUpgrade = {{ $pricing['is_upgrade'] ? 'true' : 'false' }};
    const canUseTrial = {{ $pricing['can_use_trial'] ? 'true' : 'false' }};

    const input = document.getElementById('additional_companies');
    const monthlyRadio = document.getElementById('monthly');
    const yearlyRadio = document.getElementById('yearly');
    const freeTrialRadio = document.getElementById('free_trial');
    const payNowRadio = document.getElementById('pay_now');

    const moduleManager = document.getElementById('module_manager'); // may be null in upgrade

    // summary elements
    const basePriceText = document.getElementById('basePriceText');
    const extraRow = document.getElementById('extraRow');
    const extraCountEl = document.getElementById('extraCount');
    const extraTextEl = document.getElementById('extraText');
    const extraPriceEl = document.getElementById('extraPrice');

    const managerRow = document.getElementById('managerRow');
    const managerPriceEl = document.getElementById('managerPrice');

    const discountAmountEl = document.getElementById('discountAmount');
    const dueDescEl = document.getElementById('dueDescription');
    const dueAmountEl = document.getElementById('paymentAmountValue');
    const summaryPlanLine = document.getElementById('summaryPlanLine');

    const trialNotice = document.getElementById('trial_notice');
    const trialFeature = document.getElementById('trial_feature');
    const managerFeature = document.getElementById('manager_feature');
    const billingDesc = document.getElementById('billing_description');
    const btnText = document.getElementById('button_text_content');

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
    // PRICING CALCULATIONS (UI ONLY)
    // - DO NOT TOUCH BACKEND FLOW
    // ============================================
    function updatePricing() {
        const selectedCompanies = parseInt(input.value) || 1;
        const isYearly = yearlyRadio.checked;
        const annualDiscountRate = 0.20;

        const isTrial = (!isUpgrade && canUseTrial && freeTrialRadio && freeTrialRadio.checked);
        const managerOn = (!isUpgrade && moduleManager) ? moduleManager.checked : false;
        const managerMonthly = managerOn ? fastManagerMonthly : 0;

        // base vs extra (to match screenshot logic)
        const baseCount = isUpgrade ? currentCompanies : 1;
        const extraCount = isUpgrade ? selectedCompanies : Math.max(selectedCompanies - 1, 0);

        const baseMonthlyCost = baseCount * pricePerCompany;
        const extraMonthlyCost = extraCount * pricePerCompany;

        // totals (what would be charged today if not trial)
        const checkoutMonthlyTotal = isUpgrade
            ? extraMonthlyCost
            : (selectedCompanies * pricePerCompany) + managerMonthly;

        const wouldPayToday = isYearly
            ? round2(checkoutMonthlyTotal * 12 * (1 - annualDiscountRate))
            : round2(checkoutMonthlyTotal);

        // discount display rules
        let discountDisplay = 0;
        let dueToday = 0;

        if (isTrial) {
            // free trial: discount equals full payable amount; due today = 0
            discountDisplay = wouldPayToday;
            dueToday = 0;
        } else {
            // normal: yearly shows 20% discount amount; monthly shows 0
            discountDisplay = isYearly ? round2(checkoutMonthlyTotal * 12 * annualDiscountRate) : 0;
            dueToday = wouldPayToday;
        }

        // ---- SUMMARY UI
        // plan line under brand
        const totalCompaniesShown = isUpgrade ? (currentCompanies + selectedCompanies) : selectedCompanies;
        summaryPlanLine.textContent = `${totalCompaniesShown}x pro license`;

        // base row (always monthly display like screenshot)
        basePriceText.textContent = `${currency}${baseMonthlyCost}`;

        // extra row
        if (!isUpgrade && extraCount === 0) {
            // for new subs with only 1 company, hide "+ company" row
            extraRow.style.display = 'none';
        } else {
            extraRow.style.display = 'flex';
            extraCountEl.textContent = extraCount;
            extraTextEl.textContent = (extraCount === 1 ? 'company' : 'companies');
            extraPriceEl.textContent = extraMonthlyCost;
        }

        // manager row only when selected
        if (managerRow) {
            managerRow.style.display = managerOn ? 'flex' : 'none';
            if (managerPriceEl) managerPriceEl.textContent = managerMonthly.toFixed(0);
        }
        if (managerFeature) managerFeature.style.display = managerOn ? 'list-item' : 'none';

        // discount row
        discountAmountEl.textContent = discountDisplay > 0
            ? `−${currency}${formatMoney(discountDisplay)}`
            : `−${currency}0`;

        // due today
        dueAmountEl.textContent = formatMoney(dueToday);

        // due description + trial UI bits
        if (dueDescEl) {
            if (isTrial) {
                dueDescEl.textContent = 'free trial - no charge';
            } else if (isYearly) {
                dueDescEl.textContent = 'one year prepaid (save 20%)';
            } else {
                dueDescEl.textContent = 'additional companies charge';
            }
        }

        if (trialNotice) trialNotice.style.display = (isTrial ? 'flex' : 'none');
        if (trialFeature) trialFeature.style.display = (isTrial ? 'list-item' : 'none');
        if (billingDesc) billingDesc.textContent = isTrial
            ? 'no charge today if you start the trial.'
            : 'pay annually for a 20% discount.';

        if (btnText) {
            if (isTrial) btnText.textContent = 'Start free trial';
            else btnText.textContent = 'Continue';
        }

        // recurring helper text
        const perSeatMonthly = pricePerCompany;
        const perSeatYearly = round2(pricePerCompany * (1 - annualDiscountRate));
        const monthlyRecurring = document.getElementById('monthlyRecurring');
        const yearlyRecurring = document.getElementById('yearlyRecurring');
        if (monthlyRecurring) monthlyRecurring.textContent = `${currency}${perSeatMonthly} per company / month`;
        if (yearlyRecurring) yearlyRecurring.textContent = `${currency}${perSeatYearly} per company / month`;
    }

    function round2(n){ return Math.round(n * 100) / 100; }
    function formatMoney(n){
        // keep clean like screenshot: 10, 20, 96 (no trailing .00)
        const v = round2(Number(n || 0));
        return (Number.isInteger(v) ? String(v) : v.toFixed(2));
    }

    // listeners
    monthlyRadio.addEventListener('change', updatePricing);
    yearlyRadio.addEventListener('change', updatePricing);
    input.addEventListener('input', updatePricing);

    if (freeTrialRadio) freeTrialRadio.addEventListener('change', updatePricing);
    if (payNowRadio) payNowRadio.addEventListener('change', updatePricing);
    if (moduleManager) moduleManager.addEventListener('change', updatePricing);

    // ============================================
    // PAYMENT FORM SUBMISSION (UNCHANGED FLOW)
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
        updatePricing();
    });
</script>
@endsection
