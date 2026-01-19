@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.obligations.index') }}">HMRC</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.final-declaration.index', $taxYear) }}">Final Declaration {{ $taxYear }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Review Calculation</li>
            </ol>
        </nav>

        <!-- Include progress bar -->
        @include('hmrc.final-declaration.partials.progress-bar', ['declaration' => $declaration])

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-calculator me-2"></i> Step 3: Review Tax Calculation</h4>
            </div>
            <div class="card-body">
                <p class="lead">
                    Review your tax calculation for the tax year {{ $taxYear }}.
                </p>

                <!-- Calculation Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Income</h6>
                                <h4 class="mb-0 text-info">£{{ number_format($calculation->total_income_received ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-secondary">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Allowances</h6>
                                <h4 class="mb-0 text-secondary">£{{ number_format($calculation->total_allowances_and_deductions ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Taxable Income</h6>
                                <h4 class="mb-0 text-success">£{{ number_format($calculation->total_taxable_income ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Tax Due</h6>
                                <h4 class="mb-0 text-danger">£{{ number_format($calculation->income_tax_and_nics_due ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculation Details -->
                <div class="accordion mb-4" id="calculationAccordion">
                    <!-- Summary -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSummary">
                                <i class="fas fa-list me-2"></i> Summary
                            </button>
                        </h2>
                        <div id="collapseSummary" class="accordion-collapse collapse show" data-bs-parent="#calculationAccordion">
                            <div class="accordion-body">
                                @if(isset($breakdown['summary']))
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total Income Received</td>
                                            <td class="text-end">£{{ number_format($breakdown['summary']['total_income_received'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Allowances Deducted</td>
                                            <td class="text-end">£{{ number_format($breakdown['summary']['total_allowances_deducted'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Taxable Income</td>
                                            <td class="text-end fw-bold">£{{ number_format($breakdown['summary']['total_taxable_income'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Income Tax Charged</strong></td>
                                            <td class="text-end fw-bold">£{{ number_format($breakdown['summary']['income_tax_charged'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>Total Income Tax and NICs Due</strong></td>
                                            <td class="text-end fw-bold">£{{ number_format($breakdown['summary']['total_income_tax_and_nics_due'] ?? 0, 2) }}</td>
                                        </tr>
                                    </table>
                                @else
                                    <p class="text-muted">No summary data available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tax Bands -->
                    @if(isset($breakdown['tax']['bands']))
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTaxBands">
                                <i class="fas fa-layer-group me-2"></i> Tax Bands
                            </button>
                        </h2>
                        <div id="collapseTaxBands" class="accordion-collapse collapse" data-bs-parent="#calculationAccordion">
                            <div class="accordion-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Band</th>
                                            <th class="text-end">Income</th>
                                            <th class="text-end">Rate</th>
                                            <th class="text-end">Tax</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($breakdown['tax']['bands'] as $band)
                                            <tr>
                                                <td>{{ $band['name'] ?? 'N/A' }}</td>
                                                <td class="text-end">£{{ number_format($band['income'] ?? 0, 2) }}</td>
                                                <td class="text-end">{{ $band['rate'] ?? 0 }}%</td>
                                                <td class="text-end">£{{ number_format($band['tax'] ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- NICs -->
                    @if(isset($breakdown['nics']))
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNICs">
                                <i class="fas fa-shield-alt me-2"></i> National Insurance Contributions
                            </button>
                        </h2>
                        <div id="collapseNICs" class="accordion-collapse collapse" data-bs-parent="#calculationAccordion">
                            <div class="accordion-body">
                                @if(isset($breakdown['nics']['class2']))
                                    <h6>Class 2 NICs</h6>
                                    <p>Amount: £{{ number_format($breakdown['nics']['class2']['amount'] ?? 0, 2) }}</p>
                                @endif
                                @if(isset($breakdown['nics']['class4']))
                                    <h6>Class 4 NICs</h6>
                                    <p>Amount: £{{ number_format($breakdown['nics']['class4']['amount'] ?? 0, 2) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Calculation Metadata -->
                <div class="alert alert-light">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Calculation ID:</strong> <code>{{ $calculation->calculation_id }}</code></p>
                            <p class="mb-0"><strong>Calculation Date:</strong> {{ $calculation->calculation_timestamp ? \Carbon\Carbon::parse($calculation->calculation_timestamp)->format('d M Y H:i') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Type:</strong> {{ ucfirst($calculation->type ?? 'forecast') }}</p>
                            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">{{ ucfirst($calculation->status) }}</span></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('hmrc.final-declaration.review-submissions', $taxYear) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Previous Step
                    </a>
                    
                    <button type="button" class="btn btn-success" onclick="completeStep('review_calculation')">
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
    fetch("{{ route('hmrc.final-declaration.complete-step', ['taxYear' => $taxYear, 'step' => 'review_calculation']) }}", {
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
            window.location.href = "{{ route('hmrc.final-declaration.review-income', $taxYear) }}";
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

