<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">All Obligations</h5>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#filterForm">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    @include('hmrc.obligations.partials.filter-form')

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="obligationsTable">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Business</th>
                        <th>Period</th>
                        <th>Quarter</th>
                        <th>Tax Year</th>
                        <th>Due Date</th>
                        <th>Days Until Due</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($obligations as $obligation)
                        <tr class="obligation-row urgency-{{ $obligation->urgency_level }}">
                            <td>
                                <span class="badge bg-{{ $obligation->status_badge['class'] }}">
                                    <i class="fas {{ $obligation->status_badge['icon'] }} me-1"></i>
                                    {{ $obligation->status_badge['text'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $obligation->getObligationTypeLabel() }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $obligation->business?->trading_name ?? $obligation->business_id }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $obligation->getBusinessTypeLabel() }}</small>
                                </div>
                            </td>
                            <td>
                                <small>
                                    {{ $obligation->period_start_date->format('d M Y') }}
                                    <br>
                                    to {{ $obligation->period_end_date->format('d M Y') }}
                                </small>
                            </td>
                            <td>
                                @if($obligation->quarter)
                                    <span class="badge bg-info">{{ $obligation->quarter }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $obligation->tax_year ?? '-' }}</td>
                            <td>
                                <strong>{{ $obligation->due_date->format('d M Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $obligation->due_date->format('l') }}</small>
                            </td>
                            <td>
                                @if($obligation->status === 'fulfilled')
                                    <span class="text-success">
                                        <i class="fas fa-check-circle"></i> Complete
                                    </span>
                                @elseif($obligation->is_overdue)
                                    <span class="text-danger">
                                        <i class="fas fa-exclamation-circle"></i> 
                                        {{ abs($obligation->days_until_due) }} days overdue
                                    </span>
                                @else
                                    <span class="text-{{ $obligation->days_until_due <= 7 ? 'warning' : 'info' }}">
                                        {{ $obligation->days_until_due }} days
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($obligation->status === 'open' && $obligation->getDynamicSubmissionRoute())
                                        <a href="{{ route($obligation->getDynamicSubmissionRoute(), ['obligation_id' => $obligation->id, 'business_id' => $obligation->business_id]) }}"
                                           class="btn btn-sm btn-success"
                                           title="Create submission for this obligation">
                                            <i class="fas fa-paper-plane"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('hmrc.obligations.show', $obligation) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View obligation details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No obligations found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($obligations->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $obligations->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .obligation-row {
        border-left: 3px solid transparent;
    }
    .obligation-row.urgency-critical {
        border-left-color: #dc3545;
        background-color: rgba(220, 53, 69, 0.05);
    }
    .obligation-row.urgency-urgent {
        border-left-color: #fd7e14;
        background-color: rgba(253, 126, 20, 0.05);
    }
    .obligation-row.urgency-warning {
        border-left-color: #ffc107;
        background-color: rgba(255, 193, 7, 0.05);
    }
    .obligation-row.urgency-attention {
        border-left-color: #0dcaf0;
    }
</style>

<script>
    $(document).ready(function() {
        // Filter form submission
        $('#obligationsFilterForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            
            $.get('{{ route("hmrc.obligations.list") }}?' + formData, function(data) {
                $('#listViewContainer').html(data);
            });
        });

        // Reset filters
        $('#resetFilters').on('click', function() {
            $('#obligationsFilterForm')[0].reset();
            $('#obligationsFilterForm').submit();
        });
    });
</script>

