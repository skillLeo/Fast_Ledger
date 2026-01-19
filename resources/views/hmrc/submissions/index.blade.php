@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="hmrc-icon-wrapper">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h4 class="page-title mb-1">Periodic Submissions</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Manage your quarterly income and expense submissions</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('hmrc.submissions.create') }}" class="btn btn-hmrc-primary">
                    <i class="fas fa-plus me-1"></i> New Submission
                </a>
                <a href="{{ route('hmrc.submissions.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Total Submissions -->
            <div class="stat-card stat-card-info">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Total Submissions</p>
                        <p class="stat-card-value text-info">{{ $stats['total'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-info-light">
                        <i class="fas fa-file-invoice text-info"></i>
                    </div>
                </div>
            </div>

            <!-- Submitted -->
            <div class="stat-card stat-card-success">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Submitted</p>
                        <p class="stat-card-value text-success">{{ $stats['submitted'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-success-light">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                </div>
            </div>

            <!-- Drafts -->
            <div class="stat-card stat-card-secondary">
                <div class="stat-card-body">
                    <div class="stat-card-content">
                        <p class="stat-card-label">Drafts</p>
                        <p class="stat-card-value text-secondary">{{ $stats['draft'] }}</p>
                    </div>
                    <div class="stat-card-icon bg-secondary-light">
                        <i class="fas fa-file text-secondary"></i>
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

        <!-- Financial Summary -->
        <div class="hmrc-card mb-4">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="border-end py-3">
                        <h3 class="mb-1 fw-bold text-success">£{{ number_format($stats['total_income'], 2) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Total Income</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-end py-3">
                        <h3 class="mb-1 fw-bold text-danger">£{{ number_format($stats['total_expenses'], 2) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Total Expenses</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="py-3">
                        <h3 class="mb-1 fw-bold {{ $stats['net_profit'] >= 0 ? 'text-hmrc' : 'text-danger' }}">
                            £{{ number_format($stats['net_profit'], 2) }}
                        </h3>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Net Profit/Loss</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Obligations Section -->
        <x-hmrc.obligations-section
            :obligations="$obligations"
            title="Periodic Obligations (Self-Employment)"
        />

        <!-- Submissions Table -->
        <div class="hmrc-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-hmrc mb-0 fw-semibold">All Submissions</h5>
                <div class="filter-tabs">
                    <a href="{{ route('hmrc.submissions.index') }}"
                       class="filter-tab {{ !request('status') ? 'active' : '' }}">
                        All
                    </a>
                    <a href="{{ route('hmrc.submissions.index', ['status' => 'draft']) }}"
                       class="filter-tab {{ request('status') == 'draft' ? 'active' : '' }}">
                        Drafts
                    </a>
                    <a href="{{ route('hmrc.submissions.index', ['status' => 'submitted']) }}"
                       class="filter-tab {{ request('status') == 'submitted' ? 'active' : '' }}">
                        Submitted
                    </a>
                    <a href="{{ route('hmrc.submissions.index', ['status' => 'failed']) }}"
                       class="filter-tab {{ request('status') == 'failed' ? 'active' : '' }}">
                        Failed
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="hmrc-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Business</th>
                            <th>Tax Year</th>
                            <th>Income</th>
                            <th>Expenses</th>
                            <th>Net Profit/Loss</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $submission->period_start_date->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">to {{ $submission->period_end_date->format('d M Y') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $submission->business?->trading_name ?? $submission->business_id }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $submission->business?->type_of_business ?? '' }}</small>
                                    </div>
                                </td>
                                <td>{{ $submission->tax_year }}</td>
                                <td class="text-success fw-bold">
                                    £{{ number_format($submission->total_income, 2) }}
                                </td>
                                <td class="text-danger fw-bold">
                                    £{{ number_format($submission->total_expenses, 2) }}
                                </td>
                                <td class="fw-bold {{ $submission->net_profit >= 0 ? 'text-hmrc' : 'text-danger' }}">
                                    £{{ number_format($submission->net_profit, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $submission->status_badge['class'] }}">
                                        <i class="fas {{ $submission->status_badge['icon'] }} me-1"></i>
                                        {{ $submission->status_badge['text'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($submission->submission_date)
                                        {{ $submission->submission_date->format('d M Y') }}
                                        <br>
                                        <small class="text-muted">{{ $submission->submission_date->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('hmrc.submissions.show', $submission) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           data-bs-toggle="tooltip"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($submission->canEdit())
                                            <a href="{{ route('hmrc.submissions.edit', $submission) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               data-bs-toggle="tooltip"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">No submissions found</p>
                                    <a href="{{ route('hmrc.submissions.create') }}" class="btn btn-hmrc-primary mt-3">
                                        <i class="fas fa-plus me-1"></i> Create Your First Submission
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($submissions->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $submissions->links() }}
                </div>
            @endif
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

/* Stat Cards Styling */
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

.stat-card-danger {
    border-left-color: #dc3545;
}

.stat-card-warning {
    border-left-color: #ffc107;
}

.stat-card-info {
    border-left-color: #0dcaf0;
}

.stat-card-success {
    border-left-color: #28a745;
}

.stat-card-secondary {
    border-left-color: #6c757d;
}

.stat-card-body {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-card-content {
    flex: 1;
}

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

.bg-danger-light {
    background-color: #f8d7da;
}

.bg-warning-light {
    background-color: #fff3cd;
}

.bg-info-light {
    background-color: #cff4fc;
}

.bg-success-light {
    background-color: #d1e7dd;
}

.bg-secondary-light {
    background-color: #e2e3e5;
}

.text-secondary {
    color: #6c757d;
}

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
.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.gap-4 {
    gap: 1.5rem;
}

/* Responsive */
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 767px) {
    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
    }

    .hmrc-icon-wrapper i {
        font-size: 1.25rem;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .filter-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
    }
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
    });
</script>
@endpush
