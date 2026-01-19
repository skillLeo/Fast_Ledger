@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.obligations.index') }}">HMRC</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.final-declaration.index', $taxYear) }}">Final Declaration {{ $taxYear }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Prerequisites Check</li>
            </ol>
        </nav>

        <!-- Include progress bar -->
        @include('hmrc.final-declaration.partials.progress-bar', ['declaration' => $declaration])

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Step 1: Prerequisites Check</h4>
            </div>
            <div class="card-body">
                <p class="lead">
                    Before you can submit your final declaration, we need to verify that all required submissions have been completed.
                </p>

                @if(isset($validation))
                    @if($validation['passed'])
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> All Prerequisites Met!</h5>
                            <p class="mb-0">You have completed all required submissions and can proceed with the final declaration.</p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Prerequisites Not Met</h5>
                            <p class="mb-0">Please complete the following before proceeding:</p>
                        </div>
                    @endif

                    <div class="prerequisites-checklist mt-4">
                        <!-- Quarterly Obligations -->
                        <div class="check-item mb-3 p-3 border rounded {{ $validation['checks']['quarterly_obligations']['passed'] ? 'border-success bg-light' : 'border-warning' }}">
                            <h5>
                                @if($validation['checks']['quarterly_obligations']['passed'])
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                                Quarterly Obligations
                            </h5>
                            <p>{{ $validation['checks']['quarterly_obligations']['message'] }}</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total:</strong> {{ $validation['checks']['quarterly_obligations']['total'] }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Fulfilled:</strong> <span class="text-success">{{ $validation['checks']['quarterly_obligations']['fulfilled'] }}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Open:</strong> <span class="text-danger">{{ $validation['checks']['quarterly_obligations']['open'] }}</span>
                                </div>
                            </div>
                            @if(!$validation['checks']['quarterly_obligations']['passed'] && count($validation['checks']['quarterly_obligations']['open_obligations']) > 0)
                                <div class="mt-3">
                                    <strong>Open Obligations:</strong>
                                    <ul class="mt-2">
                                        @foreach($validation['checks']['quarterly_obligations']['open_obligations'] as $obligation)
                                            <li>
                                                Period: {{ $obligation['period'] }} 
                                                (Due: {{ \Carbon\Carbon::parse($obligation['due_date'])->format('d M Y') }})
                                                <a href="{{ route('hmrc.submissions.create') }}" class="btn btn-sm btn-primary ms-2">
                                                    <i class="fas fa-plus"></i> Submit Now
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Annual Submissions -->
                        <div class="check-item mb-3 p-3 border rounded {{ $validation['checks']['annual_submissions']['passed'] ? 'border-success bg-light' : 'border-warning' }}">
                            <h5>
                                @if($validation['checks']['annual_submissions']['passed'])
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                                Annual Submissions
                            </h5>
                            <p>{{ $validation['checks']['annual_submissions']['message'] }}</p>
                            @if(!$validation['checks']['annual_submissions']['passed'] && count($validation['checks']['annual_submissions']['missing_annual']) > 0)
                                <div class="mt-2">
                                    <strong>Businesses Missing Annual Submission:</strong>
                                    <ul class="mt-2">
                                        @foreach($validation['checks']['annual_submissions']['missing_annual'] as $business)
                                            <li>
                                                {{ $business['trading_name'] ?? $business['business_id'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Tax Calculation -->
                        <div class="check-item mb-3 p-3 border rounded {{ $validation['checks']['tax_calculation']['passed'] ? 'border-success bg-light' : 'border-warning' }}">
                            <h5>
                                @if($validation['checks']['tax_calculation']['passed'])
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                                Tax Calculation
                            </h5>
                            <p>{{ $validation['checks']['tax_calculation']['message'] }}</p>
                            @if($validation['checks']['tax_calculation']['calculation'])
                                <div class="mt-2">
                                    <p class="mb-1"><strong>Calculation ID:</strong> <code>{{ $validation['checks']['tax_calculation']['calculation']['calculation_id'] }}</code></p>
                                    <p class="mb-1"><strong>Total Tax Due:</strong> £{{ number_format($validation['checks']['tax_calculation']['calculation']['total_tax_due'] ?? 0, 2) }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Crystallisation Obligation -->
                        <div class="check-item mb-3 p-3 border rounded {{ $validation['checks']['crystallisation_obligation']['passed'] ? 'border-success bg-light' : 'border-danger' }}">
                            <h5>
                                @if($validation['checks']['crystallisation_obligation']['passed'])
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                                Crystallisation Obligation
                            </h5>
                            <p>{{ $validation['checks']['crystallisation_obligation']['message'] }}</p>
                        </div>

                        <!-- Existing Crystallisation -->
                        <div class="check-item mb-3 p-3 border rounded {{ $validation['checks']['existing_crystallisation']['passed'] ? 'border-success bg-light' : 'border-danger' }}">
                            <h5>
                                @if($validation['checks']['existing_crystallisation']['passed'])
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                                Previous Final Declaration
                            </h5>
                            <p>{{ $validation['checks']['existing_crystallisation']['message'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('hmrc.obligations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Obligations
                        </a>
                        
                        @if($validation['passed'])
                            <button type="button" class="btn btn-success" onclick="completeStep('prerequisites_check')">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync"></i> Re-check Prerequisites
                            </button>
                        @endif
                    </div>
                @else
                    <div class="text-center py-5">
                        <button type="button" class="btn btn-primary btn-lg" onclick="location.reload()">
                            <i class="fas fa-check"></i> Check Prerequisites
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function completeStep(step) {
    fetch("{{ route('hmrc.final-declaration.complete-step', ['taxYear' => $taxYear, 'step' => 'prerequisites_check']) }}", {
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
            window.location.href = "{{ route('hmrc.final-declaration.review-submissions', $taxYear) }}";
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

