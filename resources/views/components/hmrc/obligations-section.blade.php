{{-- Obligations Section Component --}}
@props(['obligations', 'title' => 'Related Obligations'])

@if($obligations->isNotEmpty())
<div class="card hmrc-card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-4 text-hmrc">
            <i class="fas fa-calendar-check me-2"></i>{{ $title }}
        </h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle hmrc-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Period Dates</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obligations as $obligation)
                    <tr class="{{ $obligation->is_overdue ? 'table-danger-subtle' : '' }}">
                        <td class="fw-semibold">{{ $obligation->getObligationTypeLabel() }}</td>
                        <td>
                            <code class="text-muted">{{ $obligation->period_key }}</code>
                            @if($obligation->quarter)
                                <span class="badge bg-secondary ms-1">{{ $obligation->quarter }}</span>
                            @endif
                        </td>
                        <td>
                            <small>
                                {{ optional($obligation->period_start_date)->format('d M Y') }} -
                                {{ optional($obligation->period_end_date)->format('d M Y') }}
                            </small>
                        </td>
                        <td>
                            <span class="{{ $obligation->is_overdue ? 'text-danger fw-bold' : '' }}">
                                {{ optional($obligation->due_date)->format('d M Y') }}
                            </span>
                            @if($obligation->is_overdue)
                                <br><small class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ abs($obligation->daysUntilDue()) }} days overdue
                                </small>
                            @elseif($obligation->status === 'open')
                                <br><small class="text-muted">
                                    in {{ $obligation->daysUntilDue() }} days
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $obligation->status_badge['class'] }}">
                                <i class="fas {{ $obligation->status_badge['icon'] }} me-1"></i>
                                {{ $obligation->status_badge['text'] }}
                            </span>
                        </td>
                        <td>
                            @if($obligation->status === 'open')
                                @if($obligation->getDynamicSubmissionRoute())
                                    <a href="{{ route($obligation->getDynamicSubmissionRoute(), ['obligation_id' => $obligation->id, 'business_id' => $obligation->business_id]) }}"
                                       class="btn btn-sm btn-success">
                                        <i class="fas fa-paper-plane me-1"></i> Submit
                                    </a>
                                @endif
                            @else
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i> Fulfilled
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
