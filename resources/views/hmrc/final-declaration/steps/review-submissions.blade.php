@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.obligations.index') }}">HMRC</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.final-declaration.index', $taxYear) }}">Final Declaration {{ $taxYear }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Review Submissions</li>
            </ol>
        </nav>

        <!-- Include progress bar -->
        @include('hmrc.final-declaration.partials.progress-bar', ['declaration' => $declaration])

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i> Step 2: Review Your Submissions</h4>
            </div>
            <div class="card-body">
                <p class="lead">
                    Please review all your quarterly and annual submissions for the tax year {{ $taxYear }}.
                </p>

                <!-- Financial Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Income</h6>
                                <h3 class="mb-0 text-success">£{{ number_format($summary['totals']['income'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Expenses</h6>
                                <h3 class="mb-0 text-danger">£{{ number_format($summary['totals']['expenses'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Net Profit/Loss</h6>
                                <h3 class="mb-0 {{ $summary['totals']['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                    £{{ number_format($summary['totals']['net_profit'], 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Periodic Submissions -->
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Quarterly Submissions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Business</th>
                                    <th class="text-end">Income</th>
                                    <th class="text-end">Expenses</th>
                                    <th class="text-end">Net</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['periodic_submissions'] as $submission)
                                    <tr>
                                        <td>
                                            {{ $submission->period_start_date->format('d M Y') }}
                                            <br>
                                            <small class="text-muted">to {{ $submission->period_end_date->format('d M Y') }}</small>
                                        </td>
                                        <td>{{ $submission->business?->trading_name ?? $submission->business_id }}</td>
                                        <td class="text-end text-success">£{{ number_format($submission->total_income, 2) }}</td>
                                        <td class="text-end text-danger">£{{ number_format($submission->total_expenses, 2) }}</td>
                                        <td class="text-end fw-bold">£{{ number_format($submission->net_profit, 2) }}</td>
                                        <td>
                                            <span class="badge bg-success">Submitted</span>
                                        </td>
                                        <td>{{ $submission->submission_date ? $submission->submission_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No periodic submissions found for this tax year
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Annual Submissions -->
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-file-invoice me-2"></i> Annual Submissions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Business</th>
                                    <th>Tax Year</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['annual_submissions'] as $submission)
                                    <tr>
                                        <td>{{ $submission->business?->trading_name ?? $submission->business_id }}</td>
                                        <td>{{ $submission->tax_year }}</td>
                                        <td>Annual Adjustments</td>
                                        <td>
                                            <span class="badge bg-success">Submitted</span>
                                        </td>
                                        <td>{{ $submission->submission_date ? $submission->submission_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No annual submissions found for this tax year
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Confirmation -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i> Review Confirmation</h6>
                    <p class="mb-0">
                        By continuing, you confirm that you have reviewed all your submissions and they are accurate.
                    </p>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('hmrc.final-declaration.prerequisites-check', $taxYear) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Previous Step
                    </a>
                    
                    <button type="button" class="btn btn-success" onclick="completeStep('review_submissions')">
                        I Have Reviewed <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function completeStep(step) {
    fetch("{{ route('hmrc.final-declaration.complete-step', ['taxYear' => $taxYear, 'step' => 'review_submissions']) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ step: step })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = "{{ route('hmrc.final-declaration.review-calculation', $taxYear) }}";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush
@endsection

