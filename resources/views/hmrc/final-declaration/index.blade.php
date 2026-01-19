@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Final Declaration for {{ $taxYear }}</h1>
                <p class="text-muted mb-0">Complete your end-of-year tax submission</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body">
                <div class="progress mb-4" style="height: 30px;">
                    <div class="progress-bar bg-primary" 
                         role="progressbar" 
                         style="width: {{ $declaration->progress_percentage }}%"
                         aria-valuenow="{{ $declaration->progress_percentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ $declaration->progress_percentage }}% Complete
                    </div>
                </div>
                
                <div class="wizard-steps">
                    <div class="row text-center">
                        <div class="col step {{ $declaration->prerequisites_passed ? 'completed' : ($declaration->wizard_step === 'prerequisites_check' ? 'active' : '') }}">
                            <div class="step-number">1</div>
                            <div class="step-label">Prerequisites</div>
                        </div>
                        <div class="col step {{ $declaration->submissions_reviewed ? 'completed' : ($declaration->wizard_step === 'review_submissions' ? 'active' : '') }}">
                            <div class="step-number">2</div>
                            <div class="step-label">Review Submissions</div>
                        </div>
                        <div class="col step {{ $declaration->calculation_reviewed ? 'completed' : ($declaration->wizard_step === 'review_calculation' ? 'active' : '') }}">
                            <div class="step-number">3</div>
                            <div class="step-label">Review Calculation</div>
                        </div>
                        <div class="col step {{ $declaration->income_reviewed ? 'completed' : ($declaration->wizard_step === 'review_income' ? 'active' : '') }}">
                            <div class="step-number">4</div>
                            <div class="step-label">Review Income</div>
                        </div>
                        <div class="col step {{ $declaration->declaration_confirmed ? 'completed' : ($declaration->wizard_step === 'declaration' ? 'active' : '') }}">
                            <div class="step-number">5</div>
                            <div class="step-label">Declaration</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($declaration->wizard_step === 'completed')
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Final Declaration Submitted!</h4>
                <p>Your final declaration has been successfully submitted to HMRC.</p>
                <a href="{{ route('hmrc.final-declaration.confirmation', ['taxYear' => $taxYear, 'declaration' => $declaration->id]) }}" class="btn btn-primary">
                    View Confirmation
                </a>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .wizard-steps .step {
        position: relative;
    }
    
    .wizard-steps .step-number {
        width: 50px;
        height: 50px;
        line-height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        display: inline-block;
        font-weight: bold;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    
    .wizard-steps .step.active .step-number {
        background: #007bff;
        color: white;
    }
    
    .wizard-steps .step.completed .step-number {
        background: #28a745;
        color: white;
    }
    
    .wizard-steps .step.completed .step-number::after {
        content: "✓";
    }
    
    .wizard-steps .step-label {
        font-size: 14px;
        color: #6c757d;
    }
    
    .wizard-steps .step.active .step-label {
        color: #007bff;
        font-weight: 600;
    }
</style>
@endpush
@endsection

