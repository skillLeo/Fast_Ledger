@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg border-success rounded-3">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <br>
                            Final Declaration Submitted Successfully
                        </h2>
                    </div>
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <div class="success-icon mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 100px;"></i>
                            </div>
                            <h3>Your final declaration has been successfully submitted to HMRC</h3>
                            <p class="text-muted lead">Tax Year: <strong>{{ $taxYear }}</strong></p>
                        </div>

                        <!-- Submission Details -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-info-circle me-2"></i> Submission Details
                                </h5>
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width: 200px;">Submission Date:</td>
                                        <td>{{ $declaration->submitted_at->format('d F Y, H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Tax Year:</td>
                                        <td>{{ $taxYear }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Calculation ID:</td>
                                        <td>
                                            @if($declaration->calculation)
                                                <code>{{ $declaration->calculation->calculation_id }}</code>
                                            @else
                                                <span class="badge bg-warning">Processing...</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td><span class="badge bg-success fs-6">Submitted</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Declaration ID:</td>
                                        <td><code>#{{ $declaration->id }}</code></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- What Happens Next -->
                        <div class="alert alert-info mb-4">
                            <h5><i class="fas fa-info-circle me-2"></i> What Happens Next?</h5>
                            <ul class="mb-0">
                                <li>HMRC will process your final declaration</li>
                                <li>You will receive a final tax calculation</li>
                                <li>Payment details will be available in your HMRC online account</li>
                                @php
                                    [$startYear, $endYear] = explode('-', $taxYear);
                                    $paymentDeadline = "31 January " . (2000 + (int)$endYear + 1);
                                @endphp
                                <li><strong>Payment is due by {{ $paymentDeadline }}</strong></li>
                                <li>You can make amendments within 12 months if needed</li>
                            </ul>
                        </div>

                        <!-- Tax Calculation Summary -->
                        @if($declaration->calculation && $declaration->calculation->status === 'completed')
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i> Tax Calculation Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Total Income</h6>
                                        <h4 class="text-success">£{{ number_format($declaration->calculation->total_income_received ?? 0, 2) }}</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Taxable Income</h6>
                                        <h4 class="text-primary">£{{ number_format($declaration->calculation->total_taxable_income ?? 0, 2) }}</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-muted">Total Tax Due</h6>
                                        <h4 class="text-danger">£{{ number_format($declaration->calculation->income_tax_and_nics_due ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Important Reminders -->
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i> Important Reminders</h5>
                            <ul class="mb-0">
                                <li>Keep a copy of this confirmation for your records</li>
                                <li>Check your HMRC online account for your final calculation</li>
                                <li>Set up a payment plan if needed before the deadline</li>
                                <li>Save all supporting documents for at least 5 years</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-5">
                            <div class="btn-group-vertical gap-2" style="width: 100%; max-width: 400px;">
                                @if($declaration->calculation)
                                    <a href="{{ route('hmrc.calculations.index') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calculator me-2"></i> View Tax Calculation
                                    </a>
                                @endif
                                
                                <a href="{{ route('hmrc.obligations.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-home me-2"></i> Return to HMRC Dashboard
                                </a>
                                
                                <button onclick="window.print()" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-print me-2"></i> Print Confirmation
                                </button>
                            </div>
                        </div>

                        <!-- Audit Trail -->
                        <div class="mt-5 pt-4 border-top">
                            <h6 class="text-muted">Audit Information</h6>
                            <small class="text-muted">
                                <p class="mb-1"><strong>Submitted from IP:</strong> {{ $declaration->declaration_ip_address }}</p>
                                <p class="mb-1"><strong>User Agent:</strong> {{ Str::limit($declaration->declaration_user_agent, 100) }}</p>
                                <p class="mb-0"><strong>Confirmed at:</strong> {{ $declaration->declaration_confirmed_at->format('d M Y, H:i:s') }}</p>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, nav, .card-header, footer {
            display: none !important;
        }
        .card {
            border: 2px solid #28a745 !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush
@endsection

