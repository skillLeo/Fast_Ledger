@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.submissions.index') }}">Submissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $submission->period_label }}</li>
            </ol>
        </nav>

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
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Header Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            Submission Details
                        </h5>
                        <span class="badge bg-{{ $submission->status_badge['class'] }} px-3 py-2">
                            <i class="fas {{ $submission->status_badge['icon'] }} me-1"></i>
                            {{ $submission->status_badge['text'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Business</label>
                                <p class="fw-bold mb-0">
                                    {{ $submission->business?->trading_name ?? $submission->business_id }}
                                </p>
                                @if($submission->business)
                                    <small class="text-muted">{{ $submission->business->type_of_business }}</small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted">Tax Year</label>
                                <p class="fw-bold">{{ $submission->tax_year }}</p>
                            </div>

                            <div class="col-12"><hr></div>

                            <div class="col-md-4">
                                <label class="form-label text-muted">Period Start</label>
                                <p class="fw-bold">{{ $submission->period_start_date->format('d M Y') }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-muted">Period End</label>
                                <p class="fw-bold">{{ $submission->period_end_date->format('d M Y') }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-muted">Duration</label>
                                <p class="fw-bold">
                                    {{ $submission->period_start_date->diffInDays($submission->period_end_date) }} days
                                </p>
                            </div>

                            @if($submission->obligation)
                            <div class="col-12">
                                <label class="form-label text-muted">Linked Obligation</label>
                                <p class="mb-0">
                                    <a href="{{ route('hmrc.obligations.show', $submission->obligation) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-link me-1"></i> View Obligation
                                    </a>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Income Card -->
                <div class="card shadow-sm border-success rounded-3 mb-4">
                    <div class="card-header bg-success bg-opacity-10">
                        <h5 class="card-title mb-0 text-success">
                            <i class="fas fa-pound-sign me-2"></i>
                            Income
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($submission->income_json)
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    @if(isset($submission->income_json['turnover']))
                                    <tr>
                                        <td class="ps-0">Turnover</td>
                                        <td class="text-end pe-0 fw-bold text-success">
                                            £{{ number_format($submission->income_json['turnover'], 2) }}
                                        </td>
                                    </tr>
                                    @endif

                                    @if(isset($submission->income_json['other']))
                                    <tr>
                                        <td class="ps-0">Other Income</td>
                                        <td class="text-end pe-0 fw-bold text-success">
                                            £{{ number_format($submission->income_json['other'], 2) }}
                                        </td>
                                    </tr>
                                    @endif

                                    <tr class="border-top">
                                        <td class="ps-0 fw-bold">Total Income</td>
                                        <td class="text-end pe-0 fw-bold text-success fs-5">
                                            £{{ number_format($submission->total_income, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No income data recorded</p>
                        @endif
                    </div>
                </div>

                <!-- Expenses Card -->
                <div class="card shadow-sm border-danger rounded-3 mb-4">
                    <div class="card-header bg-danger bg-opacity-10">
                        <h5 class="card-title mb-0 text-danger">
                            <i class="fas fa-receipt me-2"></i>
                            Expenses
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($submission->expenses_json)
                            @if(isset($submission->expenses_json['consolidated_expenses']))
                                <!-- Consolidated Expenses -->
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="ps-0">Consolidated Expenses</td>
                                            <td class="text-end pe-0 fw-bold text-danger fs-5">
                                                £{{ number_format($submission->expenses_json['consolidated_expenses'], 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @elseif(isset($submission->expenses_json['breakdown']))
                                <!-- Breakdown Expenses -->
                                <div class="table-responsive">
                                    <table class="table table-sm mb-3">
                                        @php
                                            $expenseLabels = [
                                                'cost_of_goods' => 'Cost of Goods',
                                                'staff_costs' => 'Staff Costs',
                                                'travel_costs' => 'Travel Costs',
                                                'premises_running_costs' => 'Premises Running Costs',
                                                'maintenance_costs' => 'Maintenance Costs',
                                                'admin_costs' => 'Admin Costs',
                                                'business_entertainment_costs' => 'Business Entertainment',
                                                'advertising_costs' => 'Advertising Costs',
                                                'interest_on_bank_other_loans' => 'Interest on Loans',
                                                'financial_charges' => 'Financial Charges',
                                                'bad_debt' => 'Bad Debt',
                                                'professional_fees' => 'Professional Fees',
                                                'depreciation' => 'Depreciation',
                                                'other_expenses' => 'Other Expenses',
                                            ];
                                        @endphp

                                        @foreach($submission->expenses_json['breakdown'] as $key => $value)
                                            @if($value && $value != 0)
                                            <tr>
                                                <td class="ps-0">{{ $expenseLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                <td class="text-end pe-0 fw-bold text-danger">
                                                    £{{ number_format($value, 2) }}
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </table>
                                </div>
                                <div class="border-top pt-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total Expenses</span>
                                        <span class="fw-bold text-danger fs-5">
                                            £{{ number_format($submission->total_expenses, 2) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">No expense data recorded</p>
                        @endif
                    </div>
                </div>

                <!-- Net Profit/Loss Card -->
                <div class="card shadow-sm border-primary rounded-3 mb-4">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Net Profit/Loss</h6>
                        <h2 class="mb-0 {{ $submission->net_profit >= 0 ? 'text-primary' : 'text-danger' }}">
                            £{{ number_format($submission->net_profit, 2) }}
                        </h2>
                    </div>
                </div>

                <!-- Notes Card -->
                @if($submission->notes)
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $submission->notes }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i>
                            Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($submission->canSubmit())
                                <form action="{{ route('hmrc.submissions.submit', $submission) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to submit this to HMRC? This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Submit to HMRC
                                    </button>
                                </form>
                            @endif

                            @if($submission->canEdit())
                                <a href="{{ route('hmrc.submissions.edit', $submission) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Submission
                                </a>
                            @endif

                            @if($submission->status === 'draft')
                                <form action="{{ route('hmrc.submissions.destroy', $submission) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this draft?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>
                                        Delete Draft
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('hmrc.submissions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Submission Info Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Submission Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Status</dt>
                            <dd class="col-sm-6">
                                <span class="badge bg-{{ $submission->status_badge['class'] }}">
                                    {{ $submission->status_badge['text'] }}
                                </span>
                            </dd>

                            @if($submission->period_id)
                            <dt class="col-sm-6">Period ID</dt>
                            <dd class="col-sm-6"><code>{{ $submission->period_id }}</code></dd>
                            @endif

                            @if($submission->submission_date)
                            <dt class="col-sm-6">Submitted</dt>
                            <dd class="col-sm-6">
                                {{ $submission->submission_date->format('d M Y, H:i') }}
                                <br>
                                <small class="text-muted">{{ $submission->submission_date->diffForHumans() }}</small>
                            </dd>
                            @endif

                            <dt class="col-sm-6">Created</dt>
                            <dd class="col-sm-6">
                                {{ $submission->created_at->format('d M Y') }}
                            </dd>

                            <dt class="col-sm-6">Last Updated</dt>
                            <dd class="col-sm-6">
                                {{ $submission->updated_at->diffForHumans() }}
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- HMRC Response Card -->
                @if($submission->response_json && $submission->status !== 'draft')
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-server me-2"></i>
                            HMRC Response
                        </h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 border rounded" style="max-height: 300px; overflow-y: auto; font-size: 12px;">{{ json_encode($submission->response_json, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

