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
            <div class="row text-center g-2">
                <div class="col step {{ $declaration->prerequisites_passed ? 'completed' : ($declaration->wizard_step === 'prerequisites_check' ? 'active' : '') }}">
                    <div class="step-number">1</div>
                    <div class="step-label">Prerequisites</div>
                </div>
                <div class="col step {{ $declaration->submissions_reviewed ? 'completed' : ($declaration->wizard_step === 'review_submissions' ? 'active' : '') }}">
                    <div class="step-number">2</div>
                    <div class="step-label">Submissions</div>
                </div>
                <div class="col step {{ $declaration->calculation_reviewed ? 'completed' : ($declaration->wizard_step === 'review_calculation' ? 'active' : '') }}">
                    <div class="step-number">3</div>
                    <div class="step-label">Calculation</div>
                </div>
                <div class="col step {{ $declaration->income_reviewed ? 'completed' : ($declaration->wizard_step === 'review_income' ? 'active' : '') }}">
                    <div class="step-number">4</div>
                    <div class="step-label">Income</div>
                </div>
                <div class="col step {{ $declaration->declaration_confirmed ? 'completed' : ($declaration->wizard_step === 'declaration' ? 'active' : '') }}">
                    <div class="step-number">5</div>
                    <div class="step-label">Declaration</div>
                </div>
            </div>
        </div>
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
        font-size: 18px;
    }
    
    .wizard-steps .step.active .step-number {
        background: #007bff;
        color: white;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
    }
    
    .wizard-steps .step.completed .step-number {
        background: #28a745;
        color: white;
    }
    
    .wizard-steps .step.completed .step-number::after {
        content: "✓";
        font-size: 24px;
    }
    
    .wizard-steps .step-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
    }
    
    .wizard-steps .step.active .step-label {
        color: #007bff;
        font-weight: 700;
    }
    
    .wizard-steps .step.completed .step-label {
        color: #28a745;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .wizard-steps .step-number {
            width: 40px;
            height: 40px;
            line-height: 40px;
            font-size: 14px;
        }
        
        .wizard-steps .step-label {
            font-size: 11px;
        }
    }
</style>
@endpush

