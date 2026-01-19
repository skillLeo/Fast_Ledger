@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="hmrc-icon-wrapper">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1 page-title">Tax Obligations</h4>
                    <p class="text-muted mb-0 small">Track and manage your tax obligations</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Sync Button -->
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#syncModal">
                    <i class="fas fa-sync-alt me-1"></i> Sync from HMRC
                </button>

                <!-- View Switcher -->
                <div class="view-switcher">
                    <button class="view-btn active" data-view="overview">
                        <i class="fas fa-th-large me-1"></i> Overview
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list me-1"></i> List
                    </button>
                    <button class="view-btn" data-view="calendar">
                        <i class="fas fa-calendar-alt me-1"></i> Calendar
                    </button>
                </div>
            </div>
        </div>

        @if(!$hasConnection)
            <div class="alert alert-warning border-start border-4 border-warning mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                    <div>
                        <strong class="d-block">No HMRC Connection</strong>
                        <small class="text-muted">
                            Please <a href="{{ route('hmrc.auth.index') }}" class="alert-link">connect to HMRC</a> to view your obligations.
                        </small>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        @include('components.hmrc.stat-cards', ['stats' => $stats])

        <!-- View Containers -->
        <div class="view-content">
            <!-- Overview View -->
            <div class="view-panel active" id="overview-view">
                @if($overdueObligations->isEmpty() && $upcomingObligations->isEmpty())
                    <!-- All Done Message -->
                    <div class="card hmrc-card mb-4">
                        <div class="card-body text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-success mb-2">All obligations are fulfilled!</h5>
                            <p class="text-muted mb-0">You're all caught up with your tax obligations.</p>
                        </div>
                    </div>
                @else
                    <!-- Overdue Obligations (if any) -->
                    @if($overdueObligations->isNotEmpty())
                    <div class="card hmrc-card mb-4 border-danger">
                        <div class="card-body">
                            <h5 class="card-title mb-4 text-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>Overdue Obligations ({{ $overdueObligations->count() }})
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle hmrc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Period</th>
                                            <th>Period Start</th>
                                            <th>Period End</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($overdueObligations as $obligation)
                                        <tr class="table-danger-subtle">
                                            <td class="fw-semibold">{{ $obligation->getObligationTypeLabel() }}</td>
                                            <td><code class="text-muted">{{ $obligation->period_key }}</code></td>
                                            <td>{{ optional($obligation->start_date)->format('d M Y') }}</td>
                                            <td>{{ optional($obligation->end_date)->format('d M Y') }}</td>
                                            <td>{{ optional($obligation->due_date)->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge bg-danger">Overdue</span>
                                            </td>
                                            <td>
                                                @if($obligation->getDynamicSubmissionRoute())
                                                    <a href="{{ route($obligation->getDynamicSubmissionRoute(), ['obligation_id' => $obligation->id, 'business_id' => $obligation->business_id]) }}"
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-paper-plane me-1"></i> Submit
                                                    </a>
                                                @else
                                                    <a href="{{ route('hmrc.obligations.show', $obligation) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
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

                    <!-- Upcoming Obligations -->
                    @if($upcomingObligations->isNotEmpty())
                    <div class="card hmrc-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4 text-hmrc">Upcoming Obligations</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle hmrc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Period</th>
                                            <th>Period Start</th>
                                            <th>Period End</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingObligations as $obligation)
                                        <tr>
                                            <td class="fw-semibold">{{ $obligation->getObligationTypeLabel() }}</td>
                                            <td><code class="text-muted">{{ $obligation->period_key }}</code></td>
                                            <td>{{ optional($obligation->period_start_date)->format('d M Y') }}</td>
                                            <td>{{ optional($obligation->period_end_date)->format('d M Y') }}</td>
                                            <td>{{ optional($obligation->due_date)->format('d M Y') }}</td>
                                            <td>
                                                @if($obligation->status === 'F')
                                                    <span class="badge bg-success">Fulfilled</span>
                                                @else
                                                    <span class="badge bg-warning">Due</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($obligation->status === 'F')
                                                    <a href="{{ route('hmrc.obligations.show', $obligation) }}"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                @else
                                                    @if($obligation->getDynamicSubmissionRoute())
                                                        <a href="{{ route($obligation->getDynamicSubmissionRoute(), ['obligation_id' => $obligation->id, 'business_id' => $obligation->business_id]) }}"
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-paper-plane me-1"></i> Submit
                                                        </a>
                                                    @else
                                                        <a href="{{ route('hmrc.obligations.show', $obligation) }}"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </a>
                                                    @endif
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
                @endif
            </div>

            <!-- List View -->
            <div class="view-panel" id="list-view">
                <div id="listViewContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div class="view-panel" id="calendar-view">
                <div id="calendarViewContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="syncModalLabel">
                    <i class="fas fa-sync-alt me-2"></i>Sync Obligations from HMRC
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="syncForm">
                    @csrf

                    <div class="alert alert-info border-start border-4 border-info mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle me-2 mt-1"></i>
                            <div>
                                <strong class="d-block">About Syncing</strong>
                                <small class="text-muted">This will fetch your tax obligations from HMRC for the specified date range.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="{{ now()->format('Y-m-d') }}">
                            <small class="text-muted">Default: Today</small>
                        </div>
                        <div class="col-md-6">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="{{ now()->addYear()->format('Y-m-d') }}">
                            <small class="text-muted">Default: One year from today</small>
                        </div>
                    </div>
                    @if(config('hmrc.environment') === 'sandbox' )
                    <div class="row mb-3">
                        <div class="col w-100">
                            <label for="business_id" class="form-label"> Custom business id for sandbox</label>
                            <input type="text" class="form-control" id="business_id" name="business_id" value="">
                            <small class="text-muted">Custom business ID for testing in sandbox HMRC</small>
                        </div>
                    </div>

                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning bg-opacity-10">
                            <i class="fas fa-flask me-2"></i>
                            <strong>Sandbox Mode - Test Scenarios</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="gov_test_scenario" class="form-label">
                                    <i class="fas fa-vial me-1"></i>Gov-Test-Scenario
                                </label>
                                <select class="form-select" id="gov_test_scenario" name="gov_test_scenario">
                                    <option value="">DEFAULT - Standard success response</option>
                                    <option value="OPEN">OPEN - Returns open obligations</option>
                                    <option value="FULFILLED">FULFILLED - Returns fulfilled obligations</option>
                                    <option value="NOT_FOUND">NOT_FOUND - No data found</option>
                                    <option value="NO_OBLIGATIONS_FOUND">NO_OBLIGATIONS_FOUND - No obligations</option>
                                    <option value="DYNAMIC">DYNAMIC - Custom dates and status</option>
                                </select>
                                <small class="text-muted">Select a test scenario for sandbox testing</small>
                            </div>

                            <div id="scenarioInfo" class="alert alert-light small mb-0" style="display: none;">
                                <!-- Scenario descriptions will be shown here -->
                            </div>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitSync">
                    <i class="fas fa-sync-alt me-1"></i>Sync Obligations
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
<style>
/* Page Header */
.hmrc-page-header {
    background: white;
    border-bottom: 1px solid #e3e6ea;
    padding: 1rem 1.5rem;
    margin: -1rem -1.5rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #f0f4f8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #17848e;
    font-size: 1.25rem;
}

/* View Switcher */
.view-switcher {
    display: flex;
    gap: 0.5rem;
}

.view-btn {
    background: white;
    border: 1px solid #e3e6ea;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #5a6c7d;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn:hover {
    border-color: #17848e;
    color: #17848e;
}

.view-btn.active {
    background-color: #17848e;
    border-color: #17848e;
    color: white;
}

/* View Panels */
.view-content {
    position: relative;
}

.view-panel {
    display: none;
}

.view-panel.active {
    display: block;
}

/* HMRC Cards */
.hmrc-card {
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.2s ease;
}

.hmrc-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.hmrc-card .card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #2c3e50;
}

.text-hmrc {
    color: #17848e !important;
}

/* HMRC Table */
.hmrc-table thead {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.875rem;
    color: #5a6c7d;
}

.hmrc-table tbody tr {
    transition: background-color 0.2s ease;
}

.hmrc-table tbody tr:hover {
    background-color: #f8f9fa;
}

.table-danger-subtle {
    background-color: #f8d7da !important;
}

/* Gap utility */
.gap-2 {
    gap: 0.5rem !important;
}

/* Responsive */
@media (max-width: 768px) {
    .hmrc-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .hmrc-page-header .d-flex.gap-2 {
        flex-direction: column;
        width: 100%;
    }

    .view-switcher {
        width: 100%;
        justify-content: stretch;
    }

    .view-btn {
        flex: 1;
        text-align: center;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
<script>
$(document).ready(function() {
    // View Switcher
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');

        // Update active button
        $('.view-btn').removeClass('active');
        $(this).addClass('active');

        // Update active panel
        $('.view-panel').removeClass('active');
        $(`#${view}-view`).addClass('active');

        // Load content if needed
        if (view === 'list' && !$('#listViewContainer').hasClass('loaded')) {
            $.get('{{ route("hmrc.obligations.list") }}', function(data) {
                $('#listViewContainer').html(data).addClass('loaded');
            });
        }

        if (view === 'calendar' && !$('#calendarViewContainer').hasClass('loaded')) {
            $.get('{{ route("hmrc.obligations.calendar") }}', function(data) {
                $('#calendarViewContainer').html(data).addClass('loaded');
                initCalendar();
            });
        }
    });

    // Test scenario selection
    $('#gov_test_scenario').on('change', function() {
        const scenario = $(this).val();
        const infoBox = $('#scenarioInfo');

        if (!scenario) {
            infoBox.hide();
            return;
        }

        let message = '';
        switch(scenario) {
            case 'OPEN':
                message = '<strong>OPEN Scenario:</strong> Returns obligations with "open" status. Use business IDs ending in 903.';
                break;
            case 'FULFILLED':
                message = '<strong>FULFILLED Scenario:</strong> Returns obligations with "fulfilled" status. Use business IDs ending in 902.';
                break;
            case 'NOT_FOUND':
                message = '<strong>NOT_FOUND:</strong> Simulates no data found scenario.';
                break;
            case 'NO_OBLIGATIONS_FOUND':
                message = '<strong>NO_OBLIGATIONS_FOUND:</strong> Returns empty obligations list.';
                break;
            case 'DYNAMIC':
                message = '<strong>DYNAMIC Scenario:</strong> Custom dates and status. Use business IDs ending in 901.';
                break;
        }

        if (message) {
            infoBox.html(message).show();
        }
    });

    // Sync form submission
    $('#submitSync').on('click', function() {
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Syncing...');

        const formData = {
            _token: $('input[name="_token"]').val(),
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            gov_test_scenario: $('#gov_test_scenario').val()
        };

        $.ajax({
            url: '{{ route("hmrc.obligations.sync") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#syncModal').modal('hide');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to sync obligations';
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: message,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    function initCalendar() {
        const calendarEl = document.getElementById('obligationsCalendar');
        if (!calendarEl) return;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: {
                url: '{{ route("hmrc.obligations.calendar") }}',
                method: 'GET',
                failure: function() {
                    alert('Failed to load obligations');
                }
            }
        });

        calendar.render();
    }
});
</script>
@endpush
