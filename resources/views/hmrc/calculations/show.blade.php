@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.calculations.index') }}">Tax Calculations</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $calculation->tax_year }}</li>
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
            <div class="col-lg-9">
                <!-- Header Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calculator me-2"></i>
                                Tax Calculation: {{ $calculation->tax_year }}
                            </h5>
                            <small class="text-muted">{{ $calculation->type_label }}</small>
                        </div>
                        <span class="badge bg-{{ $calculation->status_badge['class'] }} px-3 py-2">
                            <i class="fas {{ $calculation->status_badge['icon'] }} me-1"></i>
                            {{ $calculation->status_badge['text'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-muted">NINO</label>
                                <p class="fw-bold">{{ $calculation->nino }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted">Tax Year</label>
                                <p class="fw-bold">{{ $calculation->tax_year }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Calculation Date</label>
                                <p class="fw-bold">
                                    @if($calculation->calculation_timestamp)
                                        {{ $calculation->calculation_timestamp->format('d M Y, H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $calculation->calculation_timestamp->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Processing...</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Income Received</h6>
                                <h3 class="mb-0 text-primary">
                                    £{{ number_format($calculation->total_income_received ?? 0, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Taxable Income</h6>
                                <h3 class="mb-0 text-info">
                                    £{{ number_format($calculation->total_taxable_income ?? 0, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tax & NICs Due</h6>
                                <h3 class="mb-0 text-danger">
                                    £{{ number_format($calculation->income_tax_and_nics_due ?? 0, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages/Warnings -->
                @if($calculation->hasErrors() || $calculation->hasWarnings())
                <div class="alert {{ $calculation->hasErrors() ? 'alert-danger' : 'alert-warning' }} mb-4">
                    <h6 class="alert-heading">
                        <i class="fas {{ $calculation->hasErrors() ? 'fa-exclamation-circle' : 'fa-exclamation-triangle' }} me-2"></i>
                        HMRC {{ $calculation->hasErrors() ? 'Errors' : 'Warnings' }}
                    </h6>
                    @foreach($calculation->getHmrcMessages() as $message)
                        <div class="mb-1">
                            <strong>{{ $message['id'] ?? 'Message' }}:</strong> {{ $message['text'] ?? $message['message'] ?? 'No details available' }}
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Tabbed Content -->
                @if($calculation->status === 'completed')
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-4" id="calculationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" 
                                        data-bs-target="#summary-content" type="button" role="tab">
                                    <i class="fas fa-chart-pie me-1"></i> Summary
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="income-tab" data-bs-toggle="tab" 
                                        data-bs-target="#income-content" type="button" role="tab">
                                    <i class="fas fa-pound-sign me-1"></i> Income
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="allowances-tab" data-bs-toggle="tab" 
                                        data-bs-target="#allowances-content" type="button" role="tab">
                                    <i class="fas fa-gift me-1"></i> Allowances
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tax-tab" data-bs-toggle="tab" 
                                        data-bs-target="#tax-content" type="button" role="tab">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Tax Calculation
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="nics-tab" data-bs-toggle="tab" 
                                        data-bs-target="#nics-content" type="button" role="tab">
                                    <i class="fas fa-id-card me-1"></i> National Insurance
                                </button>
                            </li>
                            @if(!empty($breakdown['messages']))
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="messages-tab" data-bs-toggle="tab" 
                                        data-bs-target="#messages-content" type="button" role="tab">
                                    <i class="fas fa-envelope me-1"></i> Messages
                                    <span class="badge bg-warning">{{ count($breakdown['messages']) }}</span>
                                </button>
                            </li>
                            @endif
                        </ul>

                        <div class="tab-content" id="calculationTabContent">
                            <!-- Summary Tab -->
                            <div class="tab-pane fade show active" id="summary-content" role="tabpanel">
                                @include('hmrc.calculations.partials.summary-tab', ['breakdown' => $breakdown])
                            </div>

                            <!-- Income Tab -->
                            <div class="tab-pane fade" id="income-content" role="tabpanel">
                                @include('hmrc.calculations.partials.income-tab', ['breakdown' => $breakdown])
                            </div>

                            <!-- Allowances Tab -->
                            <div class="tab-pane fade" id="allowances-content" role="tabpanel">
                                @include('hmrc.calculations.partials.allowances-tab', ['breakdown' => $breakdown])
                            </div>

                            <!-- Tax Calculation Tab -->
                            <div class="tab-pane fade" id="tax-content" role="tabpanel">
                                @include('hmrc.calculations.partials.tax-tab', ['breakdown' => $breakdown])
                            </div>

                            <!-- National Insurance Tab -->
                            <div class="tab-pane fade" id="nics-content" role="tabpanel">
                                @include('hmrc.calculations.partials.nics-tab', ['breakdown' => $breakdown])
                            </div>

                            <!-- Messages Tab -->
                            @if(!empty($breakdown['messages']))
                            <div class="tab-pane fade" id="messages-content" role="tabpanel">
                                @include('hmrc.calculations.partials.messages-tab', ['breakdown' => $breakdown])
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3">
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
                            @if($calculation->status === 'processing')
                                <form action="{{ route('hmrc.calculations.refresh', $calculation) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-sync me-2"></i>
                                        Refresh Status
                                    </button>
                                </form>
                            @endif

                            @if($calculation->canRetrigger())
                                <a href="{{ route('hmrc.calculations.create') }}" class="btn btn-success">
                                    <i class="fas fa-redo me-2"></i>
                                    New Calculation
                                </a>
                            @endif

                            @if($calculation->status !== 'completed' || !$calculation->isCrystallisation())
                                <form action="{{ route('hmrc.calculations.destroy', $calculation) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this calculation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>
                                        Delete
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('hmrc.calculations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Calculation Info Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Calculation Info
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Calculation ID</dt>
                            <dd class="col-sm-6">
                                <small><code>{{ Str::limit($calculation->calculation_id, 20) }}</code></small>
                            </dd>

                            <dt class="col-sm-6">Type</dt>
                            <dd class="col-sm-6">
                                <span class="badge {{ $calculation->isCrystallisation() ? 'bg-primary' : 'bg-info' }}">
                                    {{ $calculation->type_label }}
                                </span>
                            </dd>

                            @if($calculation->total_allowances_and_deductions)
                            <dt class="col-sm-6">Allowances</dt>
                            <dd class="col-sm-6">
                                £{{ number_format($calculation->total_allowances_and_deductions, 2) }}
                            </dd>
                            @endif

                            <dt class="col-sm-6">Created</dt>
                            <dd class="col-sm-6">
                                {{ $calculation->created_at->format('d M Y') }}
                            </dd>

                            <dt class="col-sm-6">Last Updated</dt>
                            <dd class="col-sm-6">
                                {{ $calculation->updated_at->diffForHumans() }}
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Related Actions -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-link me-2"></i>
                            Related
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('hmrc.submissions.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-invoice me-1"></i> View Submissions
                            </a>
                            <a href="{{ route('hmrc.annual-submissions.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-alt me-1"></i> Annual Submissions
                            </a>
                            <a href="{{ route('hmrc.obligations.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar-check me-1"></i> Obligations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

