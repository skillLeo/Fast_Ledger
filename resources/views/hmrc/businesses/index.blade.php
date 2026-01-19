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
                    <h4 class="mb-1 page-title">Your Businesses</h4>
                    <p class="text-muted mb-0 small">Manage your HMRC registered businesses</p>
                </div>
            </div>
            <a href="{{ route('hmrc.auth.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Connection
            </a>
        </div>

        <!-- Sync Business Card -->
        <div class="card hmrc-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="d-flex align-items-start">
                            <div class="hmrc-card-icon me-3">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Sync Businesses from HMRC</h5>
                                <p class="text-muted small mb-0">
                                    Fetch your latest business information from HMRC using your National Insurance Number
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <form id="sync-form" method="POST" action="{{ route('hmrc.businesses.sync') }}">
                            @csrf
                            <div class="mb-2">
                                <input type="text"
                                       name="nino"
                                       placeholder="Enter NINO (e.g., AA123456A)"
                                       class="form-control"
                                       required>
                            </div>
                            @if(config('hmrc.environment') === 'sandbox')
                            <div class="mb-2">
                                <label for="test_scenario" class="form-label">
                                    <i class="fas fa-vial me-1"></i>Gov-Test-Scenario
                                </label>
                                <select class="form-select" id="test_scenario" name="test_scenario">
                                    <option value="">DEFAULT - Standard success response</option>
                                    <option value="PROPERTY">PROPERTY - Simulate a successful response with a uk-property business.</option>
                                    <option value="FOREIGN_PROPERTY">FOREIGN_PROPERTY - Simulate a successful response with a foreign-property business.</option>
                                    <option value="BUSINESS_AND_PROPERTY">BUSINESS_AND_PROPERTY - Simulate a successful response with a self-employment, uk-property and foreign-property business.</option>
                                    <option value="UNSPECIFIED">UNSPECIFIED - Simulate a successful response with a property-unspecified business.</option>
                                    <option value="NOT_FOUND">NOT_FOUND - Simulates the scenario where no data is found.</option>
                                    <option value="STATEFUL">STATEFUL - Performs a stateful list.</option>

                                </select>
                                <small class="text-muted">Enter PROPERTY for property businesses in sandbox</small>
                            </div>
                            @endif
                            <button type="submit" class="btn btn-hmrc-primary w-100" id="sync-businesses">
                                <i class="fas fa-sync-alt me-1"></i> Sync from HMRC
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Businesses Table Card -->
        <div class="card hmrc-card">
            <div class="card-body">
                <h5 class="card-title mb-4">Registered Businesses</h5>

                @if($businesses->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-briefcase fa-3x text-muted opacity-50"></i>
                        </div>
                        <h6 class="text-muted">No businesses found</h6>
                        <p class="text-muted small mb-0">Sync from HMRC to fetch your registered businesses</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="businesses-table" class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Business ID</th>
                                    <th>Type</th>
                                    <th>Trading Name</th>
                                    <th>Accounting Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($businesses as $biz)
                                    <tr>
                                        <td><code class="text-muted">{{ $biz->business_id }}</code></td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $biz->type_of_business }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">{{ $biz->trading_name ?? '—' }}</td>
                                        <td>{{ $biz->accounting_type ?? '—' }}</td>
                                        <td>
                                            @if($biz->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>Ceased
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('hmrc.businesses.show', $biz) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
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

/* Card Icon */
.hmrc-card-icon {
    width: 40px;
    height: 40px;
    background: #e8f4f8;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #17a2b8;
    font-size: 1.125rem;
    flex-shrink: 0;
}

/* HMRC Primary Button */
.btn-hmrc-primary {
    background-color: #17848e;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.btn-hmrc-primary:hover {
    background-color: #136770;
    color: white;
}

.btn-hmrc-primary:active,
.btn-hmrc-primary:focus {
    background-color: #136770;
    color: white;
    box-shadow: none;
}

/* Badge Styling */
.bg-info-subtle {
    background-color: #cfe2ff;
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

@push('scripts')
<script>
// Sync form submission
document.getElementById('sync-form')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('sync-businesses');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Syncing...';
});
</script>
@endpush
@endsection
