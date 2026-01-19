@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="hmrc-icon-wrapper">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1 page-title">Business Details</h4>
                    <p class="text-muted mb-0 small">{{ $business->trading_name ?? $business->business_id }}</p>
                </div>
            </div>
            <a href="{{ route('hmrc.businesses.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Businesses
            </a>
        </div>

        <!-- Status Badge -->
        <div class="mb-4">
            @if($business->is_active)
                <span class="badge bg-success-modern px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i> Active Business
                </span>
            @else
                <span class="badge bg-secondary px-3 py-2">
                    <i class="fas fa-times-circle me-1"></i> Ceased Business
                </span>
            @endif
        </div>

        <div class="row">
            <!-- Business Information Card -->
            <div class="col-lg-6 mb-4">
                <div class="card hmrc-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Business Information
                        </h5>

                        <div class="detail-item mb-3">
                            <div class="detail-label">
                                <i class="fas fa-id-card text-muted me-2"></i>Business ID
                            </div>
                            <div class="detail-value">
                                <code>{{ $business->business_id }}</code>
                            </div>
                        </div>

                        <div class="detail-item mb-3">
                            <div class="detail-label">
                                <i class="fas fa-store text-muted me-2"></i>Trading Name
                            </div>
                            <div class="detail-value">
                                {{ $business->trading_name ?? '—' }}
                            </div>
                        </div>

                        <div class="detail-item mb-3">
                            <div class="detail-label">
                                <i class="fas fa-briefcase text-muted me-2"></i>Business Type
                            </div>
                            <div class="detail-value">
                                <span class="badge bg-info-subtle text-info">
                                    {{ $business->type_of_business }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-item mb-3">
                            <div class="detail-label">
                                <i class="fas fa-calculator text-muted me-2"></i>Accounting Type
                            </div>
                            <div class="detail-value">
                                {{ $business->accounting_type ?? '—' }}
                            </div>
                        </div>

                        <div class="detail-item mb-0">
                            <div class="detail-label">
                                <i class="fas fa-calendar-day text-muted me-2"></i>Quarterly Period Type
                            </div>
                            <div class="detail-value">
                                {{ $business->quarterly_period_type ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates & Status Card -->
            <div class="col-lg-6 mb-4">
                <div class="card hmrc-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-calendar-alt text-success me-2"></i>
                            Important Dates
                        </h5>

                        <div class="detail-item mb-3">
                            <div class="detail-label">
                                <i class="fas fa-play-circle text-muted me-2"></i>Commencement Date
                            </div>
                            <div class="detail-value">
                                {{ optional($business->commencement_date)->format('d M Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="detail-item mb-3">
                            <div class="detail-label">
                                <i class="fas fa-stop-circle text-muted me-2"></i>Cessation Date
                            </div>
                            <div class="detail-value">
                                {{ optional($business->cessation_date)->format('d M Y') ?? '—' }}
                            </div>
                        </div>

                        <div class="detail-item mb-0">
                            <div class="detail-label">
                                <i class="fas fa-toggle-on text-muted me-2"></i>Status
                            </div>
                            <div class="detail-value">
                                @if($business->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Ceased</span>
                                @endif
                            </div>
                        </div>

                        @if(!$business->is_active)
                        <div class="alert alert-warning border-start border-4 border-warning mt-4 mb-0">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                <div>
                                    <strong class="d-block">Business Ceased</strong>
                                    <small class="text-muted">This business is no longer active.</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        @if($business->business_address_json)
        <div class="card hmrc-card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                    Business Address
                </h5>

                <div class="row">
                    @php
                        $address = is_string($business->business_address_json)
                            ? json_decode($business->business_address_json, true)
                            : $business->business_address_json;
                    @endphp

                    @if(is_array($address))
                        @foreach($address as $key => $value)
                            @if($value)
                            <div class="col-md-6 mb-3">
                                <div class="detail-item">
                                    <div class="detail-label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                    <div class="detail-value">{{ $value }}</div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <div class="col-12">
                            <pre class="bg-light p-3 border rounded mb-0"><code>{{ json_encode($business->business_address_json, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Accounting Periods Card -->
        @if($business->accounting_periods_json)
        <div class="card hmrc-card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-chart-line text-warning me-2"></i>
                    Accounting Periods
                </h5>

                @php
                    $periods = is_string($business->accounting_periods_json)
                        ? json_decode($business->accounting_periods_json, true)
                        : $business->accounting_periods_json;
                @endphp

                @if(is_array($periods) && count($periods) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($periods as $index => $period)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">Period {{ $index + 1 }}</span>
                                    </td>
                                    <td>{{ $period['start'] ?? '—' }}</td>
                                    <td>{{ $period['end'] ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <pre class="bg-light p-3 border rounded mb-0"><code>{{ json_encode($business->accounting_periods_json, JSON_PRETTY_PRINT) }}</code></pre>
                @endif
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="card hmrc-card mt-4">
            <div class="card-body">
                <h6 class="card-title mb-3">Quick Actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('hmrc.obligations.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-1"></i> View Obligations
                    </a>

                    <a href="{{ route('hmrc.calculations.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calculator me-1"></i> View Tax Calculation
                        </a>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>Additional features coming soon
                </small>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Page Header */
.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #f0f4f8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1d687d;
    font-size: 1.25rem;
}

/* HMRC Cards */
.hmrc-card {
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.2s ease;
}

.hmrc-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.hmrc-card .card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #2c3e50;
}

/* Badge Styling */
.bg-success-modern {
    background-color: #28a745;
    color: white;
}

.bg-info-subtle {
    background-color: #cfe2ff;
}

/* Detail Items */
.detail-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.detail-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #5a6c7d;
    margin-bottom: 0.5rem;
}

.detail-value {
    font-size: 0.95rem;
    color: #2c3e50;
    font-weight: 500;
}

/* Table Styling */
.table thead {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    color: #5a6c7d;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Gap utility */
.gap-2 {
    gap: 0.5rem !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.25rem;
    }

    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>
@endpush
@endsection
