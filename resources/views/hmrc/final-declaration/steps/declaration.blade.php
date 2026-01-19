@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.obligations.index') }}">HMRC</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hmrc.final-declaration.index', $taxYear) }}">Final Declaration {{ $taxYear }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Declaration</li>
            </ol>
        </nav>

        <!-- Include progress bar -->
        @include('hmrc.final-declaration.partials.progress-bar', ['declaration' => $declaration])

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-gavel me-2"></i> Step 5: Final Declaration</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i> Important Information</h5>
                    <p class="mb-0">
                        By submitting this final declaration, you are confirming that the information you have provided is correct and complete to the best of your knowledge.
                    </p>
                </div>

                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Tax Year:</strong> {{ $taxYear }}</p>
                            @php
                                [$startYear, $endYear] = explode('-', $taxYear);
                                $deadline = "31 January " . (2000 + (int)$endYear + 1);
                            @endphp
                            <p class="mb-0"><strong>Submission Deadline:</strong> {{ $deadline }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Declaration Type:</strong> Final Declaration (Crystallisation)</p>
                            <p class="mb-0"><strong>Status:</strong> Ready for Submission</p>
                        </div>
                    </div>
                </div>

                <!-- Declaration Statement -->
                <div class="declaration-box border border-primary p-4 mb-4 bg-light">
                    <h5 class="text-primary mb-3">Declaration Statement</h5>
                    <p class="text-justify">
                        I declare that the information I have provided in my quarterly updates, annual submission, and additional income for the tax year <strong>{{ $taxYear }}</strong> is correct and complete to the best of my knowledge and belief.
                    </p>
                    
                    <h6 class="mt-4 mb-3">I understand that:</h6>
                    <ul>
                        <li>I may be liable to financial penalties if I provide false information</li>
                        <li>This declaration finalizes my tax position for the year</li>
                        <li>I can make amendments within 12 months of the filing deadline if needed</li>
                        <li>HMRC may conduct compliance checks on the information provided</li>
                        <li>This action cannot be undone once submitted</li>
                        <li>Payment of any tax due must be made by {{ $deadline }}</li>
                    </ul>
                </div>

                <!-- Confirmation Form -->
                <form id="declaration-form">
                    @csrf
                    <div class="card mb-4">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0"><i class="fas fa-check-square me-2"></i> Required Confirmations</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="declaration_confirmation" name="declaration_confirmation" required>
                                <label class="form-check-label fw-bold" for="declaration_confirmation">
                                    I confirm that the information I have provided is correct and complete to the best of my knowledge
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="accuracy_confirmation" name="accuracy_confirmation" required>
                                <label class="form-check-label fw-bold" for="accuracy_confirmation">
                                    I understand that I may be liable to penalties if I provide false information
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="reviewed_confirmation" name="reviewed_confirmation" required>
                                <label class="form-check-label fw-bold" for="reviewed_confirmation">
                                    I have reviewed all my submissions and calculations
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="finality_confirmation" name="finality_confirmation" required>
                                <label class="form-check-label fw-bold" for="finality_confirmation">
                                    I understand that this action cannot be undone and will finalize my tax year
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-circle me-2"></i> Final Warning</h6>
                        <p class="mb-0">
                            <strong>This action cannot be undone.</strong> Once submitted, your tax year will be finalized. 
                            Please ensure you have reviewed all information carefully.
                        </p>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('hmrc.final-declaration.review-income', $taxYear) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Previous Step
                        </a>
                        
                        <button type="submit" class="btn btn-danger btn-lg" id="submit-declaration-btn">
                            <i class="fas fa-paper-plane"></i> Submit Final Declaration to HMRC
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('declaration-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const allChecked = document.querySelectorAll('input[type="checkbox"]:checked').length === 4;
    
    if (!allChecked) {
        alert('Please confirm all declarations before submitting.');
        return;
    }
    
    // Show SweetAlert confirmation
    Swal.fire({
        title: 'Submit Final Declaration?',
        html: '<p>Are you absolutely sure you want to submit your final declaration to HMRC?</p>' +
              '<p class="text-danger fw-bold">This action cannot be undone.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Submit to HMRC',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger btn-lg',
            cancelButton: 'btn btn-secondary btn-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitDeclaration();
        }
    });
});

function submitDeclaration() {
    const submitBtn = document.getElementById('submit-declaration-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting to HMRC...';
    
    // First, confirm declaration
    fetch("{{ route('hmrc.final-declaration.confirm', $taxYear) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            declaration_confirmation: true,
            accuracy_confirmation: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show progress message
            Swal.fire({
                title: 'Submitting...',
                html: 'Please wait while we submit your final declaration to HMRC.<br><strong>Do not close this window.</strong>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Now submit to HMRC
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('hmrc.final-declaration.submit', $taxYear) }}";
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        } else {
            throw new Error(data.message || 'Failed to confirm declaration');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Final Declaration to HMRC';
        
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: 'An error occurred while submitting your declaration. Please try again.',
        });
    });
}
</script>
@endpush
@endsection

