@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-period-summaries.index') }}">UK Property Period Summaries</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $summary->from_date->format('d M Y') }} - {{ $summary->to_date->format('d M Y') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">UK Property Period Summary</h1>
                <p class="text-muted mb-0">{{ $summary->business?->trading_name ?? $summary->business_id }}</p>
            </div>
            <div>
                <span class="badge bg-{{ $summary->status_badge['class'] }} fs-6">
                    <i class="fas {{ $summary->status_badge['icon'] }} me-1"></i>
                    {{ $summary->status_badge['text'] }}
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
                <!-- Period Details Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Period Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Tax Year</h6>
                                <p class="mb-0 fw-bold fs-5">{{ $summary->tax_year }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Period</h6>
                                <p class="mb-0 fw-bold fs-5">
                                    {{ $summary->from_date->format('d M Y') }} to {{ $summary->to_date->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">({{ $summary->from_date->diffInDays($summary->to_date) + 1 }} days)</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unified UK Property Data (2025-26+) -->
                @if($summary->hasUnifiedPropertyData())
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-home me-2"></i>
                                UK Property (Combined FHL & Non-FHL)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Tax Year 2025-26+: FHL and Non-FHL properties are reported together
                            </div>

                            @if($summary->uk_property_income_json)
                                <h6 class="text-muted mb-3">Income</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm">
                                        @foreach($summary->uk_property_income_json as $key => $value)
                                            @if($value && !is_array($value))
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-success">£{{ number_format($value, 2) }}</td>
                                                </tr>
                                            @elseif(is_array($value))
                                                @foreach($value as $subKey => $subValue)
                                                    @if($subValue)
                                                        <tr>
                                                            <td>{{ ucwords(str_replace('_', ' ', $key . ' - ' . $subKey)) }}</td>
                                                            <td class="text-end fw-bold text-success">£{{ number_format($subValue, 2) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total UK Property Income</td>
                                            <td class="text-end text-success">£{{ number_format($summary->total_uk_property_income, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            @if($summary->uk_property_expenses_json)
                                <h6 class="text-muted mb-3">Expenses</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        @foreach($summary->uk_property_expenses_json as $key => $value)
                                            @if($value && !is_array($value))
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-danger">£{{ number_format(abs($value), 2) }}</td>
                                                </tr>
                                            @elseif(is_array($value))
                                                @foreach($value as $subKey => $subValue)
                                                    @if($subValue)
                                                        <tr>
                                                            <td>{{ ucwords(str_replace('_', ' ', $key . ' - ' . $subKey)) }}</td>
                                                            <td class="text-end fw-bold text-danger">£{{ number_format(abs($subValue), 2) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total UK Property Expenses</td>
                                            <td class="text-end text-danger">£{{ number_format($summary->total_uk_property_expenses, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- FHL Property Data (<=2024-25) -->
                @if($summary->hasFhlData())
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-umbrella-beach me-2"></i>
                                Furnished Holiday Lettings (FHL)
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($summary->fhl_income_json)
                                <h6 class="text-muted mb-3">Income</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm">
                                        @foreach($summary->fhl_income_json as $key => $value)
                                            @if($value && !is_array($value))
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-success">£{{ number_format($value, 2) }}</td>
                                                </tr>
                                            @elseif(is_array($value))
                                                @foreach($value as $subKey => $subValue)
                                                    @if($subValue)
                                                        <tr>
                                                            <td>{{ ucwords(str_replace('_', ' ', $key . ' - ' . $subKey)) }}</td>
                                                            <td class="text-end fw-bold text-success">£{{ number_format($subValue, 2) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total FHL Income</td>
                                            <td class="text-end text-success">£{{ number_format($summary->total_fhl_income, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            @if($summary->fhl_expenses_json)
                                <h6 class="text-muted mb-3">Expenses</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        @foreach($summary->fhl_expenses_json as $key => $value)
                                            @if($value && !is_array($value))
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-danger">£{{ number_format($value, 2) }}</td>
                                                </tr>
                                            @elseif(is_array($value))
                                                @foreach($value as $subKey => $subValue)
                                                    @if($subValue)
                                                        <tr>
                                                            <td>{{ ucwords(str_replace('_', ' ', $key . ' - ' . $subKey)) }}</td>
                                                            <td class="text-end fw-bold text-danger">£{{ number_format($subValue, 2) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total FHL Expenses</td>
                                            <td class="text-end text-danger">£{{ number_format($summary->total_fhl_expenses, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Non-FHL Property Data -->
                @if($summary->hasNonFhlData())
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-building me-2"></i>
                                Non-FHL Property
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($summary->non_fhl_income_json)
                                <h6 class="text-muted mb-3">Income</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm">
                                        @foreach($summary->non_fhl_income_json as $key => $value)
                                            @if($value && !is_array($value))
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-success">£{{ number_format($value, 2) }}</td>
                                                </tr>
                                            @elseif(is_array($value))
                                                @foreach($value as $subKey => $subValue)
                                                    @if($subValue)
                                                        <tr>
                                                            <td>{{ ucwords(str_replace('_', ' ', $key . ' - ' . $subKey)) }}</td>
                                                            <td class="text-end fw-bold text-success">£{{ number_format($subValue, 2) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total Non-FHL Income</td>
                                            <td class="text-end text-success">£{{ number_format($summary->total_non_fhl_income, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            @if($summary->non_fhl_expenses_json)
                                <h6 class="text-muted mb-3">Expenses</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        @foreach($summary->non_fhl_expenses_json as $key => $value)
                                            @if($value && !is_array($value))
                                                <tr>
                                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="text-end fw-bold text-danger">£{{ number_format($value, 2) }}</td>
                                                </tr>
                                            @elseif(is_array($value))
                                                @foreach($value as $subKey => $subValue)
                                                    @if($subValue)
                                                        <tr>
                                                            <td>{{ ucwords(str_replace('_', ' ', $key . ' - ' . $subKey)) }}</td>
                                                            <td class="text-end fw-bold text-danger">£{{ number_format($subValue, 2) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total Non-FHL Expenses</td>
                                            <td class="text-end text-danger">£{{ number_format($summary->total_non_fhl_expenses, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if($summary->notes)
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $summary->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- HMRC Response -->
                @if($summary->response_json && $summary->status === 'submitted')
                    <div class="card shadow-sm border-success rounded-3 mb-4">
                        <div class="card-header bg-success bg-opacity-10">
                            <h5 class="card-title mb-0 text-success">
                                <i class="fas fa-check-circle me-2"></i>
                                HMRC Response
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code>{{ json_encode($summary->response_json, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif

                @if($summary->response_json && $summary->status === 'failed')
                    <div class="card shadow-sm border-danger rounded-3 mb-4">
                        <div class="card-header bg-danger bg-opacity-10">
                            <h5 class="card-title mb-0 text-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                Error Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code>{{ json_encode($summary->response_json, JSON_PRETTY_PRINT) }}</code></pre>
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
                            <dd class="col-5 text-end">{{ $summary->tax_year }}</dd>

                            <dt class="col-7">Period:</dt>
                            <dd class="col-5 text-end">
                                {{ $summary->from_date->format('d M') }} - {{ $summary->to_date->format('d M Y') }}
                            </dd>

                            <dt class="col-7">Days:</dt>
                            <dd class="col-5 text-end">{{ $summary->from_date->diffInDays($summary->to_date) + 1 }}</dd>

                            <dt class="col-7">Business:</dt>
                            <dd class="col-5 text-end text-wrap">
                                {{ $summary->business?->trading_name ?? $summary->business_id }}
                            </dd>

                            <dt class="col-7">Total Income:</dt>
                            <dd class="col-5 text-end text-success">
                                £{{ number_format($summary->total_income, 2) }}
                            </dd>

                            <dt class="col-7">Total Expenses:</dt>
                            <dd class="col-5 text-end text-danger">
                                £{{ number_format($summary->total_expenses, 2) }}
                            </dd>

                            <dt class="col-7 border-top pt-2">Status:</dt>
                            <dd class="col-5 text-end border-top pt-2">
                                <span class="badge bg-{{ $summary->status_badge['class'] }}">
                                    {{ $summary->status_badge['text'] }}
                                </span>
                            </dd>

                            @if($summary->submission_date)
                                <dt class="col-7">Submitted:</dt>
                                <dd class="col-5 text-end">
                                    {{ $summary->submission_date->format('d M Y H:i') }}
                                </dd>
                            @endif

                            <dt class="col-7">Created:</dt>
                            <dd class="col-5 text-end">{{ $summary->created_at->format('d M Y') }}</dd>

                            <dt class="col-7">Updated:</dt>
                            <dd class="col-5 text-end">{{ $summary->updated_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($summary->canEdit())
                                <a href="{{ route('hmrc.uk-property-period-summaries.edit', $summary) }}"
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i> Edit Draft
                                </a>
                            @endif

                            @if($summary->canAmend())
                                <a href="{{ route('hmrc.uk-property-period-summaries.amend', $summary) }}"
                                   class="btn btn-warning">
                                    <i class="fas fa-pen me-2"></i> Amend Submission
                                </a>
                            @endif

                            @if($summary->canSubmit())
                                <form action="{{ route('hmrc.uk-property-period-summaries.submit', $summary) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to submit this to HMRC? This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane me-2"></i> Submit to HMRC
                                    </button>
                                </form>
                            @endif

                            @if($summary->canDelete())
                                <form action="{{ route('hmrc.uk-property-period-summaries.destroy', $summary) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this summary?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i> Delete
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('hmrc.uk-property-period-summaries.index') }}" class="btn btn-outline-secondary">
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
