@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="hmrc-icon-wrapper">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <h4 class="page-title mb-1">Tax Calculations</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">View and manage your Self Assessment tax calculations</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#syncModal">
                    <i class="fas fa-sync me-1"></i> Sync from HMRC
                </button>
                <a href="{{ route('hmrc.calculations.create') }}" class="btn btn-hmrc-primary">
                    <i class="fas fa-calculator me-1"></i> New Calculation
                </a>
                <a href="{{ route('hmrc.calculations.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Total Calculations -->
            <div class="stat-card stat-card-info">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Total Calculations</p>
                        <p class="stat-card-value text-info">{{ $stats['total'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-info-light">
                        <i class="fas fa-calculator text-info"></i>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="stat-card stat-card-success">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Completed</p>
                        <p class="stat-card-value text-success">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-success-light">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                </div>
            </div>

            <!-- Processing -->
            <div class="stat-card stat-card-warning">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Processing</p>
                        <p class="stat-card-value text-warning">{{ $stats['processing'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-warning-light">
                        <i class="fas fa-spinner text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Failed -->
            <div class="stat-card stat-card-danger">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Failed</p>
                        <p class="stat-card-value text-danger">{{ $stats['failed'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-danger-light">
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Tax Summary -->
        @if(isset($stats['latest_tax_due']))
        <div class="hmrc-card mb-4">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="border-end py-3">
                        <h3 class="mb-1 fw-bold text-hmrc">£{{ number_format($stats['latest_taxable_income'], 2) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Latest Taxable Income</p>
                        <small class="text-muted">Tax Year {{ $stats['latest_tax_year'] }}</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-end py-3">
                        <h3 class="mb-1 fw-bold text-danger">£{{ number_format($stats['latest_tax_due'], 2) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Tax & NICs Due</p>
                        <small class="text-muted">Latest Calculation</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="py-3">
                        <h3 class="mb-1 fw-bold text-success">{{ $stats['latest_tax_year'] }}</h3>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Latest Tax Year</p>
                        <small class="text-muted">Most Recent</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Calculations Table -->
        <div class="hmrc-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-hmrc mb-0 fw-semibold">All Calculations</h5>
                <div class="d-flex gap-2">
                    <!-- Tax Year Filter -->
                    <select class="form-select form-select-sm" id="tax-year-filter" onchange="filterCalculations()" style="width: 150px;">
                        <option value="">All Tax Years</option>
                        @foreach($taxYears as $year)
                            <option value="{{ $year }}" {{ request('tax_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <div class="filter-tabs">
                        <a href="{{ route('hmrc.calculations.index') }}"
                           class="filter-tab {{ !request('status') ? 'active' : '' }}">
                            All
                        </a>
                        <a href="{{ route('hmrc.calculations.index', ['status' => 'completed']) }}"
                           class="filter-tab {{ request('status') == 'completed' ? 'active' : '' }}">
                            Completed
                        </a>
                        <a href="{{ route('hmrc.calculations.index', ['status' => 'failed']) }}"
                           class="filter-tab {{ request('status') == 'failed' ? 'active' : '' }}">
                            Failed
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="hmrc-table">
                    <thead>
                        <tr>
                            <th>Tax Year</th>
                            <th>Type</th>
                            <th>Calculation Date</th>
                            <th>Taxable Income</th>
                            <th>Tax & NICs Due</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calculations as $calculation)
                            <tr>
                                <td>
                                    <strong>{{ $calculation->tax_year }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $calculation->nino }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $calculation->isCrystallisation() ? 'bg-primary' : 'bg-info' }}">
                                        {{ $calculation->type_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($calculation->calculation_timestamp)
                                        {{ $calculation->calculation_timestamp->format('d M Y') }}
                                        <br>
                                        <small class="text-muted">{{ $calculation->calculation_timestamp->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-hmrc fw-bold">
                                    @if($calculation->total_taxable_income)
                                        £{{ number_format($calculation->total_taxable_income, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-danger fw-bold">
                                    @if($calculation->income_tax_and_nics_due)
                                        £{{ number_format($calculation->income_tax_and_nics_due, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $calculation->status_badge['class'] }}">
                                        <i class="fas {{ $calculation->status_badge['icon'] }} me-1"></i>
                                        {{ $calculation->status_badge['text'] }}
                                    </span>
                                    @if($calculation->hasWarnings())
                                        <i class="fas fa-exclamation-triangle text-warning ms-1"
                                           data-bs-toggle="tooltip"
                                           title="Has warnings"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('hmrc.calculations.show', $calculation) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           data-bs-toggle="tooltip"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($calculation->status === 'processing')
                                            <form action="{{ route('hmrc.calculations.refresh', $calculation) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        data-bs-toggle="tooltip"
                                                        title="Refresh">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-calculator text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">No calculations found</p>
                                    <a href="{{ route('hmrc.calculations.create') }}" class="btn btn-hmrc-primary mt-3">
                                        <i class="fas fa-plus me-1"></i> Trigger Your First Calculation
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($calculations->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $calculations->links() }}
                </div>
            @endif
        </div>

        <!-- Sync Modal -->
        <div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('hmrc.calculations.sync') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="syncModalLabel">
                                <i class="fas fa-sync me-2"></i>
                                Sync Calculations from HMRC
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                This will fetch all calculations from HMRC for the specified tax year and sync them to your local database.
                            </div>

                            <div class="mb-3">
                                <label for="sync_nino" class="form-label required">National Insurance Number</label>
                                @if($businesses->isNotEmpty())
                                    <select name="nino" id="sync_nino" class="form-select" required>
                                        <option value="">Select from your businesses</option>
                                        @foreach($businesses as $business)
                                            @if($business->nino)
                                                <option value="{{ $business->nino }}">
                                                    {{ $business->nino }} - {{ $business->trading_name ?? $business->business_id }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Or enter manually below</small>
                                    <input type="text" name="nino_manual" id="sync_nino_manual"
                                           class="form-control mt-2"
                                           placeholder="AB123456C"
                                           pattern="^[A-Z]{2}[0-9]{6}[A-Z]$">
                                @else
                                    <input type="text" name="nino" id="sync_nino"
                                           class="form-control"
                                           placeholder="AB123456C"
                                           pattern="^[A-Z]{2}[0-9]{6}[A-Z]$"
                                           required>
                                    <small class="text-muted">Format: AB123456C</small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="sync_tax_year" class="form-label required">Tax Year</label>
                                <select name="tax_year" id="sync_tax_year" class="form-select" required>
                                    <option value="">Select tax year</option>
                                    @php
                                        $currentYear = now()->year;
                                        $currentMonth = now()->month;
                                        $taxYearStart = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
                                    @endphp
                                    @for($i = 0; $i < 5; $i++)
                                        @php
                                            $year = $taxYearStart - $i;
                                            $taxYear = "{$year}-" . substr($year + 1, 2, 2);
                                        @endphp
                                        <option value="{{ $taxYear }}">{{ $taxYear }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> This may take a few moments depending on how many calculations exist for this tax year.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sync me-1"></i> Sync Calculations
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* HMRC Page Header */
.hmrc-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #e8f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.hmrc-icon-wrapper i {
    color: #17848e;
    font-size: 1.5rem;
}

.page-title {
    color: #13667d;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

/* HMRC Button */
.btn-hmrc-primary {
    background-color: #17848e;
    border-color: #17848e;
    color: white;
}

.btn-hmrc-primary:hover {
    background-color: #13667d;
    border-color: #13667d;
    color: white;
}

.text-hmrc {
    color: #17848e !important;
}

/* HMRC Card */
.hmrc-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
}

/* Stat Cards */
.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border-left: 4px solid;
    transition: box-shadow 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-card-danger { border-left-color: #dc3545; }
.stat-card-warning { border-left-color: #ffc107; }
.stat-card-info { border-left-color: #0dcaf0; }
.stat-card-success { border-left-color: #28a745; }

.stat-card-body {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-card-content { flex: 1; }

.stat-card-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.stat-card-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.stat-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-danger-light { background-color: #f8d7da; }
.bg-warning-light { background-color: #fff3cd; }
.bg-info-light { background-color: #cff4fc; }
.bg-success-light { background-color: #d1e7dd; }

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.25rem;
    border-radius: 8px;
}

.filter-tab {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    color: #6c757d;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.filter-tab:hover {
    color: #17848e;
    background: white;
}

.filter-tab.active {
    background: #17848e;
    color: white;
}

/* HMRC Table */
.hmrc-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.hmrc-table thead th {
    background: #f8f9fa;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 1rem;
    border-bottom: 2px solid #e5e7eb;
    text-align: left;
}

.hmrc-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}

.hmrc-table tbody tr:hover {
    background-color: #f8f9fa;
}

.hmrc-table tbody tr:last-child td {
    border-bottom: none;
}

/* Grid utilities */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.gap-4 { gap: 1.5rem; }

@media (min-width: 768px) {
    .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

@media (max-width: 767px) {
    .hmrc-icon-wrapper { width: 40px; height: 40px; }
    .hmrc-icon-wrapper i { font-size: 1.25rem; }
    .page-title { font-size: 1.25rem; }
}

.required::after {
    content: ' *';
    color: #dc3545;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Show toast notifications
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // Handle manual NINO input in sync modal
        const ninoManual = document.getElementById('sync_nino_manual');
        const ninoSelect = document.getElementById('sync_nino');

        if (ninoManual && ninoSelect) {
            // When manual input is used, clear the select and use manual value
            ninoManual.addEventListener('input', function() {
                if (this.value) {
                    ninoSelect.value = '';
                    ninoSelect.removeAttribute('required');
                    this.setAttribute('required', 'required');
                } else {
                    ninoSelect.setAttribute('required', 'required');
                    this.removeAttribute('required');
                }
            });

            // When select is used, clear manual input
            ninoSelect.addEventListener('change', function() {
                if (this.value) {
                    ninoManual.value = '';
                    ninoManual.removeAttribute('required');
                    this.setAttribute('required', 'required');
                }
            });

            // Before form submission, use manual input if filled
            document.querySelector('#syncModal form').addEventListener('submit', function(e) {
                if (ninoManual && ninoManual.value) {
                    ninoSelect.value = ninoManual.value;
                }
            });
        }
    });

    function filterCalculations() {
        const taxYear = document.getElementById('tax-year-filter').value;
        const url = new URL(window.location.href);

        if (taxYear) {
            url.searchParams.set('tax_year', taxYear);
        } else {
            url.searchParams.delete('tax_year');
        }

        window.location.href = url.toString();
    }
</script>
@endpush
