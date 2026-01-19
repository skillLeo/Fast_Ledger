@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('hmrc.obligations.index') }}">Obligations</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $obligation->period_label }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Details Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Obligation Details
                    </h5>
                    <span class="badge bg-{{ $obligation->status_badge['class'] }} px-3 py-2">
                        <i class="fas {{ $obligation->status_badge['icon'] }} me-1"></i>
                        {{ $obligation->status_badge['text'] }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Obligation Type -->
                        <div class="col-md-6">
                            <label class="form-label text-muted">Obligation Type</label>
                            <p class="fw-bold">
                                <span class="badge bg-secondary">{{ $obligation->getObligationTypeLabel() }}</span>
                            </p>
                        </div>

                        <!-- Business Type -->
                        <div class="col-md-6">
                            <label class="form-label text-muted">Business Type</label>
                            <p class="fw-bold">{{ $obligation->getBusinessTypeLabel() }}</p>
                        </div>

                        <!-- Business Information -->
                        <div class="col-12">
                            <label class="form-label text-muted">Business</label>
                            <p class="fw-bold mb-0">
                                {{ $obligation->business?->trading_name ?? $obligation->business_id }}
                            </p>
                            @if($obligation->business)
                                <small class="text-muted">ID: {{ $obligation->business_id }}</small>
                            @endif
                        </div>

                        <div class="col-12"><hr></div>

                        <!-- Period Details -->
                        <div class="col-md-4">
                            <label class="form-label text-muted">Period Start</label>
                            <p class="fw-bold">{{ $obligation->period_start_date->format('d M Y') }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted">Period End</label>
                            <p class="fw-bold">{{ $obligation->period_end_date->format('d M Y') }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted">Duration</label>
                            <p class="fw-bold">
                                {{ $obligation->period_start_date->diffInDays($obligation->period_end_date) }} days
                            </p>
                        </div>

                        <!-- Quarter & Tax Year -->
                        @if($obligation->quarter)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Quarter</label>
                            <p class="fw-bold">
                                <span class="badge bg-info">{{ $obligation->quarter }}</span>
                            </p>
                        </div>
                        @endif

                        @if($obligation->tax_year)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tax Year</label>
                            <p class="fw-bold">{{ $obligation->tax_year }}</p>
                        </div>
                        @endif

                        <div class="col-12"><hr></div>

                        <!-- Due Date -->
                        <div class="col-md-6">
                            <label class="form-label text-muted">Due Date</label>
                            <p class="fw-bold text-{{ $obligation->is_overdue ? 'danger' : 'primary' }}">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ $obligation->due_date->format('l, d F Y') }}
                            </p>
                        </div>

                        <!-- Status Info -->
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            @if($obligation->status === 'fulfilled')
                                <p class="fw-bold text-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Fulfilled on {{ $obligation->received_date?->format('d M Y') }}
                                </p>
                            @elseif($obligation->is_overdue)
                                <p class="fw-bold text-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ abs($obligation->days_until_due) }} days overdue
                                </p>
                            @else
                                <p class="fw-bold text-info">
                                    <i class="fas fa-clock me-2"></i>
                                    {{ $obligation->days_until_due }} days remaining
                                </p>
                            @endif
                        </div>

                        <!-- Urgency Level -->
                        <div class="col-12">
                            <label class="form-label text-muted">Urgency Level</label>
                            <p>
                                @php
                                    $urgencyColors = [
                                        'critical' => 'danger',
                                        'urgent' => 'warning',
                                        'warning' => 'warning',
                                        'attention' => 'info',
                                        'normal' => 'secondary',
                                        'completed' => 'success'
                                    ];
                                    $urgencyColor = $urgencyColors[$obligation->urgency_level] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $urgencyColor }} px-3 py-2">
                                    {{ strtoupper($obligation->urgency_level) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            @if($obligation->status === 'open' && $obligation->getDynamicSubmissionRoute())
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        This obligation is ready for submission. Click below to create and submit your
                        @if($obligation->obligation_type === 'periodic')
                            periodic update
                        @else
                            annual submission
                        @endif
                        for {{ $obligation->getBusinessTypeLabel() }}.
                    </p>
                    <a href="{{ route($obligation->getDynamicSubmissionRoute(), ['obligation_id' => $obligation->id, 'business_id' => $obligation->business_id]) }}"
                       class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>
                        @if($obligation->obligation_type === 'periodic')
                            Create Periodic Submission
                        @else
                            Create Annual Submission
                        @endif
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if($obligation->received_date)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <p class="mb-1"><strong>Fulfilled</strong></p>
                                    <small class="text-muted">{{ $obligation->received_date->format('d M Y, H:i') }}</small>
                                </div>
                            </div>
                        @endif

                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $obligation->is_overdue ? 'danger' : 'primary' }}"></div>
                            <div class="timeline-content">
                                <p class="mb-1"><strong>Due Date</strong></p>
                                <small class="text-muted">{{ $obligation->due_date->format('d M Y') }}</small>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <p class="mb-1"><strong>Period End</strong></p>
                                <small class="text-muted">{{ $obligation->period_end_date->format('d M Y') }}</small>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <p class="mb-1"><strong>Period Start</strong></p>
                                <small class="text-muted">{{ $obligation->period_start_date->format('d M Y') }}</small>
                            </div>
                        </div>

                        @if($obligation->last_synced_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-light"></div>
                                <div class="timeline-content">
                                    <p class="mb-1"><strong>Last Synced</strong></p>
                                    <small class="text-muted">{{ $obligation->last_synced_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Additional Information
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Created</dt>
                        <dd class="col-sm-6">{{ $obligation->created_at->format('d M Y') }}</dd>

                        <dt class="col-sm-6">Updated</dt>
                        <dd class="col-sm-6">{{ $obligation->updated_at->diffForHumans() }}</dd>

                        @if($obligation->submission_id)
                            <dt class="col-sm-6">Submission ID</dt>
                            <dd class="col-sm-6"><code>{{ $obligation->submission_id }}</code></dd>
                        @endif

                        <dt class="col-sm-6">Period Key</dt>
                        <dd class="col-sm-6"><code>{{ $obligation->period_key }}</code></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }
    
    .timeline-content {
        padding-left: 10px;
    }
</style>
@endpush

