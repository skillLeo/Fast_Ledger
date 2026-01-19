<div class="list-group-item obligation-card urgency-{{ $obligation->urgency_level }} mb-2">
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center
                        bg-{{ $obligation->status_badge['class'] }} bg-opacity-25">
                <i class="fas {{ $obligation->status_badge['icon'] }} text-{{ $obligation->status_badge['class'] }}"></i>
            </div>
        </div>
        
        <div class="col">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <h6 class="mb-1">
                        {{ $obligation->business?->trading_name ?? $obligation->business_id }}
                        <span class="badge bg-secondary ms-2">{{ $obligation->getObligationTypeLabel() }}</span>
                    </h6>
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $obligation->period_label }}
                    </p>
                </div>
                
                <div class="text-end">
                    <span class="badge bg-{{ $obligation->status_badge['class'] }}">
                        {{ $obligation->status_badge['text'] }}
                    </span>
                    @if($obligation->quarter)
                        <span class="badge bg-info ms-1">{{ $obligation->quarter }}</span>
                    @endif
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Due: <strong>{{ $obligation->due_date->format('d M Y') }}</strong>
                    </small>
                    
                    @if($obligation->status === 'open')
                        @if($obligation->is_overdue)
                            <span class="badge bg-danger ms-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                {{ abs($obligation->days_until_due) }} days overdue
                            </span>
                        @else
                            <span class="badge bg-{{ $obligation->days_until_due <= 7 ? 'warning' : 'info' }} ms-2">
                                {{ $obligation->days_until_due }} days remaining
                            </span>
                        @endif
                    @endif
                </div>
                
                <div>
                    @if($obligation->status === 'open' && $obligation->getDynamicSubmissionRoute())
                        <a href="{{ route($obligation->getDynamicSubmissionRoute(), ['obligation_id' => $obligation->id, 'business_id' => $obligation->business_id]) }}"
                           class="btn btn-sm btn-success me-2">
                            <i class="fas fa-paper-plane me-1"></i>
                            @if($obligation->obligation_type === 'periodic')
                                Submit
                            @else
                                Annual
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('hmrc.obligations.show', $obligation) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> View
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    
    .obligation-card {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .obligation-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .obligation-card.urgency-critical {
        border-left-color: #dc3545;
    }
    
    .obligation-card.urgency-urgent {
        border-left-color: #fd7e14;
    }
    
    .obligation-card.urgency-warning {
        border-left-color: #ffc107;
    }
    
    .obligation-card.urgency-attention {
        border-left-color: #0dcaf0;
    }
</style>

