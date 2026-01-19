@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.obligations.index') }}">HMRC</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.final-declaration.index', $taxYear) }}">Final Declaration {{ $taxYear }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Review Income Sources</li>
            </ol>
        </nav>

        <!-- Include progress bar -->
        @include('hmrc.final-declaration.partials.progress-bar', ['declaration' => $declaration])

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-briefcase me-2"></i> Step 4: Review Income Sources</h4>
            </div>
            <div class="card-body">
                <p class="lead">
                    Review all your income sources for the tax year {{ $taxYear }}.
                </p>

                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i> Important</h6>
                    <p class="mb-0">
                        You must declare all sources of income, including self-employment, employment, property, dividends, and interest.
                        Ensure you have submitted all required information before proceeding.
                    </p>
                </div>

                <!-- Business Income Sources -->
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-building me-2"></i> Self-Employment Income</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Business</th>
                                    <th>Type</th>
                                    <th class="text-end">Total Income</th>
                                    <th class="text-end">Total Expenses</th>
                                    <th class="text-end">Net Profit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($businesses as $business)
                                    @php
                                        $businessSubmissions = $summary['periodic_submissions']->where('business_id', $business->business_id);
                                        $businessIncome = $businessSubmissions->sum('total_income');
                                        $businessExpenses = $businessSubmissions->sum('total_expenses');
                                        $businessProfit = $businessIncome - $businessExpenses;
                                    @endphp
                                    <tr>
                                        <td>{{ $business->trading_name ?? $business->business_id }}</td>
                                        <td>{{ ucfirst(str_replace('-', ' ', $business->type_of_business)) }}</td>
                                        <td class="text-end text-success">£{{ number_format($businessIncome, 2) }}</td>
                                        <td class="text-end text-danger">£{{ number_format($businessExpenses, 2) }}</td>
                                        <td class="text-end fw-bold">£{{ number_format($businessProfit, 2) }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Declared
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No business income sources found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Other Income Sources -->
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-coins me-2"></i> Other Income Sources</h5>
                    <div class="alert alert-warning">
                        <p class="mb-2">
                            <strong>Have you declared all other income sources?</strong>
                        </p>
                        <ul class="mb-0">
                            <li>Employment income (PAYE)</li>
                            <li>UK pensions and state benefits</li>
                            <li>Dividends from UK companies</li>
                            <li>Interest from UK banks and building societies</li>
                            <li>Property income</li>
                            <li>Foreign income</li>
                            <li>Capital gains</li>
                        </ul>
                    </div>
                    <p>
                        If you have other income sources that are not shown above, make sure you have declared them to HMRC 
                        through the appropriate channels before finalizing your declaration.
                    </p>
                </div>

                <!-- Confirmation Checkboxes -->
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Income Declaration Confirmation</h6>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="confirm_business_income" required>
                            <label class="form-check-label" for="confirm_business_income">
                                I confirm that all my self-employment income has been submitted
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="confirm_other_income" required>
                            <label class="form-check-label" for="confirm_other_income">
                                I confirm that I have declared all other income sources to HMRC
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="confirm_accuracy" required>
                            <label class="form-check-label" for="confirm_accuracy">
                                I confirm that all information is accurate to the best of my knowledge
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('hmrc.final-declaration.review-calculation', $taxYear) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Previous Step
                    </a>
                    
                    <button type="button" class="btn btn-success" id="continueBtn" onclick="completeStep('review_income')" disabled>
                        Continue to Declaration <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Enable continue button only when all checkboxes are checked
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.form-check-input[required]');
    const continueBtn = document.getElementById('continueBtn');
    
    function updateButtonState() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        continueBtn.disabled = !allChecked;
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateButtonState);
    });
});

function completeStep(step) {
    fetch("{{ route('hmrc.final-declaration.complete-step', ['taxYear' => $taxYear, 'step' => 'review_income']) }}", {
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
            window.location.href = "{{ route('hmrc.final-declaration.declaration', $taxYear) }}";
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

