@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hmrc.uk-property-annual-submissions.index') }}">UK Property Annual Submissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $submission->tax_year }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">UK Property Annual Submission - {{ $submission->tax_year }}</h1>
                <p class="text-muted mb-0">{{ $submission->business?->trading_name ?? $submission->business_id }}</p>
            </div>
            <div>
                <span class="badge bg-{{ $submission->status_badge['class'] }} fs-6">
                    <i class="fas {{ $submission->status_badge['icon'] }} me-1"></i>
                    {{ $submission->status_badge['text'] }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Info Alert: Income & Expenses in Period Summaries -->
                <div class="alert alert-info shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                        <div>
                            <h5 class="alert-heading mb-2">Annual Submission Data</h5>
                            <p class="mb-0">
                                This annual submission contains <strong>adjustments and allowances only</strong>.
                                Income and expenses are submitted through periodic/quarterly summaries.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Adjustments -->
                @if($submission->adjustments_json && !empty(array_filter($submission->adjustments_json)))
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-balance-scale me-2"></i>
                                Adjustments
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                // Check if data has nested structure (ukFhlProperty, ukProperty)
                                $hasNestedStructure = isset($submission->adjustments_json['ukProperty']) || isset($submission->adjustments_json['ukFhlProperty']);
                            @endphp

                            @if($hasNestedStructure)
                                {{-- Display nested structure for TY 2024-25+ --}}
                                @if(isset($submission->adjustments_json['ukFhlProperty']))
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-umbrella-beach me-2"></i>
                                        FHL (Furnished Holiday Lettings) Property
                                    </h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm">
                                            @foreach($submission->adjustments_json['ukFhlProperty'] as $key => $value)
                                                @if($value !== null && $value !== '')
                                                    @include('hmrc.uk-property-annual-submissions.partials.adjustment-row', ['key' => $key, 'value' => $value])
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @endif

                                @if(isset($submission->adjustments_json['ukProperty']))
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-building me-2"></i>
                                        {{ isset($submission->adjustments_json['ukFhlProperty']) ? 'Non-FHL Property' : 'UK Property' }}
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            @foreach($submission->adjustments_json['ukProperty'] as $key => $value)
                                                @if($value !== null && $value !== '')
                                                    @include('hmrc.uk-property-annual-submissions.partials.adjustment-row', ['key' => $key, 'value' => $value])
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @endif
                            @else
                                {{-- Display flat structure for older tax years --}}
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        @foreach($submission->adjustments_json as $key => $value)
                                            @if($value !== null && $value !== '')
                                                @include('hmrc.uk-property-annual-submissions.partials.adjustment-row', ['key' => $key, 'value' => $value])
                                            @endif
                                        @endforeach
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Allowances -->
                @if($submission->allowances_json && !empty(array_filter($submission->allowances_json)))
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Capital Allowances
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                // Check if data has nested structure (ukFhlProperty, ukProperty)
                                $hasNestedStructure = isset($submission->allowances_json['ukProperty']) || isset($submission->allowances_json['ukFhlProperty']);
                            @endphp

                            @if($hasNestedStructure)
                                {{-- Display nested structure for TY 2024-25+ --}}
                                @if(isset($submission->allowances_json['ukFhlProperty']))
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-umbrella-beach me-2"></i>
                                        FHL (Furnished Holiday Lettings) Property
                                    </h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm">
                                            @foreach($submission->allowances_json['ukFhlProperty'] as $key => $value)
                                                @if($value)
                                                    @include('hmrc.uk-property-annual-submissions.partials.allowance-row', ['key' => $key, 'value' => $value])
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @endif

                                @if(isset($submission->allowances_json['ukProperty']))
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-building me-2"></i>
                                        {{ isset($submission->allowances_json['ukFhlProperty']) ? 'Non-FHL Property' : 'UK Property' }}
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            @foreach($submission->allowances_json['ukProperty'] as $key => $value)
                                                @if($value)
                                                    @include('hmrc.uk-property-annual-submissions.partials.allowance-row', ['key' => $key, 'value' => $value])
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                @endif
                            @else
                                {{-- Display flat structure for older tax years --}}
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        @foreach($submission->allowances_json as $key => $value)
                                            @if($value)
                                                @include('hmrc.uk-property-annual-submissions.partials.allowance-row', ['key' => $key, 'value' => $value])
                                            @endif
                                        @endforeach
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if($submission->notes)
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $submission->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- HMRC Response -->
                @if($submission->response_json && $submission->status === 'submitted')
                    <div class="card shadow-sm border-success rounded-3 mb-4">
                        <div class="card-header bg-success bg-opacity-10">
                            <h5 class="card-title mb-0 text-success">
                                <i class="fas fa-check-circle me-2"></i>
                                HMRC Response
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code>{{ json_encode($submission->response_json, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif

                @if($submission->response_json && $submission->status === 'failed')
                    <div class="card shadow-sm border-danger rounded-3 mb-4">
                        <div class="card-header bg-danger bg-opacity-10">
                            <h5 class="card-title mb-0 text-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                Error Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code>{{ json_encode($submission->response_json, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif

                <!-- Amendment Window Closed Alert -->
                @if($submission->isOutsideAmendmentWindow())
                    <div class="alert alert-warning shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                            <div>
                                <h5 class="alert-heading mb-2">Amendment Window Closed</h5>
                                <p class="mb-2">
                                    The amendment window for this tax year has closed. You can no longer amend this submission through HMRC's API.
                                </p>
                                <hr>
                                <p class="mb-0 small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    HMRC only allows amendments within a specific time period. If you need to make changes to this submission, you may need to contact HMRC directly or wait for the next submission period.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="card shadow-sm border-primary rounded-3 mb-4">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-calculator me-2"></i>
                            Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-7">Tax Year:</dt>
                            <dd class="col-5 text-end">{{ $submission->tax_year }}</dd>

                            <dt class="col-7">Business:</dt>
                            <dd class="col-5 text-end text-wrap">
                                {{ $submission->business?->trading_name ?? $submission->business_id }}
                            </dd>

                            <dt class="col-7">Total Allowances:</dt>
                            <dd class="col-5 text-end text-success">
                                £{{ number_format($submission->total_allowances, 2) }}
                            </dd>

                            <dt class="col-7 border-top pt-2">Status:</dt>
                            <dd class="col-5 text-end border-top pt-2">
                                <span class="badge bg-{{ $submission->status_badge['class'] }}">
                                    {{ $submission->status_badge['text'] }}
                                </span>
                            </dd>

                            @if($submission->submission_date)
                                <dt class="col-7">Submitted:</dt>
                                <dd class="col-5 text-end">
                                    {{ $submission->submission_date->format('d M Y H:i') }}
                                </dd>
                            @endif

                            <dt class="col-7">Created:</dt>
                            <dd class="col-5 text-end">{{ $submission->created_at->format('d M Y') }}</dd>

                            <dt class="col-7">Updated:</dt>
                            <dd class="col-5 text-end">{{ $submission->updated_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($submission->canEdit())
                                <a href="{{ route('hmrc.uk-property-annual-submissions.edit', $submission) }}"
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i> Edit Draft
                                </a>
                            @endif

                            @if($submission->canAmend())
                                <a href="{{ route('hmrc.uk-property-annual-submissions.edit', $submission) }}"
                                   class="btn btn-warning">
                                    <i class="fas fa-pen me-2"></i> Amend Submission
                                </a>
                            @endif

                            @if($submission->canSubmit())
                                @php
                                    $isAmendment = $submission->status === 'submitted';
                                    $confirmMessage = $isAmendment
                                        ? 'Are you sure you want to amend this submission at HMRC? The existing submission will be replaced with the amended data.'
                                        : 'Are you sure you want to submit this to HMRC? This action cannot be undone.';
                                    $buttonText = $isAmendment ? 'Resubmit Amendment' : 'Submit to HMRC';
                                    $buttonClass = $isAmendment ? 'btn-warning' : 'btn-success';
                                @endphp

                                <!-- Preview Payload Button -->
                                <button type="button"
                                        class="btn btn-info w-100 mb-2"
                                        onclick="previewPayload()">
                                    <i class="fas fa-eye me-2"></i> Preview HMRC Payload
                                </button>

                                <form action="{{ route('hmrc.uk-property-annual-submissions.submit', $submission) }}"
                                      method="POST"
                                      onsubmit="return confirm('{{ $confirmMessage }}');">
                                    @csrf
                                    <button type="submit" class="btn {{ $buttonClass }} w-100">
                                        <i class="fas fa-paper-plane me-2"></i> {{ $buttonText }}
                                    </button>
                                </form>
                            @endif

                            @if($submission->canDelete())
                                <form action="{{ route('hmrc.uk-property-annual-submissions.destroy', $submission) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this submission?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i> Delete
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('hmrc.uk-property-annual-submissions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payload Preview Modal -->
<div class="modal fade" id="payloadPreviewModal" tabindex="-1" aria-labelledby="payloadPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payloadPreviewModalLabel">
                    <i class="fas fa-code me-2"></i> HMRC API Payload Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="payloadPreviewContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading payload preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyPayloadToClipboard()">
                    <i class="fas fa-copy me-2"></i> Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let payloadData = null;

function previewPayload() {
    const modal = new bootstrap.Modal(document.getElementById('payloadPreviewModal'));
    modal.show();

    // Reset content
    document.getElementById('payloadPreviewContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading payload preview...</p>
        </div>
    `;

    // Fetch payload preview
    fetch('{{ route("hmrc.uk-property-annual-submissions.preview-payload", $submission) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                payloadData = data.preview;
                displayPayloadPreview(data.preview);
            } else {
                showPayloadError(data.message || 'Failed to load payload preview');
            }
        })
        .catch(error => {
            console.error('Error loading payload:', error);
            showPayloadError('Network error: ' + error.message);
        });
}

function displayPayloadPreview(preview) {
    const isAmendment = preview.is_amendment;
    const amendmentBadge = isAmendment
        ? '<span class="badge bg-warning text-dark ms-2">Amendment</span>'
        : '<span class="badge bg-success ms-2">New Submission</span>';

    const content = `
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>This is what will be sent to HMRC</strong>
            ${amendmentBadge}
            <p class="mb-0 mt-2 small">
                Review the payload below to ensure all information is correct before submitting.
            </p>
        </div>

        <h6 class="mb-3"><i class="fas fa-network-wired me-2"></i>API Endpoint Details</h6>
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Method:</strong></div>
                    <div class="col-md-9"><code class="text-warning">${preview.method}</code></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>Endpoint:</strong></div>
                    <div class="col-md-9"><code class="text-info">${preview.endpoint}</code></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>Tax Year:</strong></div>
                    <div class="col-md-9"><strong>${preview.tax_year}</strong></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>NINO:</strong></div>
                    <div class="col-md-9"><strong>${preview.nino}</strong></div>
                </div>
            </div>
        </div>

        <h6 class="mb-3"><i class="fas fa-file-code me-2"></i>Request Headers</h6>
        <pre class="bg-dark text-light p-3 rounded mb-4"><code>${JSON.stringify(preview.headers, null, 2)}</code></pre>

        <h6 class="mb-3"><i class="fas fa-file-alt me-2"></i>Request Payload</h6>
        <pre id="payloadJsonContent" class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(preview.payload, null, 2)}</code></pre>

        <div class="alert alert-warning mt-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Once submitted, this data will be sent to HMRC. ${isAmendment ? 'This will replace the existing submission.' : 'Make sure all information is accurate.'}
        </div>
    `;

    document.getElementById('payloadPreviewContent').innerHTML = content;
}

function showPayloadError(message) {
    document.getElementById('payloadPreviewContent').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error:</strong> ${message}
        </div>
    `;
}

function copyPayloadToClipboard() {
    if (payloadData) {
        const payloadText = JSON.stringify(payloadData.payload, null, 2);
        navigator.clipboard.writeText(payloadText).then(() => {
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-2"></i> Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy to clipboard: ' + err);
        });
    }
}
</script>
@endpush

@endsection
