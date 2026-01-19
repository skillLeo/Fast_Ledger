@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.annual-submissions.index') }}">Annual Submissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $annualSubmission->tax_year }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Annual Submission - {{ $annualSubmission->tax_year }}</h1>
                <p class="text-muted mb-0">
                    {{ $annualSubmission->business?->trading_name ?? $annualSubmission->business_id }}
                </p>
            </div>
            <div>
                <span class="badge bg-{{ $annualSubmission->status_badge['class'] }} fs-6">
                    <i class="fas {{ $annualSubmission->status_badge['icon'] }} me-1"></i>
                    {{ $annualSubmission->status_badge['text'] }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Quarterly Summary (if available) -->
                @if($quarterlySummary && $quarterlySummary['period_count'] > 0)
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Quarterly Summary for {{ $annualSubmission->tax_year }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6 class="text-muted">Periods</h6>
                                    <h4>{{ $quarterlySummary['period_count'] }}</h4>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted">Total Income</h6>
                                    <h4 class="text-success">£{{ number_format($quarterlySummary['total_income'], 2) }}</h4>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted">Total Expenses</h6>
                                    <h4 class="text-danger">£{{ number_format($quarterlySummary['total_expenses'], 2) }}</h4>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted">Net Profit</h6>
                                    <h4 class="text-primary">£{{ number_format($quarterlySummary['net_profit'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Adjustments -->
                @if($annualSubmission->adjustments_json && !empty(array_filter($annualSubmission->adjustments_json)))
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-balance-scale me-2"></i>
                                Adjustments
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    @foreach($annualSubmission->adjustments_json as $key => $value)
                                        @if($value && !is_array($value))
                                            <tr>
                                                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                <td class="text-end fw-bold">£{{ number_format($value, 2) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Allowances -->
                @if($annualSubmission->allowances_json && !empty(array_filter($annualSubmission->allowances_json)))
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Allowances
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                $tradingAllowance = $annualSubmission->allowances_json['trading_income_allowance'] ?? null;
                                $structuredBuildingAllowance = $annualSubmission->allowances_json['structured_building_allowance'] ?? null;
                                $enhancedStructuredBuildingAllowance = $annualSubmission->allowances_json['enhanced_structured_building_allowance'] ?? null;
                            @endphp

                            @if($tradingAllowance)
                                <!-- Trading Allowance -->
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Trading Allowance Elected</strong>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Trading Income Allowance</td>
                                            <td class="text-end fw-bold text-success">£{{ number_format($tradingAllowance, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @else
                                <!-- Capital Allowances -->
                                <h6 class="text-muted mb-3">Capital Allowances</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm">
                                        @foreach($annualSubmission->allowances_json as $key => $value)
                                            @if($value && !is_array($value) && $key !== 'trading_income_allowance')
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-success">£{{ number_format($value, 2) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </table>
                                </div>

                                <!-- Structured Building Allowances -->
                                @if($structuredBuildingAllowance && is_array($structuredBuildingAllowance))
                                    <h6 class="text-muted mb-3 mt-4">
                                        <i class="fas fa-building me-2"></i>Structured Building Allowances
                                    </h6>
                                    @foreach($structuredBuildingAllowance as $index => $sba)
                                        <div class="card mb-2 border-start border-4 border-info">
                                            <div class="card-body py-2">
                                                <h6 class="mb-2">Building #{{ $index + 1 }}</h6>
                                                <div class="row small">
                                                    <div class="col-md-6">
                                                        <strong>Amount:</strong> £{{ number_format($sba['amount'] ?? 0, 2) }}
                                                    </div>
                                                    @if(isset($sba['first_year_qualifying_date']))
                                                        <div class="col-md-6">
                                                            <strong>Qualifying Date:</strong> {{ $sba['first_year_qualifying_date'] }}
                                                        </div>
                                                    @endif
                                                    @if(isset($sba['first_year_qualifying_amount']))
                                                        <div class="col-md-6">
                                                            <strong>Qualifying Amount:</strong> £{{ number_format($sba['first_year_qualifying_amount'], 2) }}
                                                        </div>
                                                    @endif
                                                    @if(isset($sba['building_name']))
                                                        <div class="col-md-6">
                                                            <strong>Building:</strong> {{ $sba['building_name'] }}
                                                        </div>
                                                    @endif
                                                    @if(isset($sba['building_postcode']))
                                                        <div class="col-md-6">
                                                            <strong>Postcode:</strong> {{ $sba['building_postcode'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- Enhanced Structured Building Allowances -->
                                @if($enhancedStructuredBuildingAllowance && is_array($enhancedStructuredBuildingAllowance))
                                    <h6 class="text-muted mb-3 mt-4">
                                        <i class="fas fa-building me-2"></i>Enhanced Structured Building Allowances
                                    </h6>
                                    @foreach($enhancedStructuredBuildingAllowance as $index => $esba)
                                        <div class="card mb-2 border-start border-4 border-success">
                                            <div class="card-body py-2">
                                                <h6 class="mb-2">Building #{{ $index + 1 }}</h6>
                                                <div class="row small">
                                                    <div class="col-md-6">
                                                        <strong>Amount:</strong> £{{ number_format($esba['amount'] ?? 0, 2) }}
                                                    </div>
                                                    @if(isset($esba['first_year_qualifying_date']))
                                                        <div class="col-md-6">
                                                            <strong>Qualifying Date:</strong> {{ $esba['first_year_qualifying_date'] }}
                                                        </div>
                                                    @endif
                                                    @if(isset($esba['first_year_qualifying_amount']))
                                                        <div class="col-md-6">
                                                            <strong>Qualifying Amount:</strong> £{{ number_format($esba['first_year_qualifying_amount'], 2) }}
                                                        </div>
                                                    @endif
                                                    @if(isset($esba['building_name']))
                                                        <div class="col-md-6">
                                                            <strong>Building:</strong> {{ $esba['building_name'] }}
                                                        </div>
                                                    @endif
                                                    @if(isset($esba['building_postcode']))
                                                        <div class="col-md-6">
                                                            <strong>Postcode:</strong> {{ $esba['building_postcode'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif

                            <!-- Total -->
                            <div class="table-responsive mt-3">
                                <table class="table table-sm">
                                    <tr class="table-light fw-bold">
                                        <td>Total Allowances</td>
                                        <td class="text-end text-success">£{{ number_format($annualSubmission->total_allowances, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Non-Financials -->
                @if($annualSubmission->non_financials_json && !empty(array_filter($annualSubmission->non_financials_json)))
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Business Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($annualSubmission->non_financials_json['business_address_line_1']))
                                <h6 class="text-muted mb-2">Business Address</h6>
                                <p class="mb-3">
                                    {{ $annualSubmission->non_financials_json['business_address_line_1'] ?? '' }}<br>
                                    @if(isset($annualSubmission->non_financials_json['business_address_line_2']))
                                        {{ $annualSubmission->non_financials_json['business_address_line_2'] }}<br>
                                    @endif
                                    @if(isset($annualSubmission->non_financials_json['business_address_line_3']))
                                        {{ $annualSubmission->non_financials_json['business_address_line_3'] }}<br>
                                    @endif
                                    @if(isset($annualSubmission->non_financials_json['business_address_postcode']))
                                        {{ $annualSubmission->non_financials_json['business_address_postcode'] }}
                                    @endif
                                </p>
                            @endif

                            @if(isset($annualSubmission->non_financials_json['class_4_nics_exemption_reason']))
                                <h6 class="text-muted mb-2">Class 4 NICs Exemption</h6>
                                <p class="mb-0">
                                    <span class="badge bg-info">
                                        Code: {{ $annualSubmission->non_financials_json['class_4_nics_exemption_reason'] }}
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if($annualSubmission->notes)
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $annualSubmission->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- HMRC Response -->
                @if($annualSubmission->response_json && $annualSubmission->status === 'submitted')
                    <div class="card shadow-sm border-success rounded-3 mb-4">
                        <div class="card-header bg-success bg-opacity-10">
                            <h5 class="card-title mb-0 text-success">
                                <i class="fas fa-check-circle me-2"></i>
                                HMRC Response
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code>{{ json_encode($annualSubmission->response_json, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif

                @if($annualSubmission->response_json && $annualSubmission->status === 'failed')
                    <div class="card shadow-sm border-danger rounded-3 mb-4">
                        <div class="card-header bg-danger bg-opacity-10">
                            <h5 class="card-title mb-0 text-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                Error Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code>{{ json_encode($annualSubmission->response_json, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="card shadow-sm border-primary rounded-3 mb-4">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-calculator me-2"></i>
                            Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-7">Tax Year:</dt>
                            <dd class="col-5 text-end">{{ $annualSubmission->tax_year }}</dd>

                            <dt class="col-7">Business:</dt>
                            <dd class="col-5 text-end text-wrap">
                                {{ $annualSubmission->business?->trading_name ?? $annualSubmission->business_id }}
                            </dd>

                            <dt class="col-7">Total Allowances:</dt>
                            <dd class="col-5 text-end text-success">
                                £{{ number_format($annualSubmission->total_allowances, 2) }}
                            </dd>

                            <dt class="col-7">Income Adjustments:</dt>
                            <dd class="col-5 text-end text-info">
                                £{{ number_format($annualSubmission->net_income_adjustment, 2) }}
                            </dd>

                            <dt class="col-7">Expense Adjustments:</dt>
                            <dd class="col-5 text-end text-warning">
                                £{{ number_format($annualSubmission->net_expense_adjustment, 2) }}
                            </dd>

                            <dt class="col-7 border-top pt-2">Status:</dt>
                            <dd class="col-5 text-end border-top pt-2">
                                <span class="badge bg-{{ $annualSubmission->status_badge['class'] }}">
                                    {{ $annualSubmission->status_badge['text'] }}
                                </span>
                            </dd>

                            @if($annualSubmission->submission_date)
                                <dt class="col-7">Submitted:</dt>
                                <dd class="col-5 text-end">
                                    {{ $annualSubmission->submission_date->format('d M Y H:i') }}
                                </dd>
                            @endif

                            <dt class="col-7">Created:</dt>
                            <dd class="col-5 text-end">{{ $annualSubmission->created_at->format('d M Y') }}</dd>

                            <dt class="col-7">Updated:</dt>
                            <dd class="col-5 text-end">{{ $annualSubmission->updated_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($annualSubmission->canEdit())
                                <a href="{{ route('hmrc.annual-submissions.edit', $annualSubmission) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i> Edit Draft
                                </a>
                            @endif

                            @if($annualSubmission->canSubmit())
                                <form action="{{ route('hmrc.annual-submissions.submit', $annualSubmission) }}" 
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to submit this to HMRC? This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane me-2"></i> Submit to HMRC
                                    </button>
                                </form>
                            @endif

                            @if($annualSubmission->canDelete())
                                <form action="{{ route('hmrc.annual-submissions.destroy', $annualSubmission) }}" 
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this draft?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i> Delete Draft
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('hmrc.annual-submissions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



